<template>
  <q-layout view="hHh lpR fFf">
    <transition name="fade">
      <router-view :key="$route.fullPath"  />
    </transition>
  </q-layout>
</template>

<script>
export default {
  name: 'MyLayoutPages',
  components: {},
  props: ['label'],
  data () {
    return {
    }
  },
  mounted () {
  },
  methods: {
  }
}
</script>
