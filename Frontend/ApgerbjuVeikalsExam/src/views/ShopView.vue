<template>
  <div class="shop">
    <div class="container shop-container">
      <aside class="shop-sidebar">
        <div class="filter-section">
          <h3>Brand</h3>
          <input
            v-model="brandFilter"
            type="text"
            class="filter-input"
            placeholder="Nike, Adidas..."
          >
        </div>

        <div class="filter-section">
          <h3>Size</h3>
          <select v-model="sizeFilter" class="sort-select">
            <option value="">All sizes</option>
            <option value="XS">XS</option>
            <option value="S">S</option>
            <option value="M">M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>
          </select>
        </div>

        <div
          class="filter-section color-dropdown-wrapper"
          ref="colorFilterDropdownRef"
        >
          <h3>Color</h3>

          <button
            type="button"
            class="custom-color-select shop-color-select"
            @click="isColorFilterOpen = !isColorFilterOpen"
          >
            <span
              v-if="selectedFilterColor"
              class="color-dot"
              :class="{ 'white-dot': selectedFilterColor.name === 'White' }"
              :style="{ background: selectedFilterColor.value }"
            ></span>

            <span>
              {{ selectedFilterColor ? selectedFilterColor.name : 'All colors' }}
            </span>

            <span class="custom-select-arrow">⌄</span>
          </button>

          <div v-if="isColorFilterOpen" class="custom-color-menu shop-color-menu">
            <button
              type="button"
              class="custom-color-option"
              @click="selectFilterColor('')"
            >
              <span class="color-dot empty-color-dot"></span>
              <span>All colors</span>
            </button>

            <button
              v-for="color in colors"
              :key="color.name"
              type="button"
              class="custom-color-option"
              @click="selectFilterColor(color.name)"
            >
              <span
                class="color-dot"
                :class="{ 'white-dot': color.name === 'White' }"
                :style="{ background: color.value }"
              ></span>

              <span>{{ color.name }}</span>
            </button>
          </div>
        </div>

        <div class="filter-section">
          <h3>Condition</h3>
          <select v-model="conditionFilter" class="sort-select">
            <option value="">All conditions</option>
            <option value="new">New</option>
            <option value="used">Used</option>
          </select>
        </div>

        <div class="filter-section">
          <h3>Price</h3>

          <div class="price-inputs">
            <input
              v-model="minPrice"
              type="number"
              min="0"
              class="filter-input"
              placeholder="Min"
            >

            <input
              v-model="maxPrice"
              type="number"
              min="0"
              class="filter-input"
              placeholder="Max"
            >
          </div>
        </div>

        <div class="filter-section">
          <h3>Sort by</h3>
          <select v-model="sortOption" class="sort-select">
            <option value="newest">New arrivals</option>
            <option value="price-low">Price (Low)</option>
            <option value="price-high">Price (High)</option>
            <option value="title-az">Title A-Z</option>
          </select>
        </div>

        <div class="filter-actions">
          <button class="apply-filters" @click="applyFilters">
            Apply filters
          </button>

          <button class="reset-filters" @click="resetFilters">
            Reset filters
          </button>
        </div>
      </aside>

      <main class="shop-main">
        <div class="products-header">
          <p v-if="loading">
            Loading...
          </p>

          <p v-else-if="products.length > 0">
            {{ products.length }} {{ products.length === 1 ? st.item : st.items }}
          </p>

          <div v-else class="empty-shop-message">
            <p>
              {{ st.noItems }}
            </p>

            <button
              type="button"
              class="empty-shop-btn"
              @click="resetFilters"
            >
              {{ st.resetFilters }}
            </button>
          </div>
        </div>

        <p v-if="error" class="error">
          {{ error }}
        </p>

        <div v-if="!loading" class="products-grid">
          <div
            v-for="product in products"
            :key="product.id"
            class="product-card"
            @click="viewProduct(product)"
          >
            <div class="product-image">
              <img
                v-if="product.images && product.images.length"
                :src="`${API_URL}/storage/${product.images?.[0]?.image_path || ''}`"
                :alt="product.title"
              >

              <div v-else class="no-image">
                No image
              </div>
            </div>

            <div class="product-info">
              <h3>{{ product.title }}</h3>

              <p>
                {{ product.category }}
                <span v-if="product.size"> · {{ product.size }}</span>
                <span v-if="product.condition"> · {{ formatText(product.condition) }}</span>
              </p>

              <span class="price">{{ product.price }} €</span>

              <div class="listing-actions">
                <button
                  class="interaction-btn"
                  :class="{ active: product.liked_by_me }"
                  @click.stop="toggleLike(product)"
                >
                  ♥ {{ product.likes_count || 0 }}
                </button>

                <button
                  class="interaction-btn"
                  :class="{ active: product.favorited_by_me }"
                  @click.stop="toggleFavorite(product)"
                >
                  ★ {{ product.favorites_count || 0 }}
                </button>
              </div>
            </div>

            <button
              v-if="canDelete(product)"
              class="delete-btn"
              @click.stop="deleteListing(product.id)"
            >
              Delete
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getUser, getToken, clearAuth, type AuthUser } from '../services/auth'

const router = useRouter()
const route = useRoute()

type Language = 'en' | 'lv'

type ListingImage = {
  id: number
  image_path: string
}

type Product = {
  id: number
  title: string
  description: string
  price: number
  category: string
  brand: string | null
  color: string | null
  size: string | null
  condition: string
  gender?: string | null
  images: ListingImage[]
  user_id: number
  likes_count?: number
  favorites_count?: number
  liked_by_me?: boolean
  favorited_by_me?: boolean
}

type Category = {
  id: number
  name: string
  slug: string
  department?: string | null
  parent_id?: number | null
}

const products = ref<Product[]>([])
const categories = ref<Category[]>([])

const loading = ref(true)
const error = ref<string | null>(null)

const brandFilter = ref('')
const sizeFilter = ref('')
const colorFilter = ref('')
const conditionFilter = ref('')
const minPrice = ref('')
const maxPrice = ref('')
const onlyWithImage = ref(false)
const sortOption = ref('newest')
const categoryFilter = ref('')

const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

const shopTranslations = {
  en: {
    item: 'item',
    items: 'items',
    noItems: 'No items found. Try another category.',
    resetFilters: 'Reset filters',
  },

  lv: {
    item: 'prece',
    items: 'preces',
    noItems: 'Netika atrasta neviena prece. Izmēģini citu kategoriju.',
    resetFilters: 'Atiestatīt filtrus',
  },
}

const st = computed(() => shopTranslations[language.value])

const updateLanguage = () => {
  language.value = (localStorage.getItem('language') as Language) || 'en'
}

const loadCategories = async () => {
  try {
    const response = await fetch(`${API_URL}/api/categories`, {
      headers: {
        Accept: 'application/json',
      },
    })

    const rawText = await response.text()

    let data: any = null

    try {
      data = JSON.parse(rawText)
    } catch {
      data = []
    }

    if (!response.ok) {
      console.error('Failed to load categories:', data)
      return
    }

    categories.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Load categories error:', err)
  }
}

const availableCategoryFilters = computed(() => {
  return categories.value.filter((category) => category.parent_id !== null)
})

const syncFiltersFromRoute = () => {
  brandFilter.value = String(route.query.brand || '')
  sizeFilter.value = String(route.query.size || '')
  colorFilter.value = String(route.query.color || '')
  conditionFilter.value = String(route.query.condition || '')
  minPrice.value = String(route.query.min_price || '')
  maxPrice.value = String(route.query.max_price || '')
  sortOption.value = String(route.query.sort || 'newest')
  onlyWithImage.value = String(route.query.has_images || '') === '1'
  categoryFilter.value = String(route.query.category || '')
}

const updateRouteWithFilters = () => {
  router.replace({
    path: '/shop',
    query: {
      search: route.query.search || undefined,
      section: route.query.section || undefined,

      category: categoryFilter.value || undefined,
      brand: brandFilter.value || undefined,
      size: sizeFilter.value || undefined,
      color: colorFilter.value || undefined,
      condition: conditionFilter.value || undefined,
      min_price: minPrice.value || undefined,
      max_price: maxPrice.value || undefined,
      has_images: onlyWithImage.value ? '1' : undefined,
      sort: sortOption.value !== 'newest' ? sortOption.value : undefined,
    },
  })
}

const applyFilters = () => {
  updateRouteWithFilters()
}

const fetchProducts = async () => {
  loading.value = true
  error.value = null

  try {
    const params = new URLSearchParams()

    if (route.query.search) {
      params.append('search', String(route.query.search))
    }

    if (route.query.category) {
      params.append('category', String(route.query.category))
    }

    if (route.query.section) {
      params.append('section', String(route.query.section))
    }

    if (route.query.brand) {
      params.append('brand', String(route.query.brand))
    }

    if (route.query.size) {
      params.append('size', String(route.query.size))
    }

    if (route.query.color) {
      params.append('color', String(route.query.color))
    }

    if (route.query.condition) {
      params.append('condition', String(route.query.condition))
    }

    if (route.query.min_price) {
      params.append('min_price', String(route.query.min_price))
    }

    if (route.query.max_price) {
      params.append('max_price', String(route.query.max_price))
    }

    if (route.query.has_images) {
      params.append('has_images', String(route.query.has_images))
    }

    if (route.query.sort) {
      params.append('sort', String(route.query.sort))
    }

    const queryString = params.toString()

    const url = queryString
      ? `${API_URL}/api/listings?${queryString}`
      : `${API_URL}/api/listings`

    const response = await fetchWithAuth(url, {
      method: 'GET',
    })

    const rawText = await response.text()

    let data: any = null

    try {
      data = JSON.parse(rawText)
    } catch {
      data = { message: rawText }
    }

    if (!response.ok) {
      error.value = data.message || 'Failed to load listings'
      products.value = []
      return
    }

    products.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Fetch listings error:', err)
    error.value = 'Could not connect to backend'
    products.value = []
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  brandFilter.value = ''
  sizeFilter.value = ''
  colorFilter.value = ''
  conditionFilter.value = ''
  minPrice.value = ''
  maxPrice.value = ''
  onlyWithImage.value = false
  sortOption.value = 'newest'
  categoryFilter.value = ''

  router.replace({
    path: '/shop',
    query: {
      search: route.query.search || undefined,
      section: route.query.section || undefined,
    },
  })
}

const viewProduct = (product: Product) => {
  router.push(`/listing/${product.id}`)
}

const canDelete = (product: Product) => {
  const currentUser: AuthUser | null = getUser()

  if (!currentUser) return false

  return currentUser.role === 'admin' || product.user_id === currentUser.id
}

const deleteListing = async (id: number) => {
  const currentUser = getUser()
  const token = getToken()

  if (!currentUser || !token) {
    alert('Login first')
    clearAuth()
    router.push('/login')
    return
  }

  if (!confirm('Delete listing?')) return

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${id}`, {
      method: 'DELETE',
    })

    const rawText = await response.text()

    let data: any = null

    try {
      data = JSON.parse(rawText)
    } catch {
      data = { message: rawText }
    }

    if (!response.ok) {
      alert(data.message || 'Failed to delete listing')
      return
    }

    fetchProducts()
  } catch (err) {
    console.error('Delete error:', err)
    alert('Server connection error')
  }
}

const toggleLike = async (product: Product) => {
  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${product.id}/like`, {
      method: 'POST',
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to like listing')
      return
    }

    product.liked_by_me = data.liked
    product.likes_count = data.likes_count
  } catch (err) {
    console.error('Like error:', err)
    alert('Server connection error')
  }
}

const toggleFavorite = async (product: Product) => {
  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${product.id}/favorite`, {
      method: 'POST',
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to favorite listing')
      return
    }

    product.favorited_by_me = data.favorited
    product.favorites_count = data.favorites_count
  } catch (err) {
    console.error('Favorite error:', err)
    alert('Server connection error')
  }
}

const formatText = (value?: string | null) => {
  if (!value) return ''

  return value.charAt(0).toUpperCase() + value.slice(1)
}

watch(
  () => route.query,
  () => {
    syncFiltersFromRoute()
    fetchProducts()
  }
)

const handleColorFilterClickOutside = (event: MouseEvent) => {
  const target = event.target as Node

  if (
    colorFilterDropdownRef.value &&
    !colorFilterDropdownRef.value.contains(target)
  ) {
    isColorFilterOpen.value = false
  }
}

onMounted(() => {
  syncFiltersFromRoute()
  loadCategories()
  fetchProducts()

  window.addEventListener('language-changed', updateLanguage)
  document.addEventListener('click', handleColorFilterClickOutside)
})

onBeforeUnmount(() => {
  window.removeEventListener('language-changed', updateLanguage)
  document.removeEventListener('click', handleColorFilterClickOutside)
})

const isColorFilterOpen = ref(false)
const colorFilterDropdownRef = ref<HTMLElement | null>(null)

const colors = [
  { name: 'Black', value: '#000000' },
  { name: 'White', value: '#ffffff' },
  { name: 'Gray', value: '#e5e5e5' },
  { name: 'Brown', value: '#6b4a3a' },
  { name: 'Beige', value: '#e6cf91' },
  { name: 'Yellow', value: '#ffd91a' },
  { name: 'Red', value: '#f10b0b' },
  { name: 'Orange', value: '#ff6500' },
  { name: 'Pink', value: '#ec5aaa' },
  { name: 'Purple', value: '#5f1fd6' },
  { name: 'Blue', value: '#1177bd' },
  { name: 'Green', value: '#4faf0b' },
  {
    name: 'Multi',
    value: 'linear-gradient(135deg, red, orange, yellow, green, blue, purple)',
  },
  {
    name: 'Silver',
    value: 'linear-gradient(135deg, #777, #eee, #aaa)',
  },
  {
    name: 'Gold',
    value: 'linear-gradient(135deg, #b99b22, #f3e37c, #c8a600)',
  },
]

const selectedFilterColor = computed(() => {
  return colors.find((color) => color.name === colorFilter.value) || null
})

const selectFilterColor = (colorName: string) => {
  colorFilter.value = colorName
  isColorFilterOpen.value = false
}


</script>