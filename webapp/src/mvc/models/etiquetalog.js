import Vue from 'vue'
import Usuario from 'src/mvc/models/usuario.js'
import CargaEntrada from 'src/mvc/models/cargaentrada.js'
import CargaTransfer from 'src/mvc/models/cargatransfer.js'
import CargaEntrega from 'src/mvc/models/cargaentrega.js'
import Palete from 'src/mvc/models/palete.js'
import { EtiquetaLogAction, EtiquetaLogOrigem } from './enums/cargastypes'

class EtiquetaLog {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.nordem = null
    this.ean13 = null
    this.created_at = null
    this.created_usuario = new Usuario()
    this.origem = null
    this.origemid = null
    this.action = null
    this.detalhe = null
  }

  get superdetalhe () {
    var ret = null
    var unidadesaida = null
    var motorista = null
    var veiculo = null
    switch (this.origem.value) {
      case 'cargaentradaitem':
        if (this.detalhe) {
          ret = ((this.detalhe.detalhe ? this.detalhe.detalhe !== '' : false) ? '<div><b>' + this.detalhe.detalhe + '</b></div>' : '') +
                '<div>Carga de Entrada: <b>' + this.detalhe.cargaid + '</b></div>' +
                '<div>Data da entrada: <b>' + Vue.prototype.$helpers.datetimeToBR(this.detalhe.cargadataentrada, true, true) + '</b></div>' +
                '<div>Coleta: <b>' + (this.detalhe.coletaid ? this.detalhe.coletaid : 'Nenhum coleta associada') + '</b></div>' +
                '<div>Chave da Nota: <b>' + this.detalhe.nfechave + '</b></div>'
        }
        break
      case 'cargatransferitem':
        if (this.detalhe) {
          unidadesaida = this.detalhe.unidadesaida
          if (unidadesaida) {
            unidadesaida = unidadesaida.cidade.cidade + '/' + unidadesaida.cidade.uf + ' Cód.: ' + unidadesaida.id + ' - ' + unidadesaida.fantasia
          } else {
            unidadesaida = 'Nenhum unidade associada'
          }

          var unidadeentrada = this.detalhe.unidadeentrada
          if (unidadeentrada) {
            unidadeentrada = unidadeentrada.cidade.cidade + '/' + unidadeentrada.cidade.uf + ' Cód.: ' + unidadeentrada.id + ' - ' + unidadeentrada.fantasia
          } else {
            unidadeentrada = 'Nenhum unidade associada'
          }

          motorista = this.detalhe.motorista
          if (motorista) {
            motorista = motorista.nome
          } else {
            motorista = 'Nenhum motorista'
          }
          veiculo = this.detalhe.veiculo
          if (veiculo) {
            veiculo = veiculo.placa + ' - ' + veiculo.descricao
          } else {
            veiculo = 'Nenhum veículo'
          }
          ret = ((this.detalhe.detalhe ? this.detalhe.detalhe !== '' : false) ? '<div><b>' + this.detalhe.detalhe + '</b></div>' : '') +
                '<div>Carga #: <b>' + this.detalhe.cargaid + '</b></div>' +
                '<div>Data criação: <b>' + Vue.prototype.$helpers.datetimeToBR(this.detalhe.cargacreated_at, true, true) + '</b></div>' +
                '<div>Unidade de saída: <b>' + unidadesaida + '</b></div>' +
                '<div>Unidade de entrada: <b>' + unidadeentrada + '</b></div>' +
                '<div>Motorista: <b>' + motorista + '</b></div>' +
                '<div>Veículo: <b>' + veiculo + '</b></div>'
        }
        break
      case 'cargaentregaitem':
        if (this.detalhe) {
          unidadesaida = this.detalhe.unidadesaida
          if (unidadesaida) {
            unidadesaida = unidadesaida.cidade.cidade + '/' + unidadesaida.cidade.uf + ' Cód.: ' + unidadesaida.id + ' - ' + unidadesaida.fantasia
          } else {
            unidadesaida = 'Nenhum unidade associada'
          }

          motorista = this.detalhe.motorista
          if (motorista) {
            motorista = motorista.nome
          } else {
            motorista = 'Nenhum motorista'
          }
          veiculo = this.detalhe.veiculo
          if (veiculo) {
            veiculo = veiculo.placa + ' - ' + veiculo.descricao
          } else {
            veiculo = 'Nenhum veículo'
          }
          ret = ((this.detalhe.detalhe ? this.detalhe.detalhe !== '' : false) ? '<div><b>' + this.detalhe.detalhe + '</b></div>' : '') +
                '<div>Carga #: <b>' + this.detalhe.cargaid + '</b></div>' +
                '<div>Data criação: <b>' + Vue.prototype.$helpers.datetimeToBR(this.detalhe.cargacreated_at, true, true) + '</b></div>' +
                '<div>Data da saída: <b>' + (this.detalhe.cargasaidadh ? Vue.prototype.$helpers.datetimeToBR(this.detalhe.cargasaidadh, true, true) : '-') + '</b></div>' +
                '<div>Data da entrega: <b>' + (this.detalhe.cargaentregadh ? Vue.prototype.$helpers.datetimeToBR(this.detalhe.cargaentregadh, true, true) : '-') + '</b></div>' +
                '<div>Unidade de saída: <b>' + unidadesaida + '</b></div>' +
                '<div>Motorista: <b>' + motorista + '</b></div>' +
                '<div>Veículo: <b>' + veiculo + '</b></div>'
        }
        break
      case 'paleteitem':
        if (this.detalhe) {
          ret = this.detalhe
          var unidade = this.detalhe.unidade
          if (unidade) {
            unidade = unidade.cidade.cidade + '/' + unidade.cidade.uf + ' Cód.: ' + unidade.id + ' - ' + unidade.fantasia
          } else {
            unidade = 'Nenhum unidade associada'
          }

          ret = ((this.detalhe.detalhe ? this.detalhe.detalhe !== '' : false) ? '<div><b>' + this.detalhe.detalhe + '</b></div>' : '') +
                '<div>Palete #: <b>' + this.detalhe.paleteid + '</b></div>' +
                '<div>Descrição: <b>' + this.detalhe.paletedescricao + '</b></div>' +
                '<div>Data criação: <b>' + Vue.prototype.$helpers.datetimeToBR(this.detalhe.paletecreated_at, true, true) + '</b></div>' +
                '<div>Volume total: <b>' + this.detalhe.volqtde + '</b></div>' +
                '<div>Peso total: <b>' + this.detalhe.pesototal + '</b></div>' +
                '<div>Unidade: <b>' + unidade + '</b></div>' +
                '<div>Status: <b>' + this.detalhe.status + '</b></div>'
        }
        break

      default:
        break
    }
    return ret
  }

  get origemcarga () {
    var ret = null
    switch (this.origem.value) {
      case 'cargaentradaitem':
        ret = { id: this.detalhe.cargaid, origem: 'entrada' }
        break
      case 'cargatransferitem':
        ret = { id: this.detalhe.cargaid, origem: 'transferencia' }
        break
      case 'cargaentregaitem':
        ret = { id: this.detalhe.cargaid, origem: 'entrega' }
        break
      case 'paleteitem':
        ret = { id: this.detalhe.paleteid, origem: 'palete' }
        break

      default:
        break
    }
    return ret
  }

  async EditCarga (app) {
    var self = this
    var origemcarga = self.origemcarga
    if (!origemcarga) return

    var ret = null
    if (origemcarga.origem === 'entrada') {
      var cargaEntrada = new CargaEntrada()
      cargaEntrada.id = origemcarga.id
      ret = await cargaEntrada.ShowAddOrEdit(app)
    } else if (origemcarga.origem === 'transferencia') {
      var cargaTransfer = new CargaTransfer()
      cargaTransfer.id = origemcarga.id
      ret = await cargaTransfer.ShowAddOrEdit(app)
    } else if (origemcarga.origem === 'entrega') {
      var cargaEntrega = new CargaEntrega()
      cargaEntrega.id = origemcarga.id
      ret = await cargaEntrega.ShowAddOrEdit(app)
    } else if (origemcarga.origem === 'palete') {
      var palete = new Palete()
      palete.id = origemcarga.id
      ret = await palete.ShowAddOrEdit(app)
    }
    return ret
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (typeof item.nordem !== 'undefined') self.nordem = item.nordem
    if (typeof item.id !== 'undefined') self.id = item.id
    if (typeof item.detalhe !== 'undefined') {
      if (typeof item.detalhe === 'object') {
        self.detalhe = item.detalhe
      }
      if (typeof item.detalhe === 'string') {
        try {
          self.detalhe = JSON.parse(item.detalhe)
        } catch (error) {
        }
      }
    }
    if (typeof item.ean13 !== 'undefined') self.ean13 = item.ean13
    if (typeof item.created_at !== 'undefined') self.created_at = item.created_at
    if (typeof item.created_usuario !== 'undefined') self.created_usuario = new Usuario(item.created_usuario)
    if (typeof item.origem !== 'undefined') self.origem = new EtiquetaLogOrigem(item.origem)
    if (typeof item.origemid !== 'undefined') self.origemid = item.origemid
    if (typeof item.action !== 'undefined') self.action = new EtiquetaLogAction(item.action)
  }
}

export default EtiquetaLog
