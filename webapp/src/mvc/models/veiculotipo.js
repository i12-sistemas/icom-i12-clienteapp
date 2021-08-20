import Vue from 'vue'
import Usuario from 'src/mvc/models/usuario.js'

class VeiculoTipo {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.tipo = ''
    this.ativo = true
    this.created_at = null
    this.updated_at = null
    this.veiculoscount = 0
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (item.id) self.id = item.id
    if (item.tipo) self.tipo = item.tipo
    if (item.veiculoscount) self.veiculoscount = item.veiculoscount
    self.ativo = Vue.prototype.$helpers.toBool(item.ativo)
    if (item.created_at) self.created_at = item.created_at
    if (item.updated_at) self.updated_at = item.updated_at
    if (item.created_usuario) await self.created_usuario.cloneFrom(item.created_usuario)
    if (item.updated_usuario) await self.updated_usuario.cloneFrom(item.updated_usuario)
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/veiculotipo/' + pID).then(response => {
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

  async edit (app) {
    var self = this
    var ltipo = self.tipo
    try {
      var permite = Vue.prototype.$helpers.permite('cadastros.veiculotipo.save')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }
    return new Promise((resolve) => {
      Vue.prototype.$q.dialog({
        title: (self.id > 0) ? 'Editar tipo de veículo' : 'Novo tipo de veículo',
        message: 'Informe o tipo do veículo',
        cancel: true,
        prompt: {
          model: ltipo
        }
      }).onOk(async data => {
        var ret = await self.save(data)
        resolve(ret)
      }).onCancel(() => {
        resolve({ ok: false })
      })
    })
  }

  async save (lTipo) {
    var self = this
    try {
      var permite = Vue.prototype.$helpers.permite('cadastros.veiculotipo.save')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message, warning: true }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      tipo: lTipo
    }
    let ret = await Vue.prototype.$axios.post('v1/veiculotipo', params).then(response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.veiculotipo.delete')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir tipo de veículo',
        message: 'Para excluir o tipo "' + self.tipo + '" digite o código ' + self.id + '?',
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

    let ret = await Vue.prototype.$axios.delete('v1/veiculotipo/' + self.id).then(response => {
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

export default VeiculoTipo
