<template>
  <q-dialog ref="dialog" @hide="onDialogHide" maximized >
    <q-card class="q-dialog-plugin bg-grey-3">
      <q-toolbar class="bg-primary text-white">
        <q-btn flat round dense icon="clear" @click="onCancelClick" />
        <q-toolbar-title class="text-subtitle2">
          Filtro e ordenação
        </q-toolbar-title>
        <q-btn flat stretch dense icon="check" label="aplicar" @click="onOKClick" />
      </q-toolbar>
      <q-card-section class="scroll q-pa-sm q-gutter-md" v-if="!loading" >
        <q-card class="my-card" bordered flat>
          <q-card-section class="bg-grey-2 q-pa-sm text-subtitle2" >
            <div class="row items-center no-wrap">
              <div class="col">
                Unidade
              </div>
              <div class="col-auto">
                <q-btn stretch color="grey-2" text-color="primary" unelevated label="Usar unidade do usuário" no-caps @click="actAplicarUnidadeUsuario" />
              </div>
            </div>
          </q-card-section>
          <q-card-section class="q-pa-none q-ma-none">
            <div class="q-pa-md">
              <q-option-group v-model="filter.origem" type="radio" :options="[{value: 's', label: 'Saída (origem)'},{value: 'e', label: 'Entrada (destino)'}]" inline />
            </div>
            <div class="q-pa-md" v-if="['s','e'].indexOf(filter.origem) >= 0">
              <selectunidade outlined :label="'Unidade de ' + (filter.origem === 's' ? 'Saída' : 'Destino')" v-model="filter.unidade" ref="txtunidade" :clearable="true" />
            </div>
          </q-card-section>
        </q-card>
        <q-card class="my-card" bordered flat>
          <q-card-section class="bg-grey-2 q-pa-sm text-subtitle2">Data</q-card-section>
          <q-card-section class="q-pa-none q-ma-none">
            <div class="q-pa-md">
              <inputdata outlined v-model="filter.created_at" label="Data" stacklabel  />
            </div>
          </q-card-section>
        </q-card>
        <q-card class="my-card" bordered flat>
          <q-card-section class="bg-grey-2 q-pa-sm text-subtitle2">Status</q-card-section>
          <q-card-section class="q-pa-none q-ma-none">
            <div class="q-pa-md">
              <q-option-group v-model="filter.status" type="checkbox" :options="options_status"  />
            </div>
          </q-card-section>
        </q-card>
        <q-card class="my-card" bordered flat>
          <q-card-section class="bg-grey-2 q-pa-sm text-subtitle2">Erros na carga</q-card-section>
          <q-card-section class="q-pa-none q-ma-none">
            <div class="q-pa-md">
              <q-option-group v-model="filter.erros" type="radio" :options="[{value: '2', label: 'Sem erro'},{value: '1', label: 'Com erro'},{value: null, label: 'Tudo'}]" inline />
            </div>
          </q-card-section>
        </q-card>
      </q-card-section>
      <q-card-actions class="bg-grey-3" align="between" v-if="!loading">
        <q-btn unelevated label="Aplicar filtro" color="primary" @click="onOKClick" />
        <q-btn flat label="Sair" color="primary" @click="onCancelClick" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import inputdata from 'src/components/cpn-input-data'
import selectunidade from 'src/components/cnp-select-unidade-userlogado'
import Unidade from 'src/mvc/models/unidade.js'
import { CargaTransferStatus } from 'src/mvc/models/enums/cargastypes'
export default {
  props: [
    'config'
  ],
  components: {
    inputdata,
    selectunidade
  },
  data () {
    return {
      filter: {
        status: [],
        erros: null,
        tipo: null,
        unidade: null,
        origem: 's',
        created_at: null
      },
      options_status: [],
      tevealteracao: false,
      model: null,
      loading: true,
      posting: false
    }
  },
  async mounted () {
    var app = this
    if (app.config) {
      app.filter.status = app.config.status ? app.config.status : []
      app.filter.created_at = app.config.created_at
      app.filter.unidade = app.config.unidade
      app.filter.origem = app.config.origem
      app.filter.erros = app.config.erros
    }

    var optstatus = new CargaTransferStatus('1')
    for (let index = 0; index < optstatus.options.length; index++) {
      const opt = optstatus.options[index]
      app.options_status.push({ value: opt.value, label: opt.desc })
    }
    app.loading = false
  },
  methods: {
    async actAplicarUnidadeUsuario () {
      var app = this
      var unidade = new Unidade(app.$store.state.authusuario.user.unidadeprincipal)
      app.filter.unidade = unidade
    },
    // following method is REQUIRED
    // (don't change its name --> "show")
    show () {
      this.$refs.dialog.show()
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
    onOKClick (e) {
      try {
        var app = this
        if (app.filter.unidade ? !(app.filter.unidade.id > 0) : true) throw new Error('Obrigatório informar uma unidade')
        if (app.filter.status ? app.filter.status.length === 0 : true) app.filter.status = null
      } catch (error) {
        app.$helpers.showDialog({ ok: false, msg: error.message })
        return
      }
      this.$emit('ok', this.filter)
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
