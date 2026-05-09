<template>
  <div class="shop">
    <div class="container"></div>

    <div class="container shop-container">
      <aside class="shop-sidebar">
        <div class="filter-section">
          <h3>Categories</h3>

          <div class="filter-options">
            <label v-for="category in categories" :key="category.id">
              <input
                v-model="selectedCategories"
                type="checkbox"
                :value="category.id"
              >
              <span>{{ category.title }}</span>
            </label>
          </div>
        </div>

        <div class="filter-section">
          <h3>Sort by:</h3>
          <select v-model="sortOption" class="sort-select">
            <option value="newest">New arrivals</option>
            <option value="price-low">Price (Low)</option>
            <option value="price-high">Price (High)</option>
          </select>
        </div>

        <button class="reset-filters" @click="resetFilters">
          Reset filters
        </button>
      </aside>

      <main class="shop-main">
        <div class="products-header">
          <p v-if="loading">Loading...</p>
          <p v-else>{{ filteredProducts.length }} items</p>
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading" class="products-grid">
          <div
            v-for="product in filteredProducts"
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
import { ref, computed, onMounted, watch } from 'vue'
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

const searchText = ref(String(route.query.search || ''))

const categories = ref([
  { id: 'Tshirt', title: 'Tshirt' },
  { id: 'Jeans', title: 'Jeans' },
  { id: 'Hoodie', title: 'Hoodie' },
  { id: 'Jacket', title: 'Jacket' },
  { id: 'Shirt', title: 'Shirt' },
])

const selectedCategories = ref<string[]>([])
const sortOption = ref('newest')

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
    searchText.value = String(route.query.search || '')
    fetchProducts()
  }
)

onMounted(fetchProducts)

const filteredProducts = computed(() => {
  let filtered = [...products.value]

  if (selectedCategories.value.length > 0) {
    filtered = filtered.filter((p) =>
      selectedCategories.value.includes(p.category)
    )
  }

  if (sortOption.value === 'price-low') {
    filtered.sort((a, b) => a.price - b.price)
  }

  if (sortOption.value === 'price-high') {
    filtered.sort((a, b) => b.price - a.price)
  }

  return filtered
})

const resetFilters = () => {
  selectedCategories.value = []
  sortOption.value = 'newest'
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