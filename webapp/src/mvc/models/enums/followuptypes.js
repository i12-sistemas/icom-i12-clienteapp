const enumsStatusErrosColeta = [
  { value: '0', desc: 'Sem status', color: 'grey' },
  { value: '1', desc: 'OK', color: 'green' },
  { value: '2', desc: 'Com erro', color: 'red' }
]

const enumsStatusErrosAgenda = [
  { value: '0', desc: 'Sem status', color: 'grey' },
  { value: '1', desc: 'OK', color: 'green' },
  { value: '2', desc: 'Com erro', color: 'red' }
]

const enumsStatusErrosDtPromessa = [
  { value: '0', desc: 'Sem status', color: 'grey' },
  { value: '1', desc: 'OK', color: 'green' },
  { value: '2', desc: 'Com erro', color: 'red' }
]

const enumsStatusInicioFup = [
  { value: '0', desc: 'Sem status', color: 'grey' },
  { value: '1', desc: 'Conecta', color: 'orange' },
  { value: '2', desc: 'Fornecedor', color: 'blue' }
]

const enumsStatusConfirmacaoColeta = [
  { value: '0', desc: 'Sem status', color: 'grey' },
  { value: '1', desc: 'Ok', color: 'green' },
  { value: '2', desc: 'Erro', color: 'red' }
]

const enumsStatusPlanilhaProcessamento = [
  { value: 0, icon: 'schedule', desc: 'Aguardando', color: 'grey' },
  { value: 1, icon: 'check_circle', desc: 'OK', color: 'green' },
  { value: 2, icon: 'autorenew', desc: 'Processando', color: 'blue', spinner: true },
  { value: 3, icon: 'error', desc: 'Erro', color: 'red' }
]

const enumsFileFupTipoExport = [
  { value: 1, icon: 'schedule', desc: 'Por data de importação', color: 'blue' },
  { value: 2, icon: 'schedule', desc: 'Por data de atualização FUP', color: 'indigo' },
  { value: 3, icon: 'done_all', desc: 'Todas as linhas (Ignorar período)', color: 'red' },
  { value: 3, icon: 'done_all', desc: 'Sincronizar banco - Histórico completo', color: 'red-10' }
]

const enumsFUPLogTipoOrigem = [
  { value: 1, icon: 'edit', label: 'Edição (M)', desc: 'Alteração manual pelo usuário', color: 'blue' },
  { value: 2, icon: 'add_circle_outline', label: 'Novo (I)', desc: 'Novo registro através da importação', color: 'green' },
  { value: 3, icon: 'rotate_90_degrees_ccw', label: 'Edição (I)', desc: 'Alteração de registro através da importação', color: 'orange' }
]

export class FollowUpStatusErrosColeta {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsStatusErrosColeta.length; index++) {
      const element = enumsStatusErrosColeta[index]
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

  // get icon () {
  //   if (this._value) {
  //     return this._value.icon
  //   } else {
  //     return null
  //   }
  // }

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

export class FollowUpStatusErrosAgenda {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsStatusErrosAgenda.length; index++) {
      const element = enumsStatusErrosAgenda[index]
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

  // get icon () {
  //   if (this._value) {
  //     return this._value.icon
  //   } else {
  //     return null
  //   }
  // }

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

export class FollowUpStatusErrosDtPromessa {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsStatusErrosDtPromessa.length; index++) {
      const element = enumsStatusErrosDtPromessa[index]
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

  // get icon () {
  //   if (this._value) {
  //     return this._value.icon
  //   } else {
  //     return null
  //   }
  // }

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

export class FollowUpStatusConfirmacaoColeta {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsStatusConfirmacaoColeta.length; index++) {
      const element = enumsStatusConfirmacaoColeta[index]
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

  // get icon () {
  //   if (this._value) {
  //     return this._value.icon
  //   } else {
  //     return null
  //   }
  // }

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

export class FollowUpStatusInicioFup {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = pValue
  }

  set value (pValue) {
    if (!pValue) return
    var v = pValue.toString()
    this._value = null
    for (let index = 0; index < enumsStatusInicioFup.length; index++) {
      const element = enumsStatusInicioFup[index]
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

  // get icon () {
  //   if (this._value) {
  //     return this._value.icon
  //   } else {
  //     return null
  //   }
  // }

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

export class PlanilhaStatusProcessamento {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = parseInt(pValue)
  }

  set value (pValue) {
    if (typeof pValue === 'undefined') return
    var v = parseInt(pValue)
    this._value = null
    for (let index = 0; index < enumsStatusPlanilhaProcessamento.length; index++) {
      const element = enumsStatusPlanilhaProcessamento[index]
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

  get spinner () {
    if (this._value) {
      return this._value.spinner ? this._value.spinner : false
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

export class FileFupTipoExport {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = parseInt(pValue)
  }

  set value (pValue) {
    if (typeof pValue === 'undefined') return
    var v = parseInt(pValue)
    this._value = null
    for (let index = 0; index < enumsFileFupTipoExport.length; index++) {
      const element = enumsFileFupTipoExport[index]
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

  get spinner () {
    if (this._value) {
      return this._value.spinner ? this._value.spinner : false
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

export class FUPLogTipoOrigem {
  constructor (pValue) {
    this._value = null
    if (pValue) this.value = parseInt(pValue)
  }

  set value (pValue) {
    if (typeof pValue === 'undefined') return
    var v = parseInt(pValue)
    this._value = null
    for (let index = 0; index < enumsFUPLogTipoOrigem.length; index++) {
      const element = enumsFUPLogTipoOrigem[index]
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

  get spinner () {
    if (this._value) {
      return this._value.spinner ? this._value.spinner : false
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

  get label () {
    if (this._value) {
      return this._value.label
    } else {
      return 'Indefinido'
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
