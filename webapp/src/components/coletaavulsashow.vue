<template>
  <q-dialog v-model="layout" persistent maximized >
    <q-layout view="hHr lpR fFf">
      <q-header reveal class="bg-primary text-white">
        <q-toolbar class="bg-primary text-white">
          <q-btn dense flat round icon="arrow_back_ios" @click="onClose" />
          <q-toolbar-title>Coleta avulsa</q-toolbar-title>
          <q-btn stretch flat icon="sync" @click="actUploadSync" v-if="baixa ? (!baixa.syncid) : false" />
          <q-btn stretch flat icon="delete_forever" @click="actExcluir" v-if="baixa ? (!baixa.syncid) : false" />
        </q-toolbar>
      </q-header>
      <q-page-container class="bg-grey-2">
        <q-page>
          <q-toolbar v-if="baixa ? (baixa.syncid) : false" class="bg-positive text-white">
            <q-toolbar-title class="text-body2"><q-icon name="sync" class="q-mr-md" /> Sincronizado {{ $helpers.datetimeRelativeToday(baixa.synced_at) }}</q-toolbar-title>
          </q-toolbar>
          <div v-if="baixa" class="q-pa-sm full-width fit row wrap justify-between items-end content-start">
              <div class="full-width q-py-md">
                <div class="row" >
                  <div class="col-4 q-pr-xs">
                    <q-field label="# registro" stack-label outlined>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline" tabindex="0">
                          {{ baixa.id }}
                        </div>
                      </template>
                    </q-field>
                  </div>
                  <div class="col-8">
                    <q-field label="Data do registro" stack-label outlined>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline" tabindex="0">
                          {{$helpers.dateToBR(baixa.data) + '  às  ' + $helpers.timeToBR(baixa.data, 'mm:ss')}}
                        </div>
                      </template>
                    </q-field>
                  </div>
                </div>
              </div>
              <div class="full-width q-mt-md">
                <div class="text-h6">{{ baixa.docfiscal === 'nfse' ? 'Nota de serviço' : 'Nota de produto' }}</div>
                <div class="row" v-if="baixa.docfiscal !== 'nfse' ">
                  <div class="col-12 q-py-xs">
                    <q-field label="Chave da Nota" stack-label outlined>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline" tabindex="0">
                          {{$helpers.mascaraChaveNFe(baixa.nota.chave)}}
                        </div>
                      </template>
                    </q-field>
                  </div>
                </div>
                <div class="row full-width justify-between" v-if="baixa.docfiscal !== 'nfse' ">
                  <div class="col-3 q-pr-xs">
                    <q-field label="Número nota" stack-label outlined >
                      <template v-slot:control>
                      <div class="self-center full-width no-outline" tabindex="0">{{getNFeNumero}}</div>
                      </template>
                    </q-field>
                  </div>
                  <div class="col-3 q-pr-xs">
                      <q-field label="Mês/Ano" stack-label outlined>
                      <template v-slot:control>
                      <div class="self-center full-width no-outline" tabindex="0">{{getNFeMesAno}}</div>
                      </template>
                    </q-field>
                  </div>
                  <div class="col-6">
                    <q-field label="CNPJ" stack-label outlined>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline" tabindex="0">
                          {{$helpers.mascaraCpfCnpj(getNFeCNPJ)}}
                        </div>
                      </template>
                    </q-field>
                  </div>
                </div>
                <div class="row full-width justify-between" v-if="baixa.docfiscal === 'nfse' ">
                  <div class="col-3 q-pr-xs">
                    <q-field label="Nº nota" stack-label outlined >
                      <template v-slot:control>
                      <div class="self-center full-width no-outline" tabindex="0">{{baixa.notaservico.numero}}</div>
                      </template>
                    </q-field>
                  </div>
                  <div class="col-4 q-pr-xs">
                    <q-field label="Data emissão" stack-label outlined>
                      <template v-slot:control>
                      <div class="self-center full-width no-outline" tabindex="0">{{ $helpers.dateToBR(baixa.notaservico.dh) }}</div>
                      </template>
                    </q-field>
                  </div>
                  <div class="col-5">
                    <q-field label="Valor R$" stack-label outlined>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline" tabindex="0">
                          {{ baixa.notaservico.valor }}
                        </div>
                      </template>
                    </q-field>
                  </div>
                </div>
                <div class="full-width q-mt-md">
                  <div class="text-h6">Remetente</div>
                  <div class="row full-width justify-between items-end q-mt-xs">
                    <div class="col-12">
                      <q-field label="CNPJ" stack-label outlined>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline" tabindex="0">
                            {{ $helpers.mascaraCpfCnpj(baixa.coleta.remetente.cnpj) }}
                          </div>
                        </template>
                      </q-field>
                    </div>
                    <div class="col-12 q-py-xs">
                      <q-field label="Razão Social" stack-label outlined>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline" tabindex="0">
                            {{ baixa.coleta.remetente.razaosocial }}
                          </div>
                        </template>
                      </q-field>
                    </div>
                    <div class="col-12 q-py-xs">
                      <q-field label="Endereço" stack-label outlined v-if="baixa.coleta.remetente">
                        <template v-slot:control>
                          <div class="self-center full-width no-outline text-caption" tabindex="0" input-class="text-caption">
                            <div>{{ baixa.coleta.remetente.endereco }}</div>
                            <div>{{ (baixa.coleta.remetente.bairro !== '' ? 'CEP: ' + $helpers.mascaraCEP(baixa.coleta.remetente.cep) + ' - ' : '') + baixa.coleta.remetente.bairro }}</div>
                            <div>{{ baixa.coleta.remetente.cidade + ' - ' + baixa.coleta.remetente.uf }}</div>
                          </div>
                        </template>
                      </q-field>
                    </div>
                  </div>
                </div>
                <div class="full-width q-mt-md" v-if="baixa.coleta.destinatario">
                  <div class="text-h6">Destinatário</div>
                  <div class="row full-width justify-between items-end q-mt-xs">
                    <div class="col-12" >
                      <q-field label="CNPJ" stack-label outlined>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline" tabindex="0">
                            {{ $helpers.mascaraCpfCnpj(baixa.coleta.destinatario.cnpj) }}
                          </div>
                        </template>
                      </q-field>
                    </div>
                    <div class="col-12 q-py-xs">
                      <q-field label="Razão Social" stack-label outlined>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline" tabindex="0">
                            {{ baixa.coleta.destinatario.razaosocial }}
                          </div>
                        </template>
                      </q-field>
                    </div>
                    <div class="col-12 q-py-xs">
                      <q-field label="Endereço" stack-label outlined v-if="baixa.coleta.destinatario">
                        <template v-slot:control>
                          <div class="self-center full-width no-outline text-caption" tabindex="0" input-class="text-caption">
                            <div>{{ baixa.coleta.destinatario.endereco }}</div>
                            <div>{{ (baixa.coleta.destinatario.bairro !== '' ? 'CEP: ' + $helpers.mascaraCEP(baixa.coleta.destinatario.cep) + ' - ' : '') + baixa.coleta.destinatario.bairro }}</div>
                            <div>{{ baixa.coleta.destinatario.cidade + ' - ' + baixa.coleta.destinatario.uf }}</div>
                          </div>
                        </template>
                      </q-field>
                    </div>
                  </div>
                </div>
                <div class="row full-width justify-between items-end q-mt-md">
                  <div class="col-12">
                    <q-field label="Observações" stack-label outlined v-if="baixa.obs !== ''">
                        <template v-slot:control>
                          <div class="self-center full-width no-outline text-caption" tabindex="0" input-class="text-caption">
                            {{ baixa.obs }}
                          </div>
                        </template>
                      </q-field>
                  </div>
                </div>
              </div>
          </div>
        </q-page>
      </q-page-container>
    </q-layout>
  </q-dialog>
</template>

<script>
import BaixasCollection from 'src/mvc/collections/baixas.js'
export default {
  props: ['baixaitem'],
  data: function () {
    return {
      layout: true,
      baixa: null
    }
  },
  async mounted () {
    if (this.baixaitem) {
      this.baixa = this.baixaitem
    }
    this.InitNew()
  },
  computed: {
    configCalendar: function () {
      return this.$root._i18n.messages['pt-br'].calendar
    },
    NFeIsValid () {
      try {
        var b = false
        if (!this.baixa) throw new Error()
        if (!this.baixa.nota) throw new Error()
        b = this.baixa.nota.isValid()
      } catch (error) {
        b = false
      }
      return b
    },
    getNFeNumero () {
      try {
        var num = null
        if (!this.baixa) throw new Error()
        if (!this.baixa.nota) throw new Error()
        if (!this.baixa.nota.nNF) throw new Error()
        num = this.baixa.nota.nNF
      } catch (error) {
        num = null
      }
      return num
    },
    getNFeCNPJ () {
      try {
        var num = null
        if (!this.baixa) throw new Error()
        if (!this.baixa.nota) throw new Error()
        if (!this.baixa.nota.CNPJ) throw new Error()
        num = this.baixa.nota.CNPJ
      } catch (error) {
        num = null
      }
      return num
    },
    getNFeMesAno () {
      try {
        var num = null
        if (!this.baixa) throw new Error()
        if (!this.baixa.nota) throw new Error()
        if (!this.baixa.nota.mesAno) throw new Error()
        num = this.baixa.nota.mesAno
      } catch (error) {
        num = null
      }
      return num
    }
  },
  methods: {
    async actUploadSync () {
      var app = this
      this.$store.dispatch('app/refreshConnection')
      let online = this.$store.state.app.conexaointernet.online
      if (online) {
        var baixasSync = new BaixasCollection()
        var ret = await baixasSync.SyncBaixas(app)
        if (!ret.ok) {
          app.$q.dialog({
            color: 'negative',
            title: 'Tentativa de envio',
            message: ret.msg
          }).onOk(() => {
          })
        } else {
          this.$q.notify({
            message: 'Baixa enviada com sucesso',
            color: 'positive',
            timeout: 1000
          })
        }
      }
    },
    async actExcluir () {
      var app = this
      try {
        if (!app.baixa) throw new Error('')
        var ret = await app.confirm('Excluir coleta avulsa #' + app.baixa.id + '?', 'Após excluir não será possível recuperar a coleta')
        if (!ret) throw new Error('Cancelado pelo usuário')
        ret = await app.baixa.localDelete()
        if (ret.ok) {
          app.onClose(true)
        } else {
          alert(ret.msg)
        }
      } catch (error) {
        ret = { ok: false, msg: error.message }
      }
    },
    async confirm (title, msg) {
      return new Promise((resolve, reject) => {
        this.$q.dialog({
          title: title,
          message: msg,
          persistent: true,
          ok: {
            label: 'Sim',
            color: 'negative',
            unelevated: false
          },
          cancel: {
            label: 'Não',
            color: 'negative',
            flat: true
          }
        }).onOk(() => {
          resolve(true)
        }).onCancel(() => {
          reject(new Error(''))
        }).onDismiss(() => {
        })
      })
    },
    onClose (TeveAlteracao) {
      this.$emit('close', TeveAlteracao)
    },
    InitNew () {
    },
    async actInit () {
      var app = this
      app.loading = true
      this.$store.commit('app/title', 'Coleta Avulsa')
      // app.coleta = await app.coletas.find(app.idcoleta)
      // if (app.coleta) {
      //   if (app.coleta.id > 0) {
      //     app.baixas = await app.coleta.baixas()
      //   }
      // }
      // this.$store.commit('app/title', (app.coleta ? '#' + app.coleta.id : 'Coleta não encontrada'))
      app.loading = false
    }
  }
}
</script>
