<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        Nova Carga para Entrega
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
              <selectunidade outlined label="Unidade de Saída" v-model="dataset.unidadesaida" ref="txtunidade" :clearable="false" />
            </q-card-section>
            <q-separator  spaced />
            <q-card-section >
              <selectmotorista outlined label="Motorista" nullabled v-model="dataset.motorista" :hideveiculo="true" :hidealerta="true" ref="txtmotorista" @input="changedMotorista" />
            </q-card-section>
            <q-card-section class="q-py-xs" v-if="veiculodiferente">
              <div class="col-xs-12 col-md-1 full-height self-center text-center" >
                <div class="q-pa-sm">Veículo atual é diferente do veículo do motorista</div>
                <q-btn icon="info" color="red" @click="actAplicarMesmoVeiculo" outline class="full-width q-py-sm" :label="'Aplicar placa ' + (dataset.motorista.veiculo ? $helpers.placaMask(dataset.motorista.veiculo.placa) : '')" />
              </div>
            </q-card-section>
            <q-card-section >
              <selectveiculo outlined label="Veículo" v-model="dataset.veiculo"  @input="onChangeVeiculo" :hidedescricao="true" />
            </q-card-section>
            <q-separator spaced  />
            <q-card-actions vertical align="center">
              <q-btn  label="Incluir" color="primary" unelevated icon="check" class="full-width" @click="actSave" />
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
import selectveiculo from 'src/components/cnp-select-veiculo'
import selectmotorista from 'src/components/cnp-select-motorista'
import CargaEntrega from 'src/mvc/models/cargaentrega.js'
export default {
  components: {
    selectunidade,
    selectveiculo,
    selectmotorista
  },
  data: function () {
    let dataset = new CargaEntrega()
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
    app.dataset.unidadesaida.cloneFrom(await app.$helpers.getUnidadeLogada(app))
  },
  computed: {
    veiculodiferente () {
      if (this.loading) return false
      if (!this.dataset.motorista) return false
      if (!(this.dataset.motorista.id > 0)) return false
      if (!this.dataset.motorista.veiculo) return false
      if (!(this.dataset.motorista.veiculo.id > 0)) return false
      if (!this.dataset.veiculo) return true
      if (!(this.dataset.veiculo.id > 0)) return true

      return !(this.dataset.motorista.veiculo.id === this.dataset.veiculo.id)
    },
    podeEncerrar: function () {
      try {
        var b = true
        if (this.loading) throw new Error('')
        if (!this.dataset) throw new Error('')
        if (!(this.dataset.id > 0)) throw new Error('')
        if (this.dataset.status.value !== '1') throw new Error('')
        if (this.dataset.erroqtde > 0) throw new Error('')
        if (this.dataset.volqtde === 0) throw new Error('')
        if (this.dataset.conferidoqtde !== this.dataset.volqtde) throw new Error('')
      } catch (error) {
        b = false
      }
      return b
    }
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
          app.$router.push({ name: 'cargas.entregas.edit', params: { id: app.dataset.id } })
          app.loading = false
        })
      } else {
        app.$helpers.showDialog(ret, ret.warning)
      }
      app.saving = false
    },
    async actAplicarMesmoVeiculo () {
      var app = this
      await app.dataset.veiculo.cloneFrom(app.dataset.motorista.veiculo)
      await app.onChangeVeiculo(app.dataset.motorista.veiculo)
    },
    async changedMotorista (val) {
      var app = this
      try {
        if (app.loading) throw new Error('In loading')
        if (!val) throw new Error('Nenhum motorista')
        if (!val.veiculo) throw new Error('Nenhum veiculo')
        if (!(val.veiculo.id > 0)) throw new Error('Nenhum veiculo')

        await app.dataset.veiculo.cloneFrom(val.veiculo)
        app.$q.notify({
          color: 'green',
          multiLine: true,
          timeout: 1500,
          message: 'Veículo alterado',
          caption: 'Novo veículo placa ' + app.dataset.veiculo.placa
        })
        await app.onChangeVeiculo(val.veiculo)
      } catch (error) {
      }
    },
    async onChangeVeiculo (veiculo) {
      var app = this
      try {
        if (app.loading) throw new Error('In loading')
        if (!veiculo) throw new Error('Nenhum veiculo')
        if (!(veiculo.id > 0)) throw new Error('Nenhum veiculo')
        var ret = await veiculo.getUltimoKmAcerto()
        if (ret.ok) {
          if (veiculo.ultimokmacerto > 0) {
            app.dataset.kmini = veiculo.ultimokmacerto
            app.$q.notify({
              color: 'green',
              multiLine: true,
              timeout: 1500,
              message: 'Km inicial atualizado',
              caption: 'Novo KM inicial ' + veiculo.ultimokmacerto
            })
          }
        }
      } catch (error) {
      }
    }
  }
}
</script>
