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

          <router-link to="/messages" class="header-message-link">
            {{ t.messages }}

            <span
              v-if="unreadMessagesCount > 0"
              class="unread-badge"
            >
              {{ unreadMessagesCount > 99 ? '99+' : unreadMessagesCount }}
            </span>
          </router-link>

          <div
            v-if="user"
            ref="profileMenuRef"
            class="user-menu profile-menu-wrapper"
          >
            <button
              type="button"
              class="user-info user-link profile-menu-button"
              @click="isProfileMenuOpen = !isProfileMenuOpen"
            >
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

              <span class="profile-menu-arrow">›</span>
            </button>

            <div
              v-if="isProfileMenuOpen"
              class="profile-dropdown"
            >
              <div class="profile-dropdown-header">
                <strong>{{ user.display_name || user.name }}</strong>
              </div>

              <router-link
                to="/account?tab=listings"
                class="profile-dropdown-link"
                @click="closeProfileMenu"
              >
                Your Listings
              </router-link>

              <router-link
                to="/account?tab=favorites"
                class="profile-dropdown-link"
                @click="closeProfileMenu"
              >
                Favorites
              </router-link>

              <router-link
                to="/account?tab=liked"
                class="profile-dropdown-link"
                @click="closeProfileMenu"
              >
                Liked
              </router-link>

              <router-link
                to="/account?tab=purchases"
                class="profile-dropdown-link"
                @click="closeProfileMenu"
              >
                Purchases
              </router-link>

              <router-link
                to="/account?tab=reviews"
                class="profile-dropdown-link"
                @click="closeProfileMenu"
              >
                Reviews
              </router-link>

              <div class="profile-dropdown-divider"></div>

              <router-link
                to="/settings/profile"
                class="profile-dropdown-link"
                @click="closeProfileMenu"
              >
                Settings
              </router-link>

              <button
                type="button"
                class="profile-dropdown-link profile-dropdown-logout"
                @click="logout"
              >
                Log out
              </button>

              <router-link
                v-if="user?.role === 'admin'"
                to="/admin/categories"
                class="profile-dropdown-link profile-dropdown-admin"
                @click="closeProfileMenu"
              >
                {{ t.adminCategories }}
              </router-link>
            </div>
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
        <div
          v-if="activeMegaMenu === 'designers'"
          class="mega-menu-inner designers-mega"
        >
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
            <button
              type="button"
              class="see-all-designers-btn"
              @click="showDesignersSoon"
            >
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
            <router-link
              class="mega-parent-link"
              :to="{
                path: '/shop',
                query: {
                  gender: 'men',
                  parent_category: parent.name,
                },
              }"
              @click="closeMegaMenu"
            >
              {{ categoryLabel(parent.name) }}
            </router-link>

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
              {{ categoryLabel(child.name) }}
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
              {{ t.viewCategory }} {{ categoryLabel(parent.name) }}
            </router-link>
          </div>
        </div>

        <div v-if="activeMegaMenu === 'womenswear'" class="mega-menu-inner">
          <div
            v-for="parent in womenCategoryTree"
            :key="parent.id"
            class="mega-column"
          >
            <router-link
              class="mega-parent-link"
              :to="{
                path: '/shop',
                query: {
                  gender: 'women',
                  parent_category: parent.name,
                },
              }"
              @click="closeMegaMenu"
            >
              {{ categoryLabel(parent.name) }}
            </router-link>

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
              {{ categoryLabel(child.name) }}
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
              {{ t.viewCategory }} {{ categoryLabel(parent.name) }}
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

          <router-link to="/delivery">
            Delivery
          </router-link>

          <router-link to="/refunds">
            Refunds
          </router-link>

          <router-link to="/about">
            About us
          </router-link>
        </div>

        <div class="footer-section">
          <h4>Contact Us</h4>

          <p class="footer-contact-row">
            sunny@marketplace.com
          </p>

          <p class="footer-contact-row">
            +371 00000000
          </p>
        </div>

        <div class="footer-section">
          <h4>{{ t.newsletterTitle }}</h4>

          <div class="newsletter-block">
            <form class="newsletter" @submit.prevent="subscribeNewsletter">
              <input
                v-model.trim="newsletterEmail"
                type="email"
                :placeholder="t.newsletterPlaceholder"
              >

              <button type="submit">
                →
              </button>
            </form>

            <p
              v-if="newsletterMessage"
              class="newsletter-message"
              :class="{ success: newsletterSuccess, error: !newsletterSuccess }"
            >
              {{ newsletterMessage }}
            </p>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2026 Sunny</p>
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
const unreadMessagesCount = ref(0)
const headerSearch = ref('')

const menCategoryTree = ref<CategoryNode[]>([])
const womenCategoryTree = ref<CategoryNode[]>([])

const activeMegaMenu = ref<string | null>(null)
const megaMenuRef = ref<HTMLElement | null>(null)

const popularBrands = [
  'Acne Studios',
  "Arc'teryx",
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
].sort((a, b) => a.localeCompare(b))

const lvCategoryLabels: Record<string, string> = {
  // Parent categories
  Accessories: 'Aksesuāri',
  Bottoms: 'Apakšdaļa',
  Footwear: 'Apavi',
  Outerwear: 'Virsdrēbes',
  Tailoring: 'Klasiskais apģērbs',
  Tops: 'Augšdaļa',

  // Men Tops
  'Long Sleeve T-Shirts': 'T-krekli ar garām piedurknēm',
  Polos: 'Polo krekli',
  'Shirts (Button Ups)': 'Krekli ar pogām',
  'Short Sleeve T-Shirts': 'T-krekli ar īsām piedurknēm',
  'Sweaters & Knitwear': 'Džemperi un trikotāža',
  'Sweatshirts & Hoodies': 'Džemperi un hūdiji',
  'Tank Tops & Sleeveless': 'Krekli bez piedurknēm',
  Jerseys: 'Sporta krekli',

  // Men Bottoms
  'Casual Pants': 'Ikdienas bikses',
  'Cropped Pants': 'Saīsinātas bikses',
  Denim: 'Džinsi',
  Leggings: 'Legingi',
  'Overalls & Jumpsuits': 'Kombinezoni',
  Shorts: 'Šorti',
  'Sweatpants & Joggers': 'Sporta bikses un džogeri',
  Swimwear: 'Peldapģērbs',

  // Men Outerwear
  Bombers: 'Bomberi',
  'Cloaks & Capes': 'Apmetņi',
  'Denim Jackets': 'Džinsu jakas',
  'Heavy Coats': 'Silti mēteļi',
  'Leather Jackets': 'Ādas jakas',
  'Light Jackets': 'Vieglas jakas',
  Parkas: 'Parkas',
  Raincoats: 'Lietusmēteļi',
  Vests: 'Vestes',

  // Footwear
  Boots: 'Zābaki',
  'Casual Leather Shoes': 'Ikdienas ādas apavi',
  'Formal Shoes': 'Klasiskie apavi',
  'Hi-Top Sneakers': 'Augstās kedas',
  'High-Top Sneakers': 'Augstās kedas',
  'Low-Top Sneakers': 'Zemās kedas',
  Sandals: 'Sandales',
  'Slip Ons': 'Slip-on apavi',
  Flats: 'Zempapēžu apavi',
  Heels: 'Augstpapēžu kurpes',

  // Accessories
  'Bags & Luggage': 'Somas un bagāža',
  Bags: 'Somas',
  Belts: 'Jostas',
  Glasses: 'Brilles',
  'Gloves & Scarves': 'Cimdi un šalles',
  Hats: 'Cepures',
  'Jewelry & Watches': 'Rotaslietas un pulksteņi',
  Jewelry: 'Rotaslietas',
  Scarves: 'Šalles',
  Wallets: 'Maki',
  Watches: 'Pulksteņi',
  'Socks & Underwear': 'Zeķes un apakšveļa',
  Sunglasses: 'Saulesbrilles',

  // Tailoring
  Blazers: 'Žaketes',
  'Formal Shirting': 'Klasiskie krekli',
  'Formal Trousers': 'Klasiskās bikses',
  'Formal Dresses': 'Klasiskas kleitas',
  'Formal Pants': 'Klasiskas bikses',
  Sets: 'Komplekti',
  Suits: 'Uzvalki',
  Tuxedos: 'Smokingu kostīmi',

  // Women
  Blouses: 'Blūzes',
  'Crop Tops': 'Īsie topi',
  'Long Sleeve Tops': 'Topi ar garām piedurknēm',
  'Short Sleeve Tops': 'Topi ar īsām piedurknēm',
  'Tank Tops': 'Topi bez piedurknēm',
  Jeans: 'Džinsi',
  Pants: 'Bikses',
  Skirts: 'Svārki',
  Coats: 'Mēteļi',
  'Puffer Jackets': 'Pufīgās jakas',
}

const designerSoonMessage = ref('')

const popularBrandColumns = computed(() => {
  const columnsCount = 3
  const itemsPerColumn = Math.ceil(popularBrands.length / columnsCount)

  return Array.from({ length: columnsCount }, (_, index) => {
    const start = index * itemsPerColumn
    const end = start + itemsPerColumn

    return popularBrands.slice(start, end)
  })
})

const showDesignersSoon = () => {
  designerSoonMessage.value = t.value.designersSoon
}

const userInitial = computed(() => {
  const value = user.value?.display_name || user.value?.name || '?'

  return value.charAt(0).toUpperCase()
})

const loadUnreadMessagesCount = async () => {
  if (!user.value) {
    unreadMessagesCount.value = 0
    return
  }

  try {
    const response = await fetchWithAuth(`${API_URL}/api/me/unread-messages-count`, {
      method: 'GET',
    })

    const data = await response.json()

    if (response.ok) {
      unreadMessagesCount.value = Number(data.count || 0)
    }
  } catch (err) {
    console.error('Unread messages count error:', err)
  }
}

const loadUser = async () => {
  user.value = await getCurrentUser()
  await loadUnreadMessagesCount()
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
  closeProfileMenu()
  clearAuth()
  user.value = null
  unreadMessagesCount.value = 0
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
  document.addEventListener('click', handleProfileMenuClickOutside)

  window.addEventListener('auth-changed', refreshUser)
  window.addEventListener('messages-read', loadUnreadMessagesCount)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutsideMegaMenu)
  document.removeEventListener('click', handleProfileMenuClickOutside)

  window.removeEventListener('auth-changed', refreshUser)
  window.removeEventListener('messages-read', loadUnreadMessagesCount)
})

watch(
  () => route.fullPath,
  () => {
    closeMegaMenu()

    if (route.path === '/messages') {
      loadUnreadMessagesCount()
    }
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
    popularDesigners: 'Popular Designers',
    seeAllDesigners: 'SEE ALL DESIGNERS',
    designersSoon: 'All designers page with alphabet navigation and search will be added soon.',
    newsletterTitle: 'Subscribe to our newsletter',
    newsletterPlaceholder: 'Your email',
    newsletterEmpty: 'Please enter your email address.',
    newsletterInvalid: 'Please enter a valid email address.',
    newsletterSuccess: 'You have successfully subscribed to the newsletter!',
    viewCategory: 'View',
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
    newsletterTitle: 'Pieraksties jaunumiem',
    newsletterPlaceholder: 'Tavs e-pasts',
    newsletterEmpty: 'Lūdzu, ievadi e-pasta adresi.',
    newsletterInvalid: 'Lūdzu, ievadi derīgu e-pasta adresi.',
    newsletterSuccess: 'Tu veiksmīgi pierakstījies jaunumiem!',
    viewCategory: 'Skatīt',
  },
}

const t = computed(() => translations[language.value])

const categoryLabel = (categoryName: string) => {
  if (language.value === 'lv') {
    return lvCategoryLabels[categoryName] || categoryName
  }

  return categoryName
}

const toggleLanguage = () => {
  language.value = language.value === 'en' ? 'lv' : 'en'
  localStorage.setItem('language', language.value)

  window.dispatchEvent(
    new CustomEvent('language-changed', {
      detail: language.value,
    })
  )
}

const refreshUser = async () => {
  user.value = getUser()
  await loadUnreadMessagesCount()
}

const isProfileMenuOpen = ref(false)
const profileMenuRef = ref<HTMLElement | null>(null)

const closeProfileMenu = () => {
  isProfileMenuOpen.value = false
}

const handleProfileMenuClickOutside = (event: MouseEvent) => {
  const target = event.target as Node

  if (
    profileMenuRef.value &&
    !profileMenuRef.value.contains(target)
  ) {
    isProfileMenuOpen.value = false
  }
}

const newsletterEmail = ref('')
const newsletterMessageType = ref<'empty' | 'invalid' | 'success' | ''>('')

const newsletterSuccess = computed(() => {
  return newsletterMessageType.value === 'success'
})

const newsletterMessage = computed(() => {
  if (newsletterMessageType.value === 'empty') return t.value.newsletterEmpty
  if (newsletterMessageType.value === 'invalid') return t.value.newsletterInvalid
  if (newsletterMessageType.value === 'success') return t.value.newsletterSuccess

  return ''
})

const subscribeNewsletter = () => {
  newsletterMessageType.value = ''

  if (!newsletterEmail.value) {
    newsletterMessageType.value = 'empty'
    return
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!emailRegex.test(newsletterEmail.value)) {
    newsletterMessageType.value = 'invalid'
    return
  }

  newsletterMessageType.value = 'success'
  newsletterEmail.value = ''
}
</script>