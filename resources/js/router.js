import Vue from 'vue'
import VueRouter from 'vue-router'

//ページコンポーネントをインポート
import PhotoList from './pages/PhotoList.vue'
import Login from './pages/Login.vue'

//VueRouterプラグインを使用する
Vue.use(VueRouter)

//パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    component: PhotoList
  },
  {
    path: '/login',
    component: Login
  }
]

//VueRouterインスタンスを作成する
const router = new VueRouter({
  routes
})

//VUeRouterインスタンスをエクスポートする
//app.jsでインポートする為
export default router