import Vue from 'vue'
import Usuario from 'src/mvc/models/usuario.js'
import Endereco from 'src/mvc/models/endereco.js'

class Unidade {
  constructor (pItem) {
    this.ignoreuser = true
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.razaosocial = ''
    this.fantasia = ''
    this.fantasia_old = ''
    this.cnpj = ''
    this.ie = ''
    this.fone = ''
    this.ativo = true
    this.endereco = new Endereco()
    this.created_at = null
    this.updated_at = null
    if (this.ignoreuser) {
      delete this.created_usuario
      delete this.updated_usuario
    } else {
      this.created_usuario = new Usuario()
      this.updated_usuario = new Usuario()
    }
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/unidade/' + pID).then(response => {
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

  async save () {
    var self = this
    try {
      var permite = Vue.prototype.$helpers.permite('cadastros.unidades.save')
      if (!permite.ok) throw new Error(permite.msg)
      // if (!self.cidade) throw new Error('Nenhum cidade informada')
      // if (!(self.cidade.id > 0)) throw new Error('Nenhum cidade informada')
      // if (!self.tipo) throw new Error('Nenhum tipo informado')
      // if (!(self.tipo.id > 0)) throw new Error('Nenhum tipo informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: true }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      cnpj: self.cnpj,
      ie: self.ie,
      fone: self.fone,
      razaosocial: self.razaosocial,
      fantasia: self.fantasia,
      ativo: self.ativo,
      logradouro: self.endereco.logradouro,
      endereco: self.endereco.endereco,
      numero: self.endereco.numero,
      bairro: self.endereco.bairro,
      cep: self.endereco.cep,
      complemento: self.endereco.complemento,
      cidadeid: self.endereco.cidade.id > 0 ? self.endereco.cidade.id : null
    }
    let ret = await Vue.prototype.$axios.post('v1/unidade', params).then(response => {
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

  async deleteWithQuestion (app) {
    var self = this
    try {
      var permite = Vue.prototype.$helpers.permite('cadastros.unidades.delete')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir unidade',
        message: 'Para excluir a unidade ' + self.fantasia.toUpperCase() + ' digite o código ' + self.id + '?',
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

    let ret = await Vue.prototype.$axios.delete('v1/unidade/' + self.id).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          self.limpardados()
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (item.id) self.id = item.id
    if (item.razaosocial) self.razaosocial = item.razaosocial
    if (item.fantasia) self.fantasia = item.fantasia
    self.fantasia_old = item.fantasia
    if (item.cnpj) self.cnpj = item.cnpj
    if (item.ie) self.ie = item.ie
    if (item.fone) self.fone = item.fone

    if (item.endereco ? typeof item.endereco === 'object' : false) {
      await self.endereco.cloneFrom(item.endereco)
    } else {
      if (item.logradouro) self.endereco.logradouro = item.logradouro
      if (item.endereco) self.endereco.endereco = item.endereco
      if (item.numero) self.endereco.numero = item.numero
      if (item.bairro) self.endereco.bairro = item.bairro
      if (item.cep) self.endereco.cep = item.cep
      if (item.complemento) self.endereco.complemento = item.complemento
      if (item.cidade) await self.endereco.cidade.cloneFrom(item.cidade)
    }

    self.ativo = Vue.prototype.$helpers.toBool(item.ativo)
    if (item.created_at) self.created_at = item.created_at
    if (item.updated_at) self.updated_at = item.updated_at
    if (!self.ignoreuser) {
      if (typeof item.created_usuario !== 'undefined') self.created_usuario = new Usuario(item.created_usuario)
      if (typeof item.updated_usuario !== 'undefined') self.updated_usuario = new Usuario(item.updated_usuario)
    }
  }
}

export default Unidade
