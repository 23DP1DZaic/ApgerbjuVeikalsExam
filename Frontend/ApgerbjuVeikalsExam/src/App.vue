<template>
  <div id="app">

    <!-- Main header -->
    <header class="main-header">
      <div class="container">
        <div class="logo">
          <router-link to="/">Name</router-link>
        </div>
        <nav class="main-nav">
          <router-link v-if="user" to="/create-listing">Add listing</router-link>
          <router-link to="/">Home</router-link>
          <router-link to="/shop">Shop</router-link>
          <div v-if="user" class="user-menu">
            <span>Hello, {{ user.name }}</span>
            <button @click="logout">Logout</button>
          </div>
          <div v-else class="auth-links">
            <router-link to="/login">Login</router-link>
            <router-link to="/register">Register</router-link>
          </div>
        </nav>
      </div>
    </header>

    <!-- Content -->
    <main class="main-content">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="main-footer">
      <div class="container">
        <div class="footer-section">
          <h4>Name</h4>
          <p> description </p>
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



// User
type User = {
  id: number
  name: string
  email: string
}

// User status
const user = ref<User | null>(null)

const router = useRouter()

// Load user from localStorage
onMounted(() => {
  const savedUser = localStorage.getItem('user')

  if (savedUser) {
    user.value = JSON.parse(savedUser)
  }
})

// Logout
const logout = () => {
  localStorage.removeItem('user')
  user.value = null
  router.push('/login')
}
</script>