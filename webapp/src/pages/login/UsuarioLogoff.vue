<template>
<div></div>
</template>

<script>
export default {
  async mounted () {
    var app = this
    // app.$servicos.stopLoop()
    // app.$servicos.clearAll()
    app.$store.dispatch('authusuario/logout')
  }
}
</script>
