export default async ({ app, router, store, Vue }) => {
  document.addEventListener('deviceready', function () {
    // Enable to debug issues.
    // window.plugins.OneSignal.setLogLevel({logLevel: 4, visualLevel: 4});
    var notificationOpenedCallback = function (data) {
      if (!data) return
      if (!data.notification) return
      data.notification.shown = false
      // var isAppInFocus = data.notification.isAppInFocus
      var payload = data.notification.payload
      var additionalData = payload.additionalData
      if ((additionalData.newmsg)) {
        router.push({ name: 'mensagens' })
      }
    }
    window.plugins.OneSignal
      .startInit(Vue.prototype.$configini.ONESIGNAL_APPID)
      .handleNotificationOpened(notificationOpenedCallback)
      .inFocusDisplaying(window.plugins.OneSignal.OSInFocusDisplayOption.None)
      .endInit()
  }, false)
}
