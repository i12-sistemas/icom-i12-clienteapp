import Vue from 'vue'
import CargaEntrada from 'src/mvc/models/cargaentrada.js'
import dialogfilter from 'src/pages/cargas/entrada/dialog-filter.vue'
// import ChangeMotorista from 'src/pages/operacional/coletas/trocarapida/cpn-change-motorista'

class CargasEntradas {
  constructor () {
    this.limpardados()
    this.paginationDefault()
    this.resetParams()
  }

  async setUnidadePadrao (app) {
    var unidade = await Vue.prototype.$helpers.getUnidadeLogada(app)
    this.params['unidade'] = unidade
  }

  resetParams () {
    this.params = {
      status: '1'
    }
  }
  paginationDefault () {
    this.pagination = { page: 1, rowsPerPage: 50, sortBy: 'created_at', descending: false, rowsNumber: 0 }
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
          self.params['tipo'] = filter.tipo
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
    if (self.params.unidade ? self.params.unidade.id > 0 : false) params['unidadeentradaid'] = self.params.unidade.id
    delete params['unidade']

    if (self.orderby !== null) params['orderby'] = JSON.stringify(self.orderby)

    let ret = await Vue.prototype.$axios.get('v1/cargaentrada', { params: params }).then(response => {
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
            let p = new CargaEntrada(element)
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

  // async savemass (data) {
  //   // var self = this
  //   try {
  //     // self.peso = parseFloat(self.peso)
  //     // if (!(self.peso >= 0)) throw new Error('Peso inválido')
  //   } catch (error) {
  //     return { ok: false, msg: error.message, warning: false }
  //   }
  //   let params = {
  //     data: data
  //   }

  //   let ret = await Vue.prototype.$axios.post('v1/coletas/updatemass', params).then(response => {
  //     let data = response.data
  //     var ret = { ok: false, msg: '' }
  //     if (data) {
  //       ret.msg = data.msg ? data.msg : ''
  //       if (data.ok) {
  //         ret.ok = true
  //         // if (data.data) self.cloneFrom(data.data)
  //       }
  //     }
  //     return ret
  //   }).catch(error => {
  //     return Vue.prototype.$helpers.errorReturn(error)
  //   })
  //   return ret
  // }

  // async changeMotoristaShow (app, pIDs, pStartMotoristaID) {
  //   var self = this

  //   var arrayIds = []
  //   if (typeof pIDs === 'number') {
  //     arrayIds.push(pIDs)
  //   } else {
  //     arrayIds = pIDs
  //   }

  //   return new Promise((resolve) => {
  //     app.$q.dialog({
  //       parent: app,
  //       component: ChangeMotorista,
  //       idstart: pStartMotoristaID,
  //       coletasids: arrayIds,
  //       cancel: true
  //     }).onOk(async data => {
  //       app.$q.dialog({
  //         title: 'Confirmar alteração?',
  //         message: '<p>' + (arrayIds.length <= 4 ? (arrayIds.length === 1 ? 'Coleta ' + arrayIds.join(', ') + ' será atualizada' : 'Coletas ' + arrayIds.join(', ') + ' serão atualizadas') : arrayIds.length + ' coletas serão atualizadas') +
  //         (data ? '<p><p>para o novo motorista<div><b>' + data.nome + '</b></div></p>' : '<p><p>para <div><b>** SEM MOTORISTA DEFINIDO **</b></div></p>'),
  //         html: true,
  //         class: 'bg-primary text-white',
  //         ok: {
  //           unelevated: true,
  //           color: 'primary',
  //           label: 'Confirmar'
  //         },
  //         cancel: {
  //           unelevated: true,
  //           color: 'primary',
  //           label: 'Sair'
  //         }
  //       }).onOk(async dataquestao => {
  //         var datasent = []
  //         for (let index = 0; index < arrayIds.length; index++) {
  //           const element = arrayIds[index]
  //           datasent.push({ id: element, motoristaid: data ? data.id : null })
  //         }
  //         var ret = await self.savemass(datasent)
  //         resolve(ret)
  //       }).onCancel(() => {
  //         resolve({ ok: false })
  //       })
  //     }).onCancel(() => {
  //       resolve({ ok: false })
  //     })
  //   })
  // }
}
export default CargasEntradas
