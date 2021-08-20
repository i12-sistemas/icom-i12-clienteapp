<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        Novo palete
      </q-toolbar-title>
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
            <q-card-section >
              <q-input v-model="dataset.descricao" outlined type="text" label="Descrição" maxlength="150" counter />
            </q-card-section>
            <q-separator  spaced />
            <q-card-section >
              <selectunidade outlined label="Unidade" v-model="dataset.unidade" ref="txtunidade" :clearable="false" />
            </q-card-section>
            <q-card-actions vertical align="center">
              <q-btn label="Incluir" color="primary" unelevated icon="check" class="full-width" @click="actSave" />
            </q-card-actions>
          </q-card>
        </div>
    </q-page>
  </q-page-container>
</q-layout>
</template>

<style>
</style>

<script>
import selectunidade from 'src/components/cnp-select-unidade-userlogado'
import Palete from 'src/mvc/models/palete.js'
export default {
  components: {
    selectunidade
  },
  data: function () {
    let dataset = new Palete()
    return {
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
    app.dataset.unidade.cloneFrom(await app.$helpers.getUnidadeLogada(app))
  },
  methods: {
    async actSave () {
      var app = this
      try {
        app.saving = true
        // var params = await app.dataset.parametros()
        // if (!params) throw new Error('Nenhuma alteração')
      } catch (error) {
        app.$helpers.showDialog({ ok: false, msg: error.message })
        app.saving = false
        return
      }
      var ret = await app.dataset.save()
      if (ret.ok) {
        app.$q.notify({
          message: 'Cadastro salvo!',
          color: 'positive',
          actions: [
            { label: 'OK', color: 'white', handler: () => { /* ... */ } }
          ]
        })
        app.$nextTick(() => {
          app.loading = true
          app.$router.push({ name: 'cargas.paletes.edit', params: { id: app.dataset.id } })
          app.loading = false
        })
      } else {
        app.$helpers.showDialog(ret, ret.warning)
      }
      app.saving = false
    }
  }
}
</script>
