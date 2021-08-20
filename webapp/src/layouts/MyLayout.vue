<template>
  <q-layout view="hHh lpR fFf">

    <q-header reveal class="bg-primary text-white" height-hint="98">
      <q-toolbar class="bg-primary text-white">
        <q-btn dense flat round icon="menu" @click="left = !left" />

        <q-toolbar-title>
            <q-img :src="require('assets/logoconectafundocor.png')" style="height: 30px;" contain ></q-img>
        </q-toolbar-title>
        <q-toolbar-title class="text-right">
          <span class="text-bold text-body2">{{  usuario ? usuario.login : '?' }}</span>
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="left" side="left"  elevated bordered content-class="bg-primary text-grey-1">
      <q-scroll-area style="width: 100%; height: 100%;">
      <div class="row flex-center q-pa-sm" ></div>
      <div class="row flex-center q-pa-sm" >
        <q-img
          :src="require('assets/logoconectafundocor.png')"
          style="height: 40px;" class="q-ma-xs" contain
        >
        </q-img>
      </div>
      <q-card v-if="usuario" class="full-width bg-primary text-grey-1" flat square>
        <q-card-section>
          <div class="text-body1 text-bold">
            <div>{{ usuario ? usuario.nome : 'Motorista logado' }}</div>
            <div class="text-caption" v-if="usuario">
              <div><span class="text-bold">{{  usuario.nome }}</span></div>
              <div>Usuário: <span class="text-bold">{{  usuario.username }}</span> - ID: <span class="text-bold">{{  usuario.id }}</span></div>
            </div>
            <div class="text-caption q-my-sm" v-if="usuario">
              <q-btn color="white" icon="logout" label="Trocar de usuário" class="full-width" outline @click="actLogoff"/>
            </div>
          </div>
        </q-card-section>
      </q-card>
        <div v-if="menuprincipal">
          <div v-for="(item, key) in menuprincipal.menu" :key="key" class="bg-primary text-white">
            <q-expansion-item v-if="item.itens ? item.itens.length > 0 : false" expand-separator :icon="item.icon" :label="item.categoria" >
              <div v-for="(subitem, key2) in item.itens" :key="'sub' + key2" >
                <q-item clickable tag="a" :to="subitem.to" v-if="!subitem.separator" dark class="bg-grey-3 text-primary" >
                  <q-item-section avatar v-if="subitem.icon ? subitem.icon!='' : false">
                    <q-icon :name="subitem.icon" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-body1">{{subitem.text}}</q-item-label>
                    <q-item-label class="text-caption" v-if="subitem.caption ? subitem.caption!='': false">{{subitem.caption}}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-separator space />
              </div>
            </q-expansion-item>
            <q-separator />
          </div>
        </div>
        <q-item clickable tag="a" @click="closeApp" class="bg-primary text-white q-py-md">
          <q-item-section avatar>
            <q-icon name="power_settings_new" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-body1">Sair</q-item-label>
            <q-item-label class="text-caption">Encerra o App</q-item-label>
          </q-item-section>
        </q-item>
      </q-scroll-area>
    </q-drawer>

    <q-page-container class="bg-blue-grey-2">
      <!-- <keep-alive> -->
      <transition name="fade">
        <router-view :key="$route.fullPath" />
      </transition>
      <!-- </keep-alive> -->
    </q-page-container>
  </q-layout>
</template>

<script>
import { openURL, QScrollArea, QExpansionItem } from 'quasar'
import MenuLateral from 'src/assets/menulateralprincipal.js'
export default {
  name: 'MyLayout',
  components: {
    QScrollArea,
    QExpansionItem
  },
  data () {
    let menuprincipal = new MenuLateral()
    return {
      leftDrawerOpen: this.$q.platform.is.desktop,
      leftDrawerOpenUser: this.$q.platform.is.desktop,
      miniState: false,
      miniStateUser: false,
      menuprincipal,
      left: false,
      right: false,
      usuario: null,
      msgnaolida: 0
    }
  },
  mounted () {
    var app = this
    app.usuario = app.$store.state.authusuario ? app.$store.state.authusuario.user : null
    app.$root.$on('msgmotoristanaolida', (numero) => {
      app.msgnaolida = numero
    })
  },
  computed: {
    title () {
      return this.$store.state.appicom.title
    },
    icon () {
      return this.$store.state.appicom.icon
    },
    versaoapp: function () {
      return this.$q.cordova.version
    }
  },
  methods: {
    openURL,
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
    actForceSync () {
      // var app = this
      // app.$servicos.forceSyncNow()
    }
  }
}
</script>

<style>
.border-white{
  border: 2px solid rgba(255,255,255,0.7);
}
</style>
