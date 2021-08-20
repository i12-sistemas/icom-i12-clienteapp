// import EditDialog from 'src/pages/operacional/cargas/paletes/edit/edit-dialog.vue'
// import EditChangeDialog from 'src/pages/operacional/cargas/paletes/edit/edit-change-status-dialog.vue'
import PaleteItem from 'src/mvc/models/paleteitem.js'
import Etiqueta from 'src/mvc/models/etiqueta.js'
import Usuario from 'src/mvc/models/usuario.js'
import Unidade from 'src/mvc/models/unidade.js'
import moment from 'moment'
import Etiquetas from 'src/mvc/collections/etiquetas.js'

import Vue from 'vue'
import { PaletesStatus } from './enums/cargastypes'

class Palete {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.status = new PaletesStatus('1')
    this.unidade = new Unidade()
    this.created_at = moment().format('YYYY-MM-DD HH:mm:ss')
    this.updated_at = moment().format('YYYY-MM-DD HH:mm:ss')
    this.volqtde = 0
    this.pesototal = 0
    this.erroqtde = 0
    this.erromsg = ''
    this.created_usuario = null
    this.updated_usuario = null
    this.ean13 = null
    this.itens = null
    this.descricao = null
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (typeof item.id !== 'undefined') self.id = item.id
    if (typeof item.status !== 'undefined') self.status.value = item.status
    if (typeof item.ean13 !== 'undefined') self.ean13 = item.ean13
    if (typeof item.descricao !== 'undefined') self.descricao = item.descricao
    if (typeof item.unidade !== 'undefined') self.unidade = new Unidade(item.unidade)
    if (typeof item.created_at !== 'undefined') self.created_at = item.created_at
    if (typeof item.updated_at !== 'undefined') self.updated_at = item.updated_at
    if (typeof item.pesototal !== 'undefined') self.pesototal = item.pesototal
    if (typeof item.volqtde !== 'undefined') self.volqtde = item.volqtde
    if (typeof item.erroqtde !== 'undefined') self.erroqtde = item.erroqtde
    if ((typeof item.erromsg !== 'undefined') && (self.erroqtde > 0)) {
      if (typeof item.erromsg === 'object') self.erromsg = item.erromsg
      if (typeof item.erromsg === 'string') {
        self.erromsg = JSON.parse(item.erromsg)
        for (let index = 0; index < self.erromsg.length; index++) {
          var eti = self.erromsg[index].etiqueta
          if (eti) {
            eti = new Etiqueta(self.erromsg[index].etiqueta)
            self.erromsg[index].etiqueta = eti
          }
        }
      }
    }
    if (typeof item.created_usuario !== 'undefined') self.created_usuario = new Usuario(item.created_usuario)
    if (typeof item.updated_usuario !== 'undefined') self.updated_usuario = new Usuario(item.updated_usuario)

    if (typeof item.itens !== 'undefined') {
      if (item.itens ? item.itens.length > 0 : false) {
        self.itens = []
        for (let index = 0; index < item.itens.length; index++) {
          var lItem = new PaleteItem(item.itens[index])
          self.itens.push(lItem)
        }
      }
    }
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/paletes/palete/' + pID).then(response => {
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

  async ShowChangeStatus (app) {
    var self = this
    var options = []
    var optionstatus = new PaletesStatus('1')
    for (let index = 0; index < optionstatus.options.length; index++) {
      const opt = optionstatus.options[index]
      var permite = false
      switch (opt.value) {
        case '1':
          permite = (self.status.value === '2')
          break
        case '2':
          permite = (self.status.value === '1')
          break
        case '3':
          permite = (self.status.value === '2')
          break
        case '4':
          permite = (self.status.value === '1') || (self.status.value === '2')
          break
      }
      if (opt.value === self.status.value) permite = true
      if (permite) options.push({ label: opt.desc, value: opt.value, color: opt.bgcolor, disable: (opt.value === self.status.value) })
    }

    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Palete #' + self.id,
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
        var eans = []
        for (let index = 0; index < pItens.length; index++) {
          eans.push(pItens[index].etiqueta.ean13)
        }
        var ret = await self.deleteetiquetas(eans)
        if (ret.ok) {
          if (ret.data.palete) await self.cloneFrom(ret.data.palete)
          dialog.hide()
          resolve({ ok: true, data: ret.data ? ret.data : null })
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
      if (!self.unidade) throw new Error('Unidade não foi informada')
      if (!(self.unidade.id > 0)) throw new Error('Unidade não foi informada')
      // if (self.status.value !== '1') throw new Error('Status atual não permite alteração')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {
      descricao: self.descricao,
      unidadeid: self.unidade.id
    }

    if (self.id ? (self.id > 0) : false) {
      params.id = self.id
    }

    let ret = await Vue.prototype.$axios.post('v1/paletes', params).then(response => {
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

  async addetiquetas (pListaEans) {
    var self = this
    try {
      if (!(self.id > 0)) throw new Error('Palete ainda não foi aberto')
      if (self.status.value !== '1') throw new Error('Status atual não permite alteração')
      if (!pListaEans) throw new Error('Nenhum etiqueta informada')
      if (pListaEans.length === 0) throw new Error('Nenhum etiqueta informada')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {
      eans: pListaEans
    }

    let ret = await Vue.prototype.$axios.post('v1/paletes/palete/' + self.id + '/etiquetas/add', params).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        ret.data = data.data
        if (data.ok) {
          ret.ok = true
          if (data.data.palete) self.cloneFrom(data.data.palete)
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async deleteetiquetas (pListaEans) {
    var self = this
    try {
      if (!(self.id > 0)) throw new Error('Palete ainda não foi aberto')
      if (self.status.value !== '1') throw new Error('Status atual não permite alteração')
      if (!pListaEans) throw new Error('Nenhum etiqueta informada')
      if (pListaEans.length === 0) throw new Error('Nenhum etiqueta informada')
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    let params = {
      eans: pListaEans
    }

    let ret = await Vue.prototype.$axios.post('v1/paletes/palete/' + self.id + '/etiquetas/delete', params).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        ret.data = data.data
        if (data.ok) ret.ok = true
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

  // async ShowChangeStatus (app) {
  //   var self = this
  //   try {
  //     if (!(self.id > 0)) throw new Error('Salve o palete antes de continuar')
  //     if (!((self.status.value === '1') || (self.status.value === '2'))) throw new Error('Status atual não permite alteração')
  //   } catch (error) {
  //     return { ok: false, msg: error.message, warning: false }
  //   }
  //   return new Promise((resolve) => {
  //     app.$q.dialog({
  //       parent: app,
  //       component: EditChangeDialog,
  //       palete: self,
  //       cancel: true,
  //       persistent: true
  //     }).onOk(async data => {
  //       if ((data.ok) && (data.dataset)) {
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
      var params = {
        status: newStatus
      }
      ret = await Vue.prototype.$axios.post('v1/paletes/palete/' + self.id + '/status/alterar', params).then(response => {
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

  async getPrintUrl () {
    var self = this
    let params = {
      eans: self.ean13
    }
    let ret = await Vue.prototype.$axios.get('v1/paletes/print', { params: params }).then(response => {
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

export default Palete
