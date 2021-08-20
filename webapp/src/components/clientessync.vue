<template>
  <div v-if="ativo" class="q-py-lg bg-grey-2">
    <div v-if="ativo" class="q-py-lg bg-white text-center">
      <q-circular-progress show-value font-size="12px"
          :value="perc" :indeterminate="!perc" track-color="grey-3"
          size="50px" class="text-light-blue q-ma-md">
      <span v-if="perc">{{ perc }}%</span>
      </q-circular-progress>
      <div class="text-center q-pt-md text-body1">
        {{ message }}
      </div>
      <div class="text-center text-caption">
        Este processo pode demorar alguns minutos na primeira vez!
      </div>
    </div>
  </div>
</template>

<script>
import { QCircularProgress } from 'quasar'
export default {
  props: ['ativo'],
  components: {
    QCircularProgress
  },
  data: function () {
    return {
      message: 'Sincronizando dados...',
      perc: null
    }
  },
  async mounted () {
    var app = this
    app.$root.$on('clientesdownloadsynced', (dados) => {
      app.actUpdateProgressDownload(dados)
    })
  },
  methods: {
    actUpdateProgressDownload (dados) {
      try {
        this.perc = dados.perc ? dados.perc : null
        this.message = dados.msg ? dados.msg : 'Sincronizando dados...'
      } catch (error) {
        this.perc = null
        console.error(error)
      }
    }
  }
}
</script>
