<template>
  <div class="fixed-center text-center">
    <p>
      <img
        src="~assets/sad.svg"
        style="width:30vw;max-width:150px;"
      >
    </p>
    <p class="text-faded">Desculpe-nos mas não tem nada aqui...<strong>(404)</strong></p>
    <q-btn
      color="primary"
      style="width:200px;"
      @click="$router.push('/')"
    >Voltar</q-btn>
  </div>
</template>

<script>
export default {
  name: 'Error404'
}
</script>
