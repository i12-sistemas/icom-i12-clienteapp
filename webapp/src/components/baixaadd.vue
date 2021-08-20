<template>
<div>
  <q-form ref="myForm">
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
        <q-tab-panel name="nfe">
          <div v-if="novabaixa" class="full-width fit row wrap justify-between items-end content-start text-h6">
            <div class="row full-width justify-between">
              <div class="col-xs-5">
                <q-btn :disable="saving" icon="camera_alt" no-wrap
                  @click="actScanBarcode(false)"
                  label="Câmera" color="black" text-color="white" unelevated />
                  <q-btn :disable="saving" icon="fas fa-lightbulb" v-if="false"
                  @click="actScanBarcode(true)" label="Com flash" text-color="black" dark unelevated />
              </div>
              <div class="col-xs-5 text-right">
                <q-btn @click="actAddNota" label="incluir" type="submit" icon="add " no-wrap
                  :disable="saving" :loading="saving"
                  color="secondary" text-color="white" class="full-width" unelevated/>
              </div>
            </div>
          </div>
          <div class="row full-width justify-between q-mt-md q-pa-sm rounded-borders bg-green-1" v-if="coleta ? (coleta.chavenota ? coleta.chavenota !== '' : false) : false">
            <div class="col-12">
              Somente a nota com chave abaixo por ser lançada:
            </div>
            <div class="col-12">
              {{coleta.chavenota}}
            </div>
            <div class="col-12 q-mt-sm">
              <q-btn @click="actNotaSugestao" label="confirmar esta nota" no-wrap
                  :disable="saving" :loading="saving"
                  color="green" text-color="white" class="full-width" unelevated/>
            </div>
          </div>
          <div class="full-width q-py-md">
            <div class="row">
              <div class="col-12 q-py-xs">
                <q-input v-model="chavep1" outlined type="tel"
                  ref="chave1" @input="onchangeChave1" :debounce="400"
                  label="Parte 1 da chave da nota" clearable maxlength="22" counter lazy-rules
                  :rules="[ val => val && val.length === 22 && novabaixa.docfiscal === 'nfe' || 'Digite os 22 dígitos']"
                  :disable="saving" :loading="saving"
                  input-class="text-h6"/>
              </div>
              <div class="col-12 q-py-xs">
                <q-input v-model="chavep2" outlined type="tel"
                  ref="chave2" @input="onchangeChave2" :debounce="400"
                  :disable="saving" :loading="saving" lazy-rules
                  :rules="[ val => val && val.length === 22 && novabaixa.docfiscal === 'nfe' || 'Digite os 22 dígitos']"
                  label="Parte 2 da chave da nota" clearable maxlength="22" counter
                  input-class="text-h6"/>
              </div>
            </div>
            <div class="row full-width bg-red text-white rounded-borders q-pa-sm q-my-sm" v-if="novabaixa.nota.error && novabaixa.nota.chave !== ''">
              <div class="col-12 ">
                {{novabaixa.nota.error}}
              </div>
            </div>
            <div class="row full-width bg-red text-white rounded-borders q-pa-sm q-my-sm" v-if="!novabaixa.nota.error && novabaixa.nota.chave !== '' && (coleta.remetente.cnpj !== novabaixa.nota.CNPJ)">
              <div class="col-12 ">
                CNPJ da chave difere do CNPJ do remetente da coleta!
              </div>
            </div>
            <div class="row full-width justify-between">
              <div class="col-6">
                <q-field label="Número nota" stack-label >
                  <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">{{getNFeNumero}}</div>
                  </template>
                </q-field>
              </div>
              <div class="col-5">
                  <q-field label="Mês/Ano emissão" stack-label>
                  <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">{{getNFeMesAno}}</div>
                  </template>
                </q-field>
              </div>
            </div>
            <div class="row full-width justify-between items-end">
              <div class="col-6">
                <q-field label="CNPJ" stack-label>
                  <template v-slot:control>
                    <div class="self-center full-width no-outline" tabindex="0">
                      {{$helpers.mascaraCpfCnpj(getNFeCNPJ)}}
                    </div>
                  </template>
                </q-field>
              </div>
              <div class="col-5 text-bottom text-right">
                <q-btn label="incluir" @click="actAddNota" size="md" no-wrap type="submit" icon="add"
                  :disable="saving" :loading="saving"
                  color="secondary" text-color="white" class="full-width" unelevated/>
              </div>
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
          <div class="row">
            <div class="col-12 q-py-xs">
              <q-btn label="Incluir" @click="actAddNota" size="md" type="submit" no-wrap icon="add"
                  :disable="saving" :loading="saving"
                  color="secondary" text-color="white" class="full-width" unelevated/>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
      <div class="row full-width justify-between items-end q-pa-md">
        <div class="col-12">
          <q-input v-model="obs" outlined label="Observações"
            input-class="text-body2" autogrow stack-label maxlength="150" counter />
        </div>
      </div>
      <div class="row full-width justify-between items-end q-px-md q-py-lg text-caption" v-if="GPS">
        <div class="col-12 text-right">
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
  </div>
  </q-form>
</div>
</template>

<style>
</style>

<script>
import moment from 'moment'
import Baixa from 'src/mvc/models/baixa.js'
export default {
  components: {
  },
  directives: {
  },
  props: ['coleta', 'title', 'GPS'],
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
      date: moment().format('DD/MM/YYYY HH:mm'),
      time: moment().format('HH:mm'),
      novabaixa: null,
      saving: false
    }
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
    configCalendar: function () {
      return this.$root._i18n.messages['pt-br'].calendar
    },
    NFeIsValid () {
      try {
        var b = false
        if (!this.novabaixa) throw new Error()
        if (!this.novabaixa.nota) throw new Error()
        var r = this.novabaixa.nota.isValid()
        if (!r.ok) throw new Error()
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
    actNotaSugestao () {
      var app = this
      app.chavep1 = this.coleta.chavenota.substring(0, 22)
      app.chavep2 = this.coleta.chavenota.substring(22, 44)
    },
    onChangeTab (value) {
      this.novabaixa.docfiscal = value
    },
    onchangeChave1 (value) {
      if (value) {
        if (String(value).length === 22) this.$refs.chave2.select()
      }
    },
    onchangeChave2 (value) {
      if (String(value).length === 0) this.$refs.chave1.select()
    },
    actScanBarcode (flash) {
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
    setChave (pChave) {
      this.novabaixa.nota.setChave(pChave)
    },
    InitNew () {
      this.novabaixa = new Baixa()
      this.tab = 'nfe'
      this.chavep1 = ''
      this.chavep2 = ''
      this.notanumero = null
      this.notadh = moment().format('YYYY-MM-DD')
      this.notavalor = 0
      // this.chave = '35190808957311000155550000003068981672367898'
      this.setChave(this.chave)
      if (this.coleta) {
        this.novabaixa.coleta = this.coleta
      }
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
    async actAddNota () {
      var app = this
      try {
        app.saving = true
        var ret = null

        if (app.novabaixa.docfiscal === 'nfse') {
          app.novabaixa.nota.limpardados()
          app.novabaixa.notaservico = { numero: app.notanumero, dh: app.notadh, valor: app.notavalor }
        } else {
          app.novabaixa.notaservico = null
        }
        // ret sera a posicao ou erro
        // ret = await app.getGeolocation()
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
        app.novabaixa.obs = app.obs
        app.novabaixa.geolocation = ret
        if ((ret.error) && (!ret.coords)) {
          ret = await app.confirm('GPS offline', 'O GPS está offline, deseja continuar assim mesmo?')
          if (!ret) {
            throw new Error('Cancelado pelo usuário')
          }
        }
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
        app.InitNew()
        app.$emit('updated')
      } else {
        app.showMessage(ret)
      }
      app.saving = false
    }
  }
}
</script>
