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
        </q-toolbar>
      </q-header>
      <q-page-container>
        <q-page class="q-pa-sm">
          <div id="scan" v-show="cameraStatus === 1"></div>
          <div class="text-h6" v-if="code">Codigo: {{ code }}</div>
          <div class="text-h6 text-negative" v-if="error">error: {{ error }}</div>
          <div class="q-pa-md q-gutter-sm row justify-center">
            <span class="text-caption">Exemplos de uso</span>
          </div>

        </q-page>
      </q-page-container>
    </q-layout>
  </q-dialog>
</div>
</template>

<style>
.croppa-container {
  background-color: rgba(255, 255, 255, 0.6);
  border: 1px solid #dadadaeb;
}
</style>

<script>
import Quagga from 'quagga'
export default {
  data: function () {
    return {
      loading: true,
      layout: true,
      initialImage: null,
      code: '',
      error: null,
      cameraStatus: 0
    }
  },
  mounted () {
    var app = this
    app.iniciarLeitor()
  },
  methods: {
    iniciarLeitor () {
      // this.actteste()
      this.cameraStatus = 1
      Quagga.init({
        inputStream: {
          name: 'Live',
          type: 'LiveStream',
          // constraints: {
          //   width: 300,
          //   height: 300
          // },
          target: document.querySelector('#scan')
        },
        frequency: 10,
        decoder: {
          readers: [
            'ean_reader'
          ],
          multiple: false
        }
      // numOfWorkers: navigator.hardwareConcurrency,
      // locate: false
      }, (err) => {
        if (err) {
          alert(err)
          return
        }
        alert('Initialization finished. Ready to start')
        Quagga.start()
        Quagga.onDetected(this.onDetected)
      })
    },
    onDetected (data) {
      this.code = data.codeResult.code
      this.cameraStatus = 0
      this.onStop()
    },
    onStop () {
      Quagga.stop()
      this.cameraStatus = 0
    },
    actClose () {
      var app = this
      app.layout = false
      app.$emit('close')
    },
    actConfirm () {
      var app = this
      let ret = { ok: true }
      this.onStop()
      app.$emit('confirm', ret)
      app.layout = false
    }
  }
}
</script>
