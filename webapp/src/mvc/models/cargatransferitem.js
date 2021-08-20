import Vue from 'vue'
import Etiqueta from 'src/mvc/models/etiqueta.js'
import Usuario from 'src/mvc/models/usuario.js'

class CargaTransferItem {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.cargatransferid = null
    this.etiqueta = new Etiqueta()

    this.conferidoentrada = false
    this.conferidoentradadh = null
    this.conferidoentradauuid = null
    this.conferidoentrada_usuario = null
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    this.id = item.id
    this.cargatransferid = item.cargatransferid
    if (typeof item.etiqueta !== 'undefined') self.etiqueta = new Etiqueta(item.etiqueta)

    if (typeof item.conferidoentrada !== 'undefined') self.conferidoentrada = Vue.prototype.$helpers.toBool(item.conferidoentrada)
    if (typeof item.conferidoentradadh !== 'undefined') self.conferidoentradadh = item.conferidoentradadh
    if (typeof item.conferidoentradauuid !== 'undefined') self.conferidoentradauuid = item.conferidoentradauuid
    if (typeof item.conferidoentrada_usuario !== 'undefined') self.conferidoentrada_usuario = new Usuario(item.conferidoentrada_usuario)
  }

  // somente insert
  async save (pVariosEans) {
    var self = this
    try {
      if (!self.cargatransferid) throw new Error('Número da carga não foi informado')

      var eans = []
      if (pVariosEans ? pVariosEans.length > 0 : false) {
        for (let index = 0; index < pVariosEans.length; index++) {
          eans.push(pVariosEans[index])
        }
      }
      if (self.etiqueta ? (self.etiqueta.ean13 ? self.etiqueta.ean13 !== '' : false) : false) eans.push(self.etiqueta.ean13)

      if (eans.length <= 0) throw new Error('Nenhum código de barra informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {
      ean13: eans
    }

    let ret = await Vue.prototype.$axios.post('v1/cargatransfer/carga/' + self.cargatransferid + '/itens', params).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.data) ret.data = data.data
        if (data.ok) {
          ret.ok = true
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async delete (pOutrosIDS) {
    var self = this
    try {
      var ids = []
      if (self.id ? (self.id > 0) : false) ids.push(self.id)
      if (pOutrosIDS ? pOutrosIDS.length > 0 : false) {
        for (let index = 0; index < pOutrosIDS.length; index++) {
          ids.push(pOutrosIDS[index])
        }
      }

      if (ids.length === 0) throw new Error('Nenhum item informado')
      if (!self.cargatransferid) throw new Error('Número da carga não foi informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    var params = {
      ids: ids.join(',')
    }
    let ret = await Vue.prototype.$axios.delete('v1/cargatransfer/carga/' + self.cargatransferid + '/itens', { params: params }).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.data = data.data ? data.data : null
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) ret.ok = true
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async etiquetasgerar () {
    var self = this
    try {
      if (!(self.id)) throw new Error('Número da carga não foi informado')
      if (!(self.id > 0)) throw new Error('Número da carga não foi informado')
      if (!self.cargaentradaid) throw new Error('Número da carga não foi informado')
      if (!(self.cargaentradaid > 0)) throw new Error('Número da carga não foi informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let ret = await Vue.prototype.$axios.post('v1/cargatransfer/carga/' + self.cargaentradaid + '/itens/id/' + self.id + '/etiquetas/gerar').then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          if (data.data) self.etiquetas = data.data
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }
}

export default CargaTransferItem
