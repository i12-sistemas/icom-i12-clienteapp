<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        {{ $store.state.app.title }}
      </q-toolbar-title>
      <q-btn flat icon="qr_code_scanner" label="Leitor" @click="actScanBarcode" />
    </q-toolbar>
    <q-toolbar >
      <q-input debounce="700" v-model="ean13" dark color="white" class="full-width" :loading="loading" label="Número da etiqueta" type="tel" maxlength="13"
        @input="refreshData" clearable>
        <template v-slot:prepend>
          <q-icon name="pin" />
        </template>
        <template v-slot:append>
          <q-btn round flat icon="search" @click="refreshData" />
        </template>
      </q-input>
    </q-toolbar>
  </q-header>

  <q-page-container class="bg-grey-3" >
    <q-page class="q-pa-md">

      <q-card class="bg-red text-white full-width q-ma-md text-center" bordered flat v-if="!loading && error" >
        <q-card-section>
          <q-avatar size="100px" font-size="52px" color="white" text-color="red" icon="highlight_off" />
        </q-card-section>
        <q-card-section class="text-subtitle2">
          {{error}}
        </q-card-section>
        <q-card-section class="text-subtitle2">
          <q-btn color="white" label="Tentar novamente" @click="ean13 = ''" flat size="lg" outline />
        </q-card-section>
      </q-card>

      <q-card class="bg-white text-primary full-width text-center" bordered flat v-if="!loading && !error && (etiqueta ? (etiqueta.ean13 ? etiqueta.ean13 === '' : true) : true)" >
        <q-card-section>
          <q-avatar size="100px" font-size="52px" color="grey-2" text-color="primary" icon="grid_off" />
        </q-card-section>
        <q-card-section class="text-subtitle2">
          <div class="q-pa-md">Nenhuma etiqueta encontrada!</div>
          <div class="q-pt-md">Informe o número da etiqueta</div>
          <div >ou leia o código de barra com a câmera</div>
          <div class="full-width q-pt-lg">
            <q-btn icon="qr_code_scanner" label="Leitor" @click="actScanBarcode" outline class="full-width" size="lg" />
          </div>

        </q-card-section>
      </q-card>

      <q-card class="bg-white text-primary full-width text-center" bordered flat v-if="loading">
        <q-card-section>
          <q-circular-progress indeterminate size="90px" :thickness="0.2" color="primary"
            center-color="white"
            track-color="grey-3"
            class="q-ma-md"
          />
          <div class="text-primary text-center q-pa-md" v-if="loading">Carregando...</div>
        </q-card-section>
      </q-card>

      <q-card v-if="!loading && (etiqueta ? (etiqueta.ean13 ? etiqueta.ean13 !== '' : false) : false)"  class="bg-white text-primary full-width " bordered flat >
        <q-card-section class="text-h6" v-if="!etiqueta.logdelete">
          Etiqueta {{etiqueta.ean13}}
        </q-card-section>
        <q-card-section v-if="etiqueta.logdelete" class="bg-negative text-white">
          <div class="row text-h6">
            <div class="col-9 ">
              Etiqueta cancelada!
            </div>
            <div class="col-3 text-right">
              <q-icon size="30px" color="white" name="block" />
            </div>
          </div>
        </q-card-section>
        <q-separator  />
        <q-card-section v-if="etiqueta.logdelete">
          <div>Cancelado em: {{ $helpers.datetimeToBR(etiqueta.logdelete.created_at)}}</div>
          <div class="q-pb-xs">Por: {{ etiqueta.logdelete.created_usuario.nome }}</div>
        </q-card-section>
        <q-separator v-if="etiqueta.logdelete" />
        <q-card-section v-if="etiqueta.logdelete">
          <div v-html="etiqueta.logdelete.superdetalhe" ></div>
        </q-card-section>
        <q-card-section v-if="!etiqueta.logdelete">
          <div class="row">
            <div>Fornecedor/Origem</div>
            <div class="self-center full-width no-outline q-mt-xs text-subtitle2" tabindex="0">{{etiqueta.origem.fantasia}}</div>
            <div>CNPJ: {{$helpers.mascaraDocCPFCNPJ(etiqueta.origem.cnpj)}}</div>
            <div class="self-center full-width no-outline" tabindex="0">{{etiqueta.origem.endereco.cidade.cidade + '/' + etiqueta.origem.endereco.cidade.uf}}</div>
          </div>
        </q-card-section>
        <q-separator v-if="!etiqueta.logdelete" />
        <q-card-section v-if="!etiqueta.logdelete" >
          <div class="row"  >
            <div class="col-4" >
               <div class="row">
                  <div>Nota Fiscal</div>
                  <div class="self-center full-width no-outline text-h6 text-weight-bold" tabindex="0">{{etiqueta.coletanota ? etiqueta.coletanota.notanumero : '-'}}</div>
              </div>
            </div>
            <div class="col-4" >
               <div class="row">
                  <div>Nº Coleta</div>
                  <div class="self-center full-width no-outline text-h6 text-weight-bold" tabindex="0">{{etiqueta.cargaentradaitem ? etiqueta.cargaentradaitem.coletaid : '-'}}</div>
              </div>
            </div>
            <div class="col-4" >
               <div class="row text-right">
                  <div class="text-right col-12">Volume</div>
                  <div class="self-center full-width no-outline text-h6 text-weight-bold" tabindex="0">{{etiqueta.volume}}</div>
              </div>
            </div>
          </div>
        </q-card-section>
        <q-separator v-if="!etiqueta.logdelete" />
        <q-card-section v-if="!etiqueta.logdelete" >
          <div class="row">
            <div>Destinatário</div>
            <div class="self-center full-width no-outline q-mt-xs text-subtitle2" tabindex="0">{{etiqueta.destinatario.fantasia}}</div>
            <div>CNPJ: {{$helpers.mascaraDocCPFCNPJ(etiqueta.destinatario.cnpj)}}</div>
            <div class="self-center full-width no-outline text-caption" tabindex="0">End: {{etiqueta.destinatario.endereco.enderecosemcidade}}</div>
            <div class="row self-center full-width no-outline" tabindex="0">Cidade: {{etiqueta.destinatario.endereco.cidade.cidade + ' / ' + etiqueta.destinatario.endereco.cidade.uf}}</div>
          </div>
        </q-card-section>
        <q-separator v-if="!etiqueta.logdelete && etiqueta.travado" />
        <q-card-section v-if="!etiqueta.logdelete && etiqueta.travado" >
          <div class="row q-mb-sm">
            <div class="col-10 text-red">Registro travado para inclusão ou movimentação em cargas</div>
            <div class="col-2 text-right"><q-avatar color="red"  text-color="white" icon="lock"  /></div>
          </div>
        </q-card-section>
        <q-separator v-if="!etiqueta.logdelete" />
        <q-card-section v-if="!etiqueta.logdelete" >
          <div class="row q-mb-sm">
            <div class="col-9">
              <div class="row">
                <div class="col-12 text-h6">{{etiqueta.status.description}}</div>
                <div class="col-12">
                    <div class="text-subtitle2 text-weight-bold ">{{ etiqueta.unidadeatual.fantasia }}</div>
                    <div v-if="etiqueta.unidadeatual.endereco" >{{ etiqueta.unidadeatual.endereco.cidade.cidade + '/' + etiqueta.unidadeatual.endereco.cidade.uf }}</div>
                </div>
              </div>
            </div>
            <div class="col-3 text-right">
              <q-avatar :color="etiqueta.status.color" text-color="white" :icon="etiqueta.status.icon" v-if="!etiqueta.logdelete" />
            </div>
          </div>
        </q-card-section>
        <q-separator v-if="!etiqueta.logdelete" />
        <q-card-section class="q-pa-none q-ma-none">
          <q-tabs v-model="tab" inline-label class="bg-primary text-white" :breakpoint="0" align="justify" active-color="accent">
            <q-tab name="filhos" icon="mail" label="+ Etiquetas" v-if="!etiqueta.logdelete" />
            <q-tab name="rastrear" icon="alarm" label="Rastreabilidade" />
          </q-tabs>
        </q-card-section>
          <q-tab-panels v-model="tab" animated>
            <q-tab-panel name="filhos" v-if="!etiqueta.logdelete" class="q-pa-none">
              <div class="full-width q-pa-lg" v-if="etiqueta.ean13pai ? etiqueta.ean13pai !== '' : false">
                <q-btn color="primary" class="full-width" outline size="lg" label="Etiqueta volume 001" @click="actOpenEtiqueta(etiqueta.ean13pai)" />
              </div>
              <q-list class="full-width" v-if="etiqueta.etiquetasfilhas ? etiqueta.etiquetasfilhas.length > 0 : false">
                <div v-for="(eti, k) in etiqueta.etiquetasfilhas" :key="'etifilho' + k">
                  <q-item clickable v-ripple :class="(k % 2) === 0 ? 'bg-white' : 'bg-grey-1'" @click="actOpenEtiqueta(eti.ean13)">
                    <q-item-section>
                      <div class="row">
                        <div class="col-5 text-h6">{{eti.volume}}</div>
                        <div class="col-7 text-h6 text-right">{{eti.ean13}}</div>
                      </div>
                      <div class="row">
                        <div class="col-12 text-subtitle2 text-weight-bold">
                          {{eti.status.description}}
                        </div>
                      </div>
                      <div class="row" v-if="!eti.logdelete && eti.travado">
                        <div class="col-10 text-red">Registro travado para inclusão ou movimentação em cargas</div>
                        <div class="col-2 text-right"><q-avatar color="red" size="30px" font-size="20px" text-color="white" icon="lock"  /></div>
                      </div>
                    </q-item-section>
                  </q-item>
                  <q-separator />
                </div>
              </q-list>
            </q-tab-panel>
            <q-tab-panel name="rastrear" class="q-pa-none">
              <div v-if="!loading && (etiqueta ? (etiqueta.logs ? etiqueta.logs.length > 0 : false) : false)">
                <q-table :data="etiqueta.logs" :columns="columnslog" row-key="id" dense :rows-per-page-options="[0]" bordered flat  >
                  <template v-slot:header="props">
                    <q-tr :props="props">
                      <q-th auto-width />
                      <q-th
                        v-for="col in props.cols"
                        :key="col.name"
                        :props="props"
                      >
                        {{ col.label }}
                      </q-th>
                    </q-tr>
                  </template>
                  <template v-slot:body="props">
                    <q-tr :props="props" @click="props.expand = !props.expand" class="cursor-pointer" :class="props.expand ? 'bg-grey-3' : 'bg-white'">
                      <q-td auto-width>
                        <q-icon size="sm" color="grey-9" :name="props.expand ? 'expand_less' : 'expand_more'" />
                      </q-td>
                      <q-td key="nordem" :props="props">
                        {{ props.row.nordem }}
                      </q-td>
                      <q-td key="createat" :props="props">
                        {{ $helpers.datetimeToBR(props.row.created_at, true) }}
                        <q-tooltip :delay="500">{{ $helpers.datetimeRelativeToday(props.row.created_at) }}</q-tooltip>
                      </q-td>
                      <q-td key="createdusuario" :props="props">
                        {{ props.row.created_usuario ? props.row.created_usuario.nome : '-' }}
                        <q-tooltip :delay="500">Quem executou a ação: {{ props.row.created_usuario ? props.row.created_usuario.nome : '-' }}</q-tooltip>
                      </q-td>
                      <q-td key="action" :props="props">
                        <q-icon :color="props.row.action.color" :name="props.row.action.icon" size="20px" /> {{props.row.action.description}}
                        <q-tooltip :delay="500">{{props.row.action.description + ' :: ' + props.row.action.memo}}</q-tooltip>
                      </q-td>
                      <q-td key="origem" :props="props">
                          <div >
                            <q-icon :color="props.row.origem.color" :name="props.row.origem.icon" size="20px" class="q-mr-sm" />{{props.row.origem.description }}
                            <q-tooltip :delay="500">{{props.row.origem.description + ' :: ' + props.row.origem.memo}}</q-tooltip>
                          </div>
                      </q-td>
                      <q-td key="origemid" :props="props">
                          <div >
                            {{props.row.origemcarga.id }}
                          </div>
                      </q-td>
                    </q-tr>
                    <q-tr v-show="props.expand" :props="props" >
                      <q-td colspan="100%" style="border-left: 3px solid #eeeeee">
                        <div class="q-py-md">
                          <div class="text-left q-mb-sm"><q-btn color="grey-3" text-color="grey-9" unelevated :label="'Abrir ' + props.row.origem.description + ' #' + props.row.origemcarga.id" @click="actAbrirLog(props.row)" /></div>
                          <div class="text-left"><span v-html="props.row.superdetalhe"></span></div>
                        </div>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </q-tab-panel>
          </q-tab-panels>
      </q-card>

      <!-- <div class="full-width text-body q-pa-md" v-for="(item, key) in rows" :key="key" >
        <q-card class="my-card full-width" bordered flat v-ripple @click="$router.push({ name: 'cargas.paletes.edit', params: { id: item.id } })" >
          <q-card-section>
            <div class="row">
              <div class="col-12" v-if="item.descricao !== ''" >
                <div class="text-h6"><q-icon name="double_arrow" class="q-mr-sm" size="18px" />  {{item.descricao}}</div>
              </div>
              <div class="col-7" >
                <div>Código</div>
                <div class="text-h6">{{item.ean13}}</div>
              </div>
              <div class="col-5 text-right" >
                <div>Volumes</div>
                <span class="text-h6" v-if="item.volqtde > 0">{{item.volqtde}}</span>
                <span class="text-h6 text-red" v-else>VAZIO</span>
              </div>
              <div class="col-7" >
                <div>id: {{item.id}}</div>
                <div>Criado em {{ $helpers.datetimeToBR(item.created_at, false, true) }}</div>
              </div>
              <div class="col-5 text-right" >
                <div>Peso</div>
                <span class="text-h6">{{$helpers.formatRS(item.pesototal, false, 3) }} KG</span>
              </div>
            </div>
            <div class="row">
              <div class="col-12 q-pa-sm rounded-borders q-mb-xs bg-grey-2">
                <div v-if="!item.unidade" >-</div>
                <div v-if="item.unidade">
                  <div>Unidade de alocação</div>
                  <div>
                    <span class="text-subtitle2">{{ item.unidade.fantasia }}</span>
                    <span v-if="item.unidade.endereco" class="text-weight-bold q-ml-xs">{{ item.unidade.endereco.cidade.cidade }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12 q-pa-sm rounded-borders q-mt-sm" :class="'bg-' + item.status.bgcolor + ' text-' + item.status.color">
                <q-avatar size="24px" font-size="20px" :color="item.status.bgcolor" text-color="white" :icon="item.status.icon" class="q-mr-sm" />{{item.status.description}}
              </div>
            </div>
          </q-card-section>
          <q-separator v-if="item.erroqtde ? item.erroqtde > 0 : false" />
          <q-card-section v-if="item.erroqtde ? item.erroqtde > 0 : false">
            <div class="row">
              <div class="col-12 rounded-borders q-pa-sm bg-red-1 text-red text-weight-bold"><q-icon name="info" class="q-mr-sm" size="20px" /> Existem {{item.erroqtde}} erros</div>
            </div>
          </q-card-section>
        </q-card>
      </div> -->
    </q-page>
  </q-page-container>
</q-layout>
</template>

<style>
</style>

<script>
import Etiquetas from 'src/mvc/models/etiqueta.js'
export default {
  components: {
  },
  directives: {
  },
  props: ['label'],
  data: function () {
    let etiqueta = new Etiquetas()
    return {
      etiqueta,
      tab: 'filhos',
      ean13: '2210527026775', // 2210528685193
      rows: [],
      ativos: true,
      error: null,
      text: '',
      loading: false,
      expanded: false,
      columnslog: [
        { name: 'nordem', align: 'center', label: 'Sequência', field: 'nordem' },
        { name: 'createat', align: 'left', label: 'Data e hora', field: 'createat' },
        { name: 'createdusuario', align: 'left', label: 'Usuário', field: 'createdusuario' },
        { name: 'origem', align: 'left', label: 'Origem', field: 'origem' },
        { name: 'action', align: 'left', label: 'Ação', field: 'action' },
        { name: 'origemid', align: 'center', label: 'Número', field: 'origemid' }
      ]
    }
  },
  async mounted () {
    var app = this
    this.$store.commit('app/title', app.label)
    app.etiqueta.limpardados()
    // await app.etiqueta.setUnidadePadrao(app)
    // app.refreshData(false)
  },
  methods: {
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
      app.actOpenEtiqueta(data)
    },
    onFailureScanBarCode (e) {
      if (e) {
        if (e !== 'cancelled') alert(e)
      }
    },
    async actOpenEtiqueta (pEAN) {
      var app = this
      app.ean13 = pEAN
      app.refreshData()
    },
    async refreshData () {
      var app = this
      app.loading = true
      try {
        if (!app.ean13) throw new Error('')
        if (app.ean13.length < 13) throw new Error('')
      } catch (error) {
        app.error = error.message
        app.loading = false
        return
      }
      app.error = null
      var ret = await app.etiqueta.find(app.ean13)
      if (!ret.ok) {
        app.error = ret.msg
      } else {
        if (app.etiqueta.logdelete) app.tab = 'rastrear'
      }
      app.loading = false
    }
  }
}
</script>
