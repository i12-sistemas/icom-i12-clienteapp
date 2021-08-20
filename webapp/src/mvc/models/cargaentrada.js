// import ItemEditDialog from 'src/pages/operacional/cargas/entrada/edit/dialog-itens/itemedit.vue'
// import EditDialog from 'src/pages/operacional/cargas/entrada/edit/edit-dialog.vue'
import Motorista from 'src/mvc/models/motorista.js'
import CargaEntradaItem from 'src/mvc/models/cargaentradaitem.js'
import Veiculo from 'src/mvc/models/veiculo.js'
import Usuario from 'src/mvc/models/usuario.js'
import Unidade from 'src/mvc/models/unidade.js'
import moment from 'moment'
import Etiquetas from 'src/mvc/collections/etiquetas.js'

import Vue from 'vue'
import { CargaEntradaTipo, CargaEntradaStatus } from 'src/mvc/models/enums/cargastypes'

class CargaEntrada {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.tipo = new CargaEntradaTipo('1')
    this.status = new CargaEntradaStatus('1')
    this.unidadeentrada = new Unidade()
    this.motorista = new Motorista()
    this.veiculo = new Veiculo()
    this.dhentrada = moment().format('YYYY-MM-DD HH:mm:ss')
    this.created_at = null
    this.updated_at = null
    this.conferidoprogresso = 0
    this.conferidoqtde = 0
    this.volqtde = 0
    this.peso = 0
    this.erroqtde = 0
    this.erromsg = ''
    this.editadomanualmente = false
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()
    this.itens = null
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (typeof item.id !== 'undefined') this.id = item.id
    if (typeof item.tipo !== 'undefined') this.tipo = new CargaEntradaTipo(item.tipo)
    if (typeof item.status !== 'undefined') this.status = new CargaEntradaStatus(item.status)
    if (typeof item.peso !== 'undefined') this.peso = item.peso
    if (typeof item.volqtde !== 'undefined') this.volqtde = item.volqtde
    if (typeof item.conferidoqtde !== 'undefined') this.conferidoqtde = item.conferidoqtde
    if (typeof item.conferidoprogresso !== 'undefined') this.conferidoprogresso = item.conferidoprogresso
    if (typeof item.dhentrada !== 'undefined') this.dhentrada = item.dhentrada
    if (typeof item.created_at !== 'undefined') this.created_at = item.created_at
    if (typeof item.updated_at !== 'undefined') this.updated_at = item.updated_at
    if (typeof item.erroqtde !== 'undefined') this.erroqtde = item.erroqtde
    if ((typeof item.erromsg !== 'undefined') && (this.erroqtde > 0)) {
      if (typeof item.erromsg === 'string') this.erromsg = JSON.parse(item.erromsg)
      if (typeof item.erromsg === 'object') this.erromsg = item.erromsg
    }
    if (typeof item.editadomanualmente !== 'undefined') this.editadomanualmente = Vue.prototype.$helpers.toBool(item.editadomanualmente)

    if (typeof item.veiculo !== 'undefined') self.veiculo = new Veiculo(item.veiculo)
    if (typeof item.motorista !== 'undefined') self.motorista = new Motorista(item.motorista)
    if (typeof item.unidadeentrada !== 'undefined') self.unidadeentrada = new Unidade(item.unidadeentrada)
    if (typeof item.created_usuario !== 'undefined') self.created_usuario = new Usuario(item.created_usuario)
    if (typeof item.updated_usuario !== 'undefined') self.updated_usuario = new Usuario(item.updated_usuario)

    if (typeof item.itens !== 'undefined') {
      if (item.itens ? item.itens.length > 0 : false) {
        self.itens = []
        for (let index = 0; index < item.itens.length; index++) {
          var lItem = new CargaEntradaItem(item.itens[index])
          self.itens.push(lItem)
        }
      }
    }
    // await self.abastecimentoTotaliza(false)
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/cargaentrada/carga/' + pID).then(response => {
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

  async itemDelete (app, pIdxItem) {
    var self = this
    // if (!self.roteiro) return
    // if (self.status) return
    var lItem = self.itens[pIdxItem]

    var msg = ''
    if (lItem.etiquetas ? lItem.etiquetas.length > 0 : false) msg = '<div>Existem ' + lItem.etiquetas.length + ' etiquetas e elas serão canceladas!</div>'
    msg = msg + '<br><div>' + lItem.nfechave + '</div>'
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Remover item' + (pIdxItem + 1) + ' de ' + self.itens.length + '?',
        message: msg,
        html: true,
        class: 'bg-black text-white',
        cancel: {
          color: 'red-10',
          label: 'Não'
        },
        ok: {
          color: 'blue-10',
          label: 'Sim'
        }
      }).onOk(async data => {
        const dialog = app.$q.dialog({
          message: 'Excluindo item, aguarde...',
          progress: true, // we enable default settings
          persistent: true, // we want the user to not be able to close it
          ok: false // we want the user to not be able to close it
        })
        var ret = await lItem.delete()
        if (ret.ok) {
          var lCarga = new CargaEntrada()
          await lCarga.find(self.id)
          self.cloneFrom(lCarga)
          dialog.hide()
          resolve({ ok: true })
        } else {
          dialog.hide()
          resolve({ ok: false, msg: ret.msg })
        }
        dialog.hide()
      }).onCancel(() => {
        resolve({ ok: false })
      })
    })
  }

  async save () {
    var self = this
    try {
      if (!self.unidadeentrada) throw new Error('Unidade de entrada não foi informada')
      if (!(self.unidadeentrada.id > 0)) throw new Error('Unidade de entrada não foi informada')

      if (self.tipo.value === '1') {
        if (!self.motorista) throw new Error('Nenhum motorista informado')
        if (!(self.motorista.id > 0)) throw new Error('Nenhum motorista informado')
        if (!self.veiculo) throw new Error('Nenhum veículo informado')
        if (!(self.veiculo.id > 0)) throw new Error('Nenhum veículo informado')
      } else {
        self.motorista.limpardados()
        self.veiculo.limpardados()
      }
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {}
    if (self.id ? (self.id > 0) : false) {
      // update
      params.id = self.id
    } else {
      // adding
      if (self.tipo.value === '1') {
        params.motoristaid = self.motorista.id
        params.veiculoid = self.veiculo.id
      }
      params.unidadeentradaid = self.unidadeentrada.id
      params.tipo = self.tipo.value
      params.dhentrada = self.dhentrada
    }

    let ret = await Vue.prototype.$axios.post('v1/cargaentrada/carga', params).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          if (data.data) self.cloneFrom(data.data)
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async questionDelete (app) {
    var self = this
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir Carga de Entrada #' + self.id,
        message: 'Para excluir definitivamente a carga, digite o número ' + self.id + ' e clique em OK',
        prompt: {
          model: '',
          type: 'text' // optional
        },
        cancel: true
      }).onOk(async data => {
        if (parseInt(data) === parseInt(self.id)) {
          var ret = await self.delete()
          resolve(ret)
        } else {
          resolve({ ok: false, msg: 'Informação inválida', warning: true })
        }
      }).onCancel(() => {
        resolve({ ok: false })
      })
    })
  }

  async delete () {
    var self = this
    try {
      if (!self.id) throw new Error('Nenhum carga informada')
      if (self.status.value !== '1') throw new Error('Status da carga atual não permite exclusão')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let ret = await Vue.prototype.$axios.delete('v1/cargaentrada/carga/' + self.id).then(response => {
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

  async encerrarQuestion (app) {
    var self = this
    try {
      if (self.status.value === '2') throw new Error('Carga de entrada esta encerrada')
      if (self.erroqtde ? self.erroqtde > 0 : false) throw new Error('Existem erros na carga que devem ser corrigidos antes do encerramento.')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Encerrar carga de entrada #' + self.id,
        message: 'Para encerrar digite o número da carga de entrada e clique em OK',
        prompt: {
          model: '',
          type: 'text' // optional
        },
        cancel: true
      }).onOk(async data => {
        if (parseInt(data) === parseInt(self.id)) {
          var ret = await self.alterarstatus('2')
          resolve(ret)
        } else {
          resolve({ ok: false, msg: 'Informação inválida', warning: true })
        }
      }).onCancel(() => {
        resolve({ ok: false })
      })
    })
  }

  async alterarstatus (pStatus) {
    var self = this
    try {
      if (self.status.value === '2') throw new Error('Carga de entrada esta encerrada')
      if (self.erroqtde ? self.erroqtde > 0 : false) throw new Error('Existem erros na carga que devem ser corrigidos antes do encerramento.')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {}
    let ret = await Vue.prototype.$axios.post('v1/cargaentrada/carga/' + self.id + '/alterarstatus/' + pStatus, params).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          if (data.data) self.cloneFrom(data.data)
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async marcarconferido (pEan) {
    var self = this
    let params = {
      ean13: pEan
    }
    let ret = await Vue.prototype.$axios.post('v1/cargaentrada/carga/' + self.id + '/itens/conferir', params).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          if (data.data) self.cloneFrom(data.data)
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async encerrardesfazerQuestion (app) {
    var self = this
    try {
      if (self.status.value === '1') throw new Error('Carga de entrada está em aberto')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Reabrir carga de entrada #' + self.id,
        message: 'Para reabrir digite o número da carga e clique em OK',
        prompt: {
          model: '',
          type: 'text' // optional
        },
        cancel: true
      }).onOk(async data => {
        if (parseInt(data) === parseInt(self.id)) {
          var ret = await self.alterarstatus('1')
          resolve(ret)
        } else {
          resolve({ ok: false, msg: 'Informação inválida', warning: true })
        }
      }).onCancel(() => {
        resolve({ ok: false })
      })
    })
  }

  async showEtiquetasItens (app, pTodosOsItens = true, pEANs = null) {
    var self = this
    var lEtiquetas = new Etiquetas()
    lEtiquetas.eans = []

    if (pTodosOsItens) {
      if (self.itens ? self.itens.length > 0 : false) {
        for (let index = 0; index < self.itens.length; index++) {
          const lItem = self.itens[index]
          if (lItem.etiquetas ? lItem.etiquetas.length > 0 : false) {
            for (let idxEtiqueta = 0; idxEtiqueta < lItem.etiquetas.length; idxEtiqueta++) {
              const eti = lItem.etiquetas[idxEtiqueta]
              if (eti.ean13 !== '') lEtiquetas.eans.push(eti.ean13)
            }
          }
        }
      }
    }
    if (pEANs) {
      for (let index = 0; index < pEANs.length; index++) {
        lEtiquetas.eans.push(pEANs[index])
      }
    }
    var ret = await lEtiquetas.showPrintEtiqueta(app)
    return ret
  }

  async showprintdetalhe (app, showloading = true) {
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

  async getPrintUrl () {
    var self = this
    let ret = await Vue.prototype.$axios.get('v1/cargaentrada/carga/' + self.id + '/print/detalhe').then(response => {
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
}

export default CargaEntrada
