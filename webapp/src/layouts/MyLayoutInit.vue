<template>
  <q-layout view="hHh LpR fFf">
    <q-header reveal class="bg-primary text-white" height-hint="98">
      <q-toolbar class="bg-primary text-white" style="height: 100px">
        <q-toolbar-title>
          <q-img :src="require('assets/logoi12.png')" style="height: 60px;" contain ></q-img>
        </q-toolbar-title>
        <q-btn flat round icon="power_settings_new" @click="closeApp" >
          <q-tooltip :delay="700">Fechar aplicativo</q-tooltip>
        </q-btn>
      </q-toolbar>
    </q-header>
    <q-page-container class="bg-primary">
      <transition name="slide">
        <router-view :key="$route.fullPath"  />
      </transition>
    </q-page-container>
  </q-layout>
</template>

<script>
import { openURL } from 'quasar'
export default {
  name: 'LoginDispositivo',
  data () {
    return {
    }
  },
  mounted () {
  },
  computed: {
    title () {
      return this.$store.state.appicom.title
    },
    icon () {
      return this.$store.state.appicom.icon
    }
  },
  methods: {
    openURL,
    async closeApp () {
      // var app = this
      navigator.app.exitApp()
    }
  }
}
</script>
