<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title  v-if="loading">Consultando palete...</q-toolbar-title>
      <q-toolbar-title class="text-caption" v-if="!loading">
        <div class="text-body2">Palete {{dataset ? dataset.ean13 : ''}}</div>
        <div>{{dataset.unidade.fantasia}}</div>
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
              <q-item clickable v-close-popup @click="refreshData(false)" class="text-red" v-if="!loading && (dataset ? (dataset.status.value === '1') : false)">
                <q-item-section>Excluir palete inteiro</q-item-section>
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
                <div class="text-h6">Palete {{dataset.ean13}}</div>
            </q-card-section>
            <q-separator  />
            <q-card-section class="q-py-xs">
               <div class="row q-col-gutter-xs" >
                  <div class="col-xs-9 text-h6">{{dataset.descricao}}</div>
                  <div class="col-xs-3 text-right">
                    <q-btn color="primary" icon="edit" size="sm" unelevated @click="actEditDescricao" />
                  </div>
               </div>
            </q-card-section>
            <q-separator  />
            <q-card-section class="q-py-xs">
                <div class="row q-col-gutter-xs" >
                  <div class="col-xs-4">id</div>
                  <div class="col-xs-8 text-weight-bold">{{dataset.id}}</div>
                  <div class="col-xs-4">Unidade</div>
                  <div class="col-xs-8 text-weight-bold">{{dataset.unidade.fantasia}}</div>
                  <div class="col-xs-4">Criado em</div>
                  <div class="col-xs-8 text-weight-bold">{{$helpers.datetimeToBR(dataset.created_at)}}</div>
                  <div class="col-xs-4" v-if="dataset.created_usuario">por</div>
                  <div class="col-xs-8 text-weight-bold" v-if="dataset.created_usuario">{{dataset.created_usuario.nome}}</div>
                  <div class="col-xs-4" >Status</div>
                  <div class="col-xs-8 text-weight-bold"><q-avatar size="20px" font-size="16px" :color="dataset.status.bgcolor" text-color="white" :icon="dataset.status.icon" class="q-mr-sm" />{{dataset.status.description}}</div>
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
            </q-card-section>
          </q-card>
        </div>
        <q-toolbar class="bg-primary text-white"  v-if="!loading">
          <q-toolbar-title>
            Itens
          </q-toolbar-title>
          <q-btn flat dense icon="add" @click="actScanBarcode" label="adicionar"  v-if="dataset.status.value === '1'" />
          <q-separator spaced inset vertical dark v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false)" />
          <q-btn flat round dense icon="delete" class="q-ml-xs" @click="actItemDeleteAll" v-if="!loading && (dataset.itens ? (dataset.itens.length > 0) &&  (dataset.status.value === '1') : false)" />
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
              <q-item v-for="(item, key) in dataset.itens" :key="key" :class="(key % 2) === 0 ? 'bg-white' : 'bg-grey-1'" >
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
                    </div>
                  </q-item-label>
                </q-item-section>
                <q-item-section top side>
                  <q-btn color="red" icon="delete" @click="actItemDelete(item)" round flat  v-if="dataset.status.value === '1'" />
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
            <div class="col-xs-7 text-h5 text-weight-bold">{{$helpers.formatRS(dataset.pesototal, false, 3)}} KG</div>
          </div>
        </div>
    </q-page>
  </q-page-container>
</q-layout>
</template>

<style>
</style>

<script>
import Palete from 'src/mvc/models/palete.js'
export default {
  components: {
  },
  data: function () {
    let dataset = new Palete()
    return {
      idstart: null,
      dataset,
      rows: [],
      loading: false,
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
    async actEditDescricao () {
      var app = this
      try {
        if (app.dataset.status.value !== '1') throw new Error('Carga com edição bloqueada')
        app.$q.dialog({
          title: 'Editar descrição',
          message: 'Descrição do palete? (Mínimo 3 caracteres)',
          prompt: {
            model: app.dataset.descricao,
            isValid: val => val.length > 2, // << here is the magic
            type: 'text' // optional
          },
          cancel: true,
          persistent: true
        }).onOk(async data => {
          const dialog = this.$q.dialog({
            message: 'Salvando descrição...',
            progress: true, // we enable default settings
            persistent: true, // we want the user to not be able to close it
            ok: false // we want the user to not be able to close it
          })
          app.dataset.descricao = data
          var retSave = await app.dataset.save()
          if (retSave.ok) {
            // await app.refreshData()
            this.$q.notify({
              message: 'Descrição atualizada!',
              color: 'positive',
              timeout: 1500
            })
            dialog.hide()
          } else {
            dialog.hide()
            var msg = ''
            if (retSave.msg) {
              if (retSave.msg !== '') msg = retSave.msg
            }
            app.$q.dialog({
              title: 'Falha ao alterar descrição.',
              message: msg,
              color: 'red',
              html: true
            })
          }
        })
      } catch (error) {
        return false
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

      var retSave = await app.dataset.addetiquetas(eans)
      if (retSave.ok) {
        tevealteracao = true
        dialog.hide()
      } else {
        dialog.hide()
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
        }
        app.$q.dialog({
          title: 'Falha ao inserir itens!',
          message: msg + (detail.length > 0 ? '<br><br>' + detail.join('<br>') : ''),
          color: 'red',
          html: true
        })
      }
      if (tevealteracao) app.refreshData()
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
      }
      app.deleting = false
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
      }
      app.loading = false
    }
  }
}
</script>
