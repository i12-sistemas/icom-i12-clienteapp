import Vue from 'vue'
import Veiculo from 'src/mvc/models/veiculo.js'

class Veiculos {
  constructor () {
    this.limpardados()
    this.showall = false
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
      perpage: self.pagination.rowsPerPage,
      page: self.pagination.page,
      sortby: self.pagination.sortBy,
      descending: (self.pagination.descending === true)
    }
    if ((self.proprietario !== null) && (self.proprietario !== '')) params['proprietario'] = self.proprietario
    if (BuscarSomentePorCodigo) params['buscarporcodigo'] = true
    if (self.ids) {
      if (self.ids !== null) params['ids'] = self.ids.join(',')
    }
    if (self.showall) {
      if (self.showall !== null) params['showall'] = self.showall ? 1 : 0
    }
    if (self.manutencao) {
      if (self.manutencao.length > 0) params['manutencao'] = self.manutencao.join(',')
    }

    let ret = await Vue.prototype.$axios.get('v1/veiculo', { params: params }).then(response => {
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
            let p = new Veiculo(element)
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
export default Veiculos
