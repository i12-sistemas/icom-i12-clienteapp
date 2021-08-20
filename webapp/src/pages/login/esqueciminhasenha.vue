<template>
  <div>
    <q-page class="center tex-primary">
      <div class="row flex-center full-width q-pa-lg" >
        <q-card flat bordered class="full-width bg-primary text-white" dark>
          <q-card-section>
            <div class="text-h6 text-center text-white">
              Reset de senha
            </div>
          </q-card-section>
          <q-separator dark/>
          <q-card-section>
            <q-btn-toggle v-model="usernametipo" toggle-color="accent"
              color="blue-grey-9" spread
              class="full-width" unelevated
              :options="[
                {label: 'E-mail', value: 'email'},
                {label: 'WhatsApp', value: 'whatsapp'},
                {label: 'SMS', value: 'sms'}
              ]"
            />
          </q-card-section>
          <q-card-section>
            <q-input filled color="white" input-class="text-h6 text-lowercase" dark ref="txtuser" @keyup.enter="actRequest"
              v-model="username" :loading="sending" :disable="sending || (internet ? !internet.online : true)"
              :label="usernametipo === 'email' ? 'E-mail' : (usernametipo === 'whatsapp' ? 'Whatsapp' : 'SMS')"
              >
               <template v-slot:prepend>
                  <q-icon name="mail" v-if="usernametipo === 'email'" />
                  <q-icon name="fab fa-whatsapp" v-else-if="usernametipo === 'whatsapp'" />
                  <q-icon name="sms" v-else />
                </template>
            </q-input>
          </q-card-section>
          <q-card-section class="">
            <q-checkbox dark v-model="eunaosourobo" label="Eu não sou robô"  />
          </q-card-section>
          <q-separator dark/>
          <q-card-actions align="center" class="q-pa-md" v-if="internet ? internet.online : false">
            <q-btn class="full-width" color="white" text-color="primary" unelevated size="lg"
              :loading="sending" @click="actEnviar" label="Enviar" />
          </q-card-actions>
          <q-card-actions align="center" class="q-pa-md">
            <q-btn class="full-width" color="primary" text-color="grey"
              unelevated no-caps :to="{ name: 'login.usuario' }"
              label="Voltar para o login"
              />
          </q-card-actions>
        </q-card>
      </div>
      <div class="full-width text-center text-grey-3 q-mt-md">
        <div class="text-caption text-weight-bold">{{appPackage.description}}</div>
        <div class="text-caption">Versão :: {{appPackage.version}}</div>
      </div>
    </q-page>

  </div>
</template>

<script>
import datapackage from '../../../package.json'
import axios from 'axios'
export default {
  data () {
    return {
      showpassword: false,
      appPackage: datapackage,
      username: '',
      password: '',
      eunaosourobo: false,
      sending: false,
      usernametipo: 'email'
    }
  },
  async mounted () {
    var app = this
    await app.refreshDadosSessao()
    // await this.$store.dispatch('app/refreshConnection')
    // console.log(app.$store.state.app)
    app.$refs.txtuser.focus()
  },
  computed: {
    internet: function () {
      // console.log(this.$store.state.app.conexaointernet)
      // return this.$store.state.app.conexaointernet
      return { online: true, type: 'WIFI' }
    }
  },
  methods: {
    async closeApp () {
      // var app = this
      navigator.app.exitApp()
    },
    async refreshDadosSessao () {
      var app = this
      app.sending = false
      await app.$store.dispatch('authusuario/checklogon')
      try {
        // se logado
        var authusuario = app.$store.state.authusuario
        if (authusuario.logado) {
          app.$router.push({ name: 'home.index' })
        }
      } catch (error) {
        console.error(error)
      }
    },
    async actEnviar () {
      var app = this
      app.sending = true
      try {
        if (app.username ? app.username === '' : true) {
          app.$refs.txtuser.focus()
          throw new Error('')
        }
      } catch (error) {
        app.sending = false
        return false
      }
      // aqui estaria o consumo da api
      var ret = { ok: true, msg: 'Erro de api!' }
      if (ret.ok) {
        app.$q.notify({
          message: 'Código enviado ' + (app.usernametipo === 'email' ? 'por e-mail' : (app.usernametipo === 'whatsapp' ? 'por Whatsapp' : 'por SMS')),
          color: 'positive'
        })
        app.$router.push({ name: 'login.esqueciminhasenha.checkcode' })
        app.sending = false
      } else {
        app.sending = false
        app.actShowError('Processo não foi concluido', ret.msg, 4000)
      }
    },
    async actRequest () {
      var app = this
      app.sending = true
      try {
        if (app.username ? app.username === '' : true) {
          app.$refs.txtuser.focus()
          throw new Error('')
        }
        if (app.password ? app.password === '' : true) {
          app.$refs.txtpwd.focus()
          throw new Error('')
        }
      } catch (error) {
        app.sending = false
        return false
      }
      let ret = await app.sendRequest()
      if (ret.ok) {
        app.$router.push({ name: 'home.index' })
        app.sending = false
      } else {
        app.sending = false
        app.actShowError('Acesso restrito', ret.msg, 4000)
      }
    },
    actShowError (title, msg, timeoutclose) {
      var app = this
      const dialog = app.$q.dialog({
        title: title,
        message: msg
      }).onDismiss(() => {
        clearTimeout(timer)
        app.username = ''
        app.password = ''
        app.$refs.txtuser.select()
      })

      const timer = setTimeout(() => {
        dialog.hide()
      }, timeoutclose)
    },
    async sendRequest () {
      var app = this
      try {
        if (app.username === '') {
          throw new Error('Usuário inválido')
        }
        if (app.password === '') {
          throw new Error('Senha inválida')
        }
      } catch (error) {
        return { ok: false, msg: error.message }
      }
      var credentials = 'Basic ' + btoa(app.username + ':' + app.password)
      var req = axios.create({
        baseURL: app.$configini.API_URL,
        withCredentials: false,
        headers: {
          'uuid': app.$store.state.authdevice.uuid,
          'token': app.$store.state.authdevice.token,
          'accesscode': app.$store.state.authdevice.accesscode,
          'Authorization': credentials,
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Headers': 'Authorization',
          'Access-Control-Allow-Methods': 'GET, POST, OPTIONS, PUT, PATCH, DELETE',
          'Content-Type': 'application/json;charset=UTF-8'
        }
      })
      var ret = await req.post('v1/mobile/useradmin/auth').then(response => {
        let data = response.data
        return data
      }).catch(error => {
        let msg = error
        if (error.response) {
          msg = 'Code: ' + error.response.status + ' - ' + error.response.data.message
        } else {
          msg = error.message
        }
        return { ok: false, msg: 'Falha ao consultar servidor online: ' + msg }
      })
      if (ret.ok) {
        await app.$store.dispatch('authusuario/setlocalstorage', ret.data)
        // await app.$store.dispatch('authusuario/checklogon')
      }
      // if (ret.ok) {
      //   app.$servicos.clearAll()
      //   localStorage.setItem('user_token', ret.data.usertoken)
      //   localStorage.setItem('user_tokenexpire_at', ret.data.usertokenexpire_at)
      //   localStorage.setItem('user_username', ret.data.user.username)
      //   localStorage.setItem('user', JSON.stringify(ret.data.user))
      //   app.$store.dispatch('authusuario/checklogon')
      // }
      return ret
    }
  }
}
</script>
