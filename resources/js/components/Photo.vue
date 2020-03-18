<template>
  <div class="photo">
    <!-- 写真の表示 -->
    <figure class="photo__wrapper">
      <img
        class="photo__image"
        :src="item.url"
        :alt="`Photo by $(item.owner.name)`"
      >
    </figure>

    <!-- マウスオーバー時のオーバーレイ -->
    <RouterLink
      class="photo_overlay"
      :to="`/photos/${item.id}`"
      :tilte ="`View the photo by ${item.owner.name}`"
    >
      <div class="photo__controls">
        <!-- いいねボタン -->
        <button
          class="photo__action photo__action--like"
          title="Like photo"
        >
          <i class="icon ion-md-heart"></i>12
        </button>
        <!-- ダウンロードボタン -->
        <!-- 直接サーバーにGETリクエストを送信する為<a> -->
        <!-- イベントのバブリング防止 @click.stop -->
        <a
          class="photo__action"
          title="Download photo"
          @click.stop
            :href="`/photos/${item.id}/download`"
        >
          <i class="icon ion-md-arrow-round-down"></i>
        </a>
      </div>
      <!-- 投稿者名 -->
      <div class="photo__username">
        {{ item.owner.name }}
      </div>
    </RouterLink>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      type: Object,
      required: true
    }
  }
}
</script>