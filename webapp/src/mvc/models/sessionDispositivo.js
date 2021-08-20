import Vue from 'vue'

class SessionDispositivo {
  constructor () {
    this.limpardados()
  }

  async limpardados () {
    this.uuid = null
    this.token = ''
    this.name = ''
    this.expire_at = null
    this.accesscode = ''
  }
  async cloneFrom (item) {
    this.uuid = item.uuid
    this.token = item.token
    this.expire_at = item.expire_at
    this.name = item.name
    this.accesscode = item.accesscode
  }

  async getLocal (pUUID) {
    try {
      var self = this
      self.limpardados()
      let d = await Vue.prototype.$indexesDB.db.device.where('uuid').equals(pUUID).first()
      self.cloneFrom(d)
      return { ok: true }
    } catch (error) {
      return { ok: false, msg: error.message }
    }
  }

  async localPut () {
    var ret = await Vue.prototype.$indexesDB.db.device.put(this).then(function () {
      let r = { ok: true }
      return r
    }).then(function (row) {
      let r = { ok: true }
      return r
    }).catch(function (error) {
      let r = { ok: true, msg: error.message }
      console.error('Ooops: ' + error)
      return r
    })
    return ret
  }
}

export default SessionDispositivo
