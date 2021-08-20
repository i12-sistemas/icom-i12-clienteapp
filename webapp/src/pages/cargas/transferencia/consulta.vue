<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        {{ $store.state.app.title }}
      </q-toolbar-title>
      <q-btn round flat icon="add" :to="{ name: 'cargas.transferencias.add' }" />
      <q-btn round flat icon="sync" @click="refreshData(false)" />
    </q-toolbar>
    <q-tabs v-model="tabs" class="text-white" indicator-color="accent" inline-label @input="actChangeOrigem">
      <q-tab name="s" icon="local_shipping" label="Saídas" />
      <q-tab name="e" icon="first_page" label="Entradas" />
    </q-tabs>
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
      <div class="row justify-center text-center q-pa-lg" v-if="loading">
        <div class="col-12"><q-spinner color="primary" size="30px" /></div>
        <div class="col-12 justify-center text-body">Consultando dados...</div>
      </div>
      <div class="row justify-center q-pt-lg text-body2" v-if="!loading && (rows ? rows.length === 0 :false)">
        <div class="q-pa-md">Nenhum registros localizado!</div>
        <div class="q-pa-md"><q-btn outline  icon="filter_alt" @click="actShowFilter" label="Alterar filtros" /></div>

      </div>

      <div class="full-width text-body q-pa-md" v-for="(item, key) in rows" :key="key" >
        <q-card class="my-card full-width" bordered flat v-ripple @click="$router.push({ name: 'cargas.transferencias.edit', params: { id: item.id } })" >
          <q-card-section>
            <div class="row">
              <div class="col-12" >
                <div class="row">
                  <div class="col-7" >
                    Número: <span class="text-subtitle2">{{$helpers.padLeftZero(item.id, 6)}}</span>
                  </div>
                  <div class="col-5 q-px-xs rounded-borders text-white text-center" :class="'bg-' + item.status.color" >
                    {{item.status.description}}<q-icon :name="item.status.icon" class="q-ml-sm" size="18px" />
                  </div>
                  <div class="col-12">Criado em {{ $helpers.datetimeToBR(item.created_at, false, true) }}</div>
                  <div class="col-12 text-primary q-pa-sm rounded-borders q-mb-xs" :class="dataset.params.origem === 's' ? 'bg-grey-3' : 'bg-grey-1'">
                    <div v-if="!item.unidadesaida" >-</div>
                    <div v-if="item.unidadesaida">
                      <div>Saindo de</div>
                      <div>
                        <span class="text-subtitle2 text-weight-bold  q-pr-sm" v-if="tabs === 's'">MINHA UNIDADE</span>
                        <span class="text-subtitle2">{{ item.unidadesaida.fantasia }}</span>
                        <span v-if="item.unidadesaida.endereco && tabs !== 's'" class="text-weight-bold q-ml-xs">{{ item.unidadesaida.endereco.cidade.cidade }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 text-primary q-pa-sm rounded-borders q-mb-xs" :class="dataset.params.origem === 'e' ? 'bg-grey-3' : 'bg-grey-1'">
                    <div v-if="!item.unidadeentrada" >-</div>
                    <div v-if="item.unidadeentrada">
                      <div>com destino à</div>
                      <div>
                        <span class="text-subtitle2 text-weight-bold  q-pr-sm" v-if="tabs === 'e'">MINHA UNIDADE</span>
                        <span class="text-subtitle2">{{ item.unidadesaida.fantasia }}</span>
                        <span v-if="item.unidadeentrada.endereco && tabs !== 'e'" class="text-weight-bold q-ml-xs">{{ item.unidadeentrada.endereco.cidade.cidade }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div v-if="item.motorista ? item.motorista.id > 0 : false">
                      Motorista: <span class="text-weight-bold"> {{ item.motorista.nome }}</span>
                    </div>
                    <div v-else class="text-red">
                      Motorista: Não localizado
                    </div>
                  </div>
                  <div class="col-12">
                    <div v-if="item.veiculo ? item.veiculo.id > 0 : false">
                      Veículo: <span class="text-weight-bold"> {{$helpers.placaMask(item.veiculo.placa)}}</span>
                    </div>
                    <div v-else >
                      Veículo: Não localizado
                    </div>
                  </div>
                </div>
              </div>
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
import CargasTransfers from 'src/mvc/collections/cargastransfers.js'
export default {
  components: {
  },
  directives: {
  },
  props: ['label'],
  data: function () {
    let dataset = new CargasTransfers()
    return {
      tabs: 's',
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
    async actChangeOrigem (value) {
      var app = this
      app.dataset.params.origem = value
      if (value === 'e') {
        app.dataset.params.status = ['2', '3']
      } else {
        app.dataset.params.status = ['1', '2', '3']
      }
      app.refreshData(false)
    },
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
