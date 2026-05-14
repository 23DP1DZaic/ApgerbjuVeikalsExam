<template>
  <div id="app">
    <header class="main-header">
      <div class="header-top container">
        <div class="logo">
          <router-link to="/">Sunny</router-link>
        </div>

      <form class="header-search" @submit.prevent="submitHeaderSearch">
        <input
          v-model="headerSearch"
          :placeholder="t.searchPlaceholder"
        >
        <button type="submit">{{ t.search }}</button>
      </form>

      <nav class="header-actions">
        <router-link v-if="user" to="/create-listing">
          {{ t.addListing }}
        </router-link>

        <router-link to="/messages">
          {{ t.messages }}
        </router-link>

        <router-link v-if="user?.role === 'admin'" to="/admin/categories">
          {{ t.adminCategories }}
        </router-link>

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

            <span class="user-name">
              {{ user.display_name || user.name }}
            </span>
          </router-link>
        </div>

        <div v-else class="auth-links">
          <router-link to="/login">{{ t.login }}</router-link>
          <router-link to="/register">{{ t.register }}</router-link>
        </div>

        <button
          type="button"
          class="language-toggle"
          @click="toggleLanguage"
        >
          <span class="language-flag">
            {{ language === 'lv' ? '🇱🇻' : '🇬🇧' }}
          </span>

          <span>
            {{ language === 'lv' ? 'LV' : 'EN' }}
          </span>
        </button>
      </nav>
      </div>

      <nav class="category-nav" ref="megaMenuRef">
        <div class="nav-item mega-item">
          <button
            class="nav-link-button"
            :class="{ active: activeMegaMenu === 'designers' }"
            @click="toggleMegaMenu('designers')"
          >
          {{ t.designers }}
            <span class="nav-arrow">⌄</span>
          </button>
        </div>

        <div class="nav-item mega-item">
          <button
            class="nav-link-button"
            :class="{ active: activeMegaMenu === 'menswear' }"
            @click="toggleMegaMenu('menswear')"
          >
            {{ t.menswear }}
            <span class="nav-arrow">⌄</span>
          </button>
        </div>

            <div class="nav-item mega-item">
              <button
                class="nav-link-button"
                :class="{ active: activeMegaMenu === 'womenswear' }"
                @click="toggleMegaMenu('womenswear')"
              >
            {{ t.womenswear }}
                <span class="nav-arrow">⌄</span>
              </button>
            </div>

            <div class="nav-item">
              <router-link
                :to="{ path: '/shop', query: { category: 'Low-Top Sneakers' } }"
                @click="closeMegaMenu"
              >
              {{ t.sneakers }}
              </router-link>
            </div>

            <div class="nav-item">
              <router-link to="/about" @click="closeMegaMenu">
              {{ t.aboutUs }}
              </router-link>
            </div>
          </nav>

          <div
            v-if="activeMegaMenu"
            class="mega-menu"
          >
    <div v-if="activeMegaMenu === 'designers'" class="mega-menu-inner designers-mega">
      <div class="mega-title-column">
        <h4>{{ t.popularDesigners }}</h4>
      </div>

      <div
        v-for="column in popularBrandColumns"
        :key="column.join('-')"
        class="mega-column"
      >
        <router-link
          v-for="brand in column"
          :key="brand"
          :to="{ path: '/shop', query: { brand } }"
          @click="closeMegaMenu"
        >
          {{ brand }}
        </router-link>
      </div>

      <div class="designers-footer">
        <button type="button" class="see-all-designers-btn" @click="showDesignersSoon">
          {{ t.seeAllDesigners }}
        </button>

        <p v-if="designerSoonMessage" class="designers-soon-message">
          {{ designerSoonMessage }}
        </p>
      </div>
</div>

        <div v-if="activeMegaMenu === 'menswear'" class="mega-menu-inner">
          <div
            v-for="parent in menCategoryTree"
            :key="parent.id"
            class="mega-column"
          >
            <h5>{{ parent.name }}</h5>

            <router-link
              v-for="child in parent.children"
              :key="child.id"
              :to="{
                path: '/shop',
                query: {
                  gender: 'men',
                  category: child.name,
                },
              }"
              @click="closeMegaMenu"
            >
              {{ child.name }}
            </router-link>

            <router-link
              v-if="!parent.children.length"
              :to="{
                path: '/shop',
                query: {
                  gender: 'men',
                  category: parent.name,
                },
              }"
              @click="closeMegaMenu"
            >
              View {{ parent.name }}
            </router-link>
          </div>
        </div>

        <div v-if="activeMegaMenu === 'womenswear'" class="mega-menu-inner">
          <div
            v-for="parent in womenCategoryTree"
            :key="parent.id"
            class="mega-column"
          >
            <h5>{{ parent.name }}</h5>

            <router-link
              v-for="child in parent.children"
              :key="child.id"
              :to="{
                path: '/shop',
                query: {
                  gender: 'women',
                  category: child.name,
                },
              }"
              @click="closeMegaMenu"
            >
              {{ child.name }}
            </router-link>

            <router-link
              v-if="!parent.children.length"
              :to="{
                path: '/shop',
                query: {
                  gender: 'women',
                  category: parent.name,
                },
              }"
              @click="closeMegaMenu"
            >
              View {{ parent.name }}
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <main class="main-content">
      <router-view />
    </main>

    <footer class="main-footer">
      <div class="container">
        <div class="footer-section">
          <h4>Sunny</h4>
          <p>Vienkārša platforma unikālu apģērbu pirkšanai un pārdošanai.</p>
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
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getCurrentUser, clearAuth, type AuthUser, getUser } from './services/auth'
import { API_URL, fetchWithAuth } from './services/api'



type CategoryNode = {
  id: number
  name: string
  slug: string
  department?: string | null
  parent_id?: number | null
  children: CategoryNode[]
}

const router = useRouter()
const route = useRoute()

const user = ref<AuthUser | null>(null)
const headerSearch = ref('')

const menCategoryTree = ref<CategoryNode[]>([])
const womenCategoryTree = ref<CategoryNode[]>([])

const activeMegaMenu = ref<string | null>(null)
const megaMenuRef = ref<HTMLElement | null>(null)

const popularBrands = [
  'Acne Studios',
  'Adidas',
  'Amiri',
  'Arc\'teryx',
  'Balenciaga',
  'Bape',
  'Bottega Veneta',
  'Burberry',
  'Carhartt',
  'Celine',
  'Chrome Hearts',
  'Comme des Garcons',
  'Dior',
  'Gucci',
  'Maison Margiela',
  'Moncler',
  'Nike',
  'Off-White',
  'Prada',
  'Raf Simons',
  'Rick Owens',
  'Saint Laurent Paris',
  'Stone Island',
  'Stussy',
  'Supreme',
  'The North Face',
  'Undercover',
  'Vetements',
  'Vintage',
  'Vivienne Westwood',
  'Yohji Yamamoto',
  'Zara',
]

const designerSoonMessage = ref('')

const sortedPopularBrands = computed(() => {
  return [...popularBrands].sort((a, b) => a.localeCompare(b))
})

const popularBrandColumns = computed(() => {
  const columns: string[][] = [[], [], []]

  sortedPopularBrands.value.forEach((brand, index) => {
    const columnIndex = index % columns.length
    const column = columns[columnIndex]

    if (column) {
      column.push(brand)
    }
  })

  return columns
})

const showDesignersSoon = () => {
  designerSoonMessage.value = t.value.designersSoon
}

const userInitial = computed(() => {
  const value = user.value?.display_name || user.value?.name || '?'
  return value.charAt(0).toUpperCase()
})

const loadUser = async () => {
  user.value = await getCurrentUser()
}

const loadCategoryTree = async (department: 'men' | 'women') => {
  try {
    const response = await fetch(
      `${API_URL}/api/categories/tree?department=${department}`,
      {
        headers: {
          Accept: 'application/json',
        },
      }
    )

    const rawText = await response.text()

    let data: any = []

    try {
      data = JSON.parse(rawText)
    } catch {
      data = []
    }

    if (!response.ok) {
      console.error(`Failed to load ${department} category tree:`, data)
      return
    }

    if (department === 'men') {
      menCategoryTree.value = Array.isArray(data) ? data : []
    }

    if (department === 'women') {
      womenCategoryTree.value = Array.isArray(data) ? data : []
    }
  } catch (error) {
    console.error(`Failed to fetch ${department} categories:`, error)
  }
}

const toggleMegaMenu = (menu: string) => {
  designerSoonMessage.value = ''
  activeMegaMenu.value = activeMegaMenu.value === menu ? null : menu
}

const closeMegaMenu = () => {
  activeMegaMenu.value = null
}

const handleClickOutsideMegaMenu = (event: MouseEvent) => {
  const target = event.target as Node
  const megaMenuElement = document.querySelector('.mega-menu')

  const clickedInsideNav =
    megaMenuRef.value && megaMenuRef.value.contains(target)

  const clickedInsideMenu =
    megaMenuElement && megaMenuElement.contains(target)

  if (!clickedInsideNav && !clickedInsideMenu) {
    activeMegaMenu.value = null
  }
}

const logout = () => {
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

  closeMegaMenu()
}

onMounted(() => {
  loadUser()
  loadCategoryTree('men')
  loadCategoryTree('women')
  document.addEventListener('click', handleClickOutsideMegaMenu)
  window.addEventListener('auth-changed', refreshUser)
})


onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutsideMegaMenu)
  window.removeEventListener('auth-changed', refreshUser)
})

watch(
  () => route.fullPath,
  () => {
    closeMegaMenu()
  }
)

type Language = 'en' | 'lv'

const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

const translations = {
  en: {
    addListing: 'ADD LISTING',
    messages: 'MESSAGES',
    adminCategories: 'ADMIN CATEGORIES',
    logout: 'LOGOUT',
    login: 'LOGIN',
    register: 'REGISTER',
    search: 'SEARCH',
    searchPlaceholder: 'Search for anything',
    designers: 'DESIGNERS',
    menswear: 'MENSWEAR',
    womenswear: 'WOMENSWEAR',
    sneakers: 'SNEAKERS',
    aboutUs: 'ABOUT US',
    popularDesigners: 'Shop Popular Designers',
    seeAllDesigners: 'SEE ALL DESIGNERS',
    designersSoon: 'All designers page with alphabet navigation and search will be added soon.',
  },

  lv: {
    addListing: 'PIEVIENOT SLUDINĀJUMU',
    messages: 'ZIŅAS',
    adminCategories: 'ADMIN KATEGORIJAS',
    logout: 'IZIET',
    login: 'PIESLĒGTIES',
    register: 'REĢISTRĒTIES',
    search: 'MEKLĒT',
    searchPlaceholder: 'Meklēt jebko',
    designers: 'ZĪMOLI',
    menswear: 'VĪRIEŠIEM',
    womenswear: 'SIEVIETĒM',
    sneakers: 'APAVI',
    aboutUs: 'PAR MUMS',
    popularDesigners: 'Populāri zīmoli',
    seeAllDesigners: 'SKATĪT VISUS ZĪMOLUS',
    designersSoon: 'Visu zīmolu lapa ar alfabēta navigāciju un meklēšanu tiks pievienota vēlāk.',
  },
}

const t = computed(() => translations[language.value])

const toggleLanguage = () => {
  language.value = language.value === 'en' ? 'lv' : 'en'
  localStorage.setItem('language', language.value)

  window.dispatchEvent(
    new CustomEvent('language-changed', {
      detail: language.value,
    })
  )
}

const refreshUser = () => {
  user.value = getUser()
}

</script>