import Vue from 'vue'
import Usuario from 'src/mvc/models/usuario.js'
import Etiqueta from 'src/mvc/models/etiqueta.js'
import { CargaEntradaItemProcessamento } from './enums/cargastypes'
class CargaEntradaItem {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.cargaentradaid = null
    this.nfechave = ''
    this.coletaid = null
    this.nfenumero = null
    this.nfecnpj = ''
    this.nfevol = 0
    this.nfepeso = 0
    this.tipoprocessamento = new CargaEntradaItemProcessamento('1')
    this.temerro = false
    this.etiquetas = []
    this.errors = null
    this.manualuser = new Usuario()
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    this.id = item.id
    this.cargaentradaid = item.cargaentradaid
    this.nfechave = item.nfechave
    this.nfevol = parseInt(item.nfevol ? item.nfevol : 0)
    this.nfepeso = parseFloat(item.nfepeso ? item.nfepeso : 0)
    this.tipoprocessamento.value = item.tipoprocessamento

    if (typeof item.etiquetas !== 'undefined') {
      self.etiquetas = []
      for (let index = 0; index < item.etiquetas.length; index++) {
        const et = item.etiquetas[index]
        var etiqueta = new Etiqueta(et)
        if (etiqueta.ean13 !== '') self.etiquetas.push(etiqueta)
      }
    }
    if (typeof item.errors !== 'undefined') {
      if (typeof item.errors === 'string') this.errors = JSON.parse(item.errors)
      if (typeof item.errors === 'object') this.errors = item.errors
    }
    this.temerro = this.errors ? this.errors.length > 0 : false

    if (typeof item.coletaid !== 'undefined') this.coletaid = item.coletaid
    if (typeof item.nfenumero !== 'undefined') this.nfenumero = item.nfenumero
    if (typeof item.nfecnpj !== 'undefined') this.nfecnpj = item.nfecnpj
    if (typeof item.manualuser !== 'undefined') this.manualuser = new Usuario(item.manualuser)
  }

  async save () {
    var self = this
    try {
      if (!self.cargaentradaid) throw new Error('Número da carga não foi informado')
      if (!(self.nfechave)) throw new Error('Chave da NF-e não foi informada')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {}

    // update
    if (self.id ? (self.id > 0) : false) {
      params.id = self.id
      params.nfevol = self.nfevol
      params.nfepeso = self.nfepeso
    } else {
      params.nfechave = self.nfechave
    }

    let ret = await Vue.prototype.$axios.post('v1/cargaentrada/carga/' + self.cargaentradaid + '/itens', params).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          if (data.data) await self.cloneFrom(data.data)
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async delete () {
    var self = this
    try {
      if (!self.id) throw new Error('Item com id inválido')
      if (!(self.id > 0)) throw new Error('Item com id inválido')
      if (!self.cargaentradaid) throw new Error('Número da carga não foi informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let ret = await Vue.prototype.$axios.delete('v1/cargaentrada/carga/' + self.cargaentradaid + '/itens/id/' + self.id).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
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
    let ret = await Vue.prototype.$axios.post('v1/cargaentrada/carga/' + self.cargaentradaid + '/itens/id/' + self.id + '/etiquetas/gerar').then(async response => {
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

export default CargaEntradaItem
