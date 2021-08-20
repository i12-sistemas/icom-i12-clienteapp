<template>
<q-layout view="hHh lpR fFf">
  <q-header reveal class="bg-primary text-white shadow-2">
    <q-toolbar>
      <q-btn dense round flat icon="arrow_back_ios" @click="$router.back()" />
      <q-toolbar-title>
        {{ $store.state.app.title }}
      </q-toolbar-title>
    </q-toolbar>
  </q-header>

  <q-page-container class="bg-grey-3" >
    <q-page>
      <div class="bg-white text-body1">
      <q-list class="full-width" separator>
        <q-item>
          <q-item-section avatar>
            <q-avatar icon="fas fa-id-card"></q-avatar>
          </q-item-section>
          <q-item-section class="text-body1 text-bold">
            <q-item-label >{{ usuario ? usuario.username : 'Usuário logado' }}</q-item-label>
            <q-item-label class="text-caption" v-if="usuario">
              <div>Nome: <span class="text-bold">{{  usuario.nome }}</span></div>
              <div>Usuário: <span class="text-bold">{{  usuario.username }}</span></div>
              <div>ID interno: <span class="text-bold">{{  usuario.id }}</span></div>
            </q-item-label>
          </q-item-section>
        </q-item>
        <q-space class="q-mt-md"/>
        <q-separator inset />
        <q-space class="q-mt-md"/>
        <q-item  clickable v-ripple @click="actGetDispositivo">
          <q-item-section avatar>
            <q-avatar icon="fas fa-mobile"></q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label >Cadastro dispositivo</q-item-label>
            <q-item-label caption v-if="!dispositivoinfo">Verificar informações de dispositivo</q-item-label>
            <q-item-label caption v-if="dispositivoinfo">
                <q-card class="bg-white text-caption bg-grey-2" flat >
                  <q-card-section>
                    <div class="q-pt-xs">
                      <div class="text-bold">uuid</div>
                      <div>{{ dispositivoinfo.uuid }}</div>
                    </div>
                    <div class="q-pt-xs">
                      <div class="text-bold">Descrição</div>
                      <div class="text-wrap" style="word-break: break-word;">{{  dispositivoinfo.devicename }}</div>
                    </div>
                  </q-card-section>
                  <q-separator />
                  <q-card-actions align="right">
                    <q-btn icon="mobile_off" unelevated color="black" dark @click="actDeviceLogoff" label="Logoff dispositivo" />
                  </q-card-actions>
                </q-card>
            </q-item-label>
          </q-item-section>
        </q-item>
        <q-space class="q-mt-md"/>
        <q-separator inset />
        <q-space class="q-mt-md"/>
              <q-item  clickable v-ripple @click="actOneSignal">
          <q-item-section avatar>
            <q-avatar icon="fas fa-comment"></q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label >OneSignal - PushNotification</q-item-label>
            <q-item-label caption v-if="!onesignalsubscription">Verificar informações de ID</q-item-label>
            <q-item-label caption v-if="onesignalsubscription">
                <q-card class="bg-white text-caption bg-grey-2" flat >
                  <q-card-section>
                    <div v-if="onesignalsubscription">
                      <div v-if="onesignalsubscription.permissionStatus" class="q-pt-md">
                        <div class="text-bold">permissionStatus</div>
                        <div>hasPrompted: {{  onesignalsubscription.permissionStatus.hasPrompted }}</div>
                        <div v-if="onesignalsubscription.permissionStatus.state == 1">
                          state: 1 = Authorized
                        </div>
                        <div v-if="onesignalsubscription.permissionStatus.state == 2">
                          state: 2 = Denied
                        </div>
                      </div>
                      <div v-if="onesignalsubscription.subscriptionStatus" class="q-pt-md">
                        <div class="text-bold">subscriptionStatus</div>
                        <div class="text-wrap" style="word-break: break-word;">pushToken: {{  onesignalsubscription.subscriptionStatus.pushToken }}</div>
                        <div>subscribed: {{  onesignalsubscription.subscriptionStatus.subscribed }}</div>
                        <div>userId: {{  onesignalsubscription.subscriptionStatus.userId }}</div>
                        <div>userSubscriptionSetting: {{  onesignalsubscription.subscriptionStatus.userSubscriptionSetting }}</div>
                      </div>
                    </div>
                  </q-card-section>
                </q-card>
            </q-item-label>
          </q-item-section>
        </q-item>
        <q-space class="q-mt-md"/>
        <q-separator inset />
        <q-space class="q-mt-md"/>
      </q-list>
      </div>
    </q-page>
  </q-page-container>
</q-layout>
</template>

<script>
export default {
  props: ['label'],
  data: function () {
    return {
      error: null,
      text: '',
      searching: false,
      expanded: false,
      onesignalsubscription: null,
      dispositivoinfo: null,
      usuario: null
    }
  },
  mounted () {
    var app = this
    this.$store.commit('app/title', this.label)
    app.usuario = app.$store.state.authusuario ? app.$store.state.authusuario.user : null
  },
  methods: {
    actOneSignal () {
      var app = this
      var OneSignal = window.plugins.OneSignal
      try {
        OneSignal.getPermissionSubscriptionState(function (state) {
          app.onesignalsubscription = state
        })
      } catch (error) {
        app.onesignalsubscription = '***************'
      }
    },
    actGetDispositivo () {
      var app = this
      app.dispositivoinfo = this.$store.state.authdevice
    },
    async actDeviceLogoff () {
      var app = this
      app.$store.dispatch('authdevice/logout')
    },
    actInit () {
      // app.fetchData(app.text)
    }
  }
}
</script>
