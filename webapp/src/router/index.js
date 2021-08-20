import Vue from 'vue'
import VueRouter from 'vue-router'
import routes from './routes'
// import store from '../store'

Vue.use(VueRouter)

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation
 */

export default function ({ router }) {
  const Router = new VueRouter({
    scrollBehavior: () => ({ x: 0, y: 0 }),
    routes,

    // Leave these as is and change from quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    mode: process.env.VUE_ROUTER_MODE,
    base: process.env.VUE_ROUTER_BASE
  })

  Router.beforeEach((to, from, next) => {
    var userlogged = false
    try {
      userlogged = Router.app.$store.state.authusuario.logado
    } catch (error) {
      console.error(error)
    }

    if (to.matched.some(record => record.meta.authusuario)) {
      if (!userlogged) {
        console.error('Usuário não autenticado')
        next({ name: 'login.primeiroacesso' })
      }
    }
    next()
  })

  return Router
}
