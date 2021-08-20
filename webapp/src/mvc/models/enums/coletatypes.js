const enumsColetaEncerramentoTipo = [
  { value: '1', desc: 'Interno' },
  { value: '2', desc: 'App do motorista' },
  { value: '3', desc: 'Painel do cliente' },
  { value: '4', desc: 'Reabertura de orçamento' }
]

const enumsColetaSituacao = [
  { value: '0', desc: 'Coleta bloqueada (Sem liberação)', icon: 'warning', color: 'red' },
  { value: '1', desc: 'Coleta aberta', icon: 'local_shipping', color: 'orange' },
  { value: '2', desc: 'Coleta encerrada', icon: 'check_circle', color: 'green' },
  { value: '3', desc: 'Coleta cancelada', icon: 'cancel', color: 'red' }
]

const enumsColetaOrigem = [
  { value: '1', desc: 'Interna', icon: 'trip_origin', color: 'grey' },
  { value: '2', desc: 'Orçamento', icon: 'trip_origin', color: 'blue' },
  { value: '3', desc: 'Painel do cliente', icon: 'trip_origin', color: 'orange' },
  { value: '4', desc: 'Avulsa (App Motorista)', icon: 'trip_origin', color: 'purple' }
]

export const enumsOrcamentoSituacao = [
  { value: '0', desc: 'Em aberto', icon: 'event_note', color: 'grey' },
  { value: '1', desc: 'Aprovado (Coleta bloqueada)', icon: 'thumb_up', color: 'orange' },
  { value: '2', desc: 'Aprovado e liberado', icon: 'thumb_up', color: 'blue' },
  { value: '3', desc: 'Reprovado cancelada', icon: 'thumb_down', color: 'red' }
]

export class ColetaEncerramentoTipo {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsColetaEncerramentoTipo.length; index++) {
      const element = enumsColetaEncerramentoTipo[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get value () {
    if (this._value) {
      return this._value.value
    } else {
      return null
    }
  }

  toString () {
    if (this._value) {
      return this._value.value + ' - ' + this.description
    } else {
      return this.description
    }
  }

  get description () {
    if (this._value) {
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class ColetaSituacaoTipo {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsColetaSituacao.length; index++) {
      const element = enumsColetaSituacao[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get value () {
    if (this._value) {
      return this._value.value
    } else {
      return null
    }
  }

  get icon () {
    if (this._value) {
      return this._value.icon
    } else {
      return null
    }
  }

  get color () {
    if (this._value) {
      return this._value.color
    } else {
      return null
    }
  }

  toString () {
    if (this._value) {
      return this._value.value
    } else {
      return null
    }
  }

  get description () {
    if (this._value) {
      return this._value.value + ' - ' + this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class ColetaOrigemTipo {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsColetaOrigem.length; index++) {
      const element = enumsColetaOrigem[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get value () {
    if (this._value) {
      return this._value.value
    } else {
      return null
    }
  }

  get icon () {
    if (this._value) {
      return this._value.icon
    } else {
      return null
    }
  }

  get color () {
    if (this._value) {
      return this._value.color
    } else {
      return null
    }
  }

  toString () {
    if (this._value) {
      return this._value.value
    } else {
      return null
    }
  }

  get description () {
    if (this._value) {
      return this._value.value + ' - ' + this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class OrcamentoSituacaoTipo {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsOrcamentoSituacao.length; index++) {
      const element = enumsOrcamentoSituacao[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get value () {
    if (this._value) {
      return this._value.value
    } else {
      return null
    }
  }

  get icon () {
    if (this._value) {
      return this._value.icon
    } else {
      return null
    }
  }

  get color () {
    if (this._value) {
      return this._value.color
    } else {
      return null
    }
  }

  toString () {
    if (this._value) {
      return this._value.value
    } else {
      return null
    }
  }

  get description () {
    if (this._value) {
      return this._value.value + ' - ' + this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}
