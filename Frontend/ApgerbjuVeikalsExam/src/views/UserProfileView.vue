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
                  {{ profile?.bio || t.noDescription }}
                </p>

                <p class="account-joined">
                  {{ t.userProfile }}
                </p>
              </div>
            </div>
          </div>

          <div class="account-stats">
            <div class="stat-box">
              <strong>{{ profile?.counts.listings || 0 }}</strong>
              <span>{{ t.listings }}</span>
            </div>

            <div class="stat-box">
              <strong>
                {{ profile?.hide_favorites ? '—' : profile?.counts.favorites || 0 }}
              </strong>
              <span>{{ t.favorites }}</span>
            </div>

            <div class="stat-box">
              <strong>
                {{ profile?.hide_likes ? '—' : profile?.counts.liked || 0 }}
              </strong>
              <span>{{ t.liked }}</span>
            </div>

            <div class="stat-box">
              <strong>{{ profile?.counts.purchases || 0 }}</strong>
              <span>{{ t.purchases }}</span>
            </div>

            <div class="stat-box">
              <strong>{{ profile?.counts.reviews || 0 }}</strong>
              <span>{{ t.reviews }}</span>
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
              {{ t.loading }}
            </p>
          </div>

          <p v-if="error" class="error">
            {{ error }}
          </p>

          <div v-if="activeTab === 'reviews'" class="reviews-list">
            <div
              v-for="review in profile?.reviews || []"
              :key="review.id"
              class="written-review-box user-profile-review"
            >
              <div class="written-review-stars">
                {{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}
              </div>

              <p>{{ review.text }}</p>

              <small v-if="review.buyer">
                {{ t.from }}:
                {{ review.buyer.display_name || review.buyer.name }}
              </small>

              <div
                v-if="review.listing"
                class="review-listing-mini"
                @click="goToListing(review.listing.id)"
              >
                <img
                  v-if="review.listing.images?.length"
                  :src="`${API_URL}/storage/${review.listing.images?.[0]?.image_path || ''}`"
                  :alt="review.listing.title"
                >

                <div v-else class="no-image">
                  {{ t.noImage }}
                </div>

                <span>{{ review.listing.title }}</span>
              </div>
            </div>

            <p
              v-if="!loading && !(profile?.reviews || []).length"
              class="empty-text"
            >
              {{ emptyMessage }}
            </p>
          </div>

          <div
            v-else-if="activeListings.length"
            class="listing-grid"
          >
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
                  {{ t.noImage }}
                </div>

                <div v-if="listing.status === 'sold'" class="sold-badge">
                  SOLD
                </div>
              </div>

              <div class="listing-card-info">
                <h3>{{ listing.title }}</h3>
                <p>{{ listing.category }}</p>

                <div class="product-price-row">
                  <span
                    v-if="listing.original_price && Number(listing.original_price) > Number(listing.price)"
                    class="old-price"
                  >
                    {{ formatPrice(listing.original_price) }}
                  </span>

                  <span class="current-price">
                    {{ formatPrice(listing.price) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <p
            v-else-if="!loading"
            class="empty-text"
          >
            {{ emptyMessage }}
          </p>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL } from '../services/api'

type Language = 'en' | 'lv'

type ListingImage = {
  id: number
  image_path: string
}

type Listing = {
  id: number
  title: string
  price: number | string
  original_price?: number | string | null
  category: string
  status?: string
  images?: ListingImage[]
}

type ReviewBuyer = {
  id: number
  name: string
  display_name: string | null
  avatar_url?: string | null
}

type Review = {
  id: number
  rating: number
  text: string
  created_at?: string
  listing?: Listing | null
  buyer?: ReviewBuyer | null
}

type UserProfile = {
  id: number
  name: string
  display_name: string | null
  bio: string | null
  avatar_url: string | null
  hide_likes?: boolean
  hide_favorites?: boolean
  listings: Listing[]
  favorites: Listing[]
  liked: Listing[]
  reviews?: Review[]
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

const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

const translations = {
  en: {
    noDescription: 'No description yet',
    userProfile: 'User profile',
    loading: 'Loading...',
    listings: 'Listings',
    favorites: 'Favorites',
    liked: 'Liked',
    purchases: 'Purchases',
    reviews: 'Reviews',
    yourListings: 'Listings',
    favoriteListings: 'Favorite Listings',
    likedListings: 'Liked Listings',
    hiddenFavorites: 'This user has hidden favorite listings.',
    hiddenLiked: 'This user has hidden liked listings.',
    noListings: 'No listings yet.',
    noFavorites: 'No favorite listings yet.',
    noLiked: 'No liked listings yet.',
    noPurchases: 'No purchases yet.',
    noReviews: 'No reviews yet.',
    noImage: 'No image',
    from: 'From',
    failedLoad: 'Failed to load profile',
    backendError: 'Could not connect to backend',
  },

  lv: {
    noDescription: 'Apraksta vēl nav',
    userProfile: 'Lietotāja profils',
    loading: 'Ielādē...',
    listings: 'Sludinājumi',
    favorites: 'Favorīti',
    liked: 'Patīk',
    purchases: 'Pirkumi',
    reviews: 'Atsauksmes',
    yourListings: 'Sludinājumi',
    favoriteListings: 'Favorītu sludinājumi',
    likedListings: 'Patīkamie sludinājumi',
    hiddenFavorites: 'Šis lietotājs ir paslēpis favorītu sludinājumus.',
    hiddenLiked: 'Šis lietotājs ir paslēpis patīkamos sludinājumus.',
    noListings: 'Sludinājumu vēl nav.',
    noFavorites: 'Favorītu sludinājumu vēl nav.',
    noLiked: 'Patīkamo sludinājumu vēl nav.',
    noPurchases: 'Pirkumu vēl nav.',
    noReviews: 'Atsauksmju vēl nav.',
    noImage: 'Nav attēla',
    from: 'No',
    failedLoad: 'Neizdevās ielādēt profilu',
    backendError: 'Neizdevās savienoties ar serveri',
  },
}

const t = computed(() => translations[language.value])

const updateLanguage = () => {
  language.value = (localStorage.getItem('language') as Language) || 'en'
}

const userInitial = computed(() => {
  const value = profile.value?.display_name || profile.value?.name || '?'

  return value.charAt(0).toUpperCase()
})

const getTabLabel = (tab: AccountTab) => {
  if (tab === 'listings') return t.value.yourListings
  if (tab === 'favorites') return t.value.favorites
  if (tab === 'liked') return t.value.liked
  if (tab === 'purchases') return t.value.purchases

  return t.value.reviews
}

const activeTabTitle = computed(() => {
  if (activeTab.value === 'listings') return t.value.yourListings
  if (activeTab.value === 'favorites') return t.value.favoriteListings
  if (activeTab.value === 'liked') return t.value.likedListings
  if (activeTab.value === 'purchases') return t.value.purchases

  return t.value.reviews
})

const activeListings = computed(() => {
  if (!profile.value) return []

  if (activeTab.value === 'listings') {
    return profile.value.listings || []
  }

  if (activeTab.value === 'favorites') {
    if (profile.value.hide_favorites) return []

    return profile.value.favorites || []
  }

  if (activeTab.value === 'liked') {
    if (profile.value.hide_likes) return []

    return profile.value.liked || []
  }

  return []
})

const emptyMessage = computed(() => {
  if (activeTab.value === 'favorites' && profile.value?.hide_favorites) {
    return t.value.hiddenFavorites
  }

  if (activeTab.value === 'liked' && profile.value?.hide_likes) {
    return t.value.hiddenLiked
  }

  if (activeTab.value === 'listings') return t.value.noListings
  if (activeTab.value === 'favorites') return t.value.noFavorites
  if (activeTab.value === 'liked') return t.value.noLiked
  if (activeTab.value === 'purchases') return t.value.noPurchases

  return t.value.noReviews
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
      error.value = data.message || t.value.failedLoad
      return
    }

    profile.value = data
  } catch (err) {
    console.error('Load user profile error:', err)
    error.value = t.value.backendError
  } finally {
    loading.value = false
  }
}

const goToListing = (id: number) => {
  router.push(`/listing/${id}`)
}

const formatPrice = (price: number | string) => {
  return `${Math.round(Number(price))} €`
}

watch(
  () => route.params.id,
  () => {
    loadProfile()
  }
)

onMounted(() => {
  loadProfile()
  window.addEventListener('language-changed', updateLanguage)
})

onBeforeUnmount(() => {
  window.removeEventListener('language-changed', updateLanguage)
})
</script>