<template>
  <div>
    <q-page class="flex flex-center">
      <div>IMEI {{IMEI}}</div>
    </q-page>
  </div>
</template>

<script>
export default {
  data () {
    return {
      IMEI: window.device === void 0 ? null : window.device
    }
  },
  mounted () {
    var app = this
    if (!app.IMEI) {
      app.IMEI = { 'available': true, 'platform': 'Windows10', 'version': 'Windows 10', 'uuid': 'f01f14a0e6f547c393083bf0cd6590d6', 'cordova': '6.0.0', 'model': 'Windows', 'manufacturer': 'weber-pc', 'isVirtual': false, 'serial': 'unknown' }
    } else {
      app.IMEI = window.device
    }
  }
}
</script>
