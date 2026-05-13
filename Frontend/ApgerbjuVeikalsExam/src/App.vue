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
          <router-link v-if="user?.role === 'admin'" to="/admin/categories">
      ADMIN CATEGORIES
          </router-link>

<nav class="category-nav" ref="megaMenuRef">
  <div class="nav-item mega-item">
    <button
      class="nav-link-button"
      :class="{ active: activeMegaMenu === 'designers' }"
      @click="toggleMegaMenu('designers')"
    >
      DESIGNERS
      <span class="nav-arrow">⌄</span>
    </button>

    <div v-if="activeMegaMenu === 'designers'" class="mega-menu">
      <div class="mega-menu-inner simple-menu">
        <router-link :to="{ path: '/shop', query: { brand: 'Nike' } }" @click="closeMegaMenu">Nike</router-link>
        <router-link :to="{ path: '/shop', query: { brand: 'Adidas' } }" @click="closeMegaMenu">Adidas</router-link>
        <router-link :to="{ path: '/shop', query: { brand: 'Zara' } }" @click="closeMegaMenu">Zara</router-link>
        <router-link :to="{ path: '/shop', query: { brand: 'Balenciaga' } }" @click="closeMegaMenu">Balenciaga</router-link>
        <router-link :to="{ path: '/shop', query: { brand: 'Rick Owens' } }" @click="closeMegaMenu">Rick Owens</router-link>
      </div>
    </div>
  </div>

  <div class="nav-item mega-item">
    <button
      class="nav-link-button"
      :class="{ active: activeMegaMenu === 'menswear' }"
      @click="toggleMegaMenu('menswear')"
    >
      MENSWEAR
      <span class="nav-arrow">⌄</span>
    </button>

    <div v-if="activeMegaMenu === 'menswear'" class="mega-menu">
      <div class="mega-menu-inner">
        <div class="mega-column">
          <h4>Shop By Category</h4>
        </div>

        <div class="mega-column">
          <h5>Tops</h5>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Tshirt' } }" @click="closeMegaMenu">Long Sleeve T-Shirts</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Shirt' } }" @click="closeMegaMenu">Polos</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Shirt' } }" @click="closeMegaMenu">Shirts (Button Ups)</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Tshirt' } }" @click="closeMegaMenu">Short Sleeve T-Shirts</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Hoodie' } }" @click="closeMegaMenu">Sweaters & Knitwear</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Hoodie' } }" @click="closeMegaMenu">Sweatshirts & Hoodies</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Tshirt' } }" @click="closeMegaMenu">Tank Tops & Sleeveless</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Jersey' } }" @click="closeMegaMenu">Jerseys</router-link>
        </div>

        <div class="mega-column">
          <h5>Bottoms</h5>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Casual Pants</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Cropped Pants</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Denim</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Leggings</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Overalls & Jumpsuits</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Shorts</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Sweatpants & Joggers</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Swimwear</router-link>
        </div>

        <div class="mega-column">
          <h5>Outerwear</h5>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Bombers</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Cloaks & Capes</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Denim Jackets</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Heavy Coats</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Leather Jackets</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Light Jackets</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Parkas</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Raincoats</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Vests</router-link>
        </div>

        <div class="mega-column">
          <h5>Footwear</h5>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Sneakers' } }" @click="closeMegaMenu">Boots</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Sneakers' } }" @click="closeMegaMenu">Casual Leather Shoes</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Sneakers' } }" @click="closeMegaMenu">Formal Shoes</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Sneakers' } }" @click="closeMegaMenu">Hi-Top Sneakers</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Sneakers' } }" @click="closeMegaMenu">Low-Top Sneakers</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Sneakers' } }" @click="closeMegaMenu">Sandals</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Sneakers' } }" @click="closeMegaMenu">Slip Ons</router-link>
        </div>

        <div class="mega-column">
          <h5>Accessories</h5>
          <router-link :to="{ path: '/shop', query: { gender: 'men', color: 'Black' } }" @click="closeMegaMenu">Bags & Luggage</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Belt' } }" @click="closeMegaMenu">Belts</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Glasses' } }" @click="closeMegaMenu">Glasses</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Gloves' } }" @click="closeMegaMenu">Gloves & Scarves</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Hat' } }" @click="closeMegaMenu">Hats</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Jewelry' } }" @click="closeMegaMenu">Jewelry & Watches</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Wallet' } }" @click="closeMegaMenu">Wallets</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Socks' } }" @click="closeMegaMenu">Socks & Underwear</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', brand: 'Sunglasses' } }" @click="closeMegaMenu">Sunglasses</router-link>
        </div>

        <div class="mega-column">
          <h5>Tailoring</h5>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Blazers</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Shirt' } }" @click="closeMegaMenu">Formal Shirting</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jeans' } }" @click="closeMegaMenu">Formal Trousers</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Suits</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Tuxedos</router-link>
          <router-link :to="{ path: '/shop', query: { gender: 'men', category: 'Jacket' } }" @click="closeMegaMenu">Vests</router-link>
        </div>
      </div>
    </div>
  </div>

  <div class="nav-item mega-item">
    <button
      class="nav-link-button"
      :class="{ active: activeMegaMenu === 'womenswear' }"
      @click="toggleMegaMenu('womenswear')"
    >
      WOMENSWEAR
      <span class="nav-arrow">⌄</span>
    </button>

    <div v-if="activeMegaMenu === 'womenswear'" class="mega-menu">
      <div class="mega-menu-inner simple-menu">
        <router-link :to="{ path: '/shop', query: { gender: 'women', category: 'Tshirt' } }" @click="closeMegaMenu">Tops</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'women', category: 'Jeans' } }" @click="closeMegaMenu">Bottoms</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'women', category: 'Jacket' } }" @click="closeMegaMenu">Outerwear</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'women', category: 'Sneakers' } }" @click="closeMegaMenu">Shoes</router-link>
        <router-link :to="{ path: '/shop', query: { gender: 'women', condition: 'new' } }" @click="closeMegaMenu">New Arrivals</router-link>
      </div>
    </div>
  </div>

<div class="nav-item">
  <router-link
    :to="{ path: '/shop', query: { category: 'Sneakers' } }"
    @click="closeMegaMenu"
  >
    SNEAKERS
  </router-link>
</div>

<div class="nav-item">
  <router-link to="/about" @click="closeMegaMenu">
    ABOUT US
  </router-link>
</div>
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
import { ref, onMounted, computed, onBeforeUnmount} from 'vue'
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
  document.addEventListener('click', handleClickOutsideMegaMenu)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutsideMegaMenu)
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


const activeMegaMenu = ref<string | null>(null)
const megaMenuRef = ref<HTMLElement | null>(null)

const toggleMegaMenu = (menu: string) => {
  activeMegaMenu.value = activeMegaMenu.value === menu ? null : menu
}

const closeMegaMenu = () => {
  activeMegaMenu.value = null
}


const handleClickOutsideMegaMenu = (event: MouseEvent) => {
  const target = event.target as Node

  if (megaMenuRef.value && !megaMenuRef.value.contains(target)) {
    activeMegaMenu.value = null
  }
}
</script>