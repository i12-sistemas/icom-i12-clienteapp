import Vue from 'vue'
import Cidade from 'src/mvc/models/cidade.js'

class Endereco {
  constructor (pItem) {
    this.t = new Date().getTime()
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.logradouro = 'RUA'
    this.endereco = ''
    this.numero = ''
    this.cep = ''
    this.bairro = ''
    this.complemento = ''
    this.cidade = new Cidade()
  }

  async isValid (pCEPObrigatorio = true, pBairroObrigatorio = true) {
    var erros = []

    this.logradouro = this.logradouro.trim()
    this.endereco = this.endereco.trim()
    this.numero = this.numero.trim()
    this.cep = this.cep.trim()
    this.bairro = this.bairro.trim()
    this.complemento = this.complemento.trim()

    if ((!this.logradouro) || (this.logradouro === '')) erros.push('Logradouro vazio')
    if ((!this.endereco) || (this.endereco === '')) erros.push('Endereço vazio')
    // if ((!this.numero) || (this.numero === '')) erros.push('Número do endereço vazio')
    if ((this.cep.length > 0) && (this.cep.length < 8)) erros.push('CEP inválido')
    if (((!this.cep) || (this.cep.length !== 8)) && pCEPObrigatorio) erros.push('CEP vazio ou inválido')
    if (((!this.bairro) || (this.bairro.length === '')) && pBairroObrigatorio) erros.push('Bairro vazio')
    if ((!this.cidade) || !(this.cidade.id > 0)) erros.push('Cidade inválida')
    return { ok: erros.length <= 0, msg: erros.join(', ') }
  }

  get enderecocompleto () {
    if ((this.endereco === '') || !(this.cidade.id > 0)) return ''
    return ((this.logradouro !== '') ? this.logradouro + ' ' : '') + this.endereco +
          ((this.numero !== '') ? ', ' + this.numero : '') +
          (this.cidade.id > 0 ? ', ' + this.cidade.cidade + ', ' + this.cidade.uf : '')
  }

  get endereconumeroecep () {
    if ((this.endereco === '') || !(this.cidade.id > 0)) return ''
    return ((this.logradouro !== '') ? this.logradouro + ' ' : '') + this.endereco +
          ((this.numero !== '') ? ', ' + this.numero : '') +
          ((this.cep !== '') ? ' - CEP: ' + Vue.prototype.$helpers.mascaraCEP(this.cep) : '')
  }

  get enderecosemcidade () {
    return ((this.logradouro !== '') ? this.logradouro + ' ' : '') + this.endereco +
          ((this.numero !== '') ? ', ' + this.numero : '') +
          ((this.bairro !== '') ? ' - Bairro: ' + this.bairro : '') +
          ((this.complemento !== '') ? ' - Complemento: ' + this.complemento : '') +
          ((this.cep !== '') ? ' - CEP: ' + Vue.prototype.$helpers.mascaraCEP(this.cep) : '')
  }

  get enderecocompletotext () {
    if ((this.endereco === '') || !(this.cidade.id > 0)) return ''
    return ((this.logradouro !== '') ? this.logradouro + ' ' : '') + this.endereco +
          ((this.numero !== '') ? ', ' + this.numero : '') +
          ((this.complemento !== '') ? ' - Complemento: ' + this.complemento : '') +
          ((this.cep !== '') ? ' - CEP: ' + Vue.prototype.$helpers.mascaraCEP(this.cep) : '') +
          (this.cidade.id > 0 ? ', ' + this.cidade.cidade + ', ' + this.cidade.uf : '')
  }

  async cloneFrom (item) {
    var self = this
    await self.limpardados()
    if (!item) return
    if (item.logradouro) self.logradouro = item.logradouro
    if (item.endereco) self.endereco = item.endereco
    if (item.numero) self.numero = item.numero
    if (item.cep) self.cep = item.cep
    if (item.bairro) self.bairro = item.bairro
    if (item.complemento) self.complemento = item.complemento
    if (item.t) self.t = item.t
    if (item.cidade) self.cidade = new Cidade(item.cidade)
    return true
  }

  async applyCEP (pCEP) {
    if (!pCEP) return
    var self = this
    self.logradouro = pCEP.logradouro
  }

  async splitLogradouroEndereco (pEnderecoCompleto) {
    var ret = { logradouro: '', endereco: '' }
    if (!pEnderecoCompleto) return ret
    ret.endereco = pEnderecoCompleto.toUpperCase()
    var logradouros = ['AEROPORTO', 'ALAMEDA', 'ÁREA', 'AREA', 'AVENIDA', 'AV.', 'AV', 'CAMPO', 'CHÁCARA', 'CHACARA', 'COLÔNIA', 'COLONIA', 'CONDOMÍNIO', 'CONDOMINIO', 'CONJUNTO', 'DISTRITO', 'ESPLANADA', 'ESTAÇÃO', 'ESTACAO', 'ESTRADA', 'FAVELA', 'FAZENDA', 'FAZ.', 'FAZ', 'FEIRA', 'JARDIM', 'LADEIRA', 'LAGO', 'LAGOA', 'LARGO', 'LOTEAMENTO', 'MORRO', 'NÚCLEO', 'NUCLEO', 'OUTROS', 'PARQUE', 'PASSARELA', 'PÁTIO', 'PATIO', 'PRAÇA', 'PRACA', 'QUADRA', 'RECANTO', 'RESIDENCIAL', 'RODOVIA', 'ROD.', 'ROD', 'ROTATÓRIA', 'ROTATORIA', 'RUA', 'R', 'R.', 'SETOR', 'SÍTIO', 'SITIO', 'TRAVESSA', 'TRAV', 'TRAV.', 'TRECHO', 'TREVO', 'VALE', 'VEREDA', 'VIA', 'VIADUTO', 'VIELA', 'VILA']
    var logradourosCorreto = ['AEROPORTO', 'ALAMEDA', 'ÁREA', 'ÁREA', 'AVENIDA', 'AVENIDA', 'AVENIDA', 'CAMPO', 'CHÁCARA', 'CHÁCARA', 'COLÔNIA', 'COLÔNIA', 'CONDOMÍNIO', 'CONDOMÍNIO', 'CONJUNTO', 'DISTRITO', 'ESPLANADA', 'ESTAÇÃO', 'ESTAÇÃO', 'ESTRADA', 'FAVELA', 'FAZENDA', 'FAZENDA', 'FAZENDA', 'FEIRA', 'JARDIM', 'LADEIRA', 'LAGO', 'LAGOA', 'LARGO', 'LOTEAMENTO', 'MORRO', 'NÚCLEO', 'NÚCLEO', 'OUTROS', 'PARQUE', 'PASSARELA', 'PÁTIO', 'PÁTIO', 'PRAÇA', 'PRAÇA', 'QUADRA', 'RECANTO', 'RESIDENCIAL', 'RODOVIA', 'RODOVIA', 'RODOVIA', 'ROTATÓRIA', 'ROTATÓRIA', 'RUA', 'RUA', 'RUA', 'SETOR', 'SÍTIO', 'SÍTIO', 'TRAVESSA', 'TRAVESSA', 'TRAVESSA', 'TRECHO', 'TREVO', 'VALE', 'VEREDA', 'VIA', 'VIADUTO', 'VIELA', 'VILA']
    for (let index = 0; index < logradouros.length; index++) {
      const tipo = logradouros[index].toUpperCase()
      var idx = ret.endereco.indexOf(tipo)
      if (idx >= 0) {
        ret.logradouro = logradourosCorreto[index].toUpperCase()
        ret.endereco = ret.endereco.replace(tipo, '').trim()
        break
      }
    }
    return ret
  }

  async consultaCEP (pCEP) {
    var self = this
    var cep = Vue.prototype.$helpers.clearMask(pCEP)
    try {
      if (cep.length !== 8) throw new Error('CEP deve ter 8 dígitos')
    } catch (error) {
      return { ok: false, msg: error.message }
    }
    let ret = await Vue.prototype.$axios.get('v1/viacep/' + cep).then(async response => {
      let data = response.data
      var ret = { ok: false, msg: '' }
      if (data) {
        ret.msg = data.msg ? data.msg : ''
        if (data.ok) {
          ret.ok = true
          data = data.data
          var separaEndereco = await self.splitLogradouroEndereco(data.logradouro)
          data.endereco = separaEndereco.endereco
          data.logradouro = separaEndereco.logradouro
          ret.data = data
        }
      }
      return ret
      // let data = response.data
      // var ret = { ok: false, msg: '' }
      // try {
      //   if (!data) throw new Error('Nenhum registro encontrado')
      //   if (data.erro) throw new Error('Nenhum cidade informada')
      //   ret.ok = true
      //   var separaEndereco = await self.splitLogradouroEndereco(data.logradouro)
      //   data.endereco = separaEndereco.endereco
      //   data.logradouro = separaEndereco.logradouro
      //   ret.data = data
      // } catch (error) {
      //   ret.msg = error.message
      // }
      // return ret
    }).catch(error => {
      return { ok: false, msg: 'Erro ao consultar CEP - ' + error }
    })
    return ret
  }
}

export default Endereco
