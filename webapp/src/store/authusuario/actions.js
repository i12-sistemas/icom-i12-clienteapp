import Vue from 'vue'
import axios from 'axios'

export async function checklogon ({ commit, state, dispatch }) {
  var ret = { ok: false, msg: '' }
  if ((state.userlocal ? (state.userlocal.username ? state.userlocal.username !== '' : false) : false) && (state.token ? state.token !== '' : false)) {
    var credentials = 'Basic ' + btoa(state.userlocal.username + ':' + state.token)
    var req = axios.create({
      baseURL: Vue.prototype.$configini.API_URL,
      withCredentials: false,
      headers: {
        'x-auth-uuid': state.uuid,
        'Authorization': credentials,
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Authorization',
        'Access-Control-Allow-Methods': 'GET, POST, OPTIONS, PUT, PATCH, DELETE',
        'Content-Type': 'application/json;charset=UTF-8'
      }
    })
    ret = await req.post('v1/login/usuario/checklogin').then(response => {
      let data = response.data
      return data
    }
    ).catch(error => {
      let msg = error
      if (error.response) {
        msg = 'Code: ' + error.response.status + ' - ' + error.response.data.message
      } else {
        msg = error.message
      }
      return { ok: false, msg: 'Falha ao consultar servidor online: ' + msg }
    })
  }
  await commit('setret', ret)
  if (ret.ok) {
    await commit('setusuariologado', ret.data)
    if (state.logado) {
      var pusher = Vue.prototype.$pusher
      var channelDeviceAll = pusher.subscribe('allusersconnected')
      var channelDeviceUser = pusher.subscribe('usr-' + state.user.login.toString().toLowerCase())
      channelDeviceUser.bind('disconnect', function (params) {
        var b = Vue.prototype.$helpers.toBool(params)
        if (b) dispatch('logout')
      })
      channelDeviceAll.bind('disconnect', function (params) {
        var b = Vue.prototype.$helpers.toBool(params)
        if (b) dispatch('logout')
      })

      channelDeviceUser.bind('info', function (params) {
        if (typeof params === 'object') {
          Vue.prototype.$eventbus.$emit('notification_add', params)
        }
      })
      channelDeviceAll.bind('info', function (params) {
        if (typeof params === 'object') Vue.prototype.$eventbus.$emit('notification_add', params)
      })
    } else {
      Vue.prototype.$pusher.unsubscribe('allusersconnected')
      Vue.prototype.$pusher.unsubscribe('usr-' + state.user.login.toString().toLowerCase())
    }
  }
}

export async function setunidadeatual ({ commit, state, dispatch }, unidade) {
  await commit('setunidadeatual', unidade)
}

export async function getlocalstorage ({ commit, state, dispatch }) {
  await commit('getlocalstorage')
  if (state.uuid && state.userlocal && state.token && state.expireat) {
    await dispatch('checklogon')
  }
}
export async function setlocalstorage ({ commit, dispatch }, dados) {
  await commit('setlocalstorage', dados)
  await dispatch('getlocalstorage')
}

export async function adderror401 ({ commit, state, dispatch }) {
  await commit('adderror401')
  if (state.axioserror401count >= 4) {
    await dispatch('logout')
    Vue.prototype.$q.notify({
      message: 'Sua sessão foi encerrada!',
      color: 'red',
      onDismiss: () => {
      },
      actions: [{
        icon: 'close',
        color: 'white'
      }]
    })
  }
}

export async function reseterror401 ({ commit }) {
  await commit('reseterror401')
}

export async function logout ({ commit }) {
  commit('logout')
  this.$router.push({ name: 'login.usuario' })
  return true
}
