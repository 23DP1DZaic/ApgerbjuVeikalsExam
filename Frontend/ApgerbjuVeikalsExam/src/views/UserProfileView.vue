<template>
  <div class="account-page">
    <div class="account-layout">
      <main class="account-main">
        <div class="account-header-card">
          <div class="account-top">
            <div class="account-user-block">
              <img
                v-if="profile?.avatar_url"
                :src="profile.avatar_url"
                alt="Avatar"
                class="account-avatar-image"
              >

              <div v-else class="account-avatar">
                {{ userInitial }}
              </div>

              <div class="account-user-info">
                <h1>{{ profile?.display_name || profile?.name || 'User' }}</h1>

                <p class="account-subtext">
                  {{ profile?.bio || 'No description yet' }}
                </p>

                <p class="account-joined">
                  User profile
                </p>
              </div>
            </div>
          </div>

          <div class="account-stats">
            <div class="stat-box">
              <strong>{{ profile?.counts.listings || 0 }}</strong>
              <span>Listings</span>
            </div>

            <div class="stat-box">
              <strong>{{ profile?.counts.favorites || 0 }}</strong>
              <span>Favorites</span>
            </div>

            <div class="stat-box">
              <strong>{{ profile?.counts.liked || 0 }}</strong>
              <span>Liked</span>
            </div>

            <div class="stat-box">
              <strong>{{ profile?.counts.purchases || 0 }}</strong>
              <span>Purchases</span>
            </div>

            <div class="stat-box">
              <strong>{{ profile?.counts.reviews || 0 }}</strong>
              <span>Reviews</span>
            </div>
          </div>

          <div class="account-tabs">
            <button
              v-for="tab in tabs"
              :key="tab"
              class="account-tab"
              :class="{ active: activeTab === tab }"
              @click="activeTab = tab"
            >
              {{ getTabLabel(tab) }}
            </button>
          </div>
        </div>

        <section class="account-content-card">
          <div class="account-section-header">
            <h2>{{ activeTabTitle }}</h2>

            <p v-if="loading" class="loading-text">
              Loading...
            </p>
          </div>

          <p v-if="error" class="error">
            {{ error }}
          </p>

          <div v-if="activeListings.length" class="listing-grid">
            <div
              v-for="listing in activeListings"
              :key="listing.id"
              class="listing-card"
              @click="goToListing(listing.id)"
            >
              <div class="listing-image-wrap">
                <img
                  v-if="listing.images?.length"
                  :src="`${API_URL}/storage/${listing.images?.[0]?.image_path || ''}`"
                  :alt="listing.title"
                  class="listing-card-image"
                >

                <div v-else class="no-image">
                  No image
                </div>

                <div v-if="listing.status === 'sold'" class="sold-badge">
                  SOLD
                </div>
              </div>

              <div class="listing-card-info">
                <h3>{{ listing.title }}</h3>
                <p>{{ listing.category }}</p>
                <span>{{ listing.price }} €</span>
              </div>
            </div>
          </div>

          <p v-else-if="!loading" class="empty-text">
            {{ emptyMessage }}
          </p>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL } from '../services/api'

type ListingImage = {
  id: number
  image_path: string
}

type Listing = {
  id: number
  title: string
  price: number
  category: string
  status?: string
  images?: ListingImage[]
}

type UserProfile = {
  id: number
  name: string
  display_name: string | null
  bio: string | null
  avatar_url: string | null
  listings: Listing[]
  favorites: Listing[]
  liked: Listing[]
  counts: {
    listings: number
    favorites: number
    liked: number
    purchases: number
    reviews: number
  }
}

type AccountTab = 'listings' | 'favorites' | 'liked' | 'purchases' | 'reviews'

const route = useRoute()
const router = useRouter()

const profile = ref<UserProfile | null>(null)
const loading = ref(true)
const error = ref('')
const activeTab = ref<AccountTab>('listings')

const tabs: AccountTab[] = ['listings', 'favorites', 'liked', 'purchases', 'reviews']

const userInitial = computed(() => {
  const value = profile.value?.display_name || profile.value?.name || '?'
  return value.charAt(0).toUpperCase()
})

const getTabLabel = (tab: AccountTab) => {
  if (tab === 'listings') return 'Your Listings'
  if (tab === 'favorites') return 'Favorites'
  if (tab === 'liked') return 'Liked'
  if (tab === 'purchases') return 'Purchases'
  return 'Reviews'
}

const activeTabTitle = computed(() => {
  if (activeTab.value === 'listings') return 'Listings'
  if (activeTab.value === 'favorites') return 'Favorite Listings'
  if (activeTab.value === 'liked') return 'Liked Listings'
  if (activeTab.value === 'purchases') return 'Purchases'
  return 'Reviews'
})

const activeListings = computed(() => {
  if (!profile.value) return []

  if (activeTab.value === 'listings') return profile.value.listings || []
  if (activeTab.value === 'favorites') return profile.value.favorites || []
  if (activeTab.value === 'liked') return profile.value.liked || []

  return []
})

const emptyMessage = computed(() => {
  if (activeTab.value === 'listings') return 'No listings yet.'
  if (activeTab.value === 'favorites') return 'No favorite listings yet.'
  if (activeTab.value === 'liked') return 'No liked listings yet.'
  if (activeTab.value === 'purchases') return 'No purchases yet.'
  return 'No reviews yet.'
})

const loadProfile = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetch(`${API_URL}/api/users/${route.params.id}/profile`, {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to load profile'
      return
    }

    profile.value = data
  } catch (err) {
    console.error('Load user profile error:', err)
    error.value = 'Could not connect to backend'
  } finally {
    loading.value = false
  }
}

const goToListing = (id: number) => {
  router.push(`/listing/${id}`)
}

watch(
  () => route.params.id,
  () => {
    loadProfile()
  }
)

onMounted(loadProfile)
</script>