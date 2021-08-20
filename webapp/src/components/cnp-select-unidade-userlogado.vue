<template>
<div>
  <q-select :dense="dense" :outlined="outlined" v-model="modelselect" input-debounce="300" :dark="dark"
        :label="label" stack-label
        :clearable="clearable"
        ref="txtselect" class="full-width"
        :options="options" map-options
        @clear="actOnFocus"
        clear-icon="clear"
        :loading="loading || loadingdata"
        :readonly="readonly"
      >
        <template v-slot:selected-item="scope">
            <div v-if="scope.opt ? scope.opt.id > 0 : false" class="ellipsis q-pt-xs" :class="color ? 'text-' + color : ''" >
              <q-icon name="block" size="16px" color="red" v-if="!scope.opt.ativo" class="q-mr-sm" >
                <q-tooltip :delay="700">Cadastro inativo</q-tooltip>
              </q-icon>
              <span >{{ scope.opt.fantasia }}</span>
              <span v-if="scope.opt.endereco ? scope.opt.endereco.cidade : false" class="text-weight-bold q-ml-xs">{{ scope.opt.endereco.cidade.cidade }}</span>
              <span class="q-ml-xs">({{ scope.opt.id }})</span>
              <q-tooltip :delay="700">
                <div>{{ scope.opt.fantasia }}</div>
                <div v-if="scope.opt.razaosocial != scope.opt.fantasia">{{ scope.opt.razaosocial }}</div>
                <div v-if="scope.opt.endereco">{{ scope.opt.endereco.enderecocompleto }}</div>
              </q-tooltip>
            </div>
            <div v-else>
              Selecione uma unidade
            </div>
        </template>
        <template v-slot:option="scope">
          <q-item v-bind="scope.itemProps" v-on="scope.itemEvents" dense class="border-bottom-separator" >
            <q-item-section avatar top v-if="!scope.opt.ativo">
              <q-avatar color="white" text-color="red" icon="block" font-size="22px" />
                <q-tooltip :delay="700">Cadastro inativo</q-tooltip>
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-body" >{{scope.opt.fantasia}}</q-item-label>
              <q-item-label :class="scope.opt.ativo ? '' : 'text-red'" v-if="scope.opt.endereco ? scope.opt.endereco.cidade : false">
                {{ scope.opt.endereco.cidade.cidade + ' / ' + scope.opt.endereco.cidade.uf }}
              </q-item-label>
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
export default {
  components: {
  },
  props: [
    'value', 'dense', 'outlined', 'label', 'clearable', 'nullabled', 'loading', 'readonly', 'dark', 'color'
  ],
  data () {
    return {
      usuario: null,
      modelselect: null,
      options: null,
      showing: false,
      loadingdata: false
    }
  },
  async mounted () {
    var app = this
    app.loadingdata = true
    if (app.$store.state.authusuario.logado) {
      if (app.$store.state.authusuario.user) {
        app.usuario = app.$store.state.authusuario.user
      }
    }

    app.options = []
    for (let index = 0; index < app.usuario.unidades.length; index++) {
      const element = app.usuario.unidades[index]
      app.options.push(element.unidade)
    }
    app.loadingdata = false
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
    async findUnidade (id) {
      var app = this
      for (let index = 0; index < app.options.length; index++) {
        const unidade = app.options[index]
        if (unidade.id === id) {
          return unidade
        }
      }
      return null
    },
    actOnFocus () {
      this.$nextTick(() => {
        this.$refs.txtselect.$el.focus()
      })
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
