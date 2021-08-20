<template>
  <router-view :key="$route.fullPath"  />

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
