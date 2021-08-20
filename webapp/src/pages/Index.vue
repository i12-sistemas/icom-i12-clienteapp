<template>
  <q-page class="center tex-primary">
    <q-toolbar class="bg-primary text-white">
      <q-toolbar-title class="text-body2" >
        <div class="text-weight-bold" v-if="unidadelogada">{{ unidadelogada.fantasia }}</div>
        <div class="text-caption" v-if="unidadelogada ? unidadelogada.endereco : false">{{ unidadelogada.endereco.cidade.cidade + ' / ' + unidadelogada.endereco.cidade.uf  }}</div>
      </q-toolbar-title>
      <q-btn flat round dense icon="published_with_changes" @click="actUnidadeChange" />
    </q-toolbar>
    <q-toolbar class="bg-primary text-white">
      <q-toolbar-title  class="text-body2">
        <div class="" v-if="$store.state.authusuario.user ? ($store.state.authusuario.user.nome !== '') : false">
          <q-icon name="person" size="20px" />
          {{ $store.state.authusuario.user.nome }}
        </div>
      </q-toolbar-title>
      <q-icon name="signal_wifi_off" v-if="internet ? !internet.online : true" size="24px" >
        <q-menu>
          <q-list  >
            <q-item v-close-popup>
              <q-item-section>Sem conexão de rede ou internet</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-icon>
      <q-icon name="signal_wifi_4_bar" v-if="internet ? internet.online : false" size="24px" >
        <q-menu>
          <q-list >
            <q-item v-close-popup>
              <q-item-section>Conexão: {{ internet.type ? internet.type : ''}}</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-icon>
    </q-toolbar>
    <div class="q-pa-md">
      <q-card class="my-card" flat bordered>
        <q-card-section class="q-pa-sm">
          <q-list>
            <div v-for="(categoria, key) in menuprincipal.menu" :key="'categoria' + key" dense>
              <div  v-for="(item, mkey) in categoria.itens" :key="'linha' + mkey" >
                <q-item clickable v-ripple :to="item.to" v-if="!item.separator">
                  <q-item-section avatar v-if="item.icon">
                    <q-avatar :icon="item.icon" color="primary" text-color="white" />
                  </q-item-section>

                  <q-item-section>
                    <q-item-label lines="1" class="text-body1">{{item.text}}</q-item-label>
                    <q-item-label v-if="item.caption"  class="text-caption">{{item.caption}}</q-item-label>
                  </q-item-section>

                  <q-item-section side v-if="item.infocounter">
                    <q-avatar color="green" text-color="white" size="sm" >{{item.infocounter}}</q-avatar>
                  </q-item-section>
                </q-item>
                <q-separator spaced v-if="item.separator"/>
              </div>
            </div>
            <q-separator spaced />
            <q-item clickable v-ripple @click="actLogoff">
              <q-item-section avatar top>
                <q-avatar icon="logout" color="primary" text-color="white" />
              </q-item-section>
              <q-item-section>
                <q-item-label lines="1" class="text-body1">Trocar de usuário</q-item-label>
                <q-item-label class="text-caption">Encerrar acesso do usuário atual</q-item-label>
              </q-item-section>
            </q-item>
            <q-separator spaced />
            <q-item clickable v-ripple @click="closeApp">
              <q-item-section avatar top>
                <q-avatar icon="power_settings_new" color="grey" text-color="white" />
              </q-item-section>
              <q-item-section>
                <q-item-label lines="1" class="text-body1">Sair</q-item-label>
                <q-item-label class="text-caption">Encerrar sistema</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
  </div>
  <div class="full-width text-center text-grey-9 q-mt-md">
    <div class="text-caption text-weight-bold">{{appPackage.description}}</div>
    <div class="text-caption">Versão :: {{appPackage.version}}</div>
  </div>
  </q-page>
</template>

<style>
</style>

<script>
import MenuLateral from 'src/assets/menulateralprincipal.js'
import datapackage from '../../package.json'
export default {
  name: 'PageIndex',
  data: function () {
    let menuprincipal = new MenuLateral()
    return {
      unidadelogada: null,
      appPackage: datapackage,
      internetinfo: null,
      menuprincipal
    }
  },
  async mounted () {
    var app = this
    // app.$servicos.setApp(app)
    await app.refreshUnidade()
    app.actInit()
    document.addEventListener('online', app.checkConnection, false) // destroy too
    document.addEventListener('offline', app.checkConnection, false) // destroy too
    // app.$servicos.startLoop()
    app.$root.$on('coletaencerradasynced', (id) => { // destroy too
      app.actInit()
    })
    app.$root.$on('coletasdownloadsynced', (updates) => { // destroy too
      if (updates) {
        if (updates.delete > 0) {
          this.$q.notify({
            message: updates.delete + ' coleta(s) removidas(s)',
            color: 'red'
          })
        }
        if (updates.insert > 0) {
          this.$q.notify({
            message: updates.insert + ' coleta(s) nova(s)',
            color: 'green'
          })
        }
      }
      app.actInit()
    })
    // var OneSignal = window.plugins.OneSignal
    // var motorista = this.$store.state.authusuario.motorista
    // var device = this.$store.state.authdevice
    // OneSignal.sendTags({
    //   motoristaid: motorista.id,
    //   motoristanome: motorista.nome,
    //   motoristaapelido: motorista.apelido,
    //   motoristausername: motorista.username,
    //   dispositivonome: device.devicename,
    //   dispositivouuid: device.uuid,
    //   userId: device.uuid
    // })
    // OneSignal.getPermissionSubscriptionState(function (state) {
    // })

    app.channelDevice = app.$pusher.subscribe('device.logged')
    app.channelDevice.bind('checkmsg', function (params) {
      // var motorista = app.$store.state.authusuario.motorista
      // if (!motorista) return
      // if (!params) return
      // if (!params.msg) return
      // var msg = params.msg
      // var allow = (msg.idmotoristaresp === motorista.id) || (msg.paraidmotorista === motorista.id) || (msg.todos === 1)
      // if (allow) app.$servicos.forceSyncNow()
    })
    app.channelDevice.bind('checkcoleta', function (params) {
      // var motorista = app.$store.state.authusuario.motorista
      // if (!motorista) return
      // if (!params) return
      // if (!params.motoristas) return
      // var listamotoristas = params.motoristas
      // var i = listamotoristas.indexOf(motorista.id)
      // if (i >= 0) app.$servicos.forceSyncNow()
    })
  },
  beforeDestroy () {
    var app = this
    document.removeEventListener('online', app.checkConnection, false)
    document.removeEventListener('offline', app.checkConnection, false)
    app.$root.$off('coletaencerradasynced')
    app.$root.$off('coletasdownloadsynced')
    app.channelDevice.unbind('checkmsg')
    app.channelDevice.unbind('checkcoleta')
    app.$pusher.unsubscribe('device.logged')
  },
  computed: {
    internet: function () {
      return this.$store.state.app.conexaointernet
    }
  },
  methods: {
    async refreshUnidade () {
      var app = this
      app.unidadelogada = await app.$helpers.getUnidadeLogada(app)
    },
    async actUnidadeChange () {
      var app = this
      var ret = await app.$helpers.ShowSelecaoUnidadePadrao(app)
      if (ret.ok) {
        app.refreshUnidade()
      }
    },
    async closeApp () {
      var app = this
      app.$q.dialog({
        title: 'Encerrar o app?',
        color: 'white',
        class: 'bg-primary text-white',
        persistent: true,
        ok: {
          label: 'Encerrar',
          color: 'white',
          flat: true
        },
        cancel: {
          label: 'Não',
          color: 'white',
          flat: true
        }
      }).onOk(() => {
        navigator.app.exitApp()
      })
    },
    actTeste () {
      var app = this
      app.$root.$emit('coletaencerradasynced', 'TESTE')
    },
    checkConnection () {
      this.$store.dispatch('app/refreshConnection')
      let on = this.$store.state.app.conexaointernet.online
      this.internetinfo = (on ? 'Online' : 'OFF') + ' - ' + this.$store.state.app.conexaointernet.type
    },
    async actLogoff () {
      var app = this
      app.$q.dialog({
        title: 'Trocar de usuário?',
        message: 'Encerra o acesso do usuário atual!',
        color: 'white',
        class: 'bg-primary text-white',
        persistent: true,
        ok: {
          label: 'Sair',
          color: 'white',
          flat: true
        },
        cancel: {
          label: 'Cancelar',
          color: 'white',
          flat: true
        }
      }).onOk(() => {
        app.$router.push({ name: 'usuario.logoff' })
      })
    },
    async actInit () {
      var app = this
      app.checkConnection()
      // let A = await app.coletas.count('A')
      // let H = await app.coletas.getColetasSemSync()
      // let CA = await app.baixas.count()
      // app.$root.$emit('msgmotoristanaolida', NL)
      // app.menuprincipal.forEach(element => {
      //   if (element.to) {
      //     if (element.to.name) {
      //       if (element.to.name === 'coletas') {
      //         app.$nextTick(function () {
      //           element['infocounter'] = A
      //         })
      //       }
      //       if (element.to.name === 'coletas.encerradas') {
      //         app.$nextTick(function () {
      //           element['infocounter'] = H
      //         })
      //       }
      //       if (element.to.name === 'coleta.avulsa') {
      //         app.$nextTick(function () {
      //           element['infocounter'] = CA
      //         })
      //       }
      //     }
      //   }
      // })
    }
  }
}
</script>
