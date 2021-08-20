import axios from 'axios'
export default async ({ Vue, store }) => {
  Vue.prototype.$axios = axios.create({
    baseURL: Vue.prototype.$configini.API_URL,
    withCredentials: false,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Headers': 'Authorization',
      'Access-Control-Allow-Methods': 'GET, POST, OPTIONS, PUT, PATCH, DELETE',
      'Content-Type': 'application/json;charset=UTF-8'
    }
  })

  // Add a 401 response interceptor
  Vue.prototype.$axios.interceptors.response.use(function (response) {
    return response
  }, function (error) {
    if (error.response.status === 401) {
      var baseURL = error.response.config ? error.response.config.baseURL : ''
      // var url = error.response.config ? error.response.config.url : ''
      var msg = error.message
      if (window.configini.API_URL === baseURL) {
        msg = 'Code: ' + error.response.status + ' - ' + error.response.data
        var i = -1
        var logoffdevice = (String(msg).search('Dispositivo não encontrado') >= 0) || (String(msg).search('Acesso não autorizado: Token expirado. Refaça login do dispositivo') >= 0)
        if (!logoffdevice) {
          i = String(msg).search('Motorista não encontrado')
          var logoffuser = (i >= 0)
        }
        if (logoffdevice) {
          Vue.prototype.$q.notify({
            message: msg,
            color: 'red'
          })
          store.dispatch('authdevice/logout')
          return
        } else {
          if (logoffuser) {
            Vue.prototype.$q.notify({
              message: msg + ' - Refaça o login do motorista.',
              color: 'red'
            })
            store.dispatch('authusuario/logout')
            return
          } else {
            Vue.prototype.$q.notify({
              message: msg,
              color: 'red'
            })
          }
        }
      }
      return Promise.reject(error)
    } else {
      return Promise.reject(error)
    }
  })
}
