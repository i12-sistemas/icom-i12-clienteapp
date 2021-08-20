import Vue from 'vue'
export async function refreshConnection ({ commit }) {
  console.log('refreshConnection')
  commit('changeConnection')
}

export function showLoading ({ commit, state }, params) {
  Vue.prototype.$q.loading.show({
    spinner: 'QSpinnerPie',
    spinnerColor: params.spinnerColor ? params.spinnerColor : 'white',
    messageColor: params.messageColor ? params.messageColor : 'white',
    backgroundColor: params.backgroundColor ? params.backgroundColor : 'primary',
    message: params.message,
    customClass: params.customClass ? params.customClass : 'bg-primary'
  })
}

export function hideLoading () {
  Vue.prototype.$q.loading.hide()
}

export function addFilaSync ({ commit, state }, tag) {
  commit('filasync', tag)
}

export function delFilaSync ({ commit, state }, tag) {
  commit('filasyncRemove', tag)
}
