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
            <div v-if="scope.opt ? scope.opt.id > 0 : false" class="ellipsis q-pt-xs" >
              <q-icon name="block" size="16px" color="red" v-if="!scope.opt.ativo" class="q-mr-sm" >
                <q-tooltip :delay="700">Cadastro inativo</q-tooltip>
              </q-icon>
              <div class="text-weight-bold">
                {{ scope.opt.id }} - {{ scope.opt.fantasia }}
              </div>
              <div>
                <span v-if="scope.opt.endereco" class="text-caption">{{ scope.opt.endereco.cidade.cidade }}</span>
              </div>
              <q-tooltip :delay="700">
                <div>{{ scope.opt.fantasia }}</div>
                <div v-if="scope.opt.razaosocial != scope.opt.fantasia">{{ scope.opt.razaosocial }}</div>
                <div v-if="scope.opt.endereco">{{ scope.opt.endereco.enderecocompleto }}</div>
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
              <q-item-label :class="scope.opt.ativo ? '' : 'text-red'" v-if="scope.opt.endereco">
                {{ scope.opt.endereco.cidade.cidade + ' / ' + scope.opt.endereco.cidade.uf }}
              </q-item-label>
              <q-item-label caption >{{scope.opt.fantasia}}</q-item-label>
              <q-item-label caption v-if="scope.opt.razaosocial != '' && scope.opt.razaosocial != scope.opt.fantasia" >{{scope.opt.razaosocial}}</q-item-label>
              <q-item-label caption v-if="scope.opt.endereco" >{{scope.opt.endereco.enderecocompleto}}</q-item-label>
            </q-item-section>
            <q-item-section side top>
                <q-item-label caption >{{ scope.opt.veiculo ? scope.opt.veiculo.placa : '' }}</q-item-label>
                <q-item-label caption >Cód.: {{ scope.opt.id }}</q-item-label>
            </q-item-section>
            <q-tooltip :delay="700">
              <div>{{ scope.opt.fantasia }}</div>
              <div v-if="scope.opt.razaosocial != scope.opt.fantasia">{{ scope.opt.razaosocial }}</div>
              <div v-if="scope.opt.endereco">{{ scope.opt.endereco.enderecocompleto }}</div>
            </q-tooltip>
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
import Unidades from 'src/mvc/collections/unidades.js'
export default {
  components: {
  },
  props: [
    'value', 'dense', 'outlined', 'label', 'clearable', 'nullabled', 'loading', 'readonly'
  ],
  data () {
    var dataset = new Unidades()
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
      app.dataset.filter = val
      app.dataset.showinativos = false
      // app.usuarios.readPropsTable(props)
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
