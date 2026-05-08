<template>
  <div id="app">
    <header class="main-header">
      <div class="header-top container">
        <div class="logo">
          <router-link to="/">Name</router-link>
        </div>

        <form class="header-search" @submit.prevent="submitHeaderSearch">
          <input
            v-model="headerSearch"
            placeholder="Search for anything"
          >
          <button type="submit">Search</button>
        </form>

        <nav class="header-actions">
          <router-link v-if="user" to="/create-listing">Add Listing</router-link>
          <router-link to="/shop">My Feed</router-link>

          <div v-if="user" class="user-menu">
            <span>{{ user.name }}</span>
            <button @click="logout">Logout</button>
          </div>

          <div v-else class="auth-links">
            <router-link to="/login">Login</router-link>
            <router-link to="/register">Register</router-link>
          </div>
        </nav>
      </div>

      <nav class="category-nav">
        <router-link to="/shop">Shop</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'men' } }">Menswear</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'women' } }">Womenswear</router-link>
        <router-link :to="{ path: '/shop', query: { category: 'Sneakers' } }">Sneakers</router-link>
        <router-link :to="{ path: '/shop', query: { section: 'trending' } }">Trending</router-link>
        <router-link to="/about">About us</router-link>
      </nav>
    </header>

    <main class="main-content">
      <router-view />
    </main>

    <footer class="main-footer">
      <div class="container">
        <div class="footer-section">
          <h4>Name</h4>
          <p>description</p>
        </div>

        <div class="footer-section">
          <h4>Information</h4>
          <a href="#">Delivery</a>
          <a href="#">Refunds</a>
          <router-link to="/about">About us</router-link>
        </div>

        <div class="footer-section">
          <h4>Contact Us</h4>
          <p>email.com<br>+371 123</p>
        </div>

        <div class="footer-section">
          <h4>Subscribe to our newsletter</h4>
          <div class="newsletter">
            <input type="email" placeholder="Your email">
            <button>→</button>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2026 Name</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

type User = {
  id: number
  name: string
  email: string
  role?: string
}

const router = useRouter()
const user = ref<User | null>(null)
const headerSearch = ref('')

const loadUser = () => {
  const savedUser = localStorage.getItem('user')
  user.value = savedUser ? JSON.parse(savedUser) : null
}

onMounted(() => {
  loadUser()
})

const logout = () => {
  localStorage.removeItem('user')
  localStorage.removeItem('token')
  user.value = null
  router.push('/login')
}

const submitHeaderSearch = () => {
  router.push({
    path: '/shop',
    query: {
      search: headerSearch.value || undefined,
    },
  })
}
</script>