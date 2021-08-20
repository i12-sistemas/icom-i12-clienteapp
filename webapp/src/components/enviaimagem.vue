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
            <q-toolbar-title><span class="text-weight-bold">Registro de nota fiscal</span></q-toolbar-title>
            <q-space />
            <q-btn flat extended icon="fas fa-barcode" label="Imagem" @click="open" />
            <q-btn flat extended icon="delete" @click="actClearImg" v-if="controleshabilitados" />
          </q-toolbar>
        </q-header>
        <q-page-container>
          <q-page class="q-pa-sm">
            <croppa v-model="myCroppa"
              ref="croppa"
              :height="$q.screen.width-20"
              :width="$q.screen.width-20"
              :placeholder-font-size="16"
              placeholder="Clique aqui para abrir uma imagem"
              :show-remove-button="false"
              :file-size-limit="limiteSize"
              :accept="acceptExt"
              @file-choose="onFileChoose"
              @file-size-exceed="fileSizeExceed"
              @file-type-mismatch="fileTypeError"
              @move="onZoom"
              @new-image-drawn="onNewImage"
              @zoom="onZoom"
              :initial-image="initValue"
              :prevent-white-space="false"
              :disabled="!editado"
              >
            </croppa>
            <div class="q-pa-md q-gutter-sm row justify-center">
              <span class="text-caption">Exemplos de uso</span>
            </div>
            <div class="q-gutter-sm row justify-between" v-if="dataUrl ? dataUrl != '' : false">
              <q-avatar size="150px" class="shadow-3" >
                <img :src="dataUrl" >
              </q-avatar>
              <q-avatar size="150px" square class="shadow-3" style=" border-radius: 0px">
                <img :src="dataUrl" >
              </q-avatar>
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
import { debounce } from 'quasar'
export default {
  data: function () {
    return {
      editado: false,
      loading: true,
      layout: false,
      initialImage: null,
      myCroppa: {},
      // 10 Mb = 10485760
      limiteSize: 10485760,
      acceptExt: '.jpeg,.jpg,.bmp,.tiff,.png',
      dataUrl: '',
      fileInput: null
    }
  },
  props: ['initValue', 'initDisabled'],
  mounted () {
    var app = this
    app.loading = true
    app.layout = true
    app.editado = false
    app.$nextTick(() => {
      var image = new Image()
      // Notice: it's necessary to set "crossorigin" attribute before "src" attribute.
      image.setAttribute('crossorigin', 'anonymous')
      image.src = app.initValue
      this.initialImage = image
      this.$refs.croppa.refresh()
    })
    let deb = debounce(function () {
      app.actOutput()
    }, 300)
    app.$on('editado', function () {
      if (app.loading) return
      deb()
    })
    app.loading = false
  },
  computed: {
    controleshabilitados () {
      return (this.editado && this.$refs.croppa.hasImage())
    }
  },
  methods: {
    onFileChoose (file) {
      var app = this
      app.fileInput = file
      app.editado = true
    },
    actFlip (b) {
      if (b) {
        this.$refs.croppa.flipX()
      } else {
        this.$refs.croppa.flipY()
      }
      this.$emit('editado')
    },
    actOutput () {
      var app = this
      if (app.$refs.croppa.hasImage()) {
        app.dataUrl = app.$refs.croppa.generateDataUrl()
      } else {
        app.dataUrl = ''
      }
    },
    actClearImg () {
      this.$refs.croppa.remove()
      this.editado = true
      this.fileInput = null
      this.$emit('editado')
    },
    fileSizeExceed (file) {
      this.$q.dialog({
        title: 'Arquivo muito grande!',
        message: 'Escolha um arquivo de até ' + this.$helpers.bytesToHumanFileSizeString(this.limiteSize)
      })
    },
    fileTypeError (file) {
      this.$q.dialog({
        title: 'Arquivo inválido!',
        message: 'Extensão do tipo "' + this.$helpers.getExtension(file.name) + '" não é permitida. Use somente extensões ' + this.acceptExt
      })
    },
    onNewImage () {
      this.sliderVal = this.$refs.croppa.scaleRatio
      this.sliderMin = this.$refs.croppa.scaleRatio / 2
      this.sliderMax = this.$refs.croppa.scaleRatio * 2
      this.$emit('editado')
    },
    onZoom () {
      this.$emit('editado')
    },
    open () {
      this.editado = true
      this.$nextTick(() => {
        this.editado = false
        this.$refs.croppa.chooseFile()
      })
    },
    actClose () {
      var app = this
      app.layout = false
      app.$emit('close')
    },
    actConfirm () {
      var app = this
      let ext = this.$helpers.getExtension(this.fileInput.name)
      let name = this.fileInput.name
      let data = app.$refs.croppa.generateDataUrl(this.fileInput.type)
      let size = data.length
      let ret = { data: data, name: name, ext: ext, size: size }
      app.$emit('confirm', ret)
      app.layout = false
    }
  }
}
</script>
