import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ShopView from '../views/ShopView.vue'
import RegisterView from '../views/RegisterView.vue'
import LoginView from '../views/LoginView.vue'
import CreateListingView from '../views/CreateListingView.vue'
import ListingDetailsView from '../views/ListingDetailsView.vue'
import AboutView from '../views/AboutView.vue'
import AccountView from '../views/AccountView.vue'
import ProfileView from '../views/ProfileView.vue'
import { getUser, isLoggedIn } from '../services/auth'
import SettingsAccountView from '../views/SettingsAccountView.vue'
import AdminCategoriesView from '../views/AdminCategoriesView.vue'
import MessagesView from '../views/MessagesView.vue'
import ConversationView from '../views/ConversationView.vue'


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
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: '/account',
      name: 'account',
      component: AccountView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfileView,
      meta: {
        requiresAuth: true,
      },
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
    {
      path: '/account',
      name: 'account',
      component: AccountView,
      meta: { requiresAuth: true },
    },
    {
      path: '/settings/profile',
      name: 'settings-profile',
      component: ProfileView,
      meta: { requiresAuth: true },
    },
    {
      path: '/settings/account',
      name: 'settings-account',
      component: AccountView, 
      meta: { requiresAuth: true },
    },
    {
      path: '/account',
      name: 'account',
      component: AccountView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: '/settings/profile',
      name: 'settings-profile',
      component: ProfileView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: '/settings/account',
      name: 'settings-account',
      component: SettingsAccountView,
      meta: {
        requiresAuth: true,
      },
    },
    {
  path: '/admin/categories',
  name: 'admin-categories',
  component: AdminCategoriesView,
  meta: {
    requiresAuth: true,
    requiresAdmin: true,
    },
    },
    {
    path: '/messages',
    name: 'messages',
    component: MessagesView,
    },
    {
    path: '/messages/:id',
    name: 'conversation',
    component: ConversationView,
    },
  ],
  
})

router.beforeEach((to, _from, next) => {
  const user = getUser()

  if (to.meta.requiresAuth && !isLoggedIn()) {
    next('/login')
    return
  }

  if (to.meta.requiresAdmin && user?.role !== 'admin') {
    next('/')
    return
  }

  next()
})

export default router