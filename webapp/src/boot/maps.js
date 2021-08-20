import * as VueGoogleMaps from 'vue2-google-maps'
export default async ({ Vue }) => {
  Vue.use(VueGoogleMaps, {
    load: {
      key: Vue.prototype.$configini.GOOGLEMAPS_APIKEY,
      libraries: 'places'
    }
  })
}
