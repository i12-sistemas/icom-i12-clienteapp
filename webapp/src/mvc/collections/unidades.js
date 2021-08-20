import Vue from 'vue'
import Unidade from 'src/mvc/models/unidade.js'

class Unidades {
  constructor () {
    this.limpardados()
    this.pagination = { page: 1, rowsPerPage: 20, sortBy: 'nome', descending: false, rowsNumber: 0 }
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

  async fetch (BuscarSomentePorCodigo) {
    var self = this
    self.limpardados()
    let params = {
      find: self.filter,
      showall: self.showall ? 1 : 0,
      perpage: self.pagination.rowsPerPage,
      page: self.pagination.page,
      sortby: self.pagination.sortBy,
      descending: (self.pagination.descending === true)
    }
    if (BuscarSomentePorCodigo) params['buscarporcodigo'] = true

    let ret = await Vue.prototype.$axios.get('v1/unidade', { params: params }).then(response => {
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
          self.itens = []
          for (let index = 0; index < data.rows.length; index++) {
            const element = data.rows[index]
            let p = new Unidade(element)
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
export default Unidades
