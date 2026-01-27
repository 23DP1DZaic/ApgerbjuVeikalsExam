import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ShopView from '../views/ShopView.vue'

const routes: RouteRecordRaw[] = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/shop', name: 'shop', component: ShopView }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
