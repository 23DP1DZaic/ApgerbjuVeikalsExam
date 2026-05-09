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
          <button type="submit">SEARCH</button>
        </form>

        <nav class="header-actions">
          <router-link v-if="user" to="/create-listing">ADD LISTING</router-link>
          <router-link to="/shop">MY FEED</router-link>

          <div v-if="user" class="user-menu">
            <router-link to="/account" class="user-info user-link">
              <img
                v-if="user.avatar_url"
                :src="user.avatar_url"
                alt="Avatar"
                class="header-avatar-image"
              >
              <div v-else class="avatar-circle">
                {{ userInitial }}
              </div>

              <span class="user-name">{{ user.display_name || user.name }}</span>
            </router-link>

            <button class="logout-btn" @click="logout">LOGOUT</button>
          </div>

          <div v-else class="auth-links">
            <router-link to="/login">LOGIN</router-link>
            <router-link to="/register">REGISTER</router-link>
          </div>
        </nav>
      </div>

      <nav class="category-nav">
        <router-link to="/shop">SHOP</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'men' } }">MENSWEAR</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'women' } }">WOMENSWEAR</router-link>
        <router-link :to="{ path: '/shop', query: { category: 'Sneakers' } }">SNEAKERS</router-link>
        <router-link :to="{ path: '/shop', query: { section: 'trending' } }">TRENDING</router-link>
        <router-link to="/about">ABOUT US</router-link>
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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { getCurrentUser, clearAuth, type AuthUser } from './services/auth'
import { API_URL, fetchWithAuth } from './services/api'

const router = useRouter()

const user = ref<AuthUser | null>(null)
const headerSearch = ref('')

const userInitial = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name.charAt(0).toUpperCase()
})

const loadUser = async () => {
  user.value = await getCurrentUser()
}

onMounted(() => {
  loadUser()
})

const logout = async () => {
  try {
    await fetchWithAuth(`${API_URL}/api/logout`, {
      method: 'POST',
    })
  } catch (error) {
    console.error('Logout request failed:', error)
  }

  clearAuth()
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