<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        {{ $store.state.app.title }}
      </q-toolbar-title>
      <q-btn flat icon="photo_camera" label="Ler documento" @click="captureImage" v-if="!imageSrc" />
      <q-btn flat icon="clear" label="Limpar" @click="actLimparTudo" v-if="imageSrc" />
    </q-toolbar>
    <q-tabs v-model="tab" class="text-white bg-primary"  align="justify" active-bg-color="blue-grey-9" inline-label v-if="imageIdentificada === 'N'">
      <q-tab name="porcamera" icon="qr_code_scanner" label="Leitor"  >
          <q-badge color="positive" floating rounded v-if="imageIdentificada === 'S'" />
          <q-badge color="negative" floating rounded v-if="imageIdentificada === 'N'" />
      </q-tab>
      <q-tab name="porcarga" icon="touch_app" label="Por carga"  />
      <q-tab name="porcte" icon="touch_app" label="Por CT-e"  />
    </q-tabs>
  </q-header>

  <q-page-container class="bg-grey-3" >
    <q-page class="q-pa-md">

      <q-card class="bg-red text-white full-width q-ma-md text-center" bordered flat v-if="!loading && error" >
        <q-card-section>
          <q-avatar size="100px" font-size="52px" color="white" text-color="red" icon="highlight_off" />
        </q-card-section>
        <q-card-section class="text-subtitle2">
          {{error}}
        </q-card-section>
        <q-card-section class="text-subtitle2">
          <q-btn color="white" label="Tentar novamente" @click="ean13 = ''" flat size="lg" outline />
        </q-card-section>
      </q-card>
      <q-tab-panels v-model="tab" animated class="bg-transparent">
        <q-tab-panel name="porcamera" class="q-pa-none">
          <q-card class="bg-white text-primary full-width text-center"  bordered flat v-if="!loading && !error && !imageSrc" >
            <q-card-section class="text-h6">
              <q-avatar size="100px" font-size="52px" color="bg-grey-1" text-color="primary" icon="qr_code_scanner" />
            </q-card-section>
            <q-card-section class="text-h6">
              Tire uma foto do comprovante de entrega assinado!
            </q-card-section>
            <q-card-section class="text-subtitle2">
              <q-btn icon="photo_camera" label="Ler documento" @click="captureImage" outline class="full-width" size="lg" v-if="!imageSrc" />
            </q-card-section>
          </q-card>

          <q-card class="bg-white text-primary full-width text-center" bordered flat v-if="((tipobaixa ? tipobaixa !== '' : false)  || imageSrc) && !loading" >
            <q-card-section v-if="!((tipobaixa === 'carga') || (tipobaixa === 'cte')) && imageSrc && !loading">
              <q-banner class="bg-negative text-white" rounded>
                <div class="text-h6">Nenhuma carga ou CT-e identificado no documento!</div>
                <q-separator spaced inset dark />
                <div class="text-body">Tire uma nova foto mais legível ou insira os dados manualmente.</div>
              </q-banner>
              <div class="row q-gutter-y-md q-mt-md">
                <div class="col-12">
                  <q-btn icon="photo_camera" label="Nova foto" @click="captureImage" outline class="full-width" size="lg" />
                </div>
                <div class="col-12">
                  <q-btn icon="touch_app" label="Manual por carga" @click="tab = 'porcarga'" outline class="full-width" size="lg" />
                </div>
                <div class="col-12">
                  <q-btn icon="touch_app" label="Manual por CT-e" @click="tab = 'porcte'" outline  class="full-width" size="lg"/>
                </div>
              </div>
            </q-card-section>
            <q-card-section v-if="(tipobaixa === 'carga') && (carga ? carga.cargaid > 0 : false) && imageSrc && !loading">
              <div class="row text-left text-h6">
                <div class="col-12 text-center bg-blue-grey-1 text-blue-grey-10 rounded-borders q-pa-sm q-mb-sm" >BAIXA POR CARGA</div>
                <div class="col-12">Carga: <span class="text-weight-bold">{{carga.cargaid}}</span></div>
                <div class="col-12" v-if="carga.senha ? carga.senha.length === 6 : false">Senha: <span class="text-weight-bold" >******</span></div>
                <div class="col-12 rounded-borders bg-negative text-white q-pa-xs text-center" v-if="carga.senha ? carga.senha.length !== 6 : true">Senha inválida</div>
                <div class="col-12">Criado em: <span class="text-weight-bold">{{$helpers.datetimeToBR(carga.created_at, true)}}</span></div>
                <div class="col-12">Motorista ID: <span class="text-weight-bold">{{carga.motoristaid}}</span></div>
                <div class="col-12">Placa: <span class="text-weight-bold">{{carga.veiculo}}</span></div>
              </div>

              <div class="q-mt-md text-subtitle2" v-if="imageSrc">
                <q-btn label="Salvar" unelevated @click="actSave" class="full-width" size="lg" color="positive" />
              </div>
            </q-card-section>
            <q-card-section v-if="(tipobaixa === 'cte') && (cte ? cte.isValid() : false) && imageSrc && !loading">
              <div class="row text-left text-h6">
                <div class="col-12 text-center bg-blue-grey-10 text-white rounded-borders q-pa-sm q-mb-sm" >BAIXA POR CT-E</div>
                <div class="col-12" v-if="cte.nNF ? cte.nNF > 0 : false">CT-e: <span class="text-weight-bold">{{$helpers.formatRS(cte.nNF, '', 0)}}</span></div>
                <div class="col-12">CNPJ: <span class="text-weight-bold">{{$helpers.mascaraDocCPFCNPJ(cte.CNPJ)}}</span></div>
                <div class="col-12">Mês/Ano: <span class="text-weight-bold">{{cte.mesAno}}</span></div>
                <div class="col-12 q-mt-sm text-center q-pa-sm rounded-borders" style="border: 1px solid grey">
                  <div class="col-12">Chave do CT-e</div>
                  <div class="col-12 text-caption">{{cte.chave}}</div>
                </div>
              </div>
              <div class="q-mt-md text-subtitle2" v-if="imageSrc">
                <q-btn label="Salvar" unelevated @click="actSave" class="full-width" size="lg" color="positive"  />
              </div>
            </q-card-section>
            <q-card-section v-if="imageSrc && !loading">
              <q-img :src="imageBase64" contain spinner-color="primary" spinner-size="82px" />
            </q-card-section>
          </q-card>
        </q-tab-panel>
        <q-tab-panel name="porcarga" class="q-pa-none">
          <q-card class="bg-white text-primary full-width" bordered flat>
            <q-card-section class="text-h6   ">
              Baixa manual por Carga
            </q-card-section>
            <q-card-section >
              <div class="row row full-height items-center q-gutter-y-sm">
                <div class="col-4">
                  Nº da Carga
                </div>
                <div class="col-8">
                  <q-input v-model="cargamanualid" type="number" outlined maxlength="11" ref="txtcargaid" input-class="text-h5 full-width"  />
                </div>
                <div class="col-4">
                  Senha da Carga
                </div>
                <div class="col-8">
                  <q-input v-model="cargamanualsenha" outlined type="text" maxlength="6" counter ref="txtsenha" name="txtsenha" @keyup="onInputSenha" input-class="text-h5" />
                </div>
              </div>
              <div class="q-mt-md text-subtitle2" v-if="imageSrc">
                <q-btn label="Salvar" unelevated @click="actSave" class="full-width" size="lg" color="positive"  />
              </div>
            </q-card-section>
            <q-card-section v-if="imageSrc && !loading">
              <q-img :src="imageBase64" contain spinner-color="primary" spinner-size="82px" />
            </q-card-section>
          </q-card>
        </q-tab-panel>
        <q-tab-panel name="porcte" class="q-pa-none">
          <q-card class="bg-white text-primary full-width" bordered flat>
            <q-card-section class="text-h6   ">
              Baixa manual por CT-e
            </q-card-section>
            <q-card-section >
              <div class="row">
                <div class="col-12">
                  <q-input v-model="ctemanualtext1" type="tel" label="Chave do CT-e - Parte 1"  stack-label outlined maxlength="22" counter ref="txtctemanualtext1" @input="onInputCte1" input-class="text-h6" />
                </div>
                <div class="col-12">
                  <q-input v-model="ctemanualtext2" type="tel" label="Chave do CT-e - Parte 2"  stack-label outlined maxlength="22" counter ref="txtctemanualtext2" @input="onInputCte2" input-class="text-h6" />
                </div>
                <div class="col-12 q-mt-sm" v-if="(ctemanual.chave ? (ctemanual.chave.length > 0) && (!ctemanualretorno.ok) : false)" >
                  <q-banner class="bg-negative text-white" rounded>
                    <div class="text-h6">CT-e inválido!</div>
                    <div class="text-h6">{{ctemanualretorno.msg}}</div>
                    <template v-slot:action>
                      <q-btn color="white" icon="clear" label="Limpar CT-e" @click="actLimparCteManual" outline/>
                    </template>
                  </q-banner>
                </div>
                <div class="col-12 q-mt-sm" v-if="ctemanual ? ctemanualretorno.ok : false"  >
                  <div class="row text-left text-h6" >
                    <div class="col-12" v-if="ctemanual.nNF ? ctemanual.nNF > 0 : false">CT-e: <span class="text-weight-bold">{{$helpers.formatRS(ctemanual.nNF, '', 0)}}</span></div>
                    <div class="col-12">CNPJ: <span class="text-weight-bold">{{$helpers.mascaraDocCPFCNPJ(ctemanual.CNPJ)}}</span></div>
                    <div class="col-12">Mês/Ano: <span class="text-weight-bold">{{ctemanual.mesAno}}</span></div>
                    <div class="col-12 q-mt-sm text-center q-pa-sm rounded-borders" style="border: 1px solid grey">
                      <div class="col-12">Chave do CT-e</div>
                      <div class="col-12 text-caption">{{ctemanual.chave}}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="q-mt-md text-subtitle2" v-if="imageSrc">
                <q-btn label="Salvar" unelevated @click="actSave" class="full-width" size="lg" color="positive"  />
              </div>
            </q-card-section>
            <q-card-section v-if="imageSrc && !loading">
              <q-img :src="imageBase64" contain spinner-color="primary" spinner-size="82px" />
            </q-card-section>
          </q-card>
        </q-tab-panel>
      </q-tab-panels>

    </q-page>
  </q-page-container>
</q-layout>
</template>

<style>
</style>

<script>

import NFe from 'src/mvc/models/nfe.js'
import CargaEntrega from 'src/mvc/models/cargaentrega.js'
import QrcodeDecoder from 'qrcode-decoder'
export default {
  components: {
  },
  directives: {
  },
  props: ['label'],
  data: function () {
    let cte = new NFe()
    let ctemanual = new NFe()
    return {
      tipobaixa: '',
      carga: null,
      cte,
      cargamanualid: null,
      cargamanualsenha: '',
      cteretorno: { ok: false },
      ctemanual,
      ctemanualretorno: { ok: false },
      ctemanualtext1: '3521050972153300013757',
      ctemanualtext2: '0010005637061006599120',
      tab: 'porcamera',
      error: null,
      dialog: null,
      dataImg: null,
      urlImg: null,
      imageOk: false,
      imageSrc: null,
      imageBase64: null,
      loading: false,
      expanded: false
    }
  },
  async mounted () {
    var app = this
    this.$store.commit('app/title', app.label)
    app.etiqueta.limpardados()
    // await app.etiqueta.setUnidadePadrao(app)
    // app.refreshData(false)
  },
  computed: {
    imageIdentificada: function () {
      var app = this
      var b = null
      try {
        if (!app.imageSrc) throw new Error('Sem imagem')
        if (app.loading) throw new Error('Loaging')

        var cteok = false
        if ((app.tipobaixa === 'cte') && (app.cteretorno)) {
          cteok = app.cteretorno.ok
        }
        b = (((app.tipobaixa === 'carga') && (app.carga ? app.carga.cargaid > 0 : false)) || ((app.tipobaixa === 'cte') && (cteok)))
        b = b ? 'S' : 'N'
      } catch (error) {
        b = null
      }
      return b
    }
  },
  methods: {
    async actLimparCteManual () {
      var app = this
      app.ctemanualtext1 = ''
      app.ctemanualtext2 = ''
      app.ctemanual.limpardados()
      app.$refs.txtctemanualtext1.focus()
    },
    async validaCTeManual (e) {
      var app = this
      var chave = (app.ctemanualtext1 ? app.ctemanualtext1 : '') + (app.ctemanualtext2 ? app.ctemanualtext2 : '')
      app.ctemanual.setChave(chave)
      app.ctemanualretorno = await app.ctemanual.isValid()
    },
    async onInputCte1 (e) {
      var app = this
      if (!app.ctemanualtext1) return false
      if ((app.ctemanualtext1.length === 22) && (app.ctemanualtext2 ? app.ctemanualtext2.length !== 22 : true)) {
        app.$refs.txtctemanualtext2.focus()
      }
      app.validaCTeManual()
    },
    onInputSenha () {
      var app = this
      app.cargamanualsenha = app.cargamanualsenha ? app.cargamanualsenha.trim().toUpperCase() : ''
      if (app.cargamanualsenha ? app.cargamanualsenha.length > 6 : false) {
        app.cargamanualsenha = app.cargamanualsenha.substr(0, 6)
      }
    },
    async onInputCte2 (e) {
      var app = this
      app.validaCTeManual()
    },
    async actLimparTudo () {
      this.cte.limpardados()
      this.loading = false
      this.imageOk = false
      this.carga = null
      this.dataImg = null
      this.imageSrc = null
      this.imageBase64 = null
      this.tab = 'porcamera'
    },
    async closeLoading () {
      var app = this
      try {
        app.loading = false
        app.dialog.hide()
      } catch (error) {
        console.error(error.message)
      }
      app.dialog = null
    },
    async showLoading (msg) {
      var app = this
      app.loading = true
      if (!app.dialog) {
        app.dialog = app.$q.dialog({
          message: msg,
          progress: true, // we enable default settings
          color: 'blue',
          persistent: true, // we want the user to not be able to close it
          ok: false // we want the user to not be able to close it
        })
      }
    },
    async actDecodeQrCodeData (data) {
      var app = this
      app.tab = 'porcamera'
      app.tipobaixa = null
      app.cteretorno = { ok: false }
      var retorno = { ok: false }
      if (!data) return retorno
      try {
        app.cte.limpardados()
        if (typeof data === 'string') {
          var ehCargaID = (data.indexOf('cargaid') >= 0)
          if (ehCargaID) {
            var carga = JSON.parse(data)
            if (carga ? carga.cargaid > 0 : false) {
              app.tipobaixa = 'carga'
              app.carga = carga
            }
          } else {
            var ehCTeQrCode = (data.indexOf('https://nfe.fazenda.sp.gov.br/CTeConsulta') >= 0)
            if (ehCTeQrCode) {
              var tag = 'chCTe='
              var idxCte = data.indexOf(tag)
              idxCte = idxCte + tag.length
              var cte = data.substr(idxCte, 44)
              if (cte) {
                await app.cte.setChave(cte)
                app.tipobaixa = 'cte'
                app.cteretorno = await app.cte.isValid()
              }
            }
          }
        }
      } catch (error) {
        retorno.msg = error.message
      }
      return retorno
    },
    async actDecodeImagem () {
      var app = this
      var qrcode = new QrcodeDecoder()
      await qrcode.decodeFromImage(this.imageBase64).then(async (res) => {
        try {
          if (!res) {
            app.chaveCte = null
          } else {
            await app.actDecodeQrCodeData(res.data)
          }
        } finally {
          app.closeLoading()
        }
      })
    },
    async captureImage () {
      var app = this
      console.clear()
      app.showLoading('Processando imagem, aguarde!')
      app.imageBase64 = null
      app.tipobaixa = null
      await navigator.camera.getPicture(
        async dataImg => { // on success
          app.dataImg = dataImg
          await window.resolveLocalFileSystemURL(dataImg, async (data) => {
            // app.imageSrc = await
            data.file(async (fileObject) => {
              app.imageSrc = fileObject
              app.urlImg = app.imageSrc.localURL
              app.imageBase64 = await app.getBase64FromFileObject(app.imageSrc)
              await app.actDecodeImagem()
            })
          })
        },
        () => { // on fail
          app.closeLoading()
          this.$q.notify('Não foi possível acessar a câmera do dispositivo.')
        },
        {
          // camera options
          quality: 100,
          destinationType: navigator.camera.DestinationType.FILE_URI,
          encodingType: navigator.camera.EncodingType.JPEG,
          MEDIATYPE: navigator.camera.MediaType.PICTURE,
          sourceType: navigator.camera.PictureSourceType.CAMERA,
          mediaType: navigator.camera.MediaType.PICTURE,
          cameraDirection: navigator.camera.Direction.BACK,
          saveToPhotoAlbum: false,
          correctOrientation: true,
          targetWidth: 2560,
          targetHeight: 1440
          // 1440p: 2560 x 1440
        }
      )
    },
    async getBase64FromFileObject (fileObject) {
      return new Promise((resolve, reject) => {
        var reader = new FileReader()
        reader.onloadend = function (evt) {
          var image = new Image()
          image.onload = function (e) {
            resolve(evt.target.result)
          }
          image.src = evt.target.result
        }
        reader.readAsDataURL(fileObject)
      })
    },
    async actSave () {
      var app = this
      app.loading = true
      try {
        var lTipo = null
        var docbaixa = null
        var operacao = (app.tab === 'porcamera' ? 'A' : 'M')
        if (app.tab === 'porcte') {
          lTipo = 'cte'
          await app.validaCTeManual()
          if (!app.ctemanual) throw new Error('Nenhuma chave do CT-e informada manualmente')
          if (!app.ctemanualretorno.ok) throw new Error('Chave do CT-e informada manualmente é inválida. ' + app.ctemanualretorno.msg)
          docbaixa = app.ctemanual.chave
        } else if (app.tab === 'porcarga') {
          lTipo = 'carga'
          if (app.cargamanualid ? !(parseInt(app.cargamanualid) > 0) : true) throw new Error('Nenhuma carga informada manualmente')
          app.cargamanualid = parseInt(app.cargamanualid)
          if (app.cargamanualsenha ? app.cargamanualsenha.length !== 6 : true) throw new Error('Informe a senha de 6 caracteres')
          docbaixa = {
            cargaid: app.cargamanualid,
            senha: app.cargamanualsenha
          }
        } else if (app.tab === 'porcamera') {
          lTipo = app.tipobaixa
          if (app.tipobaixa === 'cte') {
            if (!app.cte.chave) throw new Error('Nenhum CT-e informado')
            if (!app.cteretorno.ok) throw new Error('Nenhum CT-e valido encontrado')
            docbaixa = app.cte.chave
          } else if (app.tipobaixa === 'carga') {
            if (!app.carga) throw new Error('Nenhuma carga de entrega identificada')
            if (!(app.carga.cargaid > 0)) throw new Error('Nenhuma carga de entrega identificada')
            if (app.carga.senha ? app.carga.senha.length !== 6 : true) throw new Error('Senha da carga não foi identificada no QRCode')
            docbaixa = app.carga
          } else {
            throw new Error('Tipo de carga não foi identificada')
          }
        }
      } catch (error) {
        app.$helpers.showDialog({ ok: false, msg: error.message })
        app.loading = false
        return false
      }
      var carga = new CargaEntrega()
      var ret = await carga.baixaEntrega(app, app.imageBase64, lTipo, docbaixa, operacao)
      if (ret.ok) {
        this.$q.notify('Baixado com sucesso.')
        this.$q.notify({
          message: 'Baixado com sucesso!',
          color: 'positive',
          timeout: 3000
        })
        app.actLimparTudo()
      } else {
        app.$helpers.showDialog(ret)
      }
      app.loading = false
    }
  }
}
</script>
