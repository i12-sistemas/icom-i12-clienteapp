import moment from 'moment'
import Usuario from 'src/mvc/models/usuario.js'
import Cidade from 'src/mvc/models/cidade.js'
import Veiculo from 'src/mvc/models/veiculo.js'
import Vue from 'vue'
// import DialogAddOrEdit from 'src/pages/cadastro/motoristas/editdialog.vue'

class Motorista {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.username = null
    this.nome = ''
    this.nome_old = ''
    this.apelido = ''
    this.fone = ''
    this.cpf = null
    this.newpwd = null
    this.senhainformada = false
    this.gerenciamento = 0
    this.gerenciamentooutros = ''
    this.antt = ''
    this.ativo = true
    this.habilitado = false
    this.salario = 0
    this.cnhvencimento = null
    this.moppvencimento = null
    this.cidade = new Cidade()
    this.veiculo = new Veiculo()

    this.created_at = null
    this.updated_at = null
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()

    this.alertas = null
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (item.id) self.id = item.id
    if (item.username) self.username = item.username
    if (item.nome) self.nome = item.nome
    self.nome_old = item.nome
    if (item.apelido) self.apelido = item.apelido
    if (item.fone) self.fone = item.fone
    if (item.cpf) self.cpf = item.cpf
    if (item.gerenciamento) self.gerenciamento = item.gerenciamento
    if (item.gerenciamentooutros) self.gerenciamentooutros = item.gerenciamentooutros
    if (item.antt) self.antt = item.antt
    if (item.cnhvencimento) self.cnhvencimento = item.cnhvencimento
    if (item.moppvencimento) self.moppvencimento = item.moppvencimento
    if (item.salario) self.salario = parseFloat(item.salario)

    self.habilitado = Vue.prototype.$helpers.toBool(item.habilitado)
    self.ativo = Vue.prototype.$helpers.toBool(item.ativo)
    if (item.cidade) await self.cidade.cloneFrom(item.cidade)
    if (item.veiculo) await self.veiculo.cloneFrom(item.veiculo)

    if (item.created_at) self.created_at = item.created_at
    if (item.updated_at) self.updated_at = item.updated_at
    if (item.created_usuario) await self.created_usuario.cloneFrom(item.created_usuario)
    if (item.updated_usuario) await self.updated_usuario.cloneFrom(item.updated_usuario)

    self.alertas = []
    if (self.ativo) {
      if (!self.habilitado) this.alertas.push('Motorista sem habilitação')
      if ((!self.cnhvencimento) || (self.cnhvencimento === '')) {
        this.alertas.push('Data da CNH inválida')
      } else {
        var cnhtest = await Vue.prototype.$helpers.strToMoment(self.cnhvencimento)
        if (cnhtest < moment()) this.alertas.push('CNH vencida em ' + Vue.prototype.$helpers.dateToBR(cnhtest))
      }
      if ((!self.moppvencimento) || (self.moppvencimento === '')) {
        this.alertas.push('MOPP inválido')
      } else {
        var mopptest = await Vue.prototype.$helpers.strToMoment(self.moppvencimento)
        if (mopptest < moment()) this.alertas.push('MOPP vencido em ' + Vue.prototype.$helpers.dateToBR(mopptest))
      }
    }
    if (self.alertas.length === 0) self.alertas = null
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/motorista/' + pID).then(response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.motoristas.save')
      if (!permite.ok) throw new Error(permite.msg)

      var senha = null
      if (self.senhainformada) {
        permite = Vue.prototype.$helpers.permite('cadastros.motoristas.resetsenhaapp')
        if (!permite.ok) throw new Error(permite.msg)
        senha = self.newpwd ? self.newpwd : ''
        if (senha.length < 3) throw new Error('Senha deve ter no mínimo 3 caracteres')
      }
      // if (!self.nome) throw new Error('Nome do produto não foi informado')
      // if (self.nome.length < 2) throw new Error('Nome do produto deve ter no mínimo 2 caracteres')
      // self.onu = parseInt(self.onu)
      // if (!(self.onu > 0)) throw new Error('Número ONU inválida')
    } catch (error) {
      return { ok: false, msg: error.message, warning: true }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      nome: self.nome,
      apelido: self.apelido,
      fone: self.fone,
      ativo: self.ativo,
      gerenciamento: self.gerenciamento,
      gerenciamentooutros: self.gerenciamentooutros,
      antt: self.antt,
      cnhvencimento: self.cnhvencimento,
      moppvencimento: self.moppvencimento,
      habilitado: self.habilitado
    }
    if (self.cpf) params.cpf = self.cpf
    if (self.username) params.username = self.username
    if (self.salario) params.salario = self.salario
    if (self.cidade) {
      if (self.cidade.id > 0) params.cidadeid = self.cidade.id
    }
    if (self.veiculo) {
      if (self.veiculo.id > 0) params.veiculoid = self.veiculo.id
    }
    if ((self.senhainformada) && (senha)) params.senha = Vue.prototype.$helpers.md5(senha)
    let ret = await Vue.prototype.$axios.post('v1/motorista', params).then(response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.motoristas.delete')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message, warning: false }
    }

    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir motorista',
        message: 'Para excluir o motorista "' + self.nome + '" digite o código ' + self.id + '?',
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

    let ret = await Vue.prototype.$axios.delete('v1/motorista/' + self.id).then(response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
        }
      }
      return ret
    }).catch(error => {
      return Vue.prototype.$helpers.errorReturn(error)
    })
    return ret
  }

  // async dialogAddOrEdit (app) {
  //   var self = this
  //   try {
  //     var permite = null
  //     if (self.id > 0) {
  //       permite = Vue.prototype.$helpers.permite('cadastros.motoristas.consulta')
  //     } else {
  //       permite = Vue.prototype.$helpers.permite('cadastros.motoristas.save')
  //     }
  //     if (!permite.ok) throw new Error(permite.msg)
  //   } catch (error) {
  //     return { ok: false, msg: error.message, warning: false }
  //   }
  //   return new Promise((resolve) => {
  //     app.$q.dialog({
  //       parent: app,
  //       component: DialogAddOrEdit,
  //       dataset: self,
  //       adding: !(self.id > 0),
  //       cancel: true
  //     }).onOk(async retOk => {
  //       var ret = { ok: retOk }
  //       resolve(ret)
  //     }).onCancel(() => {
  //       resolve({ ok: false })
  //     })
  //   })
  // }
}

export default Motorista
