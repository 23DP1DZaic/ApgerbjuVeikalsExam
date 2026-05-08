<template>
  <div class="shop">
    <div class="container">
    </div>
    <div class="container shop-container">
      <aside class="shop-sidebar">
        <div class="filter-section">
          <h3>Categories</h3>

          <div class="filter-options">
            <label v-for="category in categories" :key="category.id">
              <input
                type="checkbox"
                :value="category.id"
                v-model="selectedCategories"
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
          <p>{{ filteredProducts.length }} items</p>
        </div>

        <div class="products-grid">
          <div
            class="product-card"
            v-for="product in filteredProducts"
            :key="product.id"
            @click="viewProduct(product)"
          >
            <div class="product-image">
              <img
                v-if="product.images && product.images.length"
                :src="`http://127.0.0.1:8000/storage/${product.images?.[0]?.image_path}`"
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



const router = useRouter()


const products = ref<Product[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const route = useRoute()
const searchText = ref(String(route.query.search || ''))

  

// Categories
const categories = ref([
  { id: 'Tshirt', title: 'Tshirt' },
  { id: 'Jeans', title: 'Jeans' },
  { id: 'Hoodie', title: 'Hoodie' },
  { id: 'Jacket', title: 'Jacket' },
  { id: 'Shirt', title: 'Shirt' },
])

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
  images: ListingImage[]
  user_id: number
}

// FIlters
const selectedCategories = ref<string[]>([])
const sortOption = ref('newest')

// Load info from backend
const fetchProducts = async () => {
  loading.value = true

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

  const response = await fetch(
    `http://127.0.0.1:8000/api/listings?${params.toString()}`
  )

  products.value = await response.json()
  loading.value = false
}



const submitSearch = () => {
  router.push({
    path: '/shop',
    query: {
      ...route.query,
      search: searchText.value || undefined,
    },
  })
}


watch(
  () => route.query,
  () => {
    searchText.value = String(route.query.search || '')
    fetchProducts()
  }
)




onMounted(fetchProducts)

// Filters and sorting by price
const filteredProducts = computed(() => {
  let filtered = [...products.value]

  if (selectedCategories.value.length > 0) {
    filtered = filtered.filter(p =>
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

// Methods
const resetFilters = () => {
  selectedCategories.value = []
  sortOption.value = 'newest'
}

const viewProduct = (product: Product) => {
  router.push(`/listing/${product.id}`)
}


const currentUser = JSON.parse(localStorage.getItem('user') || 'null')

const canDelete = (product: Product) => {
  if (!currentUser) return false

  return currentUser.role === 'admin' || product.user_id === currentUser.id
}

const deleteListing = async (id: number) => {
  if (!currentUser) {
    alert('Login first')
    return
  }

  if (!confirm('Delete listing?')) return

  await fetch(`http://127.0.0.1:8000/api/listings/${id}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      user_id: currentUser.id,
    }),
  })

  fetchProducts()
}

</script>
