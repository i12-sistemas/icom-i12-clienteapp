import Vue from 'vue'
import VeiculoTipo from 'src/mvc/models/veiculotipo.js'
import Cidade from 'src/mvc/models/cidade.js'
import Usuario from 'src/mvc/models/usuario.js'
// import EditDialog from 'src/pages/cadastro/veiculos/edit-dialog'

class Veiculo {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    delete this.ultimokmacerto
    this.id = null
    this.descricao = ''
    this.placa = ''
    this.placa_old = ''
    this.proprietario = 'T'
    this.tara = 0
    this.lotacao = 0
    this.pbt = 0
    this.pbtc = 0
    this.mediaconsumo = 0
    this.ativo = true
    this.manutencao = false
    this.created_at = null
    this.updated_at = null
    this.alertamanut = null
    this.ultimokm = null
    this.ultimokmdhcheck = null
    this.cidade = new Cidade()
    this.tipo = new VeiculoTipo()
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/veiculo/' + pID).then(response => {
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

  async getUltimoKmAcerto () {
    var self = this
    let ret = await Vue.prototype.$axios.get('v1/veiculo/' + self.id + '/ultimokmacerto').then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          self.ultimokmacerto = data.data
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
      var permite = Vue.prototype.$helpers.permite('cadastros.veiculos.save')
      if (!permite.ok) throw new Error(permite.msg)

      if (!self.cidade) throw new Error('Nenhum cidade informada')
      if (!(self.cidade.id > 0)) throw new Error('Nenhum cidade informada')
      if (!self.tipo) throw new Error('Nenhum tipo informado')
      if (!(self.tipo.id > 0)) throw new Error('Nenhum tipo informado')

      if (!self.tara) self.tara = 0
      if (!self.lotacao) self.lotacao = 0
      if (!self.pbt) self.pbt = 0
      if (!self.pbtc) self.pbtc = 0

      if (self.tara < 0) throw new Error('Valor da tara inválido')
      if (self.lotacao < 0) throw new Error('Valor da lotação inválido')
      if (self.pbt < 0) throw new Error('Valor do PBT inválido')
      if (self.pbtc < 0) throw new Error('Valor do PBTC inválido')
      if (self.mediaconsumo < 0) throw new Error('Valor da média de consumo inválido')
    } catch (error) {
      return { ok: false, msg: error.message, warning: true }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      descricao: self.descricao,
      placa: self.placa,
      ativo: self.ativo,
      tara: self.tara,
      lotacao: self.lotacao,
      pbt: self.pbt,
      pbtc: self.pbtc,
      mediaconsumo: self.mediaconsumo,
      proprietario: self.proprietario,
      manutencao: self.manutencao,
      cidadeid: self.cidade.id > 0 ? self.cidade.id : null,
      tipoid: self.tipo.id > 0 ? self.tipo.id : null
    }
    let ret = await Vue.prototype.$axios.post('v1/veiculo', params).then(response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.veiculos.delete')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir veiculo?',
        message: 'Para excluir o veiculo ' + self.regiao + ' digite o código ' + self.id + '?',
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

    let ret = await Vue.prototype.$axios.delete('v1/veiculo/' + self.id).then(response => {
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
    if (item.descricao) self.descricao = item.descricao
    if (item.placa) self.placa = item.placa
    if (item.tara) self.tara = parseFloat(item.tara)
    if (item.lotacao) self.lotacao = parseFloat(item.lotacao)
    if (item.pbt) self.pbt = parseFloat(item.pbt)
    if (item.pbtc) self.pbtc = parseFloat(item.pbtc)
    if (typeof item.mediaconsumo !== 'undefined') self.mediaconsumo = parseFloat(item.mediaconsumo)
    if (item.ultimokm) self.ultimokm = parseInt(item.ultimokm)
    if (item.ultimokmdhcheck) self.ultimokmdhcheck = item.ultimokmdhcheck
    self.placa_old = item.placa
    self.ativo = Vue.prototype.$helpers.toBool(item.ativo)
    self.manutencao = Vue.prototype.$helpers.toBool(item.manutencao)
    if (item.alertamanut) self.alertamanut = item.alertamanut
    if (item.proprietario) self.proprietario = item.proprietario
    if (item.created_at) self.created_at = item.created_at
    if (item.updated_at) self.updated_at = item.updated_at
    if (item.cidade) await self.cidade.cloneFrom(item.cidade)
    if (item.tipo) await self.tipo.cloneFrom(item.tipo)
    if (item.updated_usuario) await self.updated_usuario.cloneFrom(item.updated_usuario)
    if (item.updated_usuario) await self.updated_usuario.cloneFrom(item.updated_usuario)
  }

  // async edit (app) {
  //   var self = this
  //   try {
  //     var permite = null
  //     if (self.id > 0) {
  //       permite = Vue.prototype.$helpers.permite('cadastros.veiculos.consulta')
  //     } else {
  //       permite = Vue.prototype.$helpers.permite('cadastros.veiculos.save')
  //     }
  //     if (!permite.ok) throw new Error(permite.msg)
  //   } catch (error) {
  //     return { ok: false, msg: error.message, warning: false }
  //   }
  //   return new Promise((resolve) => {
  //     app.$q.dialog({
  //       parent: app,
  //       adding: self.id ? !(self.id > 0) : true,
  //       idstart: self.id,
  //       component: EditDialog,
  //       cancel: true
  //     }).onOk(async data => {
  //       resolve({ ok: data })
  //     }).onCancel(() => {
  //       resolve({ ok: false })
  //     })
  //   })
  // }
}

export default Veiculo
