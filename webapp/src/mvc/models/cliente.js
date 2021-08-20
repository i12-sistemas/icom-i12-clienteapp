import Email from 'src/mvc/models/email.js'
import Usuario from 'src/mvc/models/usuario.js'
import Endereco from 'src/mvc/models/endereco.js'
import Vue from 'vue'
// import DialogAddOrEdit from 'src/pages/cadastro/clientes/editdialog.vue'

class Cliente {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.cnpj = null
    this.razaosocial = ''
    this.razaosocial_old = ''
    this.fantasia_followup = ''
    this.followupid = null
    this.fone1 = ''
    this.fone2 = ''
    this.obs = ''
    this.endereco = new Endereco()
    this.ativo = true
    this.emails = null

    this.segqui_hr1_i = null
    this.segqui_hr1_f = null
    this.segqui_hr2_i = null
    this.segqui_hr2_f = null

    this.sex_hr1_i = null
    this.sex_hr1_f = null
    this.sex_hr2_i = null
    this.sex_hr2_f = null

    this.portaria_hr1_i = null
    this.portaria_hr1_f = null
    this.portaria_hr2_i = null
    this.portaria_hr2_f = null

    this.filtro = ''
    this.prazoentrega = null

    this.created_at = null
    this.updated_at = null
    this.created_usuario = new Usuario()
    this.updated_usuario = new Usuario()
  }

  temhorariosegqui () {
    return this.segqui_hr1_i && this.segqui_hr1_f && this.segqui_hr2_i && this.segqui_hr2_f
  }
  temhorariosex () {
    return this.sex_hr1_i && this.sex_hr1_f && this.sex_hr2_i && this.sex_hr2_f
  }
  temhorarioportaria () {
    return this.portaria_hr1_i && this.portaria_hr1_f && this.portaria_hr2_i && this.portaria_hr2_f
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (item.id) this.id = item.id
    if (item.cnpj) this.cnpj = item.cnpj
    if (item.cnpjmemo) this.cnpjmemo = item.cnpjmemo
    if (item.razaosocial) this.razaosocial = item.razaosocial
    self.razaosocial_old = self.razaosocial
    if (item.fantasia) this.fantasia = item.fantasia
    if (typeof item.fantasia_followup !== 'undefined') this.followupid = item.followupid
    if (item.fantasia_followup) this.fantasia_followup = item.fantasia_followup
    if (item.fone1) this.fone1 = item.fone1
    if (item.fone2) this.fone2 = item.fone2
    if (item.obs) this.obs = item.obs

    if (item.endereco instanceof Endereco) {
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

    if (item.segqui_hr1_i) self.segqui_hr1_i = item.segqui_hr1_i
    if (item.segqui_hr1_f) self.segqui_hr1_f = item.segqui_hr1_f
    if (item.segqui_hr2_i) self.segqui_hr2_i = item.segqui_hr2_i
    if (item.segqui_hr2_f) self.segqui_hr2_f = item.segqui_hr2_f

    if (item.sex_hr1_i) self.sex_hr1_i = item.sex_hr1_i
    if (item.sex_hr1_f) self.sex_hr1_f = item.sex_hr1_f
    if (item.sex_hr2_i) self.sex_hr2_i = item.sex_hr2_i
    if (item.sex_hr2_f) self.sex_hr2_f = item.sex_hr2_f

    if (item.portaria_hr1_i) self.portaria_hr1_i = item.portaria_hr1_i
    if (item.portaria_hr1_f) self.portaria_hr1_f = item.portaria_hr1_f
    if (item.portaria_hr2_i) self.portaria_hr2_i = item.portaria_hr2_i
    if (item.portaria_hr2_f) self.portaria_hr2_f = item.portaria_hr2_f

    if (item.filtro) self.filtro = item.filtro
    if (item.prazoentrega) self.prazoentrega = item.prazoentrega

    self.ativo = Vue.prototype.$helpers.toBool(item.ativo)
    if (item.created_at) self.created_at = item.created_at
    if (item.updated_at) self.updated_at = item.updated_at
    if (item.created_usuario) await self.created_usuario.cloneFrom(item.created_usuario)
    if (item.updated_usuario) await self.updated_usuario.cloneFrom(item.updated_usuario)
    if (item.emails) {
      if (item.emails.length > 0) {
        self.emails = []
        for (let index = 0; index < item.emails.length; index++) {
          const email = item.emails[index]
          let lEmail = new Email(email)
          if (lEmail.email !== '') self.emails.push(lEmail)
        }
      }
    }
  }

  async find (pID) {
    var self = this
    self.limpardados()
    let ret = await Vue.prototype.$axios.get('v1/cliente/' + pID).then(async response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.clientes.save')
      if (!permite.ok) throw new Error(permite.msg)

      if (!self.cnpj) throw new Error('Informe o CNPJ')
      if (!Vue.prototype.$helpers.validarCNPJCPF(self.cnpj)) throw new Error('CNPJ inválido')

      if (!self.razaosocial) throw new Error('Informe a Razão Social')
      if (self.razaosocial.trim() === '') throw new Error('Informe a Razão Social')
      if (!self.fantasia) throw new Error('Informe a Fantasia')
      if (self.fantasia.trim() === '') throw new Error('Informe a Fantasia')
      if (self.fantasia_followup.trim() === '') self.fantasia_followup = self.fantasia.trim()

      var cidadeid = self.endereco.cidade.id > 0 ? self.endereco.cidade.id : null
      if (!cidadeid) throw new Error('Obrigatório informar a cidade')
      if (!(cidadeid > 0)) throw new Error('Obrigatório informar a cidade')
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    let params = {
      id: self.id ? (self.id > 0 ? self.id : null) : null,
      cnpj: self.cnpj,
      razaosocial: self.razaosocial,
      fantasia: self.fantasia,
      fantasia_followup: self.fantasia_followup,
      fone1: self.fone1,
      fone2: self.fone2,
      ativo: self.ativo,
      obs: self.obs,
      logradouro: self.endereco.logradouro,
      endereco: self.endereco.endereco,
      numero: self.endereco.numero,
      bairro: self.endereco.bairro,
      cep: self.endereco.cep,
      complemento: self.endereco.complemento,
      cidadeid: cidadeid
    }

    params.segqui_hr1_i = self.segqui_hr1_i
    params.segqui_hr1_f = self.segqui_hr1_f
    params.segqui_hr2_i = self.segqui_hr2_i
    params.segqui_hr2_f = self.segqui_hr2_f

    if (self.sex_hr1_i) params.sex_hr1_i = self.sex_hr1_i
    if (self.sex_hr1_f) params.sex_hr1_f = self.sex_hr1_f
    if (self.sex_hr2_i) params.sex_hr2_i = self.sex_hr2_i
    if (self.sex_hr2_f) params.sex_hr2_f = self.sex_hr2_f

    if (self.portaria_hr1_i) params.portaria_hr1_i = self.portaria_hr1_i
    if (self.portaria_hr1_f) params.portaria_hr1_f = self.portaria_hr1_f
    if (self.portaria_hr2_i) params.portaria_hr2_i = self.portaria_hr2_i
    if (self.portaria_hr2_f) params.portaria_hr2_f = self.portaria_hr2_f

    var emailsAction = null
    if (self.emails) {
      if (self.emails.length > 0) {
        emailsAction = []
        for (let index = 0; index < self.emails.length; index++) {
          const email = self.emails[index]
          var element = { email: email, action: '' }

          if (email.id > 0) {
            if (email.updated) element.action = 'update'
            if (email.deleted) element.action = 'delete'
          } else {
            element.action = 'insert'
          }
          if (element.action !== '') emailsAction.push(element)
        }
        if (emailsAction.length === 0) emailsAction = null
      }
    }

    if (self.cnpjmemo) params.cnpjmemo = self.cnpjmemo
    if (emailsAction) params.emails = emailsAction

    let ret = await Vue.prototype.$axios.post('v1/cliente', params).then(response => {
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
      var permite = Vue.prototype.$helpers.permite('cadastros.clientes.delete')
      if (!permite.ok) throw new Error(permite.msg)
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    return new Promise((resolve) => {
      app.$q.dialog({
        title: 'Excluir cliente',
        message: 'Para excluir o cliente ' + self.razaosocial.toUpperCase() + ' digite o código ' + self.id + '?',
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
    try {
      var permissao = Vue.prototype.$helpers.permite('cadastros.clientes.delete')
      if (!permissao.ok) return permissao
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    let ret = await Vue.prototype.$axios.delete('v1/cliente/' + self.id).then(response => {
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

  // async dialogAddOrEdit (app) {
  //   var self = this
  //   try {
  //     var permite = null
  //     if (self.id > 0) {
  //       permite = Vue.prototype.$helpers.permite('cadastros.clientes.consulta')
  //     } else {
  //       permite = Vue.prototype.$helpers.permite('cadastros.clientes.save')
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
  //     }).onOk(async retDados => {
  //       var ret = retDados
  //       resolve(ret)
  //     }).onCancel(() => {
  //       resolve({ ok: false })
  //     })
  //   })
  // }
}

export default Cliente
