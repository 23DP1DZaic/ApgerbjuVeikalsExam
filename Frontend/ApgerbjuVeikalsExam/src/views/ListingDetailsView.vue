<template>
  <div class="listing-page">
    <div v-if="loading">Loading...</div>

    <div v-else-if="error" class="error">
      {{ error }}
    </div>

    <div v-else-if="listing" class="listing-layout">
      <div class="listing-gallery">
        <div class="thumbnails">
          <div
            v-for="image in imageUrls"
            :key="image"
            class="thumbnail"
            :class="{ active: selectedImage === image }"
            @click="selectedImage = image"
          >
            <img :src="image" :alt="listing.title">
          </div>

          <div v-if="imageUrls.length === 0" class="thumbnail">
            <div class="thumb-placeholder">No image</div>
          </div>
        </div>

        <div class="main-image">
          <button
            v-if="imageUrls.length > 1"
            class="image-arrow left"
            @click="previousImage"
          >
            ‹
          </button>

          <img
            v-if="selectedImage"
            :src="selectedImage"
            :alt="listing.title"
          >

          <div v-else class="details-no-image">
            No image
          </div>

          <button
            v-if="imageUrls.length > 1"
            class="image-arrow right"
            @click="nextImage"
          >
            ›
          </button>
        </div>
      </div>

      <aside class="listing-info">
        <h2>{{ listing.brand || 'Unknown brand' }}</h2>
        <h1>{{ listing.title }}</h1>

        <p class="meta">
          {{ listing.category }} · {{ listing.size }} · {{ listing.condition }}
        </p>

        <p class="price">{{ listing.price }} €</p>

        <button class="purchase-btn">Purchase</button>
        <button class="secondary-btn">Offer</button>
        <button class="secondary-btn">Message</button>

        <div class="details-section">
          <h3>Seller Description</h3>
          <p>{{ listing.description }}</p>
        </div>

        <div class="details-section">
          <h3>Details</h3>
          <p><strong>Color:</strong> {{ listing.color }}</p>
          <p><strong>Size:</strong> {{ listing.size }}</p>
          <p><strong>Condition:</strong> {{ listing.condition }}</p>
          <p><strong>Category:</strong> {{ listing.category }}</p>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

type ListingImage = {
  id: number
  image_path: string
}

type Listing = {
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
}

const route = useRoute()

const listing = ref<Listing | null>(null)
const loading = ref(true)
const error = ref('')
const selectedImage = ref<string | null>(null)

const imageUrls = computed(() => {
  if (!listing.value?.images?.length) return []

  return listing.value.images.map((image) => {
    return `http://127.0.0.1:8000/storage/${image.image_path}`
  })
})

const fetchListing = async () => {
  try {
    const response = await fetch(
      `http://127.0.0.1:8000/api/listings/${route.params.id}`
    )

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Listing not found'
      return
    }

    listing.value = data
    selectedImage.value = imageUrls.value[0] || null
  } catch {
    error.value = 'Failed to load listing'
  } finally {
    loading.value = false
  }
}

const nextImage = () => {
  if (!imageUrls.value.length || !selectedImage.value) return

  const index = imageUrls.value.indexOf(selectedImage.value)
  const nextIndex = (index + 1) % imageUrls.value.length

  selectedImage.value = imageUrls.value[nextIndex] ?? null
}

const previousImage = () => {
  if (!imageUrls.value.length || !selectedImage.value) return

  const index = imageUrls.value.indexOf(selectedImage.value)
  const previousIndex = (index - 1 + imageUrls.value.length) % imageUrls.value.length

  selectedImage.value = imageUrls.value[previousIndex] ?? null
}

onMounted(fetchListing)
</script>