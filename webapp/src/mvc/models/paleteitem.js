import Vue from 'vue'
import Etiqueta from 'src/mvc/models/etiqueta.js'

class PaleteItem {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.paleteid = null
    this.etiqueta = new Etiqueta()
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    this.id = item.id
    this.paleteid = item.paleteid
    if (typeof item.etiqueta !== 'undefined') self.etiqueta = new Etiqueta(item.etiqueta)
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

export default PaleteItem
