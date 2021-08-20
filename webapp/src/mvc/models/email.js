class Email {
  constructor (pItem) {
    this.limpardados()
    if (pItem) this.cloneFrom(pItem)
  }

  async limpardados () {
    this.id = null
    this.email = ''
    this.nome = ''
    this.deleted = false
    this.updated = false
    this.tags = []
    this.clientescount = 0
  }

  async cloneFrom (item) {
    var self = this
    self.limpardados()
    if (!item) return
    if (item.id) this.id = item.id
    if (item.email) this.email = item.email
    if (item.nome) this.nome = item.nome
    if (item.tags) this.tags = item.tags
    if (item.clientescount) this.clientescount = item.clientescount
    // somente controle de operação
    if (item.deleted) this.deleted = item.deleted
    if (item.updated) this.updated = item.updated
  }
}

export default Email
