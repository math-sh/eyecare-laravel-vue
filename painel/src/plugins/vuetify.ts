import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/dist/vuetify.min.css'

import Vue from 'vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify)

// Cores lidas direto dos prototipos em /images do repositorio do desafio.
export default new Vuetify({
  icons: { iconfont: 'mdi' },
  theme: {
    options: { customProperties: true },
    themes: {
      light: {
        primary: '#2F54EB',
        navy: '#0F1B4C',
        error: '#F5222D',
        success: '#22C55E',
        anchor: '#2F54EB',
      },
    },
  },
})
