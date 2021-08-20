<template>
<div>
  <q-select :dense="dense" :outlined="outlined" v-model="modelselect" input-debounce="300"
        :label="label" stack-label
        use-input clearable
        :disable="disable"
        ref="txtselect" class="full-width"
        :options="options" map-options
        @filter="refreshData"
        @clear="actOnFocus"
        clear-icon="clear"
        :readonly="readonly"
        :loading="loading || loadingdata"
      >
        <template v-slot:selected-item="scope">
            <div v-if="scope.opt ? scope.opt.id > 0 : false" class="ellipsis" >
              <span class="q-mr-xs" v-if="scope.opt.alertamanut ? scope.opt.alertamanut.id > 0 : false">
                <q-icon name="stop_circle" :color="scope.opt.alertamanut.prioridade === '1' ? 'blue' : (scope.opt.alertamanut.prioridade === '2' ? 'amber-9' : 'red')" />
              </span>
              {{ $helpers.placaMask(scope.opt.placa) }} <span v-if="!hidedescricao && scope.opt.descricao !== ''"> - {{scope.opt.descricao}}</span>
              <q-tooltip>
                <div v-if="scope.opt.descricao !== ''">Descricao: {{scope.opt.descricao}}</div>
                <div>Placa: {{ scope.opt.placa ? $helpers.placaMask(scope.opt.placa) : '-'}}</div>
                <div>Id: {{scope.opt.id}}</div>
                <div class="q-mt-md" v-if="scope.opt.alertamanut ? scope.opt.alertamanut.id > 0 : false">
                  <div>Manutenção ligada</div>
                  <div>Prioridade: {{scope.opt.alertamanut.prioridade === '1' ? 'Baixa' : (scope.opt.alertamanut.prioridade === '2' ? 'Normal' : 'Crítica')}}</div>
                  <div>Tempo previsto: {{$helpers.MinToHourTxt(scope.opt.alertamanut.tempoprevisto, "h ", "m") }}</div>
                  <div v-if="scope.opt.alertamanut.obs !== ''">Obs: {{scope.opt.alertamanut.obs}}</div>
                </div>
              </q-tooltip>
            </div>
        </template>
        <template v-slot:option="scope">
          <q-item v-bind="scope.itemProps" v-on="scope.itemEvents" dense class="border-bottom-separator" >
            <q-item-section >
              <q-item-label >{{scope.opt.placa ? $helpers.placaMask(scope.opt.placa) : '-'}}</q-item-label>
              <q-item-label caption v-if="scope.opt.descricao !== ''">{{scope.opt.descricao}}</q-item-label>
              <q-item-label caption v-if="!scope.opt.ativo" class="text-red">Inativo</q-item-label>
            </q-item-section>
            <q-item-section avatar v-if="scope.opt.alertamanut ? scope.opt.alertamanut.id > 0 : false">
              <q-icon name="stop_circle" :color="scope.opt.alertamanut.prioridade === '1' ? 'blue' : (scope.opt.alertamanut.prioridade === '2' ? 'amber-9' : 'red')" />
            </q-item-section>
          </q-item>
        </template>
        <template v-slot:no-option>
          <q-item>
            <q-item-section class="text-grey">
              Sem resultados
            </q-item-section>
          </q-item>
        </template>
      </q-select>
</div>
</template>

<script>
import Veiculos from 'src/mvc/collections/veiculos.js'
export default {
  components: {
  },
  props: [
    'value', 'dense', 'outlined', 'label', 'clearable', 'nullabled', 'loading', 'proprietario', 'readonly', 'hidedescricao', 'disable'
  ],
  data () {
    var dataset = new Veiculos()
    return {
      dataset,
      modelselect: null,
      options: null,
      showing: false,
      loadingdata: false
    }
  },
  async mounted () {
    if (this.value) {
      this.modelselect = this.value
    }
  },
  computed: {
    selecao: function () {
      var app = this
      if (!app.options) return false
      var sel = null
      for (let index = 0; index < app.options.length; index++) {
        const element = app.options[index]
        if (app.registroselecionado === element.id) {
          sel = element.dados
          break
        }
      }
      return sel
    }
  },
  watch: {
    modelselect: async function (val) {
      var app = this
      app.$emit('input', val)
      app.$emit('updated')
    },
    value: async function (val) {
      var app = this
      app.modelselect = val
    }
  },
  methods: {
    actOnFocus () {
      this.$nextTick(() => {
        this.$refs.txtselect.$el.focus()
      })
    },
    async refreshData (val, update) {
      var app = this
      app.loadingdata = true
      app.dataset.filter = val
      if (app.proprietario) app.dataset.proprietario = app.proprietario
      app.dataset.showinativos = false
      if (app.dataset.filter === '') {
        update(() => {
          app.error = null
          this.options = []
        })
        app.loadingdata = false
        return
      }
      var ret = await app.dataset.fetch()
      if (ret.ok) {
        update(() => {
          this.options = []
          for (let index = 0; index < app.dataset.itens.length; index++) {
            const element = app.dataset.itens[index]
            app.options.push(element)
          }
        })
      }
      app.loadingdata = false
    }
  }
}
</script>
<style>
.border-bottom-separator {
  border-bottom: 1px solid #e4e4e4;
  padding-top: 3px;
  padding-bottom: 3px;
}
</style>
