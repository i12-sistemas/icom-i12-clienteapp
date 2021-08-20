<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title class="text-caption" v-if="!loading">
        <div class="text-body2">Carga de Entrada {{dataset ? '#' + dataset.id : ''}}</div>
        <div>{{dataset.unidadeentrada.fantasia}}</div>
      </q-toolbar-title>
      <q-btn round dense flat icon="sync" @click="refreshData(false)" />
    </q-toolbar>
  </q-header>

  <q-page-container class="bg-grey-3" >
    <q-page>
      <q-tabs v-model="tab" class="bg-primary text-white" indicator-color="accent" inline-label >
        <q-tab name="info" icon="info" label="Info" />
        <q-tab name="checagem" icon="qr_code_scanner" label="Conferência" />
      </q-tabs>
       <q-tab-panels v-model="tab" animated >
        <q-tab-panel name="info" class="q-pa-none">
          <div class="q-pa-md text-center" v-if="loading">
            <div class="row" >
              <div class="col-12">
                  <q-circular-progress size="100px" indeterminate :thickness="0.2" color="accent" center-color="white" track-color="grey-3" class="q-ma-lg" />
              </div>
            </div>
            <div class="text-h6">Carregando...</div>
          </div>
          <div class="q-pa-md" v-if="!loading">
            <div class="text-h6">Carga de Entrada</div>
            <q-separator spaced />
            <div class="row" >
              <div class="col-xs-4">Número</div>
              <div class="col-xs-8 text-weight-bold">{{dataset.id}}</div>
              <div class="col-xs-4">Data entrada</div>
              <div class="col-xs-8 text-weight-bold">{{$helpers.datetimeToBR(dataset.dhentrada, false, true)}}</div>
              <div class="col-xs-4">Unidade</div>
              <div class="col-xs-8 text-weight-bold">{{dataset.unidadeentrada.fantasia}}</div>
              <div class="col-xs-4">Tipo entrada</div>
              <div class="col-xs-8 text-weight-bold"><q-icon :name="dataset.tipo.icon" :color="dataset.tipo.color" size="xs" /> {{dataset.tipo.description}}</div>
              <div class="col-xs-4" v-if="dataset.tipo.value === '1'">Motorista</div>
              <div class="col-xs-8 text-weight-bold" v-if="dataset.tipo.value === '1'">{{dataset.motorista.nome}}</div>
              <div class="col-xs-4" v-if="dataset.tipo.value === '1'">Veículo</div>
              <div class="col-xs-8 text-weight-bold" v-if="dataset.tipo.value === '1'">{{dataset.veiculo.placa}}</div>
            </div>
            <div class="row q-my-sm" v-if="!loading && (dataset ? dataset.erroqtde > 0 : false)" >
              <div class="col-12">
                <q-banner class="bg-red-1 text-red q-pa-sm" rounded>
                  <div>
                    <div class="text-subtitle2">Esta carga contêm erros e não pode ser encerrada!</div>
                    <ul>
                      <li  v-html="dataset.erromsg.join('')" />
                    </ul>
                  </div>
                </q-banner>
              </div>
            </div>
            <div class="row"  v-if="podeEncerrar">
              <div class="col-12">
                <q-btn color="positive" label="Encerrar carga" @click="actEncerrarCarga" size="lg" unelevated class="full-width" :disable="loading" />
              </div>
            </div>
            <div class="row"  v-if="!loading && (dataset ? dataset.status.value !== '1' : false)">
              <div class="col-12 text-h6 text-center rounded-borders q-pa-md q-my-md text-white" :class="'bg-' + dataset.status.color">
                {{dataset.status.description}}
              </div>
            </div>
            <q-separator spaced />
            <div class="row">
              <div class="col-xs-6 text-h6">Itens</div>
              <div class="col-xs-6 text-right">
                <div class="text-subtitle2">{{$helpers.formatRS(dataset.conferidoprogresso, false, 0)}} % conferido</div>
                <q-linear-progress :value="dataset.conferidoprogresso" rounded :color="dataset.conferidoprogresso === 100 ? 'positive' : 'accent'" track-color="grey-3" class="q-mt-sm" size="12px" />
              </div>
            </div>
            <div class="row text-center" v-if="dataset.itens ? dataset.itens.length === 0 : true" >
              <div class="col-xs-12">Nenhum item na carga!</div>
            </div>
          </div>
          <div class="row" v-if="!loading && (dataset.itens ? dataset.itens.length > 0 : false)" >
            <div class="col-xs-12">
              <q-list class="full-width" separator transition-show="slide-right"  transition-hide="slide-left"  >
                <q-item v-for="(item, key) in dataset.itens" :key="key" :class="(key/2) === 0 ? 'bg-white' : 'bg-grey-1'" >
                  <q-item-section top side>
                    <q-avatar size="20px" font-size="14px" :color="item.temerro ? 'red' : 'grey-3'" :text-color="item.temerro ? 'white' : 'primary'" >{{key+1}}</q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label >
                      <div :class="(item.nfevol ? !(item.nfevol > 0) : true) ? 'bg-red-1' : ''" class="cursor-pointer">
                        <div v-if="(item.nfepeso ? (item.nfepeso > 0) : false)">Peso: <span class="text-subtitle2">{{$helpers.formatRS(item.nfepeso, false, 3)}} KG</span></div>
                        <div v-if="(item.nfepeso ? (item.nfepeso === 0) : true)">Sem peso informado</div>
                      </div>
                    </q-item-label>
                    <q-item-label>
                      <div >Nota Fiscal {{ item.nfenumero }}</div>
                    </q-item-label>
                    <q-item-label>
                      <div v-if="(item.coletaid ? (item.coletaid > 0) : false)">Coleta #{{ item.coletaid }}</div>
                      <div v-if="(item.coletaid ? (item.coletaid === 0) : true)">Sem coleta</div>
                    </q-item-label>
                    <q-item-label v-if="item.temerro">
                      <q-banner class="bg-red-1 text-red q-pa-sm" rounded>
                        <div>
                          <div  v-html="item.errors.join('<br>')" />
                        </div>
                      </q-banner>
                    </q-item-label>
                    <q-item-label>
                      <div>
                        <div v-for="(etiqueta, keti) in item.etiquetas" :key="'etiq' + keti" >
                          <div class="row">
                            <div class="col-9 text-weight-bold">{{etiqueta.ean13}}</div>
                            <div class="col-3 text-right">
                              <q-icon name="check" color="green" size="20px" v-if="etiqueta.conferidoentrada" />
                              <q-icon name="clear" color="red" size="20px" v-if="!etiqueta.conferidoentrada" />
                            </div>
                            <div class="col-12 text-caption" v-if="etiqueta.conferidoentradadh && etiqueta.conferidoentrada">Conferido em {{ $helpers.datetimeToBR(etiqueta.conferidoentradadh) }}</div>
                            <div class="col-12 text-caption" v-if="etiqueta.conferidoentrada_usuario && etiqueta.conferidoentrada">por {{ etiqueta.conferidoentrada_usuario.nome }}</div>
                          </div>
                          <q-separator spaced />
                        </div>
                      </div>
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side top >
                    <q-item-label>
                      <div :class="(item.nfevol ? !(item.nfevol > 0) : true) ? 'bg-red-1' : ''" >
                        Volumes: <span class="text-h6">{{(item.nfevol ? (item.nfevol === 0) : true) ? '-' : item.nfevol}}</span>
                        <q-tooltip :delay="700">
                          <div v-if="(item.nfevol ? (item.nfevol > 0) : false)">{{item.nfevol}} volume(s)</div>
                          <div v-if="(item.nfevol ? (item.nfevol === 0) : true)">Nenhum volume informado</div>
                        </q-tooltip>
                      </div>
                    </q-item-label>
                    <div class="q-mt-sm">
                      <q-chip :icon="item.tipoprocessamento.icon" size="md" color="grey-3" :text-color="item.tipoprocessamento.color">
                        <q-tooltip :delay="500">{{item.tipoprocessamento.value + ' - ' + item.tipoprocessamento.description}}</q-tooltip>
                      </q-chip>
                    </div>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>
          </div>
          <q-separator spaced />
          <div class="q-pa-md text-h6" v-if="!loading">
            <div class="row text-right" >
              <div class="col-xs-5">Volumes</div>
              <div class="col-xs-7 text-h5 text-weight-bold">{{dataset.volqtde}}</div>
              <div class="col-xs-5">Peso total</div>
              <div class="col-xs-7 text-h5 text-weight-bold">{{$helpers.formatRS(dataset.peso, false, 3)}} KG</div>
            </div>
          </div>
        </q-tab-panel>
        <q-tab-panel name="checagem">
          <!-- inicio -->
          <q-card class="white full-width full-height q-mt-md" bordered style="min-height: 60vh" flat v-if="!retetiqueta.ok && (retetiqueta.msg ? retetiqueta.msg === '' : true)">
            <q-card-section v-if="!loading && (dataset ? dataset.status.value !== '1' : false)">
              <div class="row"  >
                <div class="col-12 text-h6 text-center rounded-borders q-pa-md q-my-md text-white" :class="'bg-' + dataset.status.color">
                  {{dataset.status.description}}
                </div>
              </div>
            </q-card-section>
            <q-card-section>
              <div class="text-center full-width q-pa-MD">
                <q-circular-progress size="170px" :indeterminate="loading" :show-value="!loading" :thickness="0.2"
                  :color="(dataset.volqtde > 0  && (dataset.conferidoqtde === dataset.volqtde)) ? 'positive' : 'accent'"
                  center-color="white" track-color="grey-3" class="q-ma-md" :value="dataset.conferidoprogresso" >
                  {{$helpers.formatRS(dataset.conferidoprogresso, false, 0)}} %
                </q-circular-progress>
              </div>
            </q-card-section>
            <q-card-section align="center" v-if="loading">
              <div class="text-h6">Carregando...</div>
            </q-card-section>
            <q-card-section align="center" v-if="!loading">
              <div class="text-h6">Itens conferidos</div>
              <div class="text-h4">
                {{dataset.conferidoqtde + ' de ' + dataset.volqtde}}
              </div>
            </q-card-section>
            <q-card-section>
              <div class="text-center full-width q-pa-lg">
                <q-btn color="positive" label="Encerrar carga" @click="actEncerrarCarga" size="lg" unelevated class="full-width" :disable="loading" v-if="(dataset.volqtde > 0  && (dataset.conferidoqtde === dataset.volqtde)) && podeEncerrar" />
                <q-btn color="primary" label="Ler etiqueta" @click="actScanBarcode" size="lg"  class="full-width" outline :disable="loading" v-if="dataset.volqtde > 0  && (dataset.conferidoqtde !== dataset.volqtde)" />
              </div>
            </q-card-section>
          </q-card>
          <!-- inicio -->
          <!-- validado -->
          <q-card class="bg-green text-white full-width full-height q-mt-md" style="min-height: 60vh" flat v-if="retetiqueta.ok">
            <q-card-section>
              <div class="text-center full-width">
                <div class="text-h4 text-weight-bold">Validado!</div>
                <q-circular-progress size="120px" show-value :thickness="0.2" color="white" center-color="green" track-color="green-2" class="q-ma-md" :value="dataset.conferidoprogresso" >
                  {{$helpers.formatRS(dataset.conferidoprogresso, false, 0)}} %
                </q-circular-progress>
              </div>
            </q-card-section>
            <q-card-section align="center">
              <div class="text-h6">
                {{retetiqueta.msg}}
              </div>
            </q-card-section>
            <q-card-section>
              <div class="text-center full-width q-pa-lg">
                <q-icon name="check_circle" size="120px" />
              </div>
            </q-card-section>
            <q-card-section>
              <div class="text-center full-width">
                <q-btn color="white" label="Ler novamente" @click="actScanBarcode"  class="full-width" outline size="lg"  />
              </div>
            </q-card-section>
          </q-card>
          <!-- validado -->
          <!-- erro -->
          <q-card class="bg-negative text-white full-width full-height q-mt-md" @click="actScanBarcode" style="min-height: 60vh" flat v-if="!retetiqueta.ok && (retetiqueta.msg ? retetiqueta.msg !== '' : false)">
            <q-card-section>
              <div class="text-center full-width">
                <q-icon name="error" size="70px" />
                <div class="text-h4 text-weight-bold q-pt-md">Erro!</div>
              </div>
            </q-card-section>
            <q-card-section align="center">
              <div class="text-h6">
                {{retetiqueta.msg}}
              </div>
            </q-card-section>
            <q-card-section>
              <div class="text-center full-width">
                <q-btn color="white" label="Ler novamente" @click="actScanBarcode"  class="full-width" outline size="lg"  />
              </div>
            </q-card-section>
          </q-card>
          <!-- erro -->
        </q-tab-panel>
      </q-tab-panels>
      <q-card v-if="expanded" class="bg-primary text-white full-width" square flat >
          <q-card-section>
            <q-btn flat @click="refreshData(false)" class="full-width">Sincronizar dados</q-btn>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat @click="expanded=!expanded" class="full-width">Fechar</q-btn>
          </q-card-actions>
      </q-card>
    </q-page>
  </q-page-container>

 <q-footer reveal elevated bordered v-if="dataset.pagination" >
    <q-toolbar>
      <q-toolbar-title class="text-caption">
        {{rows.length}} de {{dataset.pagination.rowsNumber}} registros
      </q-toolbar-title>
      <q-btn flat icon="add"  @click="refreshData(true)" label="Carregar mais" :loading="loading" v-if="(dataset.pagination.rowNumber > rows.length)" />
    </q-toolbar>
  </q-footer>
</q-layout>
</template>

<style>
</style>

<script>
// import CargasEntradas from 'src/mvc/collections/cargasentradas.js'
import CargaEntrada from 'src/mvc/models/cargaentrada.js'
export default {
  components: {
  },
  data: function () {
    let dataset = new CargaEntrada()
    return {
      tab: 'info',
      idstart: null,
      dataset,
      rows: [],
      ativos: true,
      error: null,
      text: '',
      loading: false,
      expanded: false,
      retetiqueta: { ok: false, msg: null }
    }
  },
  async mounted () {
    var app = this
    this.$store.commit('app/title', app.label)
    app.idstart = app.$route.params.id
    await console.clear()
    app.refreshData(false)
  },
  computed: {
    podeEncerrar: function () {
      try {
        var b = true
        if (this.loading) throw new Error('')
        if (!this.dataset) throw new Error('')
        if (!(this.dataset.id > 0)) throw new Error('')
        if (this.dataset.status.value !== '1') throw new Error('')
        if (this.dataset.erroqtde > 0) throw new Error('')
        if (this.dataset.volqtde === 0) throw new Error('')
        if (this.dataset.conferidoqtde !== this.dataset.volqtde) throw new Error('')
      } catch (error) {
        b = false
      }
      return b
    }
  },
  methods: {
    actScanBarcode () {
      this.retetiqueta = { ok: false, msg: null }
      let params = {
        'prompt_message': 'Escaneia o código da nota fiscal', // Change the info message. A blank message ('') will show a default message
        'orientation_locked': false, // Lock the orientation screen
        'camera_id': 0, // Choose the camera source
        'beep_enabled': true, // Enables a beep after the scan
        'scan_type': 'normal ', // Types of scan mode: normal = default black with white background / inverted = white bars on dark background / mixed = normal and inverted modes
        'barcode_formats': ['EAN_13'], // Put a list of formats that the scanner will find. A blank list ([]) will enable scan of all barcode types
        'extras': {} // Additional extra parameters. See [ZXing Journey Apps][1] IntentIntegrator and Intents for more details
      }
      window.plugins.zxingPlugin.scan(params, this.onSuccessScanBarCode, this.onFailureScanBarCode)
    },
    async onSuccessScanBarCode (data) {
      var app = this
      var dialog = app.$q.dialog({
        message: 'Validando código...',
        progress: true, // we enable default settings
        color: 'blue',
        persistent: true, // we want the user to not be able to close it
        ok: false // we want the user to not be able to close it
      })
      app.retetiqueta = await app.dataset.marcarconferido(data)
      dialog.hide()
      if (app.retetiqueta.ok) {
        if (app.retetiqueta.msg ? app.retetiqueta.msg === '' : true) {
          app.retetiqueta.msg = 'Código ' + data + ' conferido com sucesso!'
        }
        if (app.dataset.conferidoprogresso < 100) {
          setTimeout(() => {
            app.actScanBarcode()
          }, 1000)
        } else {
          setTimeout(() => {
            app.retetiqueta = { ok: false, msg: null }
          }, 1000)
        }
      } else {
        if (app.retetiqueta.msg ? app.retetiqueta.msg === '' : true) {
          app.retetiqueta.msg = 'Erro ao conferir etiqueta!'
        }
      }
    },
    onFailureScanBarCode (e) {
      if (e) {
        if (e !== 'cancelled') alert(e)
      }
      // this.closeBarCode('')
    },
    async actShowFilter () {
      var app = this
      var ret = await app.dataset.ShowDialogFilter(app)
      if (ret.ok) app.refreshData(false)
    },
    actClearText () {
      this.text = ''
    },
    async actEncerrarCarga () {
      var app = this
      app.loading = true
      var dialog = app.$q.dialog({
        message: 'Encerrando carga...',
        progress: true, // we enable default settings
        color: 'blue',
        persistent: true, // we want the user to not be able to close it
        ok: false // we want the user to not be able to close it
      })
      var ret = await app.dataset.alterarstatus('2')
      dialog.hide()
      if (ret.ok) {
        app.loading = false
      } else {
        app.loading = false
        var a = app.$helpers.showDialog(ret)
        await a.then(function () {})
      }
    },
    async refreshData (pLoadMore = false) {
      var app = this
      app.loading = true
      app.msgError = ''
      var ret = await app.dataset.find(app.idstart)
      if (!ret.ok) {
        var a = app.$helpers.showDialog(ret)
        await a.then(function () {})
      }
      app.loading = false
    }
  }
}
</script>
