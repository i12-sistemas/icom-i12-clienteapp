import Vue from 'vue'
import Usuario from 'src/mvc/models/usuario.js'
import Regiao from 'src/mvc/models/regiao.js'

class Cidade {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.id_uf = null
    this.uf = ''
    this.ativo = true
    this.estado = ''
    this.cidade = ''
    this.cidade_old = ''
    this.codigo_ibge = null
    this.latitude = null
    this.longitude = null
    this.created_at = null
    this.updated_at = null
    this.regiao = new Regiao()
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/cidade/' + pID).then(response => {
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

  async findByCodigoIBGE (pCodigoIBGE, pCidade, pUF) {
    var self = this
    let params = {
      find: '',
      showall: 1,
      perpage: 1,
      page: 1
    }
    if (pCodigoIBGE) params.codigoibge = pCodigoIBGE
    if (pCidade) params.cidade = pCidade
    if (pUF) params.uf = pUF
    let ret = await Vue.prototype.$axios.get('v1/cidade', { params: params }).then(async response => {
      let data = response.data
      self.limpardados()
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          data = data.data
          ret.ok = true
          if (data.rows.length > 0) {
            await self.cloneFrom(data.rows[0])
          }
        }
      }
      return ret
    }).catch(error => {
      self.limpardados()
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async cloneFrom (pItem) {
    var self = this
    await self.limpardados()
    if (!pItem) return
    if (pItem.id) self.id = pItem.id
    if (pItem.id_uf) self.id_uf = pItem.id_uf
    if (pItem.uf) self.uf = pItem.uf
    if (pItem.codigo_ibge) self.codigo_ibge = pItem.codigo_ibge
    if (pItem.estado) self.estado = pItem.estado.toUpperCase()
    if (pItem.cidade) self.cidade = pItem.cidade.toUpperCase()
    self.cidade_old = pItem.cidade.toUpperCase()
    self.ativo = Vue.prototype.$helpers.toBool(pItem.ativo)
    if (pItem.latitude) self.latitude = pItem.latitude
    if (pItem.longitude) self.longitude = pItem.longitude
    if (pItem.created_at) self.created_at = pItem.created_at
    if (pItem.updated_at) self.updated_at = pItem.updated_at
    if (pItem.regiao) await self.regiao.cloneFrom(pItem.regiao)
    if (pItem.created_usuario) await self.created_usuario.cloneFrom(pItem.created_usuario)
    if (pItem.updated_usuario) await self.updated_usuario.cloneFrom(pItem.updated_usuario)
    return true
  }

  async save () {
    var self = this
    try {
      var permite = Vue.prototype.$helpers.permite('cadastros.cidades.save')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      codigo_ibge: self.codigo_ibge,
      cidade: self.cidade,
      regiaoid: (self.regiao ? (self.regiao.id > 0 ? self.regiao.id : null) : null),
      uf: self.uf,
      ativo: self.ativo ? 1 : 0
    }
    let ret = await Vue.prototype.$axios.post('v1/cidade', params).then(response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.cidades.delete')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir cidade',
        message: 'Para excluir a cidade ' + self.cidade + ' digite o código ' + self.codigo_ibge + '?',
        prompt: {
          model: '',
          type: 'text' // optional
        },
        cancel: true
      }).onOk(async data => {
        if (parseInt(data) === parseInt(self.codigo_ibge)) {
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

    let ret = await Vue.prototype.$axios.delete('v1/cidade/' + self.id).then(response => {
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

export default Cidade
