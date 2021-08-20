import Vue from 'vue'
import Unidade from 'src/mvc/models/unidade.js'
class Usuario {
  constructor (pItem, pIgnoreChild = false) {
    this.child_ignored = pIgnoreChild
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.nome = ''
    this.login = ''
    this.ativo = true
    this.email = ''
    this.fotourl = null
    this.created_at = null
    this.updated_at = null
    this.unidades = null
    this.unidadeprincipal = null
    if (!this.child_ignored) {
      this.created_usuario = new Usuario(null, true)
      this.updated_usuario = new Usuario(null, true)
    }
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/usuarios/usuario/' + pID).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          await self.cloneFrom(data.data)
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
      // if (!self.cidade) throw new Error('Nenhum cidade informada')
      // if (!(self.cidade.id > 0)) throw new Error('Nenhum cidade informada')
      // if (!self.tipo) throw new Error('Nenhum tipo informado')
      // if (!(self.tipo.id > 0)) throw new Error('Nenhum tipo informado')
    } catch (error) {
      return { ok: false, msg: error.message, warning: true }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      unidadeprincipalid: self.unidadeprincipal ? (self.unidadeprincipal.id > 0 ? self.unidadeprincipal.id : null) : null,
      login: self.login,
      nome: self.nome,
      email: self.email,
      ativo: self.ativo,
      fotourl: self.fotourl
    }
    let ret = await Vue.prototype.$axios.post('v1/usuarios', params).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          if (data.data) await self.cloneFrom(data.data)
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async permite (pIDPermissao) {
    try {
      var self = this
      if (!self.permissoes) throw new Error('Sem permissao')
      if (self.permissoes.length === 0) throw new Error('Sem permissao')

      var idx = await self.permissoes.findIndex((element) => {
        return (element.idpermissao === pIDPermissao)
      })
      return (idx ? idx >= 0 : false)
    } catch (error) {
      return false
    }
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (typeof item.id !== 'undefined') self.id = parseInt(item.id)
    if (item.nome) self.nome = item.nome.toString().toUpperCase()
    self.nome_old = item.nome.toString().toUpperCase()
    if (item.login) self.login = item.login.toString().toUpperCase()
    self.ativo = Vue.prototype.$helpers.toBool(item.ativo)
    if (item.email) self.email = item.email.toString().toLowerCase()
    if (item.fotourl) self.fotourl = item.fotourl
    if (item.created_at) self.created_at = item.created_at
    if (item.updated_at) self.updated_at = item.updated_at
    if (typeof item.unidadeprincipal !== 'undefined') {
      self.unidadeprincipal = new Unidade()
      self.unidadeprincipal.ignoreuser = true
      await self.unidadeprincipal.cloneFrom(item.unidadeprincipal)
    }
    if (typeof item.unidades !== 'undefined') {
      if (item.unidades) {
        self.unidades = []
        for (let index = 0; index < item.unidades.length; index++) {
          const element = item.unidades[index]
          element.unidade = new Unidade(element.unidade)
          element.created_usuario = new Usuario(element.created_usuario)
          self.unidades.push(element)
        }
      }
    }
    if (!this.child_ignored) {
      if ((item.created_usuario) && (self.created_usuario)) await self.created_usuario.cloneFrom(item.created_usuario)
      if ((item.updated_usuario) && (self.updated_usuario)) await self.updated_usuario.cloneFrom(item.updated_usuario)
    }
  }

  async deleteWithQuestion (app) {
    var self = this
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir usuário',
        message: 'Para excluir o usuário ' + self.nome.toUpperCase() + ' digite o código ' + self.id + '?',
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

    let ret = await Vue.prototype.$axios.delete('v1/usuarios/usuario/' + self.id).then(response => {
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
}

export default Usuario
