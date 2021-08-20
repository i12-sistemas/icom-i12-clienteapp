<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        {{ $store.state.app.title }}
      </q-toolbar-title>
      <q-btn round dense flat icon="sync" @click="refreshData(false)" />
    </q-toolbar>
    <q-toolbar >
      <q-toolbar-title >
        <q-input dense debounce="500" v-model="text" dark class="full-width" :loading="loading" placeholder="Consultar..." type="text" @input="refreshData(false)" clearable>
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </q-toolbar-title>
      <q-btn flat round dense icon="filter_alt" @click="actShowFilter" />
    </q-toolbar>
  </q-header>

  <q-page-container class="bg-grey-2" >
    <q-page>
      <q-card v-if="expanded" class="bg-primary text-white full-width" square flat >
          <q-card-section>
            <q-btn flat @click="refreshData(false)" class="full-width">Sincronizar dados</q-btn>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat @click="expanded = !expanded" class="full-width">Fechar</q-btn>
          </q-card-actions>
      </q-card>
      <p class="text-red" v-if="error">{{error}}</p>
      <div class="row justify-center" v-if="loading"><q-spinner-pie color="primary" size="30px" v-if="loading"/></div>
      <div class="row justify-center" v-if="loading">Consultando dados...</div>
      <div class="row justify-center q-pt-lg text-body2" v-if="!loading && (rows ? rows.length === 0 :false)">
        <div class="q-pa-md">Nenhum registros localizado!</div>
        <div class="q-pa-md"><q-btn outline  icon="filter_alt" @click="actShowFilter" label="Alterar filtros" /></div>

      </div>

      <div class="full-width text-body q-pa-md" v-for="(item, key) in rows" :key="key" >
        <q-card class="my-card full-width" bordered flat v-ripple @click="$router.push({ name: 'cargas.entradas.edit', params: { id: item.id } })" >
          <q-card-section>
            <div class="row">
              <div class="col-8" >
                <div class="row">
                  <div class="col-12" >
                    Número: <span class="text-subtitle2">{{$helpers.padLeftZero(item.id, 9)}}</span>
                  </div>
                  <div class="col-12">{{ $helpers.datetimeToBR(item.dhentrada, false, true) }}</div>
                  <div class="col-12">
                    <div v-if="!item.unidadeentrada" >-</div>
                    <div v-if="item.unidadeentrada">
                      <span class="text-subtitle2">{{ item.unidadeentrada.fantasia }}</span>
                      <span v-if="item.unidadeentrada.endereco" class="text-weight-bold q-ml-xs">{{ item.unidadeentrada.endereco.cidade.cidade }}</span>
                    </div>
                  </div>
                  <div v-if="item.tipo.value === '2'" class="col-12">
                    Entrada através do cliente
                    <q-icon :name="item.tipo.icon" :color="item.tipo.color" size="xs" />
                  </div>
                  <div v-if="item.tipo.value === '1'" class="col-12">
                    <div v-if="item.motorista ? item.motorista.id > 0 : false">
                      Motorista: <span class="text-weight-bold"> {{ item.motorista.nome }}</span>
                    </div>
                    <div v-else class="text-red">
                      Motorista: Não localizado
                    </div>
                  </div>
                  <div v-if="item.tipo.value === '1'" class="col-12">
                    <div v-if="item.veiculo ? item.veiculo.id > 0 : false">
                      Veículo: <span class="text-weight-bold"> {{$helpers.placaMask(item.veiculo.placa)}}</span>
                    </div>
                    <div v-else >
                      Veículo: Não localizado
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-4 text-right q-pa-none" >
                <div class="fit row no-wrap justify-end items-start content-start full-width q-pa-none">
                  <div class="col-12">
                    <q-circular-progress size="70px" show-value :thickness="0.2"
                      :color="(item.volqtde > 0  && (item.conferidoqtde === item.volqtde)) ? 'positive' : 'accent'"
                      center-color="white" track-color="grey-3" class="q-ma-md" :value="item.conferidoprogresso" >
                      {{$helpers.formatRS(item.conferidoprogresso, false, 0)}} %
                    </q-circular-progress>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
          <q-separator v-if="item.status.value !== '1'" />
          <q-card-section v-if="item.status.value !== '1'" class="q-pa-sm text-white" :class="'bg-' + item.status.color" >
            <div class="text-h6 text-center full-width" >
              {{item.status.description}}
            </div>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <div class="row">
              <div class="col-12">
                <div class="row" v-if="item.volqtde > 0" >
                  <div class="col-8" >Peso: <span class="text-weight-bold text-h6">{{ $helpers.formatRS(item.peso, false, 3) }}</span> KG</div>
                  <div class="col-4 text-right" >Volumes: <span class="text-weight-bold text-h6">{{ item.volqtde }}</span></div>
                </div>
                <div class="row rounded-borders bg-red-1 text-red text-weight-bold" v-else >
                  <div class="col-12 text-center text-h6" >CARGA VAZIA</div>
                </div>
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
      </div>
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
import CargasEntradas from 'src/mvc/collections/cargasentradas.js'
export default {
  components: {
  },
  directives: {
  },
  props: ['label'],
  data: function () {
    let dataset = new CargasEntradas()
    return {
      dataset,
      rows: [],
      ativos: true,
      error: null,
      text: '',
      loading: false,
      expanded: false
    }
  },
  async mounted () {
    var app = this
    this.$store.commit('app/title', app.label)
    await app.dataset.setUnidadePadrao(app)
    app.refreshData(false)
  },
  methods: {
    async actShowFilter () {
      var app = this
      var ret = await app.dataset.ShowDialogFilter(app)
      if (ret.ok) app.refreshData(false)
    },
    actClearText () {
      this.text = ''
    },
    async refreshData (pLoadMore = false) {
      var app = this
      app.loading = true
      app.msgError = ''
      // app.dataset.readPropsTable(null)
      if (!pLoadMore) app.dataset.paginationDefault()

      app.dataset.params['find'] = app.text

      app.dataset.orderby = null
      if (app.orderbylist) {
        var c = Object.keys(app.orderbylist).length
        if (c > 0) app.dataset.orderby = app.orderbylist
      }

      var ret = await app.dataset.fetch(pLoadMore)

      if (ret.ok) {
        app.rows = app.dataset.itens
      } else {
        var a = app.$helpers.showDialog(ret)
        await a.then(function () {})
      }
      app.loading = false
    }
  }
}
</script>
