import Usuario from 'src/mvc/models/usuario.js'
import Cliente from 'src/mvc/models/cliente.js'
import Unidade from 'src/mvc/models/unidade.js'
import CargaEntrada from 'src/mvc/models/cargaentrada.js'
import EtiquetaLog from 'src/mvc/models/etiquetalog.js'
import Palete from 'src/mvc/models/palete.js'
// import moment from 'moment'
import Vue from 'vue'
import { EtiquetaStatus } from './enums/cargastypes'

class Etiqueta {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.ean13 = null
    this.cargaentrada = null
    this.cargaentradaitem = null
    this.coletanota = null
    this.dataref = null
    this.origem = new Cliente()
    this.destinatario = new Cliente()
    delete this.etiquetasfilhas
    this.ean13pai = null
    this.unidadeatual = new Unidade()
    this.numero = null
    this.created_at = null
    this.updated_at = null
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()
    this.conferidoentrada_usuario = null
    this.conferidoentrada = false
    this.conferidoentradadh = null
    this.conferidoentradauuid = null
    this.travado = false
    this.volnum = 0
    this.voltotal = 0
    this.pesototal = 0
    this.palete = null
    this.status = new EtiquetaStatus('1')
    this.logs = null
    delete this.logdelete
  }

  get volume () {
    return Vue.prototype.$helpers.padLeftZero(this.volnum, 3) + '/' + Vue.prototype.$helpers.padLeftZero(this.voltotal, 3)
  }

  get pesoindividual () {
    var p = 0
    if (this.pesototal > 0) {
      p = this.pesototal
      if (this.voltotal > 0) {
        p = this.pesototal / this.voltotal
      }
    }
    return p
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (typeof item.ean13 !== 'undefined') self.ean13 = item.ean13
    if (typeof item.origem !== 'undefined') self.origem = new Cliente(item.origem)
    if (typeof item.destinatario !== 'undefined') self.destinatario = new Cliente(item.destinatario)
    if (typeof item.ean13pai !== 'undefined') self.ean13pai = item.ean13pai
    if (typeof item.coletanota !== 'undefined') self.coletanota = item.coletanota
    if (typeof item.cargaentrada !== 'undefined') self.cargaentrada = new CargaEntrada(item.cargaentrada)
    if (typeof item.unidadeatual !== 'undefined') self.unidadeatual = new Unidade(item.unidadeatual)
    if (typeof item.cargaentradaitem !== 'undefined') self.cargaentradaitem = item.cargaentradaitem
    if (typeof item.dataref !== 'undefined') self.dataref = item.dataref
    if (typeof item.travado !== 'undefined') self.travado = Vue.prototype.$helpers.toBool(item.travado)
    if (typeof item.conferidoentrada !== 'undefined') self.conferidoentrada = Vue.prototype.$helpers.toBool(item.conferidoentrada)
    if (typeof item.conferidoentradadh !== 'undefined') self.conferidoentradadh = item.conferidoentradadh
    if (typeof item.conferidoentradauuid !== 'undefined') self.conferidoentradauuid = item.conferidoentradauuid
    if (typeof item.conferidoentrada_usuario !== 'undefined') self.conferidoentrada_usuario = new Usuario(item.conferidoentrada_usuario)
    if (typeof item.created_at !== 'undefined') self.created_at = item.created_at
    if (typeof item.updated_at !== 'undefined') self.updated_at = item.updated_at
    if (typeof item.created_usuario !== 'undefined') self.created_usuario = new Usuario(item.created_usuario)
    if (typeof item.updated_usuario !== 'undefined') self.updated_usuario = new Usuario(item.updated_usuario)
    if (typeof item.numero !== 'undefined') self.numero = item.numero
    if (typeof item.volnum !== 'undefined') self.volnum = parseInt(item.volnum)
    if (typeof item.voltotal !== 'undefined') self.voltotal = parseInt(item.voltotal)
    if (typeof item.pesototal !== 'undefined') self.pesototal = item.pesototal
    if (typeof item.status !== 'undefined') self.status.value = item.status
    if (typeof item.ultimolog !== 'undefined') self.ultimolog = new EtiquetaLog(item.ultimolog)
    if (typeof item.palete !== 'undefined') self.palete = new Palete(item.palete)
    if (typeof item.logdelete !== 'undefined') self.logdelete = new EtiquetaLog(item.logdelete)

    if (typeof item.logs !== 'undefined') {
      self.logs = []
      if (item.logs ? item.logs.length > 0 : false) {
        for (let index = 0; index < item.logs.length; index++) {
          const log = new EtiquetaLog(item.logs[index])
          self.logs.push(log)
        }
      }
    }

    if ((self.volnum === 1) && (self.voltotal > 0)) {
      if (typeof item.etiquetasfilhas !== 'undefined') {
        self.etiquetasfilhas = []
        if (item.etiquetasfilhas ? item.etiquetasfilhas.length > 0 : false) {
          for (let index = 0; index < item.etiquetasfilhas.length; index++) {
            const filha = new Etiqueta(item.etiquetasfilhas[index])
            self.etiquetasfilhas.push(filha)
          }
        }
      }
    }
  }

  async find (pEan) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/etiquetas/find/ean/' + pEan).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          self.cloneFrom(data.data)
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async getPrintUrl () {
    var self = this
    let params = {
      eans: self.ean13
    }
    let ret = await Vue.prototype.$axios.get('v1/etiquetas/print', { params: params }).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        ret.ok = data.ok ? data.ok : false
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async showPrintEtiqueta (app, showloading = true) {
    var self = this
    if (showloading) {
      var dialog = app.$q.dialog({
        message: 'Preparando documento, aguarde...',
        progress: true, // we enable default settings
        color: 'blue',
        persistent: true, // we want the user to not be able to close it
        ok: false // we want the user to not be able to close it
      })
    }
    var ret = await self.getPrintUrl()
    if (showloading) dialog.hide()
    if (ret.ok) {
      Vue.prototype.$helpers.showPrint(ret.msg)
    } else {
      if (showloading) {
        if (ret.msg ? ret.msg !== '' : false) {
          var a = app.$helpers.showDialog(ret)
          await a.then(function () {})
        }
      }
    }
    return ret
  }
}

export default Etiqueta
