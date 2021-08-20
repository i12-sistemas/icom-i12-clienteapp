export const title = (state, val) => {
  state.title = val
}

export const changeConnection = (state, val) => {
  var networkState = navigator.network.connection.type ? navigator.network.connection.type : navigator.network.connection.effectiveType
  var online = navigator.onLine ? navigator.onLine : (navigator.network.connection.effectiveType !== '')
  state.conexaointernet = { online: online, type: networkState }
}

export const syncclientes = (state, val) => {
  state.sync.clientes = val
}

export const filasync = (state, val) => {
  let q = state.filasync.indexOf(val)
  if (q < 0) {
    state.filasync.push(val)
  }
}

export const filasyncRemove = (state, val) => {
  let q = state.filasync.indexOf(val)
  if (q >= 0) {
    state.filasync.remove(q)
  }
}
