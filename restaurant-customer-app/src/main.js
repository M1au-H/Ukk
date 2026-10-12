import { createApp } from 'vue'
import { createPinia } from 'pinia'
import '@fontsource/bitter/600.css'
import '@fontsource/bitter/700.css'
import '@fontsource/atkinson-hyperlegible/400.css'
import '@fontsource/atkinson-hyperlegible/700.css'
import './styles.css'
import App from './App.vue'
import router from './router'

// Pinia harus dipasang sebelum router, supaya store bisa dipakai di route guard.
createApp(App).use(createPinia()).use(router).mount('#app')
