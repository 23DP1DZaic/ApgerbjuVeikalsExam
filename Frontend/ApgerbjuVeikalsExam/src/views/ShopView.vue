<template>
  <div class="shop">
    <div class="shop-header">
      <div class="container">
        <h1>SHOP</h1>
        <p>All items</p>
      </div>
    </div>

    <div class="container shop-container">
      <!-- Side bar with filters -->
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

      <!-- Main clothing space -->
      <main class="shop-main">
        <div class="products-header">
          <p>{{ filteredProducts.length }} items</p> 
        <!-- amount of items -->
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
import { ref, computed } from 'vue'

type Product = {
  id: number
  name: string
  price: number
  image: string
  category: string
}

// Данные
const products = ref<Product[]>([
  { id: 1, name: '1', price: 29, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', category: 'Tshirt' },
  { id: 2, name: '2', price: 89, image: 'https://images.unsplash.com/photo-1542272604-787c3835535d?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', category: 'Jeans' },
  { id: 3, name: '3', price: 65, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', category: 'Hoodie' },
  { id: 4, name: '4', price: 120, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', category: 'Jacket' },
  { id: 5, name: '5', price: 55, image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', category: 'Shirt' },
  { id: 6, name: '6', price: 25, image: 'https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', category: 'Tshirt' },
])

const categories = ref([
  { id: 'all', name: 'All items' },
  { id: 'Tshirt', name: 'Tshirt' },
  { id: 'Jeans', name: 'Jeans' },
  { id: 'Hoodie', name: 'Hoodie' },
  { id: 'Jacket', name: 'Jacket' },
  { id: 'Shirt', name: 'Shirt' },
])

// Filters
const selectedCategories = ref<string[]>(['all'])
const sortOption = ref('newest')

// Filter and sort
const filteredProducts = computed(() => {
  let filtered = [...products.value]
  
  // FIlter by category
  if (!selectedCategories.value.includes('all')) {
    filtered = filtered.filter(p => selectedCategories.value.includes(p.category))
  }
  
  // Sort by price
  switch (sortOption.value) {
    case 'price-low':
      filtered.sort((a, b) => a.price - b.price)
      break
    case 'price-high':
      filtered.sort((a, b) => b.price - a.price)
      break
    case 'newest':
    default:

      break
  }
  
  return filtered
})

// Methods
const resetFilters = () => {
  selectedCategories.value = ['all']
  sortOption.value = 'newest'
}

const addToCart = (product: Product) => {
  console.log('Added to cart:', product)
  // Add to cart
}

const viewProduct = (product: Product) => {
  console.log('See product:', product)
  // Go to product page
}
</script>
