<template>
<div>
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar >
      <q-btn round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
          {{ $store.state.app.title }}
      </q-toolbar-title>
      <q-space />
      <q-btn round flat icon="sync" @click="refreshData(false)" />
      <q-btn round flat icon="more_vert" aria-label="+" >
        <q-menu transition-show="jump-down" transition-hide="jump-up" >
          <q-list style="min-width: 100px">
            <q-item clickable v-ripple @click="refreshData(false)" v-close-popup>
              <q-item-section>Atualizar</q-item-section>
              <q-item-section avatar>
                <q-icon name="sync" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
    </q-toolbar>
    <q-toolbar>
      <q-toolbar-title>
        <q-input dense debounce="500" v-model="text" dark class="full-width" :loading="loading" placeholder="Consultar..." type="text" @input="refreshData(false)" clearable>
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </q-toolbar-title>
    </q-toolbar>
  </q-header>
  <q-footer reveal elevated bordered v-if="dataset.pagination">
    <q-toolbar>
      <q-toolbar-title class="text-caption">
        {{rows.length}} de {{dataset.pagination.rowsNumber}} registros
      </q-toolbar-title>
        <q-btn flat icon="add"  @click="refreshData(true)" label="Carregar mais" :loading="loading" />
    </q-toolbar>
  </q-footer>
  <q-page-container class="bg-grey-2" >
    <q-page>
      <q-card v-if="expanded" class="bg-primary text-white full-width" square flat >
          <q-card-section>
            <div>
              <q-btn flat @click="refreshData(false)" class="full-width">Sincronizar dados</q-btn>
            </div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat @click="expanded=!expanded" class="full-width">Fechar</q-btn>
          </q-card-actions>
      </q-card>
      <p class="text-red" v-if="error">{{error}}</p>
      <div class="row justify-center" v-if="loading"><q-spinner-pie color="primary" size="30px" v-if="loading"/></div>
      <div class="row justify-center" v-if="loading">Consultando dados...</div>

      <q-list class="full-width" separator transition-show="slide-right"  transition-hide="slide-left">
        <q-item v-for="(item, key) in rows" :key="key" clickable :to="{ name: 'coletas.clientes', params: { id: item.id }}" >
          <q-item-section>
            <q-item-label class="text-weight-medium ellipsis-2-lines">{{item.razaosocial}}</q-item-label>
            <q-item-label class="text-caption" v-if="item.fantasia !== '' && (item.razaosocial !== item.fantasia)">{{item.fantasia}}</q-item-label>
            <!-- <q-item-label caption>{{item.remetente.endereco + (item.remetente.bairro!=='' ? ' - ' + item.remetente.bairro : '')}}</q-item-label> -->
          </q-item-section>
          <q-item-section side top>
            <q-item-label>
              <!-- <q-avatar size="sm" color="green" text-color="white" v-if="item.counter ? item.counter > 1 : false">{{item.counter}}</q-avatar> -->
            </q-item-label>
            <q-item-label>
              <div class="q-gutter-sm">
                <!-- <q-icon size="sm" name="star" color="indigo" v-if="item.exclusivo==1" />
                <q-icon size="sm" name="offline_bolt" color="orange" v-if="item.urgente==1" />
                <q-icon size="sm" name="fas fa-radiation" color="red" v-if="item.produtoperigoso==1" /> -->
              </div>
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-page>
  </q-page-container>
</div>
</template>

<style>
</style>

<script>
import { QMenu, ClosePopup } from 'quasar'
import Clientes from 'src/mvc/collections/clientes.js'
export default {
  components: {
    QMenu
  },
  directives: {
    ClosePopup
  },
  props: ['label', 'filterstatus'],
  data: function () {
    let dataset = new Clientes()
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
  mounted () {
    var app = this
    this.$store.commit('app/title', app.label)
    app.refreshData()
  },
  methods: {
    actClearText () {
      this.text = ''
    },
    async refreshData (pLoadMore = false) {
      var app = this
      app.loading = true
      app.msgError = ''
      // app.dataset.readPropsTable(null)
      if (!pLoadMore) app.dataset.paginationDefault()
      app.dataset.params = {}
      app.dataset.params['find'] = app.text

      app.dataset.orderby = null
      if (app.orderbylist) {
        var c = Object.keys(app.orderbylist).length
        if (c > 0) app.dataset.orderby = app.orderbylist
      }

      var ret = await app.dataset.fetch(pLoadMore)

      if (ret.ok) {
        app.rows = app.dataset.itens
        try {
          // var query = await app.dataset.makequery()
          // if (query) app.$router.replace({ name: app.$route.name, query: query, replace: true, append: false })
        } catch (error) {
          console.error(error)
        }
      } else {
        var a = app.$helpers.showDialog(ret)
        await a.then(function () {})
      }
      app.loading = false
    }
  }
}
</script>
