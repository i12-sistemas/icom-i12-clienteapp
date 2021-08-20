export default async ({ Vue, store }) => {
  Vue.use(require('vue-pusher'), {
    api_key: Vue.prototype.$configini.PUSHER_APP_KEY,
    options: {
      cluster: Vue.prototype.$configini.PUSHER_CLUSTER,
      encrypted: true
    }
  })
}
