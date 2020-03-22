<template>
  <div
    v-if="photo"
    class="photo-detail"
    :class="{ 'photo-detail--column': fullWidth }"
  >
    <figure
      class="photo-detail__pane photo-detail___image"
      @click="fullWidth = ! fullWidth"
    >
      <img :src="photo.url" alt="">
      <figcaption>Posted by {{ photo.owner.name }}</figcaption>
    </figure>
    <div class="photo-detail__pane">
      <!-- いいねボタン -->
      <button
        class="button button--like"
        :class="{ 'button--liked': photo.liked_by_user }"
        title="Like photo"
        @click="onLikeClick"
      >
        <i class="icon ion-md-heart"></i>{{ photo.likes_count }}
      </button>
      <a
        :href="`/photos/${photo.id}/download`"
        class="button"
        title="Download photo"
      >
        <i class="icon ion-md-arrow-round-down"></i>Download
      </a>
      <h2 class="photo-detail__title">
        <i class="icon ion-md-chatboxes"></i>Comments
      </h2>
      <!-- コメントが１件以上ある時はリストを表示 -->
      <ul v-if="photo.comments.length > 0" class="photo-detail__comments">
        <li
          v-for="comment in photo.comments"
          :key="comment.content"
          class="photo-detail__commentItem"
        >
          <p class="photo-detail__commentBody">
            {{ comment.content }}
          </p>
          <p class="photo-detail__commentInfo">
            {{ comment.author.name }}
          </p>
        </li>
      </ul>
      <!-- コメントがない時に表示 -->
      <p v-else>No comments yet.</p>
      <form @submit.prevent="addComment" class="form" v-if="isLogin">
        <div v-if="commentErrors" class="errors">
          <ul v-if="commentErrors.content">
            <li v-for="msg in commentErrors.content" :key="msg">{{ msg }}</li>
          </ul>
        </div>
        <textarea class="form__item" v-model="commentContent"></textarea>
        <div class="form__button">
          <button type="submit" class="button button--inverse">submit comment</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

export default {
  props: {
    id: {
      type: String,
      required: true
    }
  },
  data () {
    return {
      photo: null,
      fullWidth: false,
      commentContent: '',
      commentErrors: null
    }
  },
  methods: {
    //写真取得
    async fetchPhoto () {
      const response = await axios.get(`/api/photos/${this.id}`)

      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.photo = response.data
    },
    //コメント入力
    async addComment () {
      //web apiよりコメントをPOST
      const response = await axios.post(`/api/photos/${this.id}/comments`, {
        content: this.commentContent
      })

      //バリデーション エラー
      if (response.status === UNPROCESSABLE_ENTITY) {
        this.commentErrors = response.data.errors
        return false
      }

      this.commentContent = ''

      //エラーメッセージをクリア
      this.commentErrors = null

      //その他のエラー
      if (response.status !== CREATED) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      //投稿したてのコメントを表示する
      //comments配列にレスポンスデータを挿入
      this.photo.comments = [
        response.data,
        ...this.photo.comments
      ]
    },
    //いいねクリック時動作
    onLikeClick () {
      if (! this.isLogin) {
        alert('いいね機能を使うにはログインしてください')
        return false
      }

      //既にいいねされていればいいね削除
      if (this.photo.liked_by_user) {
        this.unlike()
      } else {
        this.like()
      }
    },
    //いいね
    async like () {
      //いいねAPI
      const response = await axios.put(`/api/photos/${this.id}/like`)

      //エラー処理
      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      //表示更新
      this.photo.likes_count = this.photo.likes_count + 1
      this.photo.liked_by_user = true
    },
    //いいね削除
    async unlike () {
      //いいねAPIに削除
      const response = await axios.delete(`/api/photos/${this.id}/like`)

      //エラー処理
      if (response.status !== OK) {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      //表示更新
      this.photo.likes_count = this.photo.likes_count - 1
      this.photo.liked_by_user = false
    }
  },
  watch: {
    $route: {
      async handler () {
        await this.fetchPhoto()
      },
      immediate: true
    }
  },
  computed: {
    isLogin () {
      return this.$store.getters['auth/check']
    }
  }
}
</script>