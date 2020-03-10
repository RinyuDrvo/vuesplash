import Vue from 'vue'
//ルーティングの定義
import router from './router'
//ルートコンポーネント
import App from './App.vue'

new Vue({
  el: '#app',
  router, //ルーティングの定義読み込み
  components: { App }, //ルートコンポーネントの使用を宣言する
  template: '<app />' //ルートコンポーネントを描画する
})