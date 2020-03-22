<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\StoreComment;
use App\Http\Requests\StorePhoto;
use App\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function __construct()
    {
        //認証が必要
        $this->middleware('auth')->except(['index', 'download', 'show']);
    }

    /**
     * 写真一覧
     */
    public function index()
    {
        $photos = Photo::with(['owner'])
            ->orderBy(Photo::CREATED_AT, 'desc')->paginate();

        return $photos;
    }

    /**
     * 写真投稿
     * @param StorePhoto $request
     * @return \Illuminate\Http\Response
     */
    public function create(StorePhoto $request)
    {
        //投稿写真の拡張子を取得する
        $extension = $request->photo->extension();

        $photo=new Photo();

        //インスタンス生成時に割り振られたランダムなID値と
        //本来の拡張子を組み合わせてファイル名とする
        $photo->filename = $photo->id . '.' . $extension;

        //S3にファイルを保存する
        //第3引数の'public'はファイルを公開状態で保存する為
        Storage::cloud()
            ->putFileAs('', $request->photo, $photo->filename, 'public');

        //データベースエラー時にファイル削除を行う為
        //トランザクションを利用する
        DB::beginTransaction();

        try{
            Auth::user()->photos()->save($photo);
            DB::commit();
        }catch(\Exception $exception){
            DB::rollBack();
            //DBとの不整合を避ける為アップロードしたファイルを削除
            Storage::cloud()->delete($photo->filename);
            throw $exception;
        }

        //リソースの新規作成なので
        //レスポンスコードは201(CREATED)を返却する
        return response($photo, 201);

    }

    /**
     * 写真ダウンロード
     * @param Photo $photo
     * @return \Illuminate\Http\Response
     */
    public function download(Photo $photo)
    {
        //写真の存在チェック
        if (! Storage::cloud()->exists($photo->filename)){
            abort(404);
        }

        $disposition = 'attachment; filename="' . $photo->filename . '"';
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => $disposition,
        ];

        return response(Storage::cloud()->get($photo->filename), 200, $headers);
    }

    /**
     * 写真詳細
     * @param string $id
     * @return Photo
     */
    public function show(string $id)
    {
        //指定idの写真とコメントを取得
        $photo = Photo::where('id', $id)->with(['owner', 'comments.author'])->first();
        //データなければ404を返す
        return $photo ?? abort(404);
    }

    /**
     * コメント投稿
     * `param Photo $photo
     * @param StoreComment $request
     * @return \Illuminate\Http\Response
     */
     public function addComment(Photo $photo, StoreComment $request)
     {
         $comment = new Comment();
         $comment->content = $request->get('content');
         $comment->user_id = Auth::user()->id;
         $photo->comments()->save($comment);

         //authorリレーションをロードする為にコメントを取得しなおす
         $new_comment = Comment::where('id', $comment->id)->with('author')->first();

         return response($new_comment, 201);
     }

     /**
      * いいね
      * @param string $id
      * @return array
      */
      public function like(string $id)
      {
          $photo = Photo::where('id', $id)->with('likes')->first();

          if (! $photo) {
              abort(404);
          }

          //何回実行してもいいねが1つしかつかないようにする
          //いいね削除
          $photo->likes()->detach(Auth::user()->id);
          //いいね追加
          $photo->likes()->attach(Auth::user()->id);

          return ["photo_id" => $id];
      }

      /**
       * いいね削除
       * @param string $id
       * @return array
       */
      public function unlike(string $id)
      {
          $photo = Photo::where('id', $id)->with('likes')->first();

          if (! $photo) {
            abort(404);
          }

          //いいね削除
          $photo->likes()->detach(Auth::user()->id);

          return ["photo_id" => $id];
      }
}
