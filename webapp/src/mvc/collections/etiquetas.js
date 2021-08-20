import Vue from 'vue'
import Etiqueta from 'src/mvc/models/etiqueta.js'
// import DialogConsultaImport from 'src/pages/operacional/cargas/etiquetas/consulta-import-dialog.vue'

class Etiquetas {
  constructor () {
    this.limpardados()
    this.pagination = { page: 1, rowsPerPage: 20, sortBy: 'created_at', descending: true, rowsNumber: 0 }
  }

  async limpardados () {
    this.itens = null
  }

  readPropsTable (props) {
    if (!props) return
    const { page, rowsPerPage, sortBy, descending, rowsNumber } = props.pagination
    const filter = props.filter
    if (this.filter !== filter) this.page = 1
    this.pagination = { page: page, rowsPerPage: rowsPerPage, sortBy: sortBy, descending: descending, rowsNumber: rowsNumber }
    this.filter = filter
  }

  async loadmore () {
    this.pagination.page = parseInt(this.pagination.page) + 1
    var ret = await this.fetch(true)
    return ret
  }

  async fetch (pContinuos = false) {
    var self = this
    if (!pContinuos) self.limpardados()
    let params = {
      perpage: self.pagination.rowsPerPage,
      page: self.pagination.page
    }
    // if (self.filter) {
    //   if (self.filter !== '') params['find'] = self.filter
    // }
    // if (self.id !== null) params['id'] = self.id
    if (typeof self.showall !== 'undefined') params['showall'] = self.showall
    if (typeof self.somentedisponivel !== 'undefined') params['somentedisponivel'] = self.somentedisponivel ? 1 : 0
    if (typeof self.unidadeatualid !== 'undefined') params['unidadeatualid'] = self.unidadeatualid
    // if (self.cidadedestino !== null) params['cidadedestino'] = self.cidadedestino.join(',')
    // if (self.cidadedestino !== null) params['cidadedestino'] = self.cidadedestino.join(',')
    // if (self.status !== null) params['status'] = self.status === 'A' ? 0 : 1
    // if (self.motorista !== null) params['motorista'] = self.motorista.join(',')
    // if (self.veiculo !== null) params['veiculo'] = self.veiculo.join(',')
    // if (self.dhacertoi !== null) params['dhacertoi'] = Vue.prototype.$helpers.strDateToFormated(self.dhacertoi, 'YYYY/MM/DD', 'YYYY-MM-DD')
    // if (self.dhacertof !== null) params['dhacertof'] = Vue.prototype.$helpers.strDateToFormated(self.dhacertof, 'YYYY/MM/DD', 'YYYY-MM-DD')
    // if (self.createdati !== null) params['createdati'] = Vue.prototype.$helpers.strDateToFormated(self.createdati, 'YYYY/MM/DD', 'YYYY-MM-DD')
    // if (self.createdatf !== null) params['createdatf'] = Vue.prototype.$helpers.strDateToFormated(self.createdatf, 'YYYY/MM/DD', 'YYYY-MM-DD')

    // if (self.orderby) params['orderby'] = JSON.stringify(self.orderby)

    let ret = await Vue.prototype.$axios.get('v1/etiquetas', { params: params }).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          data = data.data
          self.total = data.total ? parseInt(data.total) : 0
          // don't forget to update local pagination object
          self.pagination.page = data.current_page
          self.pagination.rowsPerPage = parseInt(data.per_page)
          self.pagination.sortBy = data.sortby ? data.sortby : ''
          self.pagination.descending = (data.descending === 'desc')
          self.pagination.rowsNumber = data.total ? parseInt(data.total) : 0
          ret.ok = true
          if ((!pContinuos) || (!self.itens)) self.itens = []
          for (let index = 0; index < data.rows.length; index++) {
            const element = data.rows[index]
            let p = new Etiqueta(element)
            self.itens.push(p)
          }
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
      eans: self.eans.join(',')
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

  // async showConsultaImport (app, pUnidadeFilter = null) {
  //   var self = this
  //   try {
  //     // var permite = Vue.prototype.$helpers.permite('operacional.coletas.save')
  //     // if (!permite.ok) throw new Error(permite.msg)
  //   } catch (error) {
  //     return { ok: false, msg: error.message, warning: false }
  //   }

  //   return new Promise((resolve) => {
  //     app.$q.dialog({
  //       parent: app,
  //       component: DialogConsultaImport,
  //       dataset: self,
  //       unidadefiltrostart: pUnidadeFilter,
  //       cancel: true
  //     }).onOk(async data => {
  //       resolve({ ok: true, data: data })
  //     }).onCancel(() => {
  //       resolve({ ok: false })
  //     })
  //   })
  // }
}
export default Etiquetas
