import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ShopView from '../views/ShopView.vue'
import RegisterView from '../views/RegisterView.vue'
import LoginView from '../views/LoginView.vue'
import CreateListingView from '../views/CreateListingView.vue'
import ListingDetailsView from '../views/ListingDetailsView.vue'
import AboutView from '../views/AboutView.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/shop',
      name: 'shop',
      component: ShopView,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/create-listing',
      name: 'create-listing',
      component: CreateListingView,
    },
    {
      path: '/listing/:id',
      name: 'listing-details',
      component: ListingDetailsView,
    },
    {
    path: '/about',
    name: 'about',
    component: AboutView,
    },
    ],
})
export default router
