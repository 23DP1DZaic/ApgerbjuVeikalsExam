<template>
  <div class="shop">
    <div class="container"></div>

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

  <div class="filter-section">
    <h3>Color</h3>
    <select v-model="colorFilter" class="sort-select">
      <option value="">All colors</option>
      <option value="Black">Black</option>
      <option value="White">White</option>
      <option value="Gray">Gray</option>
      <option value="Brown">Brown</option>
      <option value="Beige">Beige</option>
      <option value="Yellow">Yellow</option>
      <option value="Red">Red</option>
      <option value="Orange">Orange</option>
      <option value="Pink">Pink</option>
      <option value="Purple">Purple</option>
      <option value="Blue">Blue</option>
      <option value="Green">Green</option>
      <option value="Multi">Multi</option>
      <option value="Silver">Silver</option>
      <option value="Gold">Gold</option>
    </select>
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
    <h3>Gender</h3>
    <select v-model="genderFilter" class="sort-select">
      <option value="">All</option>
      <option value="men">Men</option>
      <option value="women">Women</option>
      <option value="unisex">Unisex</option>
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
          <p v-if="loading">Loading...</p>
          <p v-else>{{ products.length }} items</p>
        </div>

        <p v-if="error" class="error">{{ error }}</p>

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
              <p>{{ product.category }}</p>
              <span class="price">{{ product.price }} €</span>
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
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getUser, getToken, clearAuth, type AuthUser } from '../services/auth'

const router = useRouter()
const route = useRoute()

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
}

const products = ref<Product[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const brandFilter = ref('')
const sizeFilter = ref('')
const colorFilter = ref('')
const conditionFilter = ref('')
const genderFilter = ref('')
const minPrice = ref('')
const maxPrice = ref('')
const onlyWithImage = ref(false)
const sortOption = ref('newest')

const syncFiltersFromRoute = () => {
  brandFilter.value = String(route.query.brand || '')
  sizeFilter.value = String(route.query.size || '')
  colorFilter.value = String(route.query.color || '')
  conditionFilter.value = String(route.query.condition || '')
  genderFilter.value = String(route.query.gender || '')
  minPrice.value = String(route.query.min_price || '')
  maxPrice.value = String(route.query.max_price || '')
  sortOption.value = String(route.query.sort || 'newest')
  onlyWithImage.value = String(route.query.has_images || '') === '1'
}

const updateRouteWithFilters = () => {
  router.replace({
    path: '/shop',
    query: {
      search: route.query.search || undefined,
      category: route.query.category || undefined,
      section: route.query.section || undefined,

      brand: brandFilter.value || undefined,
      size: sizeFilter.value || undefined,
      color: colorFilter.value || undefined,
      condition: conditionFilter.value || undefined,
      gender: genderFilter.value || undefined,
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

    if (route.query.gender) {
      params.append('gender', String(route.query.gender))
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

    const response = await fetch(url, {
      headers: {
        Accept: 'application/json',
      },
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

    products.value = data
  } catch (err) {
    console.error('Fetch listings error:', err)
    error.value = 'Could not connect to backend'
    products.value = []
  } finally {
    loading.value = false
  }
}

watch(
  () => route.query,
  () => {
    syncFiltersFromRoute()
    fetchProducts()
  }
)



onMounted(() => {
  syncFiltersFromRoute()
  fetchProducts()
})

const resetFilters = () => {
  brandFilter.value = ''
  sizeFilter.value = ''
  colorFilter.value = ''
  conditionFilter.value = ''
  genderFilter.value = ''
  minPrice.value = ''
  maxPrice.value = ''
  onlyWithImage.value = false
  sortOption.value = 'newest'

  router.replace({
    path: '/shop',
    query: {
      search: route.query.search || undefined,
      category: route.query.category || undefined,
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
</script>