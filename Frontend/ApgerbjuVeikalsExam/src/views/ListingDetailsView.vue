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

        <div class="listing-actions detail-actions">
          <button
            class="interaction-btn"
            :class="{ active: listing.liked_by_me }"
            @click="toggleLike"
          >
            ♥ {{ listing.likes_count || 0 }}
          </button>

          <button
            class="interaction-btn"
            :class="{ active: listing.favorited_by_me }"
            @click="toggleFavorite"
          >
            ★ {{ listing.favorites_count || 0 }}
          </button>
        </div>

        <button class="purchase-btn">Purchase</button>
        <button class="secondary-btn">Offer</button>
        <button class="secondary-btn">Message</button>

        <div class="details-section">
          <h3>Seller Description</h3>
          <p>{{ listing.description }}</p>
        </div>

        <div class="details-section">
          <h3>Details</h3>
          <p><strong>Color:</strong> {{ listing.color || 'Not specified' }}</p>
          <p><strong>Size:</strong> {{ listing.size || 'Not specified' }}</p>
          <p><strong>Condition:</strong> {{ listing.condition }}</p>
          <p><strong>Category:</strong> {{ listing.category }}</p>
          <p><strong>Gender:</strong> {{ listing.gender || 'Not specified' }}</p>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getToken } from '../services/auth'

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
  gender?: string | null
  brand: string | null
  color: string | null
  size: string | null
  condition: string
  images: ListingImage[]
  likes_count?: number
  favorites_count?: number
  liked_by_me?: boolean
  favorited_by_me?: boolean
}

const route = useRoute()
const router = useRouter()

const listing = ref<Listing | null>(null)
const loading = ref(true)
const error = ref('')
const selectedImage = ref<string | null>(null)

const imageUrls = computed(() => {
  if (!listing.value?.images?.length) return []

  return listing.value.images.map((image) => {
    return `${API_URL}/storage/${image.image_path}`
  })
})

const fetchListing = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${route.params.id}`, {
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
      error.value = data.message || 'Listing not found'
      return
    }

    listing.value = data
    selectedImage.value = imageUrls.value[0] || null
  } catch (err) {
    console.error('Fetch listing error:', err)
    error.value = 'Failed to load listing'
  } finally {
    loading.value = false
  }
}

const toggleLike = async () => {
  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  if (!listing.value) return

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${listing.value.id}/like`, {
      method: 'POST',
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to like listing')
      return
    }

    listing.value.liked_by_me = data.liked
    listing.value.likes_count = data.likes_count
  } catch (err) {
    console.error('Like error:', err)
    alert('Server connection error')
  }
}

const toggleFavorite = async () => {
  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  if (!listing.value) return

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${listing.value.id}/favorite`, {
      method: 'POST',
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to favorite listing')
      return
    }

    listing.value.favorited_by_me = data.favorited
    listing.value.favorites_count = data.favorites_count
  } catch (err) {
    console.error('Favorite error:', err)
    alert('Server connection error')
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

onMounted(() => {
  fetchListing()
})
</script>