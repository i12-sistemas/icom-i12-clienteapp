// Configuration for your app

module.exports = function (ctx) {
  return {
    // app boot file (/src/boot)
    // --> boot files are part of "main.js"
    boot: [
      'i18n',
      'config',
      'axios',
      '/helpers',
      // 'database',
      // 'pusher',
      // 'OneSignal',
      // 'servicos',
      // 'maps',
      'prepareapp'
    ],

    css: [
      'app.styl',
      'app.css'
    ],

    extras: [
      'roboto-font',
      'material-icons', // optional, you are not bound to it
      // 'ionicons-v4',
      // 'mdi-v3',
      'fontawesome-v5'
      // 'eva-icons'
    ],

    framework: {
      all: true, // --- includes everything; for dev only!
      components: [
        'QOptionGroup',
        'QField',
        'QImg',
        'QLayout',
        'QHeader',
        'QDrawer',
        'QPageContainer',
        'QPage',
        'QToolbar',
        'QToolbarTitle',
        'QBtn',
        'QIcon',
        'QList',
        'QItem',
        'QItemSection',
        'QItemLabel',
        'QSeparator',
        'QAvatar',
        'QInfiniteScroll',
        'QSpinnerDots',
        'QSpinnerPie',
        'QSpinnerOval',
        'QPageSticky',
        'QInput',
        'QCard',
        'QCardSection',
        'QCardActions',
        'QExpansionItem',
        'QCheckbox',
        'QBadge',
        'QPageScroller',
        'QTabs',
        'QTab',
        'QRouteTab',
        'QTabPanels',
        'QTabPanel',
        'QSelect',
        'QToggle',
        'QSpace',
        'QForm',
        'QSpinnerRings',
        'QLinearProgress',
        'QDialog',
        'QFooter',
        'QSlider',
        'QBreadcrumbs',
        'QBreadcrumbsEl',
        'QBanner'
      ],

      config: {
        cordova: {
          iosStatusBarPadding: true, // add the dynamic top padding on iOS mobile devices
          backButtonExit: true // Quasar handles app exit on mobile phone back button
        }
      },

      directives: [
        'Ripple',
        'GoBack',
        'ClosePopup'
      ],

      // Quasar plugins
      plugins: [
        'Notify',
        'BottomSheet',
        'Dialog',
        'Loading'
      ]
      // iconSet: 'ionicons-v4'
      // lang: 'de' // Quasar language
    },

    supportIE: true,

    build: {
      scopeHoisting: true,
      vueRouterMode: 'history',
      showProgress: true,
      // gzip: true,
      // analyze: true,
      // extractCSS: false,
      extendWebpack (cfg) {
        cfg.module.rules.push({
          enforce: 'pre',
          test: /\.(js|vue)$/,
          loader: 'eslint-loader',
          exclude: /node_modules/
        })
      }
    },

    devServer: {
      https: false,
      port: 8090,
      open: true, // opens browser window automatically
      host: 'cliente.icom.local',
      allowedHosts: [
        'cdvfile://localhost',
        'http://localhost',
        'http://cliente.icom.local:8090',
        'http://clienteapi.icom.local'
      ],
      before (app) {
        const cors = require('cors')
        app.use(cors())
      },
      headers: {
        // 'Access-Control-Allow-Methods': 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
        // 'Access-Control-Allow-Headers': 'Content-Type, Authorization',
        'Access-Control-Allow-Origin': '*'
      }
    },

    // animations: 'all', // --- includes all animations
    animations: 'all',

    ssr: {
      pwa: false
    },

    pwa: {
      // workboxPluginMode: 'InjectManifest',
      // workboxOptions: {}, // only for NON InjectManifest
      manifest: {
        name: 'Painel do Cliente - i12 Sistemas',
        short_name: 'painelcliente',
        description: 'Painel do Cliente - i12 Sistemas',
        display: 'standalone',
        orientation: 'portrait',
        background_color: '#ffffff',
        theme_color: '#263238',
        icons: [
          {
            'src': 'statics/icons/icon-128x128.png',
            'sizes': '128x128',
            'type': 'image/png'
          },
          {
            'src': 'statics/icons/icon-192x192.png',
            'sizes': '192x192',
            'type': 'image/png'
          },
          {
            'src': 'statics/icons/icon-256x256.png',
            'sizes': '256x256',
            'type': 'image/png'
          },
          {
            'src': 'statics/icons/icon-384x384.png',
            'sizes': '384x384',
            'type': 'image/png'
          },
          {
            'src': 'statics/icons/icon-512x512.png',
            'sizes': '512x512',
            'type': 'image/png'
          }
        ]
      }
    },

    cordova: {
      version: '21.06.28',
      // id: 'org.cordova.quasar.app'
      backButtonExit: true // Quasar handles app exit on mobile phone back button
      // noIosLegacyBuildFlag: true // uncomment only if you know what you are doing,
    },

    electron: {
      // bundler: 'builder', // or 'packager'

      extendWebpack (cfg) {
        // do something with Electron main process Webpack cfg
        // chainWebpack also available besides this extendWebpack
      },

      packager: {
        // https://github.com/electron-userland/electron-packager/blob/master/docs/api.md#options

        // OS X / Mac App Store
        // appBundleId: '',
        // appCategoryType: '',
        // osxSign: '',
        // protocol: 'myapp://path',

        // Window only
        // win32metadata: { ... }
      },

      builder: {
        // https://www.electron.build/configuration/configuration
        // appId: 'quasar-app'
      }
    }
  }
}
