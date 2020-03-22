<?php

namespace Tests\Feature;

use App\Photo;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LikeApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        //ダミーユーザー作成
        $this->user = factory(User::class)->create();

        //ダミー写真データ作成
        factory(Photo::class)->create();
        //写真データ取得
        $this->photo = Photo::first();
    }

    /**
     * @test
     */
    public function should_いいねを追加できる()
    {
        //ログインしていいねする
        //PUT(リソースの置き換え)をしている
        $response = $this->actingAs($this->user)
            ->json('PUT', route('photo.like', [
                'id' => $this->photo->id
            ]));

        //正常なJSONが帰ってくるか
        $response->assertStatus(200)
            ->assertJsonFragment([
                'photo_id' => $this->photo->id
            ]);

        //いいねが1つついているか
        $this->assertEquals(1, $this->photo->likes()->count());
    }

    /**
     * @test
     */
    public function should_2回同じ写真にいいねしても1個しかいいねがつかない()
    {
        //idを設定
        $param = ['id' => $this->photo->id];
        //2回いいねする
        $this->actingAs($this->user)->json('PUT', route('photo.like', $param));
        $this->actingAs($this->user)->json('PUT', route('photo.like', $param));

        //いいねが1個だけか
        $this->assertEquals(1, $this->photo->likes()->count());
    }

    /**
     * @test
     */
    public function should_いいねを解除できる()
    {
        //いいね
        $this->photo->likes()->attach($this->user->id);

        //削除レスポンス
        $response = $this->actingAs($this->user)
            ->json('DELETE', route('photo.like', [
                'id' => $this->photo->id
            ]));

        //いいねが削除されているか
        $this->assertEquals(0, $this->photo->likes()->count());
    }
}
