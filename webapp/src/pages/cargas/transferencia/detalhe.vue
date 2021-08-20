<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title class="text-caption" >
        <div class="text-subtitle2" v-if="loading || (dataset ? !(dataset.id > 0) : true)" >Carga de Transferência</div>
        <div v-if="!loading && (dataset ? dataset.id > 0 : false)" class="text-body2">Carga de Transferência {{dataset ? '#' + dataset.id : ''}}</div>
        <div v-if="!loading && (dataset ? dataset.id > 0 : false)">{{dataset.unidadesaida.fantasia}} <q-icon name="east" /> {{dataset.unidadeentrada.fantasia}}</div>
      </q-toolbar-title>
      <q-btn round dense flat icon="more_vert"  v-if="!loading && (dataset ? dataset.id > 0 : false)"  >
          <q-menu auto-close  >
            <q-list style="min-width: 80vw">
              <q-item clickable v-close-popup @click="actChangeStatus">
                <q-item-section>Alterar status</q-item-section>
                <q-item-section avatar>
                  <q-icon color="grey-9" :name="dataset ? dataset.status.icon : 'info'" size="20px"  />
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable v-close-popup @click="refreshData(false)">
                <q-item-section>Atualizar</q-item-section>
                <q-item-section avatar>
                  <q-icon color="grey-9" name="sync" size="20px"  />
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable v-close-popup @click="actItemDeleteAll" v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false)">
                <q-item-section>Remover todos os itens</q-item-section>
                <q-item-section avatar>
                  <q-icon color="grey-9" name="clear" size="20px"  />
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable v-close-popup @click="refreshData" class="text-red" v-if="!loading && (dataset.itens ? (dataset.status.value === '1') : false)">
                <q-item-section>Excluir carga completa</q-item-section>
                <q-item-section avatar>
                  <q-icon color="red" name="delete" size="20px"  />
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
      </q-btn>
    </q-toolbar>
  </q-header>

  <q-page-container class="bg-grey-3" >
    <q-page>
      <q-tabs v-model="tab" class="bg-primary text-white" indicator-color="accent" inline-label v-if="!loading && podeConferir" >
        <q-tab name="info" icon="info" label="Info" />
        <q-tab name="checagem" icon="qr_code_scanner" label="Conferência de entrada"  />
      </q-tabs>
      <div class="q-pa-md text-center" v-if="loading">
        <div class="row" >
          <div class="col-12">
              <q-circular-progress size="100px" indeterminate :thickness="0.2" color="accent" center-color="white" track-color="grey-3" class="q-ma-lg" />
          </div>
        </div>
        <div class="text-h6">Carregando...</div>
      </div>
      <div class="q-pa-md" v-if="!loading && (msgError ? msgError !== '' : false)">
          <q-banner class="bg-negative text-white" rounded>
            {{msgError}}
            <template v-slot:action>
              <q-btn unelevated color="negative" label="Tentar novamente" @click="refreshData" />
            </template>
          </q-banner>
      </div>
      <q-tab-panels v-model="tab" animated >
        <q-tab-panel name="info" class="q-pa-none" v-if="!loading && (dataset ? dataset.id > 0 : false)">
          <div class="q-pa-sm" >
            <q-card class="full-width q-ma-sm" bordered flat>
              <q-card-section class="q-py-xs">
                  <div class="text-h6">Carga de Transferência</div>
              </q-card-section>
              <q-separator  />
              <q-card-section class="q-py-xs">
                  <div class="row q-col-gutter-xs" >
                    <div class="col-xs-4">Número</div>
                    <div class="col-xs-8 text-weight-bold">{{dataset.id}}</div>
                    <div class="col-xs-4">Status</div>
                    <div class="col-xs-6 text-weight-bold">
                      <q-avatar size="20px" font-size="16px" :color="dataset.status.color" text-color="white" :icon="dataset.status.icon" class="q-mr-xs" />
                      {{dataset.status.description}}
                    </div>
                    <div class="col-xs-2">
                      <q-btn color="primary" flat dense label="Alterar" @click="actChangeStatus" />
                    </div>
                    <div class="col-12 " >
                      <q-card class="my-card" flat bordered>
                        <q-card-section class="q-py-xs bg-grey-3">
                          <div class="text-subtitle2">Saída</div>
                        </q-card-section>
                        <q-card-section class="q-py-xs">
                          <div class="row" >
                            <div class="col-xs-4" v-if="dataset.saidadh">Data</div>
                            <div class="col-xs-8 text-weight-bold" v-if="dataset.saidadh">{{$helpers.datetimeToBR(dataset.saidadh, false, true)}}</div>
                            <div class="col-xs-4">Unidade</div>
                            <div class="col-xs-8 text-weight-bold">{{dataset.unidadesaida.fantasia}}</div>
                          </div>
                        </q-card-section>
                      </q-card>
                    </div>
                    <div class="col-12 q-mb-sm" >
                      <q-card class="my-card" flat bordered>
                        <q-card-section class="q-py-xs bg-grey-3">
                          <div class="text-subtitle2">Entrada</div>
                        </q-card-section>
                        <q-card-section class="q-py-xs">
                          <div class="row" >
                            <div class="col-xs-4" v-if="dataset.entradadh">Data</div>
                            <div class="col-xs-8 text-weight-bold" v-if="dataset.entradadh">{{$helpers.datetimeToBR(dataset.entradadh, false, true)}}</div>
                            <div class="col-xs-4">Unidade</div>
                            <div class="col-xs-8 text-weight-bold">{{dataset.unidadeentrada.fantasia}}</div>
                          </div>
                        </q-card-section>
                      </q-card>
                    </div>
                    <div class="col-xs-4" >Motorista</div>
                    <div class="col-xs-8 text-weight-bold">{{dataset.motorista.nome}}</div>
                    <div class="col-xs-4">Veículo</div>
                    <div class="col-xs-8 text-weight-bold">{{dataset.veiculo.placa}}</div>
                  </div>
                  <div class="row q-my-sm" v-if="!loading && (dataset ? dataset.erroqtde > 0 : false)" >
                    <div class="col-12">
                      <q-banner class="bg-red-1 text-red q-pa-sm" rounded>
                        <div>
                          <span class="text-weight-bold" v-if="dataset.erroqtde === 1">Existe {{dataset.erroqtde}} erro</span>
                          <span class="text-weight-bold" v-else>Existem {{dataset.erroqtde}} erros</span>
                          <ul>
                            <li v-for="(erro, k) in dataset.erromsg" :key="k">{{erro.msg}}</li>
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
              </q-card-section>
            </q-card>
          </div>
          <q-toolbar class="bg-primary text-white">
            <q-toolbar-title>
              Itens
            </q-toolbar-title>
            <q-btn flat dense icon="add" @click="actScanBarcode" label="adicionar"  v-if="dataset.status.value === '1'" />
            <q-separator spaced inset vertical dark v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false)" />
            <q-btn flat round dense icon="delete" class="q-ml-xs" @click="actItemDeleteAll"
                v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false) && (selected_row ? selected_row.length === 0 : true)" />
            <q-btn flat round dense icon="delete" class="q-ml-xs" @click="actItemDeleteSelected"
              v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) && (dataset.status.value === '1') : false) && (selected_row ? selected_row.length > 0 : false)" >
              <q-badge color="yellow-9" text-color="white" :label="selected_row.length" floating rounded />
            </q-btn>
          </q-toolbar>
          <div class="row" v-if="(dataset.itens ? dataset.itens.length === 0 : true)"  >
            <div class="col-xs-12">
              <div class="row text-center q-pa-lg" >
                <div class="col-xs-12">Nenhum item na carga!</div>
                <div class="col-xs-12 q-mt-md"><q-btn color="primary" icon="add" label="adicionar" @click="actScanBarcode" size="lg"  unelevated  v-if="dataset.status.value === '1'" /></div>
              </div>
            </div>
          </div>
          <div class="row" v-if="(dataset.itens ? dataset.itens.length > 0 : false)" >
            <div class="col-xs-12">
              <q-list class="full-width" separator transition-show="slide-right"  transition-hide="slide-left"  >
                <q-item v-for="(item, key) in dataset.itens" :key="key" :class="(selected_row ? selected_row.indexOf(item.id) >= 0 : false) ? 'bg-yellow-2' : ((key % 2) === 0 ? 'bg-white' : 'bg-grey-1')" >
                  <q-item-section>
                    <q-item-label>
                      <div class="row text-h6">
                        <div class="col-xs-5">{{item.etiqueta.volume}}</div>
                        <div class="col-xs-7 text-right">{{ item.etiqueta.ean13 }}</div>
                      </div>
                    </q-item-label>
                    <q-item-label >
                      <div class="row">
                        <div class="col-xs-5">
                          Peso: {{$helpers.formatRS(item.etiqueta.pesoindividual, false, 3)}} KG
                          <q-tooltip :delay="500">
                            <div>Proporcional por etiqueta: {{$helpers.formatRS(item.etiqueta.pesoindividual, false, 3)}} Kg</div>
                            <div>Total das etiquetas: {{$helpers.formatRS(item.etiqueta.pesototal, false, 3)}} Kg</div>
                          </q-tooltip>
                        </div>
                        <div class="col-xs-7 text-right">
                          <span v-if="item.etiqueta ? item.etiqueta.ean13 !== '' : false">Nota Fiscal: {{item.etiqueta.cargaentradaitem.nfenumero}}</span>
                          <span v-else>Sem Nota Fiscal</span>
                        </div>
                        <div class="col-xs-12 text-right">
                          <div v-if="(item.coletaid ? (item.coletaid > 0) : false)">Coleta #{{ item.coletaid }}</div>
                          <div v-if="(item.coletaid ? (item.coletaid === 0) : true)">Sem coleta</div>
                        </div>
                        <div class="col-xs-12 text-right" v-if="(item.etiqueta ? (item.etiqueta.palete ? item.etiqueta.palete.id > 0 : false) : false)">
                          <div class="text-weight-bold">
                          Palete: #{{item.etiqueta.palete.id}} - {{item.etiqueta.palete.descricao}}
                          </div>
                        </div>
                      </div>
                    </q-item-label>
                    <q-item-label v-if="['3', '4'].indexOf(dataset.status.value) >= 0">
                       <div class="row" v-if="item.conferidoentrada">
                        <div class="col-12">
                          <div class="col-12 text-center bg-green-1 rounded-borders q-pa-xs">
                          <q-icon name="check_circle" size="20px" color="positive" class="q-mr-sm" /> Entrada conferida!
                          </div>
                        </div>
                      </div>
                      <div class="row" v-else>
                        <div class="col-12 text-center bg-grey-2 rounded-borders q-pa-xs">
                          <q-icon name="check_circle" size="20px" color="grey-4" class="q-mr-sm" /> Pendente conferência de entrada
                        </div>
                      </div>
                    </q-item-label>
                    <q-item-label v-if="linhaComErro(item)">
                      <q-banner class="bg-red-1 text-red q-py-xs" rounded>
                        Contêm erros!
                        <q-tooltip :delay="500">
                          <div v-for="(msg, keylinh) in linhaComErrosDoGrupo(item)" :key="keylinh">{{msg}}</div>
                        </q-tooltip>
                      </q-banner>
                      <q-btn label="Corrigir" outline icon="error" class="full-width" color="red" v-if="linhaComErro(item) && dataset.status.value === '1'"  >
                        <q-menu auto-close anchor="center middle" self="center middle"  >
                          <q-list style="min-width: 100px" >
                            <q-item clickable v-close-popup @click="actItemImportarPorGrupoRestante(item.etiqueta.cargaentradaitem.id)">
                                <q-item-section avatar>
                                  <q-icon color="grey-9" name="done_all" size="20px"  />
                                </q-item-section>
                              <q-item-section>Adicionar todas abaixo</q-item-section>
                            </q-item>
                            <q-separator />
                            <div v-for="(erro, k) in dataset.erromsg" :key="'erro' + k">
                              <q-item clickable v-close-popup v-if="(erro.cargaentradaitemid === item.etiqueta.cargaentradaitem.id)"
                                @click="actItemImportarPorEtiquetas([erro.etiqueta])">
                                <q-item-section avatar>
                                  <q-icon color="grey-9" name="fas fa-barcode" size="20px"  />
                                </q-item-section>
                                <q-item-section>Adicionar vol. {{erro.etiqueta.volume}} - Código: {{erro.etiqueta.ean13 }}</q-item-section>
                              </q-item>
                            </div>
                          </q-list>
                        </q-menu>
                      </q-btn>
                    </q-item-label>
                  </q-item-section>
                  <q-item-section top side>
                    <div>
                      <q-btn color="red" icon="delete" @click="actItemDelete(item)" round flat  v-if="dataset.status.value === '1'" />
                    </div>
                    <div class="q-mt-lg">
                      <q-checkbox left-label v-model="selected_row" :val="item.id" color="yellow-9"  size="lg" />
                    </div>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>
          </div>
          <div class="q-pa-md text-h6 bg-white" >
            <q-separator spaced />
            <div class="row text-right" >
              <div class="col-xs-5">Volumes</div>
              <div class="col-xs-7 text-h5 text-weight-bold">{{dataset.volqtde}}</div>
              <div class="col-xs-5">Peso total</div>
              <div class="col-xs-7 text-h5 text-weight-bold">{{$helpers.formatRS(dataset.peso, false, 3)}} KG</div>
            </div>
          </div>
        </q-tab-panel>
        <q-tab-panel name="checagem"  v-if="!loading && podeConferir">
          <!-- inicio -->
          <q-card class="white full-width full-height q-mt-md" bordered style="min-height: 60vh" flat v-if="!retetiqueta.ok && (retetiqueta.msg ? retetiqueta.msg === '' : true)">
            <q-card-section align="center" v-if="!loading">
              <div class="text-h6">Itens conferidos</div>
              <div class="text-h4">
                {{dataset.conferidoentradaqtde + ' de ' + dataset.volqtde}}
              </div>
            </q-card-section>
            <q-card-section>
              <div class="text-center full-width q-pa-lg">
                <q-btn color="positive" label="Encerrar carga" @click="actEncerrarCarga" size="lg" unelevated class="full-width" :disable="loading" v-if="(dataset.volqtde > 0  && (dataset.conferidoentradaqtde === dataset.volqtde)) && podeEncerrar" />
                <q-btn color="primary" label="Ler etiqueta" @click="actScanBarcodeConferencia" size="lg"  class="full-width" outline :disable="loading" v-if="dataset.volqtde > 0  && (dataset.conferidoentradaqtde !== dataset.volqtde)" />
              </div>
            </q-card-section>
          </q-card>
          <!-- inicio -->
          <!-- validado -->
          <q-card class="bg-green text-white full-width full-height q-mt-md" style="min-height: 60vh" flat v-if="retetiqueta.ok">
            <q-card-section>
              <div class="text-center full-width">
                <div class="text-h4 text-weight-bold">Validado!</div>
                <q-circular-progress size="120px" show-value :thickness="0.2" color="white" center-color="green" track-color="green-2" class="q-ma-md" :value="dataset.conferidoentradaprogresso" >
                  {{$helpers.formatRS(dataset.conferidoentradaprogresso, false, 0)}} %
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
                <q-btn color="white" label="Ler novamente" @click="actScanBarcodeConferencia"  class="full-width" outline size="lg"  />
              </div>
            </q-card-section>
          </q-card>
          <!-- validado -->
          <!-- erro -->
          <q-card class="bg-negative text-white full-width full-height q-mt-md" @click="actScanBarcodeConferencia" style="min-height: 60vh" flat v-if="!retetiqueta.ok && (retetiqueta.msg ? retetiqueta.msg !== '' : false)">
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
                <q-btn color="white" label="Ler novamente" @click="actScanBarcodeConferencia"  class="full-width" outline size="lg"  />
              </div>
            </q-card-section>
          </q-card>
          <!-- erro -->
        </q-tab-panel>
      </q-tab-panels>
    </q-page>
  </q-page-container>
</q-layout>
</template>

<style>
</style>

<script>
import CargaTransfer from 'src/mvc/models/cargatransfer.js'
import CargaTransferItem from 'src/mvc/models/cargatransferitem.js'
export default {
  components: {
  },
  data: function () {
    let dataset = new CargaTransfer()
    return {
      tab: 'checagem',
      msgError: null,
      idstart: null,
      dataset,
      rows: [],
      selected_row: [],
      ativos: true,
      error: null,
      unidadelogada: null,
      text: '',
      loading: false,
      expanded: false,
      retetiqueta: { ok: false, msg: null }
    }
  },
  async mounted () {
    var app = this
    this.$store.commit('app/title', app.label)
    app.unidadelogada = await app.$helpers.getUnidadeLogada(app)
    app.idstart = app.$route.params.id
    app.refreshData()
  },
  computed: {
    podeEncerrar: function () {
      try {
        var b = true
        if (this.loading) throw new Error('')
        if (!this.dataset) throw new Error('')
        if (!(this.dataset.id > 0)) throw new Error('')
        if (this.dataset.status.value !== '3') throw new Error('')
        if (this.dataset.erroqtde > 0) throw new Error('')
        if (this.dataset.volqtde === 0) throw new Error('')
        if (this.dataset.conferidoentradaqtde !== this.dataset.volqtde) throw new Error('')
      } catch (error) {
        b = false
      }
      return b
    },
    podeConferir: function () {
      try {
        var b = true
        if (this.loading) throw new Error('loading')
        if (!this.dataset) throw new Error('Sem dataset')
        if (!(this.dataset.id > 0)) throw new Error('')
        if (this.dataset.status.value !== '3') throw new Error('status.value !== 3')
        if (this.dataset.erroqtde > 0) throw new Error('dataset.erroqtde > 0')
        if (this.dataset.volqtde === 0) throw new Error('dataset.volqtde === 0')
        if (this.unidadelogada ? !(this.unidadelogada.id > 0) : true) throw new Error('Sem unidadelogada')
        if (this.unidadelogada.id !== this.dataset.unidadeentrada.id) throw new Error('unidadelogada.id !== this.dataset.unidadeentrada.id')
      } catch (error) {
        b = false
        console.error(error)
      }
      return b
    }
  },
  methods: {
    async actChangeStatus () {
      var app = this
      var ret = await app.dataset.ShowChangeStatus(app)
      if (!ret.ok) {
        if (ret.msg) {
          if (ret.msg !== '') app.$helpers.showDialog(ret)
        }
      }
    },
    async addEtiqueta (pEAN) {
      var app = this
      try {
        if (app.dataset.status.value !== '1') throw new Error('Carga com edição bloqueada')
      } catch (error) {
        return
      }
      var tevealteracao = false
      const dialog = this.$q.dialog({
        message: 'Inserindo etiqueta ' + pEAN,
        progress: true, // we enable default settings
        persistent: true, // we want the user to not be able to close it
        ok: false // we want the user to not be able to close it
      })
      var eans = []
      eans.push(pEAN)

      var carga = new CargaTransferItem()
      carga.cargatransferid = app.dataset.id
      var retSave = await carga.save(eans)
      if (retSave.ok) {
        if (!app.dataset.itens) app.dataset.itens = []
        app.dataset.itens.push(carga)
        tevealteracao = true
      } else {
        var msg = ''
        if (retSave.msg) {
          if (retSave.msg !== '') msg = retSave.msg
        }
        var detail = []
        if (retSave.data) {
          for (let index = 0; index < retSave.data.length; index++) {
            const item = retSave.data[index]
            detail.push('Código: <b>' + item.ean13 + '</b> : ' + item.erro)
          }
        } else {
          if (retSave.msg ? retSave.msg !== '' : false) detail.push(retSave.msg)
        }
        app.$q.dialog({
          title: 'Falha ao inserir itens!',
          message: msg + (detail.length > 0 ? '<br><br>' + detail.join('<br>') : ''),
          color: 'red',
          html: true
        })
      }
      dialog.hide()

      if (tevealteracao) app.refreshData()
    },
    linhaComErro (pLinha) {
      var app = this
      var b = false
      if (app.dataset.erroqtde > 0) {
        if (pLinha) {
          for (let index = 0; index < app.dataset.erromsg.length; index++) {
            const element = app.dataset.erromsg[index]
            if (element.cargaentradaitemid === pLinha.etiqueta.cargaentradaitem.id) {
              b = true
              break
            }
          }
        }
      }
      return b
    },
    async actItemDeleteAll () {
      var app = this
      try {
        if (app.dataset.status.value !== '1') throw new Error('Carga com edição bloqueada')
      } catch (error) {
        return
      }
      var itens = []
      for (let index = 0; index < app.dataset.itens.length; index++) {
        const element = app.dataset.itens[index]
        itens.push(element)
      }
      var ret = await app.dataset.itemDelete(app, itens)
      if (!ret.ok) {
        if (ret.msg) {
          if (ret.msg !== '') app.$helpers.showDialog(ret)
        }
      } else {
        app.selected_row = []
      }
      app.deleting = false
    },
    async actItemDeleteSelected () {
      var app = this
      try {
        if (app.dataset.status.value !== '1') throw new Error('Carga com edição bloqueada')
        if (!app.selected_row) throw new Error('Nenhum item selecionado')
        if (app.selected_row.length === 0) throw new Error('Nenhum item selecionado')
      } catch (error) {
        return
      }
      var itens = []
      for (let index = 0; index < app.dataset.itens.length; index++) {
        const element = app.dataset.itens[index]
        if (app.selected_row.indexOf(element.id) >= 0) {
          itens.push(element)
        }
      }
      var ret = await app.dataset.itemDelete(app, itens)
      if (!ret.ok) {
        if (ret.msg) {
          if (ret.msg !== '') app.$helpers.showDialog(ret)
        }
      } else {
        app.selected_row = []
      }
      app.deleting = false
    },
    async actItemDelete (item) {
      var app = this
      try {
        if (app.dataset.status.value !== '1') throw new Error('Carga com edição bloqueada')
      } catch (error) {
        return
      }
      if (!item) return
      var ret = await app.dataset.itemDelete(app, [item])
      if (!ret.ok) {
        if (ret.msg) {
          if (ret.msg !== '') app.$helpers.showDialog(ret)
        }
      } else {
        app.selected_row = []
      }
      app.deleting = false
    },
    linhaComErrosDoGrupo (pLinha) {
      var app = this
      var b = []
      if (app.dataset.erroqtde > 0) {
        if (pLinha) {
          for (let index = 0; index < app.dataset.erromsg.length; index++) {
            const element = app.dataset.erromsg[index]
            if (element.cargaentradaitemid === pLinha.etiqueta.cargaentradaitem.id) {
              b.push(element.msg)
            }
          }
        }
      }
      return b
    },
    async actItemImportarPorEtiquetas (pEtiquetas) {
      var app = this
      try {
        if (!pEtiquetas) throw new Error('Sem etiqueta informada')
        if (app.dataset.status.value !== '1') throw new Error('Carga com edição bloqueada')
      } catch (error) {
        return
      }
      const dialog = this.$q.dialog({
        message: 'Inserindo etiquetas... 0%',
        progress: true, // we enable default settings
        persistent: true, // we want the user to not be able to close it
        ok: false // we want the user to not be able to close it
      })
      let percentage = 0
      for (let index = 0; index < pEtiquetas.length; index++) {
        percentage = Math.floor((index / pEtiquetas.length) * 100)

        // we update the dialog
        dialog.update({
          message: 'Inserindo etiquetas ' + (index + 1) + ' de ' + pEtiquetas.length + ' - ' + percentage + '%'
        })
        const etiqueta = pEtiquetas[index]

        var carga = new CargaTransferItem()
        carga.cargatransferid = app.dataset.id
        carga.etiqueta = etiqueta
        var retSave = await carga.save()
        if (retSave.ok) {
          if (!app.dataset.itens) app.dataset.itens = []
          app.dataset.itens.push(carga)
        } else {
          var msg = ''
          if (retSave.msg) {
            if (retSave.msg !== '') msg = retSave.msg
          }
          app.$q.notify({
            message: 'Item ' + (index + 1) + ' de ' + pEtiquetas.length + ' não foi inserido!',
            color: 'red',
            group: 'erros',
            caption: msg,
            actions: [
              { label: 'OK', color: 'white', handler: () => { /* ... */ } }
            ]
          })
        }
      }
      dialog.hide()
      app.refreshData()
    },
    async actItemImportarPorGrupoRestante (pCargaentradaitemid) {
      var app = this
      if (app.dataset.erroqtde <= 0) return
      if (!app.dataset.erromsg) return
      var etiquetas = []
      for (let index = 0; index < app.dataset.erromsg.length; index++) {
        var element = app.dataset.erromsg[index]
        if (element.cargaentradaitemid === pCargaentradaitemid) {
          etiquetas.push(element.etiqueta)
        }
      }
      if (etiquetas.length > 0) {
        app.actItemImportarPorEtiquetas(etiquetas)
      }
    },
    actScanBarcode () {
      this.retetiqueta = { ok: false, msg: null }
      let params = {
        'prompt_message': 'Ler etiqueta de volume', // Change the info message. A blank message ('') will show a default message
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
      app.addEtiqueta(data)
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
      if (ret.ok) app.refreshData()
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

    actScanBarcodeConferencia () {
      this.retetiqueta = { ok: false, msg: null }
      let params = {
        'prompt_message': 'Transferência :: Ler etiqueta de volume', // Change the info message. A blank message ('') will show a default message
        'orientation_locked': false, // Lock the orientation screen
        'camera_id': 0, // Choose the camera source
        'beep_enabled': true, // Enables a beep after the scan
        'scan_type': 'normal ', // Types of scan mode: normal = default black with white background / inverted = white bars on dark background / mixed = normal and inverted modes
        'barcode_formats': ['EAN_13'], // Put a list of formats that the scanner will find. A blank list ([]) will enable scan of all barcode types
        'extras': {} // Additional extra parameters. See [ZXing Journey Apps][1] IntentIntegrator and Intents for more details
      }
      window.plugins.zxingPlugin.scan(params, this.onSuccessScanBarCodeConferencia, this.onFailureScanBarCodeeConferencia)
    },
    async onSuccessScanBarCodeConferencia (data) {
      var app = this
      var dialog = app.$q.dialog({
        message: 'Conferindo etiqueta...',
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
        if (app.dataset.conferidoentradaprogresso < 100) {
          setTimeout(() => {
            app.actScanBarcodeConferencia()
          }, 1000)
        } else {
          setTimeout(() => {
            app.retetiqueta = { ok: false, msg: null }
            app.refreshData()
          }, 1000)
        }

        // await app.dataset.cloneFrom(lCarga)
      } else {
        if (app.retetiqueta.msg ? app.retetiqueta.msg === '' : true) {
          app.retetiqueta.msg = 'Erro ao conferir etiqueta!'
        }
      }
    },
    onFailureScanBarCodeeConferencia (e) {
      if (e) {
        if (e !== 'cancelled') alert(e)
      }
      // this.closeBarCode('')
    },
    async refreshData () {
      var app = this
      app.loading = true
      app.msgError = null
      var ret = await app.dataset.find(app.idstart)
      if (!ret.ok) {
        this.$q.notify({
          message: ret.msg,
          color: 'negative',
          timeout: 4000
        })
        app.msgError = ret.msg
        app.tab = 'info'
        app.loading = false
      } else {
        app.selected_row = []
        app.$nextTick(() => {
          if (!app.podeConferir) app.tab = 'info'
        })
      }
      app.loading = false
    }
  }
}
</script>
