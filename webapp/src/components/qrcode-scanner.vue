<template>
  <div id="qr-code-full-region"></div>
</template>
<script>
export default {
  name: 'qrcode-scanner',
  props: {
    qrbox: {
      type: Number,
      default: 250
    },
    fps: {
      type: Number,
      default: 10
    }
  },
  mounted () {
    const config = {
      fps: this.fps,
      qrbox: this.qrbox
    }

    // eslint-disable-next-line no-undef
    const html5QrcodeScanner = new Html5QrcodeScanner('qr-code-full-region', config)
    html5QrcodeScanner.render(this.onScanSuccess)
  },
  methods: {
    onScanSuccess (qrCodeMessage) {
      this.$emit('result', qrCodeMessage)
    }
  }
}
</script>
