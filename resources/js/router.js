import Vue from 'vue'
import VueRouter from 'vue-router'

import PhotoList from './pages/PhotoList.vue'
import Login from './pages/Login.vue'

import store from './store'
import SystemError from './pages/errors/System.vue'

import PhotoDetail from './pages/PhotoDetail.vue'

import NotFound from './pages/errors/NotFound.vue'

//VueRouterプラグインを使用する
Vue.use(VueRouter)

//パスとコンポーネントのマッピング
const routes = [
  {
    path: '/',
    component: PhotoList,
    //PhotoListコンポーネントにクエリパラメータpageの値が、pageというpropsとして渡される
    props: route => {
      //routeからクエリパラメータpageを取り出した上で正規表現を使って整数と解釈されない値は「1」を返す
      const page = route.query.page
      return { page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1 }
    }
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
  },
  {
    //定義されたルート以外のアクセスを404へ
    path: '*',
    component: NotFound
  }
]

//VueRouterインスタンスを作成する
const router = new VueRouter({
  mode: 'history',
  scrollBehavior() {
    return { x: 0, y: 0 }
  },
  routes
})

//VUeRouterインスタンスをエクスポートする
//app.jsでインポートする為
export default router