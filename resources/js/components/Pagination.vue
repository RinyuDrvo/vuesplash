<template>
  <div class="pagination">
    <!-- 1ページ目以外の時perv表示 -->
    <RouterLink
      v-if="! isFirstPage"
      :to="`/?page=${currentPage - 1}`"
      class="button"
    >&laquo; prev
    </RouterLink>
    <!-- 最後のページ以外の時next表示 -->
    <RouterLink
      v-if="! isLastPage"
      :to="`/?page=${currentPage + 1}`"
      class="button"
    >next &raquo;
    </RouterLink>
  </div>
</template>

<script>
  export default {
    props: {
      currentPage: {
        type: Number,
        required: true
      },
      lastPage: {
        type: Number,
        required: true
      }
    },
    computed: {
      isFirstPage () {
        return this.currentPage === 1
      },
      isLastPage () {
        return this.currentPage === this.lastPage
      }
    }
  }
</script>