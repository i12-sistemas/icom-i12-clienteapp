// import ItemEditDialog from 'src/pages/operacional/cargas/transfer/edit/dialog-itens/itemedit.vue'
// import EditDialog from 'src/pages/operacional/cargas/transfer/edit/edit-dialog.vue'
// import EditChangeDialog from 'src/pages/cargas/transferencia/edit-change-status-dialog.vue'
import Motorista from 'src/mvc/models/motorista.js'
import CargaTransferItem from 'src/mvc/models/cargatransferitem.js'
import Veiculo from 'src/mvc/models/veiculo.js'
import Usuario from 'src/mvc/models/usuario.js'
import Unidade from 'src/mvc/models/unidade.js'
import Etiqueta from 'src/mvc/models/etiqueta.js'
import moment from 'moment'
import Etiquetas from 'src/mvc/collections/etiquetas.js'

import Vue from 'vue'
import { CargaTransferStatus } from './enums/cargastypes'

class CargaTransfer {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.dataraw = null
    this.id = null
    this.status = new CargaTransferStatus('1')
    this.unidadeentrada = new Unidade()
    this.unidadesaida = new Unidade()
    this.motorista = new Motorista()
    this.veiculo = new Veiculo()
    this.saidadh = null
    this.entradadh = null
    this.created_at = moment().format('YYYY-MM-DD HH:mm:ss')
    this.updated_at = moment().format('YYYY-MM-DD HH:mm:ss')
    this.volqtde = 0
    this.peso = 0
    this.erroqtde = 0
    this.erromsg = ''
    this.conferidoentradaqtde = 0
    this.conferidoentradaprogresso = 0
    this.entrada_usuario = null
    this.saida_usuario = null
    this.created_usuario = null
    this.updated_usuario = null
    this.itens = null
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (typeof item.id !== 'undefined') self.id = item.id
    if (typeof item.status !== 'undefined') self.status = new CargaTransferStatus(item.status)
    if (typeof item.veiculo !== 'undefined') self.veiculo = new Veiculo(item.veiculo)
    if (typeof item.motorista !== 'undefined') self.motorista = new Motorista(item.motorista)
    if (typeof item.unidadeentrada !== 'undefined') self.unidadeentrada = new Unidade(item.unidadeentrada)
    if (typeof item.unidadesaida !== 'undefined') self.unidadesaida = new Unidade(item.unidadesaida)
    if (typeof item.peso !== 'undefined') self.peso = item.peso
    if (typeof item.volqtde !== 'undefined') self.volqtde = item.volqtde
    if (typeof item.entradadh !== 'undefined') self.entradadh = item.entradadh
    if (typeof item.conferidoentradaprogresso !== 'undefined') this.conferidoentradaprogresso = Math.trunc(item.conferidoentradaprogresso)
    if (typeof item.conferidoentradaqtde !== 'undefined') this.conferidoentradaqtde = item.conferidoentradaqtde
    if (typeof item.saidadh !== 'undefined') self.saidadh = item.saidadh
    if (typeof item.created_at !== 'undefined') self.created_at = item.created_at
    if (typeof item.updated_at !== 'undefined') self.updated_at = item.updated_at
    if (typeof item.erroqtde !== 'undefined') self.erroqtde = item.erroqtde
    if ((typeof item.erromsg !== 'undefined') && (self.erroqtde > 0)) {
      if (typeof item.erromsg === 'object') self.erromsg = item.erromsg
      if (typeof item.erromsg === 'string') {
        self.erromsg = JSON.parse(item.erromsg)
        for (let index = 0; index < self.erromsg.length; index++) {
          var eti = new Etiqueta(self.erromsg[index].etiqueta)
          self.erromsg[index].etiqueta = eti
        }
      }
    }
    if (typeof item.entrada_usuario !== 'undefined') self.entrada_usuario = new Usuario(item.entrada_usuario)
    if (typeof item.saida_usuario !== 'undefined') self.saida_usuario = new Usuario(item.saida_usuario)
    if (typeof item.created_usuario !== 'undefined') self.created_usuario = new Usuario(item.created_usuario)
    if (typeof item.updated_usuario !== 'undefined') self.updated_usuario = new Usuario(item.updated_usuario)

    if (typeof item.itens !== 'undefined') {
      if (item.itens ? item.itens.length > 0 : false) {
        self.itens = []
        for (let index = 0; index < item.itens.length; index++) {
          var lItem = new CargaTransferItem(item.itens[index])
          self.itens.push(lItem)
        }
      }
    }
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/cargatransfer/carga/' + pID).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          await self.cloneFrom(data.data)
          self.dataraw = data.data
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async itemDelete (app, pItens) {
    var self = this
    // if (!self.roteiro) return
    // if (self.status) return
    var msg = ''
    var title = ''
    if (pItens.length === 1) {
      var lItem = pItens[0]
      msg = 'Código de barra: ' + lItem.etiqueta.ean13
      title = 'Remover volume ' + lItem.etiqueta.volume + '?'
    } else {
      msg = pItens.length + ' itens serão excluidos!'
      title = 'Remover itens?'
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: title,
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
        var ids = []
        for (let index = 0; index < pItens.length; index++) {
          ids.push(pItens[index].id)
        }
        var lItem = new CargaTransferItem()
        lItem.cargatransferid = self.id
        var ret = await lItem.delete(ids)
        if (ret.ok) {
          self.cloneFrom(ret.data)
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

  async questionDelete (app) {
    var self = this
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir Carga de Transferência #' + self.id,
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
    let ret = await Vue.prototype.$axios.delete('v1/cargatransfer/carga/' + self.id).then(response => {
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

  async save () {
    var self = this
    try {
      if (!self.unidadesaida) throw new Error('Unidade de saida não foi informada')
      if (!(self.unidadesaida.id > 0)) throw new Error('Unidade de saida não foi informada')
      if (!self.unidadeentrada) throw new Error('Unidade de entrada não foi informada')
      if (!(self.unidadeentrada.id > 0)) throw new Error('Unidade de entrada não foi informada')

      if (self.unidadeentrada.id === self.unidadesaida.id) throw new Error('Unidade de saida e de entrada não pode ser igual')

      if (!self.motorista) throw new Error('Nenhum motorista informado')
      if (!(self.motorista.id > 0)) throw new Error('Nenhum motorista informado')
      if (!self.veiculo) throw new Error('Nenhum veículo informado')
      if (!(self.veiculo.id > 0)) throw new Error('Nenhum veículo informado')

      if (self.status.value !== '1') throw new Error('Status atual não permite alteração')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {
      motoristaid: self.motorista.id,
      veiculoid: self.veiculo.id,
      unidadeentradaid: self.unidadeentrada.id,
      unidadesaidaid: self.unidadesaida.id
    }
    if (self.id ? (self.id > 0) : false) {
      params.id = self.id
    }

    let ret = await Vue.prototype.$axios.post('v1/cargatransfer/carga', params).then(response => {
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

  // async ShowAddOrEdit (app) {
  //   var self = this
  //   return new Promise((resolve) => {
  //     app.$q.dialog({
  //       parent: app,
  //       component: EditDialog,
  //       idstart: self.id,
  //       cancel: true,
  //       persistent: true
  //     }).onOk(async data => {
  //       if (data.dataset) {
  //         self.cloneFrom(data.dataset)
  //         resolve({ ok: true, dados: data.dataset })
  //       } else {
  //         resolve({ ok: false })
  //       }
  //     }).onCancel(() => {
  //       resolve({ ok: false })
  //     })
  //   })
  // }

  async ShowChangeStatus (app) {
    var self = this
    var options = []
    var unidadeatual = await app.$helpers.getUnidadeLogada(app)

    var origem = (self.unidadeentrada.id === unidadeatual.id) ? 'e' : 's'
    if ((self.unidadesaida.id !== unidadeatual.id) && (origem === 's')) origem = ''
    var optionstatus = new CargaTransferStatus('1')
    for (let index = 0; index < optionstatus.options.length; index++) {
      const opt = optionstatus.options[index]
      var permite = false
      switch (opt.value) {
        case '1':
          permite = (self.status.value === '2') && (origem === 's')
          break
        case '2':
          permite = ((self.status.value === '1') || (self.status.value === '3')) && (origem === 's')
          break
        case '3':
          permite = ((self.status.value === '2') && (origem === 's')) || ((self.status.value === '4') && (origem === 'e'))
          break
        case '4':
          permite = (self.status.value === '3') && (origem === 'e')
          break
      }
      if (opt.value === self.status.value) permite = true
      if (permite) options.push({ label: opt.desc, value: opt.value, color: opt.color, disable: (opt.value === self.status.value) })
    }

    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Carga #' + self.id,
        message: 'Status atual ' + self.status.description + ' - Escolha o novo status:',
        options: {
          type: 'radio',
          model: self.status.value,
          // inline: true
          items: options
        },
        cancel: true,
        persistent: true
      }).onOk(async data => {
        var ret = await self.alterarstatus(app, data)
        resolve(ret)
      })
    })
  }

  // async itemAddOrEdit (app, pIdxItem) {
  //   var self = this
  //   var lItem = new CargaTransferItem()
  //   if (pIdxItem >= 0) {
  //     lItem = self.itens[pIdxItem]
  //   } else {
  //     lItem.cargaentradaid = self.id
  //   }
  //   return new Promise((resolve) => {
  //     app.$q.dialog({
  //       parent: app,
  //       component: ItemEditDialog,
  //       item: lItem,
  //       cargaentrada: self,
  //       cancel: true,
  //       persistent: true
  //     }).onOk(async data => {
  //       if (data) {
  //         if (pIdxItem >= 0) {
  //           await self.itens[pIdxItem].cloneFrom(data)
  //         } else {
  //           if (!self.itens) self.itens = []
  //           self.itens.push(data)
  //         }
  //         resolve({ ok: true })
  //       } else {
  //         resolve({ ok: false })
  //       }
  //     }).onCancel(() => {
  //       resolve({ ok: false })
  //     })
  //   })
  // }

  async alterarstatus (app, newStatus) {
    var self = this
    try {
      var ret = { ok: false, msg: '' }
      var dialog = app.$q.dialog({
        message: 'Alterando status...',
        progress: true, // we enable default settings
        color: 'blue',
        persistent: true, // we want the user to not be able to close it
        ok: false // we want the user to not be able to close it
      })

      if (!self.id) throw new Error('Carga não foi informada')
      if (!(self.id > 0)) throw new Error('Carga não foi informada')
      if (!newStatus) throw new Error('Nenhum status informado')
      ret = await Vue.prototype.$axios.post('v1/cargatransfer/carga/' + self.id + '/alterarstatus/' + newStatus).then(response => {
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
    } catch (error) {
      ret = { ok: false, msg: error.message, warning: false }
    } finally {
      dialog.hide()
    }
    return ret
  }

  async showEtiquetasItens (app, pTodosOsItens = true, pEANs = null) {
    var self = this
    var lEtiquetas = new Etiquetas()
    lEtiquetas.eans = []

    if (pTodosOsItens) {
      if (self.itens ? self.itens.length > 0 : false) {
        for (let index = 0; index < self.itens.length; index++) {
          const lItem = self.itens[index]
          if (lItem.etiqueta ? lItem.etiqueta.ean13 !== '' : false) {
            lEtiquetas.eans.push(lItem.etiqueta.ean13)
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

  async marcarconferido (pEan) {
    var self = this
    let params = {
      ean13: pEan
    }
    var ret = await Vue.prototype.$axios.post('v1/cargatransfer/carga/' + self.id + '/itens/conferir', params).then(response => {
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
}

export default CargaTransfer
