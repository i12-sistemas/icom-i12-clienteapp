import Vue from 'vue'
import Vuex from 'vuex'
import Croppa from 'vue-croppa'
Vue.use(Croppa)

import app from './app'
import authusuario from './authusuario'

Vue.use(Vuex)

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation
 */

export default function ({ Vue }) {
  const Store = new Vuex.Store({
    modules: {
      app,
      authusuario
    },

    // enable strict mode (adds overhead!)
    // for dev mode only
    strict: process.env.DEV
  })
  return Store
}
