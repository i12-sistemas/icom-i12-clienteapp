import Vue from 'vue'
import CargaEntrega from 'src/mvc/models/cargaentrega.js'
import dialogfilter from 'src/pages/cargas/entrega/dialog-filter.vue'

class CargasEntregas {
  constructor () {
    this.limpardados()
    this.paginationDefault()
    this.resetParams()
  }

  async limpardados () {
    this.itens = null
  }

  async setUnidadePadrao (app) {
    var unidade = await Vue.prototype.$helpers.getUnidadeLogada(app)
    this.params['unidade'] = unidade
  }

  resetParams () {
    this.params = {
      status: ['1', '2', '3']
    }
  }
  paginationDefault () {
    this.pagination = { page: 1, rowsPerPage: 50, sortBy: 'created_at', descending: false, rowsNumber: 0 }
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

  async fetch (pLoadMore = false) {
    var self = this
    if (!pLoadMore) {
      self.limpardados()
    } else {
      self.pagination.page = self.pagination.page + 1
    }
    let params = {
      showall: self.showall ? 1 : 0,
      perpage: self.pagination.rowsPerPage,
      page: self.pagination.page
    }

    if (self.ids) {
      if (self.ids !== null) params['ids'] = self.ids.join(',')
    }
    if (self.filter) {
      if ((self.filter !== null) && (self.filter !== '')) params['find'] = self.filter
    }
    if (self.params) {
      for (var prop in self.params) {
        var value = self.params[prop]
        if (value !== null && value !== '') params[prop] = value
      }
    }

    if (self.params.unidade ? self.params.unidade.id > 0 : false) {
      params['unidadesaidaid'] = self.params.unidade.id
    }
    delete params['unidade']

    if (self.params.status ? self.params.status.length > 0 : false) {
      params['status'] = self.params.status.join(',')
    }

    if (self.orderby !== null) params['orderby'] = JSON.stringify(self.orderby)

    let ret = await Vue.prototype.$axios.get('v1/cargaentrega', { params: params }).then(response => {
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
          if ((!pLoadMore) || (!self.itens)) self.itens = []
          for (let index = 0; index < data.rows.length; index++) {
            const element = data.rows[index]
            let p = new CargaEntrega(element)
            if (p.id > 0) self.itens.push(p)
          }
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async ShowDialogFilter (app, config) {
    var self = this
    return new Promise((resolve) => {
      app.$q.dialog({
        parent: app,
        component: dialogfilter,
        config: self.params,
        cancel: true
      }).onOk(async filter => {
        if (filter) {
          if (!self.params) self.params = {}
          self.params['status'] = filter.status
          self.params['created_at'] = filter.created_at
          self.params['origem'] = filter.origem
          self.params['unidade'] = filter.unidade
          self.params['erros'] = filter.erros
          resolve({ ok: true, filter: filter })
        } else {
          resolve({ ok: false })
        }
      }).onCancel(() => {
        resolve({ ok: false })
      })
    })
  }
}
export default CargasEntregas
