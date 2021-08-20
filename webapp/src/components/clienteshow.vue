<template>
<div>
  <q-dialog v-model="layout" persistent maximized
      transition-show="slide-left"
      transition-hide="slide-rigth"
      >
    <q-layout view="hHr lpR fFf" class="bg-white">
      <q-header class="bg-primary">
        <q-toolbar>
          <q-btn flat v-close-popup round dense icon="arrow_back" @click="actClose" />
          <q-toolbar-title>Detalhes do cliente</q-toolbar-title>
        </q-toolbar>
      </q-header>
      <q-page-container>
        <q-page class="q-pa-md">
          <div v-if="loading" class="full-width text-center q-pa-xl">
            <q-spinner-pie color="primary" size="2em" />
            <p>Carregando dados...</p>
          </div>
          <div v-if="!loading && (cliente ? (cliente.id  ? !(cliente.id > 0) : true) : true)"  class="full-width text-center q-pa-xl">
            <p>Nenhum cliente encontrado!</p>
          </div>
          <div v-if="!loading && (cliente ? (cliente.id  ? (cliente.id > 0) : false) : false)"  class="full-width text-center">
            <div class="row">
              <div class="col-12">
                <q-field outlined label="Razão Social" stack-label>
                  <template v-slot:control>
                    <div class="self-center full-width no-outline" tabindex="0">{{cliente.razaosocial}}</div>
                  </template>
                </q-field>
              </div>
              <div class="col-12 q-pt-xs" v-if="cliente.fantasia ? ((cliente.fantasia !== '') && (cliente.fantasia !== cliente.razaosocial)) : false">
                <q-field outlined label="Fantasia" stack-label>
                  <template v-slot:control>
                    <div class="self-center full-width no-outline" tabindex="0">{{cliente.fantasia}}</div>
                  </template>
                </q-field>
              </div>
            </div>
            <div class="row q-pt-xs justify-between">
              <div class="col-8 q-pr-xs" v-if="cliente.cnpj ? (cliente.cnpj !== '') : false">
                <q-field outlined label="CNPJ" stack-label>
                  <template v-slot:control>
                    <div class="self-center full-width no-outline" tabindex="0">{{ $helpers.mascaraDocCPFCNPJ(cliente.cnpj) }}</div>
                  </template>
                </q-field>
              </div>
              <div class="col-4" >
                <q-field outlined label="ID" stack-label>
                  <template v-slot:control>
                    <div class="self-center full-width no-outline" tabindex="0">{{cliente.id}}</div>
                  </template>
                </q-field>
              </div>
            </div>
          </div>
          <div class="row q-pt-md">
            <div class="col-12">
              <q-field outlined label="Endereço" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">
                    <div>{{cliente.endereco}}</div>
                    <div v-if="cliente.bairro ? cliente.bairro !== '' : false">{{'Bairro: ' + cliente.bairro }}</div>
                    <div v-if="cliente.cep ? cliente.cep !== '' : false">{{'CEP: ' + $helpers.mascaraCEP(cliente.cep)}}</div>
                    <div>{{cliente.cidade + ' - ' + cliente.uf}}</div>
                  </div>
                </template>
              </q-field>
            </div>
          </div>
          <div class="row q-pt-md">
            <div class="col-12">
              <q-field outlined label="Horário de funcionamento" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">
                    <div>Segunda à Quinta</div>
                    <div v-if="cliente.hr1seg || cliente.hr2seg || cliente.hr2seg || cliente.hr4seg">
                      {{cliente.hr1seg + ' às ' + cliente.hr2seg}} | {{cliente.hr3seg + ' às ' + cliente.hr4seg}}
                    </div>
                    <div v-if="!(cliente.hr1seg || cliente.hr2seg || cliente.hr2seg || cliente.hr4seg)">
                        Nenhum horário de funcionamento
                    </div>
                    <div class="q-pt-md">Sextas</div>
                    <div v-if="cliente.hr1sex || cliente.hr2sex || cliente.hr2sex || cliente.hr4sex">
                      {{cliente.hr1sex + ' às ' + cliente.hr2sex}} | {{cliente.hr3sex + ' às ' + cliente.hr4sex}}
                    </div>
                    <div v-if="!(cliente.hr1sex || cliente.hr2sex || cliente.hr2sex || cliente.hr4sex)">
                        Nenhum horário de funcionamento
                    </div>

                    <div class="q-pt-md">Portaria</div>
                    <div v-if="cliente.portaria1 || cliente.portaria2 || cliente.portaria3 || cliente.portaria4">
                      {{cliente.portaria1 + ' às ' + cliente.portaria2}} | {{cliente.portaria3 + ' às ' + cliente.portaria4}}
                    </div>
                    <div v-if="!(cliente.portaria1 || cliente.portaria2 || cliente.portaria3 || cliente.portaria4)">
                        Nenhum horário de funcionamento
                    </div>
                  </div>
                </template>
              </q-field>
            </div>
          </div>
          <div class="row q-pt-md" v-if="(cliente.fone1 ? cliente.fone1 !== '' : false) || (cliente.fone2 ? cliente.fone2 !== '' : false)">
            <div class="col-12">
              <q-field outlined label="Telefones" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">
                    <div v-if="cliente.fone1 ? cliente.fone1 !== '' : false">
                      {{cliente.fone1}}
                    </div>
                    <div v-if="cliente.fone2 ? cliente.fone2 !== '' : false">
                      {{cliente.fone2}}
                    </div>
                  </div>
                </template>
              </q-field>
            </div>
          </div>
          <div class="row q-pt-md" v-if="cliente.email ? cliente.email !== '' : false">
            <div class="col-12">
              <q-field outlined label="E-mail" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline" tabindex="0">
                    {{cliente.email}}
                  </div>
                </template>
              </q-field>
            </div>
          </div>
        </q-page>
      </q-page-container>
    </q-layout>
  </q-dialog>
</div>
</template>

<script>
import Cliente from 'src/mvc/models/cliente.js'
export default {
  data: function () {
    let cliente = new Cliente()
    return {
      cliente,
      loading: true,
      layout: true
    }
  },
  props: ['idcliente'],
  mounted () {
    var app = this
    app.loading = true
    app.refreshData()
  },
  methods: {
    async refreshData () {
      await this.cliente.localFind(this.idcliente)
      this.loading = false
    },
    actClose () {
      var app = this
      app.layout = false
      app.$emit('close')
    }
  }
}
</script>
