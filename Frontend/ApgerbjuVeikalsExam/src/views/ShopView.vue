<template>
  <div class="shop">

    <!-- Header -->
    <div class="shop-header">
      <div class="container">
        <h1>SHOP</h1>
        <p>All items</p>
      </div>
    </div>

    <div class="container shop-container">


      <!-- Sidebar with filters -->
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
              <span>{{ category.name }}</span>
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

        <button class="reset-filters" @click="resetFilters">Reset filters</button>
      </aside>

      <!-- Main area with items -->
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
              <img :src="product.image" :alt="product.name">
            </div>
            <div class="product-info">
              <h3>{{ product.name }}</h3>
              <p>{{ product.category }}</p>
              <span class="price">{{ product.price }} €</span>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

type Product = {
  id: number
  name: string
  price: number
  image: string
  category: string
}

const products = ref<Product[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

// Categories
const categories = ref([
  { id: 'Tshirt', name: 'Tshirt' },
  { id: 'Jeans', name: 'Jeans' },
  { id: 'Hoodie', name: 'Hoodie' },
  { id: 'Jacket', name: 'Jacket' },
  { id: 'Shirt', name: 'Shirt' },
])

// FIlters
const selectedCategories = ref<string[]>([])
const sortOption = ref('newest')

// Load info from backend
const fetchProducts = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/products')
    products.value = await response.json()
  } catch (e) {
    error.value = 'Failed to load products'
  } finally {
    loading.value = false
  }
}

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
  console.log('See product:', product)
}
</script>
