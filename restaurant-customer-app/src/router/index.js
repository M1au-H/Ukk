import { createRouter, createWebHistory } from 'vue-router'
import { useSessionStore } from '../stores/session'
import LandingPage from '../views/LandingPage.vue'
import MenuPage from '../views/MenuPage.vue'
import CartPage from '../views/CartPage.vue'
import StatusPage from '../views/StatusPage.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'landing', component: LandingPage },
    { path: '/menu/:tableToken', name: 'menu', component: MenuPage },
    { path: '/cart', name: 'cart', component: CartPage },
    { path: '/status', name: 'status', component: StatusPage },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
  scrollBehavior: () => ({ top: 0 }),
})

// Keranjang & status hanya masuk akal kalau customer sudah scan QR (punya sesi meja).
router.beforeEach((to) => {
  if (to.name === 'cart' || to.name === 'status') {
    const session = useSessionStore()
    session.restore()
    if (!session.sessionToken) return { name: 'landing' }
  }
})

export default router
