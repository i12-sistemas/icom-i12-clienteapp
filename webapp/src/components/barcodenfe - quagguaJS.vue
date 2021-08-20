<template>
<div>
  <q-dialog v-model="layout" persistent maximized
      transition-show="slide-left"
      transition-hide="slide-rigth"
      >
    <q-layout view="hHr lpR fFf" class="bg-black">
      <q-page-container>
        <q-page class="q-pa-sm">
          <div class="row items-center" style="height: 100vh">
            <div class="col text-center q-pa-sm ">
              <div id="scan" v-show="cameraStatus === 1"></div>
              <q-page-sticky position="bottom-right" :offset="[18, 18]">
                <q-btn icon="cancel" color="primary" label="Cancelar" v-show="cameraStatus === 1"
                @click="actCancel" />
              </q-page-sticky>
            </div>
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
      cameraStatus: 0,
      halfSample: true,
      patchSize: 1,
      zoom: 1
    }
  },
  mounted () {
    var app = this
    setTimeout(() => {
      app.iniciarLeitor()
    }, 300)
  },
  methods: {
    changepatchSize () {
      this.patchSize += this.patchSize
      if (this.patchSize > 4) {
        this.patchSize = 0
      }
    },
    iniciarLeitor () {
      var app = this
      // this.actteste()
      let patchSizeList = [ 'x-small', 'small', 'medium', 'large', 'x-large' ]
      this.cameraStatus = 1
      Quagga.init({
        inputStream: {
          name: 'Live',
          type: 'LiveStream',
          constraints: {
            height: (app.$q.screen.width - 40),
            width: (app.$q.screen.height - 20),
            facingMode: 'environment',
            aspectRatio: { min: 1, max: 2 }
          },
          // area: {
          //   top: '10%',
          //   right: '10%',
          //   left: '10%',
          //   bottom: '10%'
          // },
          target: document.querySelector('#scan')
        },
        locator: {
          patchSize: 'medium',
          halfSample: true
        },
        frequency: 10,
        debug: {
          drawBoundingBox: true,
          showFrequency: true,
          drawScanline: true,
          showPattern: true
        },
        halfSample: app.halfSample,
        patchSize: patchSizeList[this.patchSize], // x-small, small, medium, large, x-large
        decoder: {
          readers: [
            'code_128_reader'
          ],
          multiple: false
        },
        numOfWorkers: navigator.hardwareConcurrency,
        locate: true
      }, (err) => {
        if (err) {
          alert(err)
          return
        }
        Quagga.start()
        Quagga.onDetected(this.onDetected)
      })
    },
    onDetected (data) {
      this.code = data.codeResult.code
      if (this.code.length === 44) {
        this.cameraStatus = 0
        this.onStop()
        this.actClose(this.code)
      } else {
        alert('Código inválido para uma nota fiscal.\n' + this.code)
      }
    },
    onStop () {
      Quagga.stop()
      this.cameraStatus = 0
    },
    actCancel () {
      var app = this
      if (app.cameraStatus === 1) {
        app.onStop()
      }
      app.layout = false
      app.$emit('close', '')
    },
    actClose (barcode) {
      var app = this
      app.onStop()
      app.layout = false
      app.$emit('close', barcode)
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
