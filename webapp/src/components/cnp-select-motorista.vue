<template>
<div>
  <q-select :dense="dense" :outlined="outlined" v-model="modelselect" input-debounce="300"
        :label="label" stack-label
        use-input clearable
        ref="txtselect" class="full-width"
        :options="options" map-options
        @filter="refreshData"
        @clear="actOnFocus"
        clear-icon="clear"
        :loading="loading || loadingdata"
        :readonly="readonly"
      >
        <template v-slot:selected-item="scope">
            <div v-if="scope.opt ? scope.opt.id > 0 : false" class="ellipsis" >
              <q-icon name="block" size="16px" color="red" v-if="!scope.opt.ativo" class="q-mr-sm" >
                <q-tooltip :delay="700">Cadastro inativo</q-tooltip>
              </q-icon>
              {{ scope.opt.nome }}
              <span v-if="!hideveiculo && scope.opt.veiculo">{{ ' - Placa: ' + scope.opt.veiculo.placa }}</span>
              <q-tooltip>
                <div>
                  {{scope.opt.nome}}
                </div>
                <div caption v-if="scope.opt.apelido != '' && scope.opt.apelido != scope.opt.nome" >{{scope.opt.apelido}}</div>
                <div caption v-if="scope.opt.veiculo" >{{ 'Veículo: ' + scope.opt.veiculo.placa + ' - ' + scope.opt.veiculo.descricao}}</div>
                <div caption v-if="scope.opt.fone != ''" >{{ 'Telefone: ' + $helpers.mascaratel(scope.opt.fone) }}</div>
              </q-tooltip>
            </div>
        </template>
        <template v-slot:option="scope">
          <q-item v-bind="scope.itemProps" v-on="scope.itemEvents" dense class="border-bottom-separator" >
            <q-item-section avatar top v-if="!scope.opt.ativo">
              <q-avatar color="white" text-color="red" icon="block" font-size="22px" />
                <q-tooltip :delay="700">Cadastro inativo</q-tooltip>
            </q-item-section>
            <q-item-section>
              <q-item-label :class="scope.opt.ativo ? '' : 'text-red'" >
                {{scope.opt.nome}}
              </q-item-label>
              <q-item-label caption v-if="scope.opt.apelido != '' && scope.opt.apelido != scope.opt.nome" >{{scope.opt.apelido}}</q-item-label>
            </q-item-section>
            <q-item-section side top>
                <q-item-label caption >{{ scope.opt.veiculo ? scope.opt.veiculo.placa : '' }}</q-item-label>
                <q-item-label caption >Cód.: {{ scope.opt.id }}</q-item-label>
            </q-item-section>
          </q-item>
        </template>
        <template v-slot:no-option>
          <q-item>
            <q-item-section class="text-grey">
              Sem resultados
            </q-item-section>
          </q-item>
          <q-item v-if="error">
            <q-item-section class="text-red">
              {{error}}
            </q-item-section>
          </q-item>
        </template>
      </q-select>
</div>
</template>

<script>
import Motoristas from 'src/mvc/collections/motoristas.js'
export default {
  components: {
  },
  props: [
    'value', 'dense', 'outlined', 'label', 'clearable', 'nullabled', 'loading', 'readonly', 'hideveiculo', 'hidealerta'
  ],
  data () {
    var dataset = new Motoristas()
    return {
      dataset,
      modelselect: null,
      error: null,
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
    'value.id': async function (val) {
      if (this.value) {
        this.modelselect = this.value
      } else {
        this.modelselect = null
      }
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
      app.error = null
      app.dataset.filter = val
      app.dataset.showinativos = false
      app.dataset.resumedata = true
      // app.usuarios.readPropsTable(props)
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
          app.error = null
          this.options = []
          for (let index = 0; index < app.dataset.itens.length; index++) {
            const element = app.dataset.itens[index]
            app.options.push(element)
          }
        })
      } else {
        app.error = ret.msg
        update(() => {
          app.options = []
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
