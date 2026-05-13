<template>
  <div class="home-page">
    <section class="featured-section">
      <h2>Featured</h2>

      <p v-if="loading" class="home-message">
        Loading listings...
      </p>

      <p v-else-if="error" class="home-error">
        {{ error }}
      </p>

      <div v-else-if="featuredListings.length" class="featured-grid">
        <div
          v-for="listing in featuredListings"
          :key="listing.id"
          class="featured-card"
          @click="goToListing(listing.id)"
        >
          <div class="featured-image-wrapper">
            <img
              v-if="listing.images && listing.images.length"
              :src="`${API_URL}/storage/${listing.images?.[0]?.image_path || ''}`"
              :alt="listing.title"
              class="featured-image"
            >

            <div v-else class="featured-placeholder">
              No image
            </div>
          </div>

          <h3>{{ listing.title }}</h3>
          <p>{{ listing.price }} €</p>
        </div>
      </div>

      <p v-else class="home-message">
        No listings yet.
      </p>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL } from '../services/api'

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
  brand?: string | null
  color?: string | null
  size?: string | null
  condition: string
  images: ListingImage[]
  user_id: number
}

const router = useRouter()

const featuredListings = ref<Listing[]>([])
const loading = ref(true)
const error = ref('')

const loadFeaturedListings = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetch(`${API_URL}/api/listings/random?limit=3`, {
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
      error.value = data.message || 'Failed to load featured listings'
      featuredListings.value = []
      return
    }

    featuredListings.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Load featured listings error:', err)
    error.value = 'Server connection error'
  } finally {
    loading.value = false
  }
}

const goToListing = (id: number) => {
  router.push(`/listing/${id}`)
}

onMounted(() => {
  loadFeaturedListings()
})
</script>