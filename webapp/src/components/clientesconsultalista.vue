<template>
  <q-dialog v-model="layout" persistent>
    <q-layout view="hHh LpR fFf" container style="height: 90vh; max-height: 90vh; width: 90vw; max-width: 90vw">
      <q-header class="bg-primary text-white">
        <q-toolbar class="bg-primary text-white">
          <q-btn dense flat round icon="arrow_back_ios" @click="onClose" />
          <q-input dark dense standout v-model="text" class="full-width" :autofocus="text === ''" ref="txtbusca"
          :loading="searching" placeholder="Consultar clientes" type="text" @change="onSearch"
          input-class="text-body1">
          <template v-slot:append>
            <q-btn round unelevated icon="search" @click="onSearch" v-if="!searching"  :disable="searching"/>
            <q-btn round unelevated icon="clear" @click="actClearText" v-if="!searching && text !== ''"/>
          </template>
        </q-input>
        </q-toolbar>
      </q-header>
      <q-footer v-if="textisValid && !searching && (dataset ? (dataset.length > 0) &&  (dataset.length <= 100) : false) && (totalcount > 0)" class="bg-blue-grey">
        <div class="text-center q-pa-xs text-white">
          {{ 'Exibindo somente ' + dataset.length + ' de ' + totalcount + ' clientes'}}
        </div>
      </q-footer>
      <q-page-container class="bg-grey-2" >
        <q-page class="q-pa-sm">
        <div v-if="textisValid && searching" class="full-width text-center q-pa-lg text-body2">
          <q-spinner-pie color="primary" size="3em" />
          <p>Consultando...</p>
        </div>
          <div v-if="!textisValid" class="text-center text-body1 q-pa-lg">
              Informe no mínimo 3 caracteres para iniciar uma consulta!
          </div>
          <div v-if="textisValid && !searching && (dataset ? dataset.length == 0 : true)" class="text-center text-body1 q-pa-lg">
              Nenhuma cliente encontrado com o termo
              <p><strong>{{text}}</strong></p>
          </div>
          <q-list class="full-width text-body1" separator v-if="dataset && textisValid">
            <q-item v-for="(item,key) in dataset" :key="key" clickable ripple
              @click="actSelectCliente(item)"
              v-bind:class="$helpers.isOdd(key) ? 'bg-grey-2': 'bg-white'"
              >
              <q-item-section>
                <q-item-label lines="1" v-if="item.fantasia ? (item.fantasia !== '') : false" class="text-weight-medium">{{ item.fantasia }}</q-item-label>
                <q-item-label lines="1" v-if="item.razaosocial ? ((item.razaosocial !== '') && (item.fantasia !== item.razaosocial)) : false" class="text-body2 text-weight-medium">{{ item.razaosocial }}</q-item-label>
                <q-item-label lines="1" v-if="item.cnpj ? (item.cnpj !== '') : false" class="text-body2">{{ $helpers.mascaraCpfCnpj(item.cnpj) }}</q-item-label>
                <q-item-label class="text-body2" v-if="(item.cidade != '') || (item.uf != '')">{{item.cidade + '/' + item.uf}}</q-item-label>
                <q-item-label class="text-body2">{{item.endereco + (item.bairro!=='' ? ' - ' + item.bairro : '')}}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-page>
      </q-page-container>
    </q-layout>
  </q-dialog>
</template>

<script>
import Clientes from 'src/mvc/collections/clientes.js'
import { GoBack } from 'quasar'
export default {
  props: ['findInit'],
  directives: {
    GoBack
  },
  data: function () {
    let clientes = new Clientes()
    return {
      clientes,
      layout: true,
      text: '',
      searching: false,
      dataset: null,
      error: null,
      totalcount: 0
    }
  },
  async mounted () {
    var app = this
    document.addEventListener('deviceready', function () {
      document.addEventListener('backbutton', function () {
        app.onClose()
      }, false)
    }, false)
    if (app.findInit) {
      app.text = app.findInit
      app.actInit()
    }
  },
  watch: {
    text: function (newtext, oldtext) {
      if ((newtext ? newtext.length < 3 : true) && (oldtext ? oldtext.length >= 3 : false)) {
        this.dataset = null
      }
    }
  },
  computed: {
    textisValid: function () {
      return (this.text ? this.text.length >= 3 : false)
    }
  },
  methods: {
    actInit () {
      var app = this
      app.dataset = []
      app.error = null
      window.scrollTo(0, 0)
      app.fetchData()
    },
    async fetchData () {
      var app = this
      app.totalcount = 0
      app.searching = true
      app.clientes.page = 1
      app.clientes.limit = 100
      var ret = await app.clientes.fetch(app.text)
      if (ret.ok) {
        app.dataset = []
        app.dataset = app.clientes.itens
        app.totalcount = app.clientes.totalfetch
      } else {
        app.error = ret.msg
      }
      app.searching = false
    },
    actClearText () {
      this.text = ''
    },
    onSearch () {
      var app = this
      try {
        if (app.text ? app.text.length < 3 : false) throw new Error('Minimo 3')
      } catch (error) {
        console.error(error)
        return
      }
      app.fetchData()
    },
    onClose (TeveAlteracao) {
      this.layout = false
      this.$emit('close')
    },
    actSelectCliente (item) {
      this.layout = false
      this.$emit('selected', item)
    }
  }
}
</script>
