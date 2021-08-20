<template>
<div>
  <q-dialog v-model="layout" persistent maximized
      transition-show="slide-left"
      transition-hide="slide-rigth"
      >
    <q-layout view="hHr lpR fFf" class="bg-black">
      <q-page-container>
        <q-page class="q-pa-sm">
          <div class="col text-center full-width q-pa-sm text-red" v-if="error">
            <p class="text-body1">Infelizmente a câmera falhou.</p>
            <p class="text-body2">Verifique as permissões da câmera.</p>
            <p class="text-caption">{{ error }}</p>
          </div>
          <div class="row items-center" style="height: 100vh">
            <div class="col text-center q-pa-sm ">
              <q-page-sticky position="bottom-right" :offset="[18, 18]">
                <q-btn icon="cancel" color="primary" label="Cancelar" @click="actCancel" />
              </q-page-sticky>
            </div>
          </div>
        </q-page>
      </q-page-container>
    </q-layout>
  </q-dialog>
</div>
</template>

<script>
export default {
  data: function () {
    return {
      loading: true,
      layout: true,
      code: '',
      error: null
    }
  },
  mounted () {
    var app = this
    setTimeout(() => {
      app.actScan()
    }, 200)
  },
  methods: {
    actScan () {
      let params = {
        'prompt_message': 'Escaneia o código da nota fiscal', // Change the info message. A blank message ('') will show a default message
        'orientation_locked': true, // Lock the orientation screen
        'camera_id': 0, // Choose the camera source
        'beep_enabled': true, // Enables a beep after the scan
        'scan_type': 'normal', // Types of scan mode: normal = default black with white background / inverted = white bars on dark background / mixed = normal and inverted modes
        'barcode_formats': ['QR_CODE', 'CODE_128'], // Put a list of formats that the scanner will find. A blank list ([]) will enable scan of all barcode types
        'extras': {} // Additional extra parameters. See [ZXing Journey Apps][1] IntentIntegrator and Intents for more details
      }
      window.plugins.zxingPlugin.scan(params, this.onSuccess, this.onFailure)
    },
    onSuccess (data) {
      this.code = data
      if (this.code.length === 44) {
        this.actClose(this.code)
      } else {
        alert('Código inválido para uma nota fiscal.\n' + this.code)
      }
    },
    onFailure (e) {
      var app = this
      app.error = e
      // app.layout = false
      // app.$emit('close', '')
    },
    actCancel () {
      var app = this
      app.layout = false
      app.actClose('')
    },
    actClose (barcode) {
      var app = this
      app.layout = false
      app.$emit('close', barcode)
    }
  }
}
</script>
