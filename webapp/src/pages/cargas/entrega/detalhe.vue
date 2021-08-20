<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title class="text-caption" v-if="!loading">
        <div class="text-body2">Carga de Entrega {{dataset ? '#' + dataset.id : ''}}</div>
        <div>{{dataset.unidadesaida.fantasia}}</div>
      </q-toolbar-title>
      <q-btn round dense flat icon="more_vert"  >
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
              <q-item clickable v-close-popup @click="refreshData(false)" class="text-red" v-if="!loading && (dataset.itens ? (dataset.status.value === '1') : false)">
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
        <div class="q-pa-md text-center" v-if="loading">
          <div class="row" >
            <div class="col-12">
                <q-circular-progress size="100px" indeterminate :thickness="0.2" color="accent" center-color="white" track-color="grey-3" class="q-ma-lg" />
            </div>
          </div>
          <div class="text-h6">Carregando...</div>
        </div>
        <div class="q-pa-sm"  v-if="!loading">
          <q-card class="full-width q-ma-sm" bordered flat>
            <q-card-section class="q-py-xs">
                <div class="text-h6">Carga de Entrega</div>
            </q-card-section>
            <q-separator  />
            <q-card-section class="q-py-xs">
                <div class="row q-col-gutter-xs" >
                  <div class="col-xs-3">Número</div>
                  <div class="col-xs-4 text-weight-bold">{{dataset.id}}</div>
                  <div class="col-xs-5 text-right">Senha: {{dataset.senha}}</div>
                  <div class="col-xs-3">Status</div>
                  <div class="col-xs-6 text-weight-bold">
                    <q-avatar size="20px" font-size="16px" :color="dataset.status.color" text-color="white" :icon="dataset.status.icon" class="q-mr-xs" />
                    {{dataset.status.description}}
                  </div>
                  <div class="col-xs-3">
                    <q-btn color="primary"  label="Alterar" @click="actChangeStatus" outline />
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
        <q-toolbar class="bg-primary text-white"  v-if="!loading">
          <q-toolbar-title>
            Itens
          </q-toolbar-title>
          <q-btn flat dense icon="add" @click="actScanBarcode" label="adicionar" v-if="dataset.status.value === '1'" />
          <q-separator spaced inset vertical dark v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false) && (selected_row ? selected_row.length > 0 : false)" />
          <q-btn flat dense icon="edit" @click="actItemEdit(selected_row)" label="Informar CT-e" v-if="dataset.status.value === '1'  && (selected_row ? selected_row.length > 0 : false)" >
            <q-badge color="yellow-9" text-color="white" :label="selected_row.length" floating rounded />
          </q-btn>
          <q-separator spaced inset vertical dark v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false)" />
          <q-btn flat round dense icon="delete" class="q-ml-xs" @click="actItemDeleteAll"
            v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false) && (selected_row ? selected_row.length === 0 : true)" />
          <q-btn flat round dense icon="delete" class="q-ml-xs" @click="actItemDeleteSelected"
            v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false) && (selected_row ? selected_row.length > 0 : false)" >
            <q-badge color="yellow-9" text-color="white" :label="selected_row.length" floating rounded />
          </q-btn>
        </q-toolbar>
        <div class="row" v-if="!loading && (dataset.itens ? dataset.itens.length === 0 : true)"  >
          <div class="col-xs-12">
            <div class="row text-center q-pa-lg" >
              <div class="col-xs-12">Nenhum item na carga!</div>
              <div class="col-xs-12 q-mt-md"><q-btn color="primary" icon="add" label="adicionar" @click="actScanBarcode" size="lg"  unelevated  v-if="dataset.status.value === '1'" /></div>
            </div>
          </div>
        </div>
        <div class="row" v-if="!loading && (dataset.itens ? dataset.itens.length > 0 : false)" >
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
                      <div class="col-xs-6">
                        <div v-if="item.ctenumero ? !(item.ctenumero > 0) : true" class="text-red">Sem CT-e</div>
                        <div v-else>CT-e {{ item.ctenumero }}</div>
                      </div>
                      <div class="col-xs-6 text-right">
                        <div v-if="(item.coletaid ? (item.coletaid > 0) : false)">Coleta #{{ item.coletaid }}</div>
                        <div v-if="(item.coletaid ? (item.coletaid === 0) : true)">Sem coleta</div>
                      </div>
                      <div class="col-xs-12 text-right" v-if="item.ctenumero ? !(item.ctenumero > 0) : true">
                        <q-btn icon="edit" @click="actItemEdit([item.id])" class="full-width q-my-sm" outline label="Informar CT-e"  />
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
                          <q-item clickable v-close-popup @click="actItemEdit([item.id])">
                              <q-item-section avatar>
                                <q-icon color="grey-9" name="edit" size="20px"  />
                              </q-item-section>
                            <q-item-section>Informar/editar CT-e</q-item-section>
                          </q-item>
                          <q-separator />
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
                  <div class="q-mt-lg">
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
        <div class="q-pa-md text-h6 bg-white" v-if="!loading">
          <q-separator spaced />
          <div class="row text-right" >
            <div class="col-xs-5">Volumes</div>
            <div class="col-xs-7 text-h5 text-weight-bold">{{dataset.volqtde}}</div>
            <div class="col-xs-5">Peso total</div>
            <div class="col-xs-7 text-h5 text-weight-bold">{{$helpers.formatRS(dataset.peso, false, 3)}} KG</div>
          </div>
        </div>
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
</q-layout>
</template>

<style>
</style>

<script>
import CargaEntrega from 'src/mvc/models/cargaentrega.js'
import CargaEntregaItem from 'src/mvc/models/cargaentregaitem.js'
export default {
  components: {
  },
  data: function () {
    let dataset = new CargaEntrega()
    return {
      tab: 'info',
      itemEdit: null,
      idstart: null,
      dataset,
      selected_row: [],
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
    async actChangeStatus () {
      var app = this
      var ret = await app.dataset.ShowChangeStatus(app)
      if (!ret.ok) {
        if (ret.msg) {
          if (ret.msg !== '') app.$helpers.showDialog(ret)
        }
      }
    },
    async actItemEdit (pItens) {
      var app = this
      try {
        var chaveatual = null
        var numeroatual = null
        app.itemEdit = []
        for (let index = 0; index < app.dataset.itens.length; index++) {
          const element = app.dataset.itens[index]
          if (pItens.indexOf(element.id) >= 0) {
            if (!chaveatual && element.ctechave) {
              chaveatual = element.ctechave
              numeroatual = element.ctenumero
            }
            app.itemEdit.push(element)
          }
        }
        if (app.itemEdit.length === 0) throw new Error('Nenhum item selecionado')
        if (chaveatual ? chaveatual !== '' : false) {
          var question = new Promise((resolve) => {
            app.$q.dialog({
              title: 'Informar CT-e?',
              message: 'Aplicar o CT-e atual para os itens selecionados?' + '<br>Nº: ' + numeroatual,
              html: true,
              cancel: true,
              options: {
                type: 'radio',
                model: 'atual',
                // inline: true
                items: [
                  { label: 'Usar CT-e ' + numeroatual, value: 'atual' },
                  { label: 'Usar câmera/leitor', value: 'leitor' }
                ]
              }
            }).onOk(async data => {
              resolve(data)
            }).onCancel(() => {
              resolve(null)
            })
          })
          var resposta = await question
          if (!resposta) throw new Error('Abortado pelo usuário')
          if (resposta === 'atual') {
            this.onSuccessScanCTe(chaveatual)
            throw new Error('End : usando atual')
          }
        }
      } catch (error) {
        console.error(error)
        return
      }

      let params = {
        'prompt_message': 'Leia QrCode ou código de barra do CT-e', // Change the info message. A blank message ('') will show a default message
        'orientation_locked': false, // Lock the orientation screen
        'camera_id': 0, // Choose the camera source
        'beep_enabled': true, // Enables a beep after the scan
        'scan_type': 'normal ', // Types of scan mode: normal = default black with white background / inverted = white bars on dark background / mixed = normal and inverted modes
        'barcode_formats': ['EAN_128'], // Put a list of formats that the scanner will find. A blank list ([]) will enable scan of all barcode types
        'extras': {} // Additional extra parameters. See [ZXing Journey Apps][1] IntentIntegrator and Intents for more details
      }
      window.plugins.zxingPlugin.scan(params, this.onSuccessScanCTe)
    },
    async onSuccessScanCTe (data) {
      var app = this
      var ids = []
      const dialog = this.$q.dialog({
        message: 'Atualizando dados, aguarde...',
        progress: true, // we enable default settings
        persistent: true, // we want the user to not be able to close it
        ok: false // we want the user to not be able to close it
      })
      try {
        for (let index = 0; index < app.itemEdit.length; index++) {
          const element = app.itemEdit[index]
          ids.push(element.id)
        }
        var datacte = data
        if ((datacte.length > 0) && (datacte.length > 44)) datacte = await app.$helpers.cteExtraiQrCode(datacte)
        var item = app.itemEdit[0]
        item.ctechave = datacte
        var ret = await item.saveedit(ids)
        if (ret.ok) {
          dialog.update({
            message: 'Alteração concluída, atualizando tela...'
          })
          app.$q.notify({
            message: 'Alteração concluida!',
            color: 'positive'
          })
          await app.refreshData()
        } else {
          dialog.hide()
          if (ret.msg) {
            if (ret.msg !== '') app.$helpers.showDialog(ret)
          }
        }
      } finally {
        app.itemEdit = null
        try {
          dialog.hide()
        } catch (error) {
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

      var carga = new CargaEntregaItem()
      carga.cargaentregaid = app.dataset.id
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
            if ((element.cargaentradaitemid === pLinha.etiqueta.cargaentradaitem.id) || (element.id === pLinha.id)) {
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
        if (app.selected_row.indexOf(element.id) >= 0) itens.push(element)
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
            if ((element.cargaentradaitemid === pLinha.etiqueta.cargaentradaitem.id) || (element.id === pLinha.id)) {
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

        var carga = new CargaEntregaItem()
        carga.cargaentregaid = app.dataset.id
        carga.etiqueta = etiqueta
        var retSave = await carga.save()
        if (retSave.ok) {
          if (!app.dataset.itens) app.dataset.itens = []
          app.dataset.itens.push(carga)
          app.selected_row = []
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
    async refreshData () {
      var app = this
      app.loading = true
      app.msgError = ''
      var ret = await app.dataset.find(app.idstart)
      if (!ret.ok) {
        var a = app.$helpers.showDialog(ret)
        await a.then(function () {})
      } else {
        app.selected_row = []
      }
      app.loading = false
    }
  }
}
</script>
