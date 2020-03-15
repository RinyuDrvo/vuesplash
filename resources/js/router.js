import Vue from 'vue'
import VueRouter from 'vue-router'

import PhotoList from './pages/PhotoList.vue'
import Login from './pages/Login.vue'

import store from './store'
import SystemError from './pages/errors/System.vue'

import PhotoDetail from './pages/PhotoDetail.vue'

//VueRouterプラグインを使用する
Vue.use(VueRouter)

//パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    component: PhotoList
  },
  {
    path: '/photos/:id',
    component: PhotoDetail,
    props: true
  },
  {
    path: '/login',
    component: Login,
    beforeEnter(to, from, next) {
      if (store.getters['auth/check']) {
        next('/')
      } else {
        next()
      }
    }
  },
  {
    path: '/500',
    component: SystemError
  }
]

//VueRouterインスタンスを作成する
const router = new VueRouter({
  mode: 'history',
  routes
})

//VUeRouterインスタンスをエクスポートする
//app.jsでインポートする為
export default router