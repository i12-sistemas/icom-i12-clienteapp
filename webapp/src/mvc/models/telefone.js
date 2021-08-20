import Vue from 'vue'
import moment from 'moment'

class Telefone {
  constructor () {
    this.limpardados()
  }

  async limpardados () {
    this.id = null
    this.telefone = ''
    this.contato = ''
    this.updated_at = moment().format('YYYY-MM-DD') + ' 00:00:00'
    this.categ = ''
    this.icon = ''
    this.nordem = 0
  }

  async localPut () {
    Vue.prototype.$indexesDB.db.telefones.put(this).then(function () {
    }).then(function (row) {
    }).catch(function (error) {
      console.error('Ooops: ' + error)
    })
  }

  async cloneFrom (item) {
    this.id = item.id
    this.telefone = item.telefone
    this.contato = item.contato
    this.updated_at = item.updated_at
    this.categ = item.categ
    this.icon = item.icon
    this.nordem = item.nordem
  }

  async localDelete () {
    try {
      if (!this.id) throw new Error('Nenhum registro encontrado pra excluir.')
      if (!(this.id > 0)) throw new Error('Nenhum registro encontrado pra excluir.')
      return await Vue.prototype.$indexesDB.db.telefones.where('id').equals(this.id).delete().then(function (deleteCount) {
        return { ok: true, msg: deleteCount + ' registro(s) excluido(s)' }
      }).catch(function (error) {
        console.error('Ooops: ' + error)
        return { ok: false, msg: error }
      })
    } catch (error) {
      return { ok: false, msg: error.message }
    }
  }
}

export default Telefone
