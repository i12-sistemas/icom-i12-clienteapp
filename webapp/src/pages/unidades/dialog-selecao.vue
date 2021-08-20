<template>
  <q-dialog ref="dialog" @hide="onDialogHide" maximized >
    <q-card class="q-dialog-plugin bg-grey-3">
      <q-toolbar class="bg-primary text-white">
        <q-btn flat round dense icon="clear" @click="onCancelClick" />
        <q-toolbar-title>
          Unidade principal
        </q-toolbar-title>
        <q-btn flat round dense icon="sync" @click="refreshData(false)" />
      </q-toolbar>
      <q-card-section v-if="loading" class="text-center">
        <q-spinner-pie color="primary" size="30px" v-if="loading"/>
        <div class="row justify-center" v-if="loading">Consultando dados...</div>
      </q-card-section>
      <q-card-section class="scroll q-pa-sm q-gutter-md" v-if="!loading" >
        <q-list class="full-width" separator transition-show="slide-right"  transition-hide="slide-left" >
          <q-item v-for="(item, key) in rows" :key="key" @click="onOKClick(item)" :clickable="item.id !== idatual" :v-ripple="item.id !== idatual" :class="item.id === idatual ? 'text-accent' : ''">
            <q-item-section avatar v-if="item.id === idatual" >
              <q-avatar size="50px" text-color="accent" icon="check" />
            </q-item-section>
            <q-item-section >
              <q-item-label class="text-h6">
                {{item.fantasia}}
              </q-item-label>
              <q-item-label class="ellipsis-2-lines text-weight-bold" lines="2" v-if="item.endereco ? item.endereco.cidade : false">
                {{ item.endereco.cidade.cidade + ' / ' + item.endereco.cidade.uf }}
              </q-item-label>
              <q-item-label class="text-caption">
                Cód.: {{item.id}}
              </q-item-label>
              <q-item-label class="text-caption text-weight-bold" v-if="idminhaunidade === item.id">Minha unidade</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
      <q-card-actions class="bg-grey-3 text-caption" align="right" v-if="!loading && (rows ? rows.length > 0 : false)">
        toque na unidade para selecionar
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import Unidades from 'src/mvc/collections/unidades.js'
export default {
  props: [
    'unidade.dialog.selecao'
  ],
  components: {
  },
  data () {
    var dataset = new Unidades()
    return {
      usuario: null,
      idatual: null,
      idminhaunidade: null,
      dataset,
      model: null,
      loading: true,
      rows: null,
      posting: false
    }
  },
  async mounted () {
  },
  methods: {
    async refreshData () {
      var app = this
      app.loading = true

      if (app.$store.state.authusuario.logado) {
        if (app.$store.state.authusuario.user) {
          app.usuario = app.$store.state.authusuario.user
        }
      }
      app.rows = []
      for (let index = 0; index < app.usuario.unidades.length; index++) {
        const element = app.usuario.unidades[index]
        app.rows.push(element.unidade)
      }

      app.loading = false
    },
    // following method is REQUIRED
    // (don't change its name --> "show")
    show () {
      this.$refs.dialog.show()
      var app = this
      if (app.$store.state.authusuario.unidade ? app.$store.state.authusuario.unidade.id > 0 : false) {
        app.idatual = app.$store.state.authusuario.unidade.id
      }
      if (app.$store.state.authusuario.unidade ? app.$store.state.authusuario.user.unidadeprincipal.id > 0 : false) {
        app.idminhaunidade = app.$store.state.authusuario.user.unidadeprincipal.id
      }
      app.refreshData(false)
    },
    // following method is REQUIRED
    // (don't change its name --> "hide")
    hide () {
      this.$refs.dialog.hide()
    },
    onDialogHide () {
      // required to be emitted
      // when QDialog emits "hide" event
      this.$emit('ok')
    },
    onOKClick (item) {
      try {
        var app = this
        var unid = JSON.parse(JSON.stringify(item))
        app.$store.dispatch('authusuario/setunidadeatual', unid)
      } catch (error) {
        app.$helpers.showDialog({ ok: false, msg: error.message })
        return
      }
      app.$q.notify({
        message: 'Nova unidade selecionada!',
        color: 'positive',
        timeout: 2000,
        actions: [{ icon: 'close', color: 'white' }]
      })
      this.$emit('ok', true)
      this.hide()
    },
    onCancelClick () {
      this.$emit('ok')
      this.hide()
    }
  }
}
</script>
<style scoped>
.full-70 {
  min-width: 90vw;
  min-height: auto;
}
</style>
