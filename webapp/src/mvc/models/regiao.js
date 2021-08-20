import Vue from 'vue'
import Cidade from 'src/mvc/models/cidade.js'
import Usuario from 'src/mvc/models/usuario.js'

class Regiao {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.regiao = ''
    this.regiao_old = ''
    this.cidadescount = 0
    this.sugerirmotorista = false
    this.created_at = null
    this.updated_at = null
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()
    this.cidadespivot = null
    delete this.resumosugestao
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/regiao/' + pID).then(response => {
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

  async fechtCidades (force = false) {
    var self = this
    self.cidadespivot = null
    let ret = await Vue.prototype.$axios.get('v1/regiao/' + self.id + '/cidades').then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          if (data.data) {
            if (data.data.length > 0) {
              self.cidadespivot = []
              for (let index = 0; index < data.data.length; index++) {
                const lcidadespivot = data.data[index]
                let lItem = new Cidade()
                await lItem.cloneFrom(lcidadespivot)
                if (lItem.id > 0) {
                  self.cidadespivot.push({ pivot: (lcidadespivot.pivot ? lcidadespivot.pivot : null), cidade: lItem, id: lItem.id })
                }
              }
            }
          }
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
      var permite = Vue.prototype.$helpers.permite('cadastros.regiao.save')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      regiao: self.regiao,
      sugerirmotorista: self.sugerirmotorista
    }
    let ret = await Vue.prototype.$axios.post('v1/regiao', params).then(response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.regiao.delete')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir região',
        message: 'Para excluir a região ' + self.regiao + ' digite o código ' + self.id + '?',
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

    let ret = await Vue.prototype.$axios.delete('v1/regiao/' + self.id).then(response => {
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

  async cidadesDelete (pListaIDs) {
    var self = this
    try {
      var permite = Vue.prototype.$helpers.permite('cadastros.regiao.save')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    let params = {
      cidadesids: pListaIDs
    }
    let ret = await Vue.prototype.$axios.post('v1/regiao/' + self.id + '/cidades/delete', params).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true

          for (let index = 0; index < pListaIDs.length; index++) {
            for (let n = 0; n < self.cidadespivot.length; n++) {
              const cidade = self.cidadespivot[n].cidade
              if (cidade.id === pListaIDs[index]) {
                self.cidadespivot.splice(n, 1)
                break
              }
            }
          }
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  async cidadesAdd (pListaCidades) {
    var self = this
    try {
      var permite = Vue.prototype.$helpers.permite('cadastros.regiao.save')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    var ids = []
    for (let index = 0; index < pListaCidades.length; index++) {
      const cidade = pListaCidades[index]
      if (cidade.id > 0) ids.push(cidade.id)
    }
    let params = {
      cidadesids: ids
    }
    var ret = await Vue.prototype.$axios.post('v1/regiao/' + self.id + '/cidades/add', params).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          await self.fechtCidades(true)
          self.cidadescount = self.cidadespivot ? self.cidadespivot.length : 0
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
    if (item.regiao) self.regiao = item.regiao.toUpperCase()
    self.regiao_old = self.regiao.toUpperCase()
    if (item.cidadescount) self.cidadescount = item.cidadescount
    if (item.sugerirmotorista) self.sugerirmotorista = Vue.prototype.$helpers.toBool(item.sugerirmotorista)
    if (item.created_at) self.created_at = item.created_at
    if (item.updated_at) self.updated_at = item.updated_at

    if (item.created_usuario) self.created_usuario = new Usuario(item.created_usuario)
    if (item.updated_usuario) self.updated_usuario = new Usuario(item.updated_usuario)
  }
}

export default Regiao
