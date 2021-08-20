import Vue from 'vue'
import Cliente from 'src/mvc/models/cliente.js'

class Clientes {
  constructor () {
    this.limpardados()
    this.paginationDefault()
    this.showall = false
  }

  paginationDefault () {
    this.pagination = { page: 1, rowsPerPage: 50, sortBy: 'razao', descending: false, rowsNumber: 0 }
  }

  async limpardados () {
    this.itens = null
  }

  readPropsTable (props) {
    this.paginationDefault()
    if (!props) return
    const { page, rowsPerPage, sortBy, descending, rowsNumber } = props.pagination
    const filter = props.filter
    if (this.filter !== filter) this.page = 1
    this.pagination = { page: page, rowsPerPage: rowsPerPage, sortBy: sortBy, descending: descending, rowsNumber: rowsNumber }
    this.filter = filter
  }

  async makequery () {
    var self = this
    try {
      var query = {}
      query.t = new Date().getTime()
      if (self.pagination.page !== null && self.pagination.page > 1) query.page = self.pagination.page
      if ((self.pagination.rowsPerPage !== null) && (self.pagination.rowsPerPage > 0) && (parseInt(self.pagination.rowsPerPage) !== 20)) query.pagesize = self.pagination.rowsPerPage
      if (self.orderby !== null) query.sortby = JSON.stringify(self.orderby)

      if (self.params) {
        for (var prop in self.params) {
          var value = self.params[prop]
          if (value !== null && value !== '') query[prop] = value
        }
      }
      return query
    } catch (error) {
      return null
    }
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

    if (self.orderby !== null) params['orderby'] = JSON.stringify(self.orderby)

    let ret = await Vue.prototype.$axios.get('v1/cliente', { params: params }).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          data = data.data
          self.total = data.total ? parseInt(data.total) : 0
          // don't forget to update local pagination object
          this.pagination.page = data.current_page
          this.pagination.rowsPerPage = data.per_page
          // this.pagination.sortBy = data.sortby ? data.sortby : ''
          this.pagination.descending = (data.descending === true)
          this.pagination.rowsNumber = data.total ? parseInt(data.total) : 0

          ret.ok = true

          if ((!pLoadMore) || (!self.itens)) self.itens = []
          for (let index = 0; index < data.rows.length; index++) {
            const element = data.rows[index]
            let p = new Cliente(element)
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
}
export default Clientes
