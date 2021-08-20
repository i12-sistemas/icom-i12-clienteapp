import Vue from 'vue'
import Etiqueta from 'src/mvc/models/etiqueta.js'

class CargaEntregaItem {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.cargaentregaid = null
    this.etiqueta = new Etiqueta()
    this.ctechave = null
    this.ctecnpj = null
    this.ctenumero = null
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    this.id = item.id
    this.cargaentregaid = item.cargaentregaid
    if (typeof item.ctechave !== 'undefined') self.ctechave = item.ctechave
    if (typeof item.ctecnpj !== 'undefined') self.ctecnpj = item.ctecnpj
    if (typeof item.ctenumero !== 'undefined') self.ctenumero = item.ctenumero
    if (typeof item.etiqueta !== 'undefined') self.etiqueta = new Etiqueta(item.etiqueta)
  }

  // somente insert
  async save (pVariosEans) {
    var self = this
    try {
      if (!self.cargaentregaid) throw new Error('Número da carga não foi informado')

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

    let ret = await Vue.prototype.$axios.post('v1/cargaentrega/carga/' + self.cargaentregaid + '/itens', params).then(async response => {
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

  // somente insert
  async saveedit (pIds) {
    var self = this
    try {
      if (!self.cargaentregaid) throw new Error('Número da carga não foi informado')
      if (!self.id) throw new Error('Item não foi encontrada')
      if (!(self.id > 0)) throw new Error('Item não foi encontrada')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {
      ids: pIds,
      ctechave: self.ctechave
    }

    let ret = await Vue.prototype.$axios.post('v1/cargaentrega/carga/' + self.cargaentregaid + '/itens/update', params).then(async response => {
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
      if (!self.cargaentregaid) throw new Error('Número da carga não foi informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    var params = {
      ids: ids.join(',')
    }
    let ret = await Vue.prototype.$axios.delete('v1/cargaentrega/carga/' + self.cargaentregaid + '/itens', { params: params }).then(async response => {
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
      if (!self.cargaentregaid) throw new Error('Número da carga não foi informado')
      if (!(self.cargaentregaid > 0)) throw new Error('Número da carga não foi informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let ret = await Vue.prototype.$axios.post('v1/cargaentrega/carga/' + self.cargaentregaid + '/itens/id/' + self.id + '/etiquetas/gerar').then(async response => {
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

export default CargaEntregaItem
