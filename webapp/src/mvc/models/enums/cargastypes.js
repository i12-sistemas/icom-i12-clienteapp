const enumsCargaEntradaTipo = [
  { value: '1', desc: 'Motorista', color: 'green', icon: 'local_shipping' },
  { value: '2', desc: 'Cliente', color: 'cyan', icon: 'accessibility' }
]

const enumsCargaEntradaStatus = [
  { value: '1', desc: 'Em edição', color: 'orange', icon: 'folder_open' },
  { value: '2', desc: 'Encerrado', color: 'blue', icon: 'check' }
]
const enumsCargaEntradaItemProcessamento = [
  { value: '1', desc: 'Auto', color: 'blue', icon: 'auto_fix_high' },
  { value: '2', desc: 'Manual', color: 'deep-orange', icon: 'pan_tool' }
]
const enumsEtiquetaStatus = [
  { value: '1', desc: 'Depósito', color: 'grey-9', icon: 'bookmark' },
  { value: '2', desc: 'Em Tranferência', color: 'grey-9', icon: 'bookmark' },
  { value: '3', desc: 'Em Entrega', color: 'grey-9', icon: 'bookmark' },
  { value: '4', desc: 'Entregue', color: 'grey-9', icon: 'bookmark' },
  { value: '5', desc: 'Extraviado', color: 'grey-9', icon: 'bookmark' }
]
const enumsCargaTransferStatus = [
  { value: '1', desc: 'Em edição', memo: 'Em edição permite alteração', color: 'blue', icon: 'edit' },
  { value: '2', desc: 'Liberado', memo: 'Bloqueia edição e libera carga para transporte', color: 'orange', icon: 'published_with_changes' },
  { value: '3', desc: 'Trânsito', memo: 'Carga em processo de transferência', color: 'red', icon: 'local_shipping' },
  { value: '4', desc: 'Encerrado', memo: 'Processo encerrado', color: 'green', icon: 'check_circle' }
]

const enumsCargaEntregaStatus = [
  { value: '1', desc: 'Em edição', memo: 'Em edição permite alteração', color: 'blue', icon: 'edit' },
  { value: '2', desc: 'Liberado', memo: 'Bloqueia edição e libera carga para transporte', color: 'orange', icon: 'published_with_changes' },
  { value: '3', desc: 'Trânsito', memo: 'Carga em processo de entrega', color: 'red', icon: 'local_shipping' },
  { value: '4', desc: 'Entregue', memo: 'Processo encerrado', color: 'green', icon: 'check_circle' }
]

const enumsEtiquetaLogAction = [
  { value: 'add', desc: 'Inclusão', memo: 'Inclusão do item na carga', color: 'blue', icon: 'add' },
  { value: 'delete', desc: 'Exclusão', memo: 'Exclusão do item da carga', color: 'red', icon: 'clear' },
  { value: 'update', desc: 'Alteração', memo: 'Alteração do carga associada', color: 'green', icon: 'edit' }
]

const enumsEtiquetaLogOrigem = [
  { value: 'cargaentradaitem', desc: 'Entrada', memo: 'Carga de entrega', color: 'purple', icon: 'double_arrow' },
  { value: 'cargatransferitem', desc: 'Transferência', memo: 'Carga de transferência', color: 'green', icon: 'swap_horiz' },
  { value: 'cargaentregaitem', desc: 'Entrega', memo: 'Carga de entrega', color: 'blue', icon: 'local_shipping' },
  { value: 'paleteitem', desc: 'Palete', memo: 'Palete de volumes', color: 'indigo', icon: 'widgets' }
]

const enumsPaletesStatus = [
  { value: '1', desc: 'Em aberto', memo: 'Em edição', color: 'white', bgcolor: 'blue-grey', icon: 'edit' },
  { value: '2', desc: 'Lacrado', memo: 'Lacrado não permite edição', color: 'white', bgcolor: 'blue-10', icon: 'lock' },
  { value: '3', desc: 'Despachado', memo: 'Despachado para outra unidade (processo irreversível)', color: 'white', bgcolor: 'green', icon: 'local_shipping' },
  { value: '4', desc: 'Cancelado', memo: 'Palete desfeito (processo irreversível)', color: 'white', bgcolor: 'red-10', icon: 'clear' }
]

export class CargaEntradaTipo {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  get options () {
    return enumsCargaEntradaTipo
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsCargaEntradaTipo.length; index++) {
      const element = enumsCargaEntradaTipo[index]
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class CargaEntradaStatus {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsCargaEntradaStatus.length; index++) {
      const element = enumsCargaEntradaStatus[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get options () {
    return enumsCargaEntradaStatus
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class CargaEntradaItemProcessamento {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsCargaEntradaItemProcessamento.length; index++) {
      const element = enumsCargaEntradaItemProcessamento[index]
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class EtiquetaStatus {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsEtiquetaStatus.length; index++) {
      const element = enumsEtiquetaStatus[index]
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class CargaTransferStatus {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsCargaTransferStatus.length; index++) {
      const element = enumsCargaTransferStatus[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get options () {
    return enumsCargaTransferStatus
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

  get memo () {
    if (this._value) {
      return this._value.memo
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class CargaEntregaStatus {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsCargaEntregaStatus.length; index++) {
      const element = enumsCargaEntregaStatus[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get options () {
    return enumsCargaEntregaStatus
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

  get memo () {
    if (this._value) {
      return this._value.memo
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class EtiquetaLogAction {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsEtiquetaLogAction.length; index++) {
      const element = enumsEtiquetaLogAction[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get options () {
    return enumsEtiquetaLogAction
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

  get memo () {
    if (this._value) {
      return this._value.memo
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class EtiquetaLogOrigem {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsEtiquetaLogOrigem.length; index++) {
      const element = enumsEtiquetaLogOrigem[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get options () {
    return enumsEtiquetaLogAction
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

  get memo () {
    if (this._value) {
      return this._value.memo
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}

export class PaletesStatus {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsPaletesStatus.length; index++) {
      const element = enumsPaletesStatus[index]
      if (element.value === v) {
        this._value = element
      }
    }
  }

  get options () {
    return enumsPaletesStatus
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

  get bgcolor () {
    if (this._value) {
      return this._value.bgcolor
    } else {
      return null
    }
  }

  get memo () {
    if (this._value) {
      return this._value.memo
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
      return this._value.desc
    } else {
      return 'Indefinido'
    }
  }
}
