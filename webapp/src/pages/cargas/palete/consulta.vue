<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        {{ $store.state.app.title }}
      </q-toolbar-title>
      <q-btn round flat icon="add" :to="{ name: 'cargas.paletes.add' }" />
      <q-btn round flat icon="sync" @click="refreshData(false)" />
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
      <div class="row justify-center text-center q-pa-lg" v-if="loading">
        <div class="col-12"><q-spinner color="primary" size="30px" /></div>
        <div class="col-12 justify-center text-body">Consultando dados...</div>
      </div>
      <div class="row justify-center q-pt-lg text-body2" v-if="!loading && (rows ? rows.length === 0 :false)">
        <div class="q-pa-md">Nenhum registros localizado!</div>
        <div class="q-pa-md"><q-btn outline  icon="filter_alt" @click="actShowFilter" label="Alterar filtros" /></div>

      </div>

      <div class="full-width text-body q-pa-md" v-for="(item, key) in rows" :key="key" >
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
import Paletes from 'src/mvc/collections/paletes.js'
export default {
  components: {
  },
  directives: {
  },
  props: ['label'],
  data: function () {
    let dataset = new Paletes()
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
