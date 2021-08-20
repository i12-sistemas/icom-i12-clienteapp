<template>
<div>
  <q-form ref="myForm" autocorrect="off" autocapitalize="off" autocomplete="off" spellcheck="false">
    <div v-if="novabaixa" class="full-width">
      <div class="full-width">
        <q-tabs
          v-model="tab"
          class="text-white bg-primary"
          active-color="white"
          indicator-color="white"
          align="justify"
          narrow-indicator
          @input="onChangeTab"
        >
          <q-tab name="nfe" label="NOTA DE PRODUTO" />
          <q-tab name="nfse" label="NOTA DE SERVIÇO" />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="nfe" class="no-padding">
            <q-toolbar class="bg-grey-3 text-black" v-if="novabaixa">
              <q-toolbar-title>
                Avulsa <span class="text-caption" v-if="novabaixa.avulsaidcoleta">#{{ novabaixa.avulsaidcoleta }}</span>
              </q-toolbar-title>
              <q-btn :disable="saving" stretch flat icon="usb" label="Leitor"
                @click="actLeitorToogle"
                :class="digitacaotipo == 'manual' ? 'text-black' : 'bg-deep-orange-1 text-primary'"
                no-wrap class="q-mr-xs" />
              <q-btn :disable="saving" icon="camera_alt"
                @click="actScanBarcode"
                label="Câmera" color="black" stretch flat no-wrap size="md"/>
            </q-toolbar>
            <div class="row q-pa-md">
              <div class="col-12 q-mb-md" v-if="digitacaotipo === 'leitor'">
                <q-input v-model="chave" outlined type="tel" :debounce="500"
                  label="Chave da nota" maxlength="44" counter
                  ref="txtchavecompleta" @input="onInputChaveCompleta"
                  :disable="saving" :loading="saving" lazy-rules
                  :rules="[ val => val && val.length === 44 && novabaixa.docfiscal === 'nfe' || 'Digite os 44 dígitos']"
                  input-class="text-caption text-weight-bold"/>
              </div>
              <div class="col-12 q-mb-md" v-if="digitacaotipo === 'leitor' && chave !== ''">
                <q-btn label="limpar chave" @click="actClearChaveCompleta" size="md" no-wrap icon="clear" color="black" class="bg-grey-2 full-width" flat/>
              </div>
              <div class="col-12 q-py-xs" v-if="digitacaotipo !== 'leitor'">
                <q-input v-model="chavep1" outlined type="tel"
                  label="Parte 1 da chave da nota" clearable maxlength="22" counter
                  ref="chave1" @input="onchangeChave1" @keypress="setDigitacaoManual"
                  :disable="saving" :loading="saving" lazy-rules
                  :rules="[ val => val && val.length === 22 && novabaixa.docfiscal === 'nfe' || 'Digite os 22 dígitos']"
                  input-class="text-h6"/>
              </div>
              <div class="col-12 q-py-xs" v-if="digitacaotipo !== 'leitor'">
                <q-input v-model="chavep2" outlined type="tel"
                  ref="chave2" @input="onchangeChave2" @keypress="setDigitacaoManual"
                  :disable="saving" :loading="saving"
                  label="Parte 2 da chave da nota" clearable maxlength="22" counter
                  :rules="[ val => val && val.length === 22 && novabaixa.docfiscal === 'nfe' || 'Digite os 22 dígitos']"
                  input-class="text-h6"/>
              </div>
              <div class="col-3 q-pr-xs">
                <q-field label="Número nota" stack-label >
                  <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">{{getNFeNumero}}</div>
                  </template>
                </q-field>
              </div>
              <div class="col-3 q-pr-xs">
                  <q-field label="Mês/Ano" stack-label>
                  <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">{{getNFeMesAno}}</div>
                  </template>
                </q-field>
              </div>
              <div class="col-6">
                <q-field label="CNPJ" stack-label>
                  <template v-slot:control>
                    <div class="self-center full-width no-outline" tabindex="0">
                      {{$helpers.mascaraCpfCnpj(getNFeCNPJ)}}
                    </div>
                  </template>
                </q-field>
              </div>
            </div>
          </q-tab-panel>

          <q-tab-panel name="nfse">
            <div class="row">
              <div class="col-12 q-py-xs">
                <q-input v-model="notadh" stack-label outlined type="date" label="Data de emissão" />
              </div>
            </div>
            <div class="row full-width justify-between">
              <div class="col-5 q-py-xs">
                <q-input v-model="notanumero" stack-label outlined type="tel" label="Nº da nota" maxlength="9"
                :rules="[ val => val && $helpers.isInt(val) && novabaixa.docfiscal === 'nfse' || 'Valor inválido' ]"
                />
              </div>
              <div class="col-6 q-py-xs">
                <q-input v-model="notavalor" stack-label outlined type="tel" label="Valor da nota"
                mask="#.##"
                fill-mask="0"
                reverse-fill-mask
                clearable
                :rules="[ val => val && $helpers.isFloat(val) && parseFloat(val) > 0 && novabaixa.docfiscal === 'nfse' || 'Valor inválido' ]"
                />
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>
    <div v-if="novabaixa" class="full-width fit row wrap justify-between items-end content-start text-h6 q-px-md">
      <div class="full-width">
        <div class="row full-width justify-between items-end q-mt-md">
          <div class="col-12">
            <q-input v-model="obs" outlined label="Observações"
              input-class="text-body2" autogrow stack-label maxlength="150" counter />
          </div>
        </div>
        <div class="row full-width justify-between items-end q-mt-md">
          <div class="col-12 text-bottom text-right">
            <q-btn label="Incluir" @click="actAddNota" size="md" type="submit" no-wrap icon="add"
              :disable="saving" :loading="saving"
              color="secondary" text-color="white" class="full-width" unelevated/>
          </div>
        </div>
      </div>
    </div>
    <div v-if="novabaixa" class="full-width fit row wrap justify-between items-end content-start text-h6 q-pa-md">
      <div class="full-width text-right">
        <div v-if="GPS" class="text-caption">
          <div v-if="GPS.searching" class="text-blue">{{ '(' + GPS.tentativa + ') Tentando achar GPS...' }}</div>
          <div v-if="GPS.ok" class="text-right text-blue">
            <span class="q-mr-xs text-caption">GPS online</span>
            <q-spinner-rings color="blue" size="2em" />
          </div>
          <div class="text-red" v-if="!GPS.ok && !GPS.searching">
            <span class="q-mr-xs">GPS offline</span>
            <q-icon name="fas fa-map-marker-alt" size="1em" />
          </div>
        </div>
      </div>
    </div>
    <div v-if="showlistaclientes" >
      <listaclientes :findInit="showlistaclientesStartBusca" @selected="onClienteSelecionado" @close="onCloseCliente" />
    </div>
  </q-form>
</div>
</template>

<script>
import moment from 'moment'
import Clientes from 'src/mvc/collections/clientes.js'
import Baixa from 'src/mvc/models/baixa.js'
import BaixasCollection from 'src/mvc/collections/baixas.js'
import listaclientes from 'src/components/clientesconsultalista'
export default {
  components: {
    listaclientes
  },
  props: ['coletaref', 'title', 'GPS'],
  data: function () {
    return {
      tab: 'nfe',
      notanumero: null,
      notadh: moment().format('YYYY-MM-DD'),
      notavalor: 0,
      geolocalizacao: null,
      chave: '',
      chavep1: '',
      chavep2: '',
      obs: '',
      digitacaotipo: 'manual',
      destinatario: { cnpj: '', razaosocial: '', object: null },
      novabaixa: null,
      saving: false,
      showlistaclientes: null,
      showlistaclientesorigem: null,
      showlistaclientesStartBusca: null,
      showdestinatario: false,
      loadingcliente: false
    }
  },
  created () {
    var app = this
    this.$root.$on('baixaaddavulsa-save', (e) => {
      app.actAddNota()
    })
  },
  destroyed () {
    this.$root.$off('baixaaddavulsa-save')
  },
  async mounted () {
    this.InitNew()
  },
  watch: {
    chavep1: function (newchave, oldchave) {
      this.chave = newchave + this.chavep2
      this.setChave(this.chave)
    },
    chavep2: function (newchave, oldchave) {
      this.chave = this.chavep1 + newchave
      this.setChave(this.chave)
    }
  },
  computed: {
    destinatarioIsValid: function () {
      var app = this
      var item = app.destinatario
      var b = false
      try {
        if (!item) throw new Error('1')
        if (!item.object) throw new Error('2')
        if (item.object.cnpj !== item.cnpj) throw new Error('3')
        if (item.object.razaosocial !== item.razaosocial) throw new Error('4')
        b = true
      } catch (error) {
        b = false
      }
      return b
    },
    NFeIsValid () {
      try {
        var b = false
        if (!this.novabaixa) throw new Error()
        if (!this.novabaixa.nota) throw new Error()
        b = this.novabaixa.nota.isValid()
      } catch (error) {
        b = false
      }
      return b
    },
    getNFeNumero () {
      try {
        var num = null
        if (!this.novabaixa) throw new Error()
        if (!this.novabaixa.nota) throw new Error()
        if (!this.novabaixa.nota.nNF) throw new Error()
        num = this.novabaixa.nota.nNF
      } catch (error) {
        num = null
      }
      return num
    },
    getNFeCNPJ () {
      try {
        var num = null
        if (!this.novabaixa) throw new Error()
        if (!this.novabaixa.nota) throw new Error()
        if (!this.novabaixa.nota.CNPJ) throw new Error()
        num = this.novabaixa.nota.CNPJ
      } catch (error) {
        num = null
      }
      return num
    },
    getNFeMesAno () {
      try {
        var num = null
        if (!this.novabaixa) throw new Error()
        if (!this.novabaixa.nota) throw new Error()
        if (!this.novabaixa.nota.mesAno) throw new Error()
        num = this.novabaixa.nota.mesAno
      } catch (error) {
        num = null
      }
      return num
    }
  },
  methods: {
    actLeitorToogle () {
      var app = this
      app.digitacaotipo = ((app.digitacaotipo === 'leitor') ? 'manual' : 'leitor')
      app.chave = ''
      app.chave1 = ''
      app.chave2 = ''
      if (app.digitacaotipo === 'leitor') {
        app.$nextTick(() => {
          app.$refs.txtchavecompleta.focus()
        })
      } else {
        app.$nextTick(() => {
          app.$refs.chave1.focus()
        })
      }
    },
    actClearChaveCompleta () {
      var app = this
      app.chave = ''
      app.chave1 = ''
      app.chave2 = ''
      if (app.digitacaotipo === 'leitor') {
        app.$nextTick(() => {
          app.$refs.txtchavecompleta.focus()
        })
      } else {
        app.$nextTick(() => {
          app.$refs.chave1.focus()
        })
      }
    },
    setDigitacaoManual () {
      this.digitacaotipo = 'manual'
    },
    onChangeTab (value) {
      this.novabaixa.docfiscal = value
    },
    actCloseDestinatario () {
      var app = this
      app.destinatario.cnpj = ''
      app.destinatario.razaosocial = ''
      app.destinatario.object = null
      app.showdestinatario = false
    },
    onchangeChave1 (value) {
      if (value) {
        if (String(value).length === 22) this.$refs.chave2.select()
      }
    },
    onchangeChave2 (value) {
      if (String(value).length === 0) this.$refs.chave1.select()
    },
    actScanBarcode () {
      let params = {
        'prompt_message': 'Escaneia o código da nota fiscal', // Change the info message. A blank message ('') will show a default message
        'orientation_locked': false, // Lock the orientation screen
        'camera_id': 0, // Choose the camera source
        'beep_enabled': true, // Enables a beep after the scan
        'scan_type': 'normal', // Types of scan mode: normal = default black with white background / inverted = white bars on dark background / mixed = normal and inverted modes
        'barcode_formats': ['QR_CODE', 'CODE_128'], // Put a list of formats that the scanner will find. A blank list ([]) will enable scan of all barcode types
        'extras': {} // Additional extra parameters. See [ZXing Journey Apps][1] IntentIntegrator and Intents for more details
      }
      window.plugins.zxingPlugin.scan(params, this.onSuccessScanBarCode, this.onFailureScanBarCode)
    },
    onSuccessScanBarCode (data) {
      this.code = data
      if (this.code.length === 44) {
        this.digitacaotipo = 'camera'
        this.closeBarCode(this.code)
      } else {
        alert('Código inválido para uma nota fiscal.\n' + this.code)
      }
    },
    onFailureScanBarCode (e) {
      if (e) {
        if (e !== 'cancelled') alert(e)
      }
      this.closeBarCode('')
    },
    closeBarCode (barcode) {
      var app = this
      if (barcode) {
        if (barcode !== '') {
          app.chavep1 = barcode.substring(0, 22)
          app.chavep2 = barcode.substring(22, 44)
        }
      }
    },
    actClearDestRem (item) {
      item.object = null
      item.razaosocial = ''
      item.cnpj = ''
    },
    actShowListaCliente (origem, findInit) {
      this.onCloseCliente()
      this.showlistaclientes = true
      this.showlistaclientesorigem = origem
      this.showlistaclientesStartBusca = findInit
    },
    onCloseCliente () {
      this.showlistaclientes = false
      this.showlistaclientesorigem = null
    },
    onClienteSelecionado (cliente) {
      if (cliente) {
        this.showlistaclientesorigem.object = cliente
        this.showlistaclientesorigem.razaosocial = this.showlistaclientesorigem.object.razaosocial
        this.showlistaclientesorigem.cnpj = this.showlistaclientesorigem.object.cnpj
      } else {
        this.showlistaclientesorigem.object = null
      }
      this.showlistaclientes = false
      this.showlistaclientesorigem = null
    },
    async findCliente (origem, key, value) {
      var app = this
      app.loadingcliente = true
      var clientes = new Clientes()
      if ((value === '') || (!value)) {
        this.actShowListaCliente(origem, value)
        app.loadingcliente = false
        return
      }
      var cli = await clientes.fetchByKey(key, value)
      origem.object = null
      if (cli) {
        origem.object = cli
        origem.razaosocial = origem.object.razaosocial
        origem.cnpj = origem.object.cnpj
      } else {
        this.actShowListaCliente(origem, value)
      }
      app.loadingcliente = false
    },
    setChave (pChave) {
      this.novabaixa.nota.setChave(pChave)
    },
    InitNew () {
      this.notanumero = null
      this.notadh = moment().format('YYYY-MM-DD')
      this.notavalor = 0
      this.novabaixa = new Baixa()
      this.novabaixa.avulsa = true
      if (this.coletaref) {
        this.novabaixa.avulsaidcoleta = this.coletaref.id
        // if (this.coletaref.remetente) {
        //   this.remetente.cnpj = this.coletaref.remetente.cnpj
        //   this.remetente.razaosocial = this.coletaref.remetente.razaosocial
        //   this.remetente.object = this.coletaref.remetente
        // }
      }
      this.chavep1 = ''
      this.chavep2 = ''
      this.setChave(this.chave)
    },
    onInputChaveCompleta (e) {
      this.setChave(this.chave)
    },
    async confirm (title, msg) {
      return new Promise((resolve, reject) => {
        this.$q.dialog({
          title: title,
          message: msg,
          persistent: true,
          ok: {
            label: 'Sim',
            color: 'secondary',
            unelevated: false
          },
          cancel: {
            label: 'Cancelar',
            color: 'negative',
            flat: true
          }
        }).onOk(() => {
          resolve(true)
        }).onCancel(() => {
          reject(new Error(''))
        }).onDismiss(() => {
        })
      })
    },
    async showMessage (ret) {
      var app = this
      if (!ret.msg) return
      if (ret.msg === '') return
      if (!ret.ok) {
        app.$q.dialog({
          color: 'negative',
          title: 'Atenção',
          message: ret.msg
        }).onOk(() => {
        })
      } else {
        app.$q.dialog({
          color: 'positive',
          title: 'Olá',
          message: ret.msg
        }).onOk(() => {
        })
      }
    },
    async actInit () {
      var app = this
      app.loading = true
      app.baixas = null
      this.$store.commit('app/title', 'Coleta')
      app.coleta = await app.coletas.find(app.idcoleta)
      if (app.coleta) {
        if (app.coleta.id > 0) {
          app.baixas = await app.coleta.baixas()
        }
      }
      // this.$store.commit('app/title', (app.coleta ? '#' + app.coleta.id : 'Coleta não encontrada'))
      app.loading = false
    },
    async getGeolocation () {
      return new Promise((resolve, reject) => {
        if (!('geolocation' in navigator)) {
          reject(new Error('Geolocation is not available.'))
        }
        navigator.geolocation.getCurrentPosition(position => {
          resolve(position)
        }, err => {
          reject(err)
        }, { timeout: 5000 })
      })
    },
    async checkForm () {
      var b = await this.$refs.myForm.validate().then(success => {
        if (success) {
          return true
        } else {
          return false
          // at least an invalid value
        }
      })
      return b
    },
    async actAddNota () {
      var app = this
      try {
        app.saving = true
        var ret = await app.checkForm()
        if (!ret) throw new Error('Verifique os dados do formulário')

        if (app.novabaixa.docfiscal === 'nfse') {
          app.novabaixa.nota.limpardados()
          app.novabaixa.notaservico = { numero: app.notanumero, dh: app.notadh, valor: app.notavalor }
        } else {
          app.novabaixa.notaservico = null
        }
        // ret sera a posicao ou erro
        // ret = await app.getGeolocation()
      } catch (error) {
        app.showMessage({ ok: false, msg: error.message })
        console.error(error)
        app.saving = false
        return
      }
      try {
        if (app.GPS.ok) {
          ret = app.GPS.position
          let coords = {
            latitude: ret.coords.latitude,
            longitude: ret.coords.longitude,
            altitude: ret.coords.altitude,
            speed: ret.coords.speed,
            accuracy: ret.coords.accuracy,
            altitudeAccuracy: ret.coords.altitudeAccuracy,
            heading: ret.coords.heading
          }
          let posicao = {
            coords: coords,
            timestamp: ret.timestamp
          }
          ret = posicao
        } else {
          ret = { error: app.GPS.msg }
        }
      } catch (error) {
        ret = { error: error.message + (error.code ? ' (Code: ' + error.code + ')' : '') }
      }
      try {
        app.novabaixa.geolocation = ret
        if ((ret.error) && (!ret.coords)) {
          ret = await app.confirm('GPS offline', 'O GPS está offline, deseja continuar assim mesmo?')
          if (!ret) {
            throw new Error('Cancelado pelo usuário')
          }
        }

        let c = {
          destinatario: app.showdestinatario ? app.destinatario.object : null
        }
        app.novabaixa.coleta = c
        app.novabaixa.obs = app.obs
        ret = await app.novabaixa.localSave()
      } catch (error) {
        ret = { ok: false, msg: error.message }
      }
      if (ret.ok) {
        this.$q.notify({
          message: 'Baixa incluida com sucesso',
          color: 'positive',
          timeout: 1000
        })

        this.$store.dispatch('app/refreshConnection')
        let online = this.$store.state.app.conexaointernet.online
        if (online) {
          var baixasSync = new BaixasCollection()
          ret = await baixasSync.SyncBaixas(app)
          if (!ret.ok) {
            app.$q.dialog({
              color: 'negative',
              title: 'Tentativa de envio',
              message: ret.msg
            }).onOk(() => {
            })
          } else {
            this.$q.notify({
              message: 'Baixa enviada com sucesso',
              color: 'positive',
              timeout: 1000
            })
          }
        }

        app.InitNew()
        app.$emit('updated')
      } else {
        app.showMessage(ret)
        console.error(ret.msg)
      }
      app.saving = false
    }
  }
}
</script>
