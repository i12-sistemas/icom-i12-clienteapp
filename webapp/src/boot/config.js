import configini from 'src/statics/config/index.js'
export default async ({ Vue }) => {
  Vue.prototype.$configini = configini
  Vue.prototype.$qtable = {
    rowsperpageoptions: [20, 50, 100, 250, 500]
  }
}
