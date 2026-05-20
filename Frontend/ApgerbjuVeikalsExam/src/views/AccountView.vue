<template>
  <div class="account-page">
    <div class="account-layout">
      <main class="account-main">
        <div class="account-header-card">
          <div class="account-top">
            <div class="account-user-block">
              <img
                v-if="user?.avatar_url"
                :src="user.avatar_url"
                alt="Avatar"
                class="account-avatar-image"
              >

              <div v-else class="account-avatar">
                {{ userInitial }}
              </div>

              <div class="account-user-info">
                <h1>{{ user?.display_name || user?.name || 'User' }}</h1>

                <p class="account-subtext">
                  {{ user?.bio || 'No description yet' }}
                </p>

                <p class="account-joined">
                  Joined in 2025
                </p>
              </div>
            </div>

            <div class="account-actions">
              <button
                type="button"
                class="account-logout-link"
                @click="logout"
              >
                Logout
              </button>

              <router-link to="/settings/profile" class="edit-profile-btn">
                Edit Profile
              </router-link>
            </div>
          </div>

          <div class="account-stats">
            <div class="stat-box">
              <strong>{{ sellingListings.length }}</strong>
              <span>Listings</span>
            </div>

            <div class="stat-box">
              <strong>{{ favoriteListings.length }}</strong>
              <span>Favorites</span>
            </div>

            <div class="stat-box">
              <strong>{{ likedListings.length }}</strong>
              <span>Liked</span>
            </div>

            <div class="stat-box">
              <strong>0</strong>
              <span>Purchases</span>
            </div>

            <div class="stat-box">
              <strong>0</strong>
              <span>Reviews</span>
            </div>
          </div>

          <div class="account-tabs">
            <button
              v-for="tab in tabs"
              :key="tab"
              class="account-tab"
              :class="{ active: activeTab === tab }"
              @click="setActiveTab(tab)"
            >
              {{ getTabLabel(tab) }}
            </button>
          </div>
        </div>

        <section class="account-content-card">
          <div class="account-section-header">
            <h2>{{ activeTabTitle }}</h2>

            <p v-if="loadingTab" class="loading-text">
              Loading...
            </p>
          </div>

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

          <p v-else-if="!loadingTab" class="empty-text">
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
import { API_URL, fetchWithAuth } from '../services/api'
import { clearAuth, getCurrentUser, type AuthUser, type Listing } from '../services/auth'

const router = useRouter()
const route = useRoute()

const user = ref<AuthUser | null>(null)

const sellingListings = ref<Listing[]>([])
const favoriteListings = ref<Listing[]>([])
const likedListings = ref<Listing[]>([])

const loadingTab = ref(false)

const tabs = ['listings', 'favorites', 'liked', 'purchases', 'reviews'] as const
type AccountTab = typeof tabs[number]

const activeTab = ref<AccountTab>('listings')

const normalizeTab = (tab: unknown): AccountTab => {
  const value = String(tab || 'listings').toLowerCase()

  if (value === 'selling') return 'listings'
  if (value === 'favorite') return 'favorites'

  if (tabs.includes(value as AccountTab)) {
    return value as AccountTab
  }

  return 'listings'
}

const logout = () => {
  clearAuth()
  router.push('/login')
}

const userInitial = computed(() => {
  const value = user.value?.display_name || user.value?.name || '?'
  return value.charAt(0).toUpperCase()
})

const getTabLabel = (tab: AccountTab) => {
  if (tab === 'listings') {
    return sellingListings.value.length === 1 ? 'Your Listing' : 'Your Listings'
  }

  if (tab === 'favorites') return 'Favorites'
  if (tab === 'liked') return 'Liked'
  if (tab === 'purchases') return 'Purchases'

  return 'Reviews'
}

const activeTabTitle = computed(() => {
  if (activeTab.value === 'listings') {
    return sellingListings.value.length === 1 ? 'Your Listing' : 'Your Listings'
  }

  if (activeTab.value === 'favorites') return 'Favorite Listings'
  if (activeTab.value === 'liked') return 'Liked Listings'
  if (activeTab.value === 'purchases') return 'Purchases'

  return 'Reviews'
})

const activeListings = computed(() => {
  if (activeTab.value === 'listings') return sellingListings.value
  if (activeTab.value === 'favorites') return favoriteListings.value
  if (activeTab.value === 'liked') return likedListings.value

  return []
})

const emptyMessage = computed(() => {
  if (activeTab.value === 'listings') return 'No listings yet.'
  if (activeTab.value === 'favorites') return 'No favorite listings yet.'
  if (activeTab.value === 'liked') return 'No liked listings yet.'
  if (activeTab.value === 'purchases') return 'No purchases yet.'

  return 'No reviews yet.'
})

const loadFavorites = async () => {
  loadingTab.value = true

  try {
    const response = await fetchWithAuth(`${API_URL}/api/me/favorites`, {
      method: 'GET',
    })

    const data = await response.json()

    if (response.ok) {
      favoriteListings.value = Array.isArray(data) ? data : []
    }
  } catch (err) {
    console.error('Load favorites error:', err)
  } finally {
    loadingTab.value = false
  }
}

const loadLikes = async () => {
  loadingTab.value = true

  try {
    const response = await fetchWithAuth(`${API_URL}/api/me/likes`, {
      method: 'GET',
    })

    const data = await response.json()

    if (response.ok) {
      likedListings.value = Array.isArray(data) ? data : []
    }
  } catch (err) {
    console.error('Load likes error:', err)
  } finally {
    loadingTab.value = false
  }
}

const loadAccount = async () => {
  const currentUser = await getCurrentUser()

  if (!currentUser) {
    router.push('/login')
    return
  }

  user.value = currentUser
  sellingListings.value = currentUser.listings || []

  await loadFavorites()
  await loadLikes()
}

const setActiveTab = async (tab: AccountTab) => {
  activeTab.value = tab

  await router.replace({
    path: '/account',
    query: {
      tab,
    },
  })

  if (tab === 'favorites') {
    await loadFavorites()
  }

  if (tab === 'liked') {
    await loadLikes()
  }
}

const goToListing = (id: number) => {
  router.push(`/listing/${id}`)
}

watch(
  () => route.query.tab,
  async (tab) => {
    activeTab.value = normalizeTab(tab)

    if (activeTab.value === 'favorites') {
      await loadFavorites()
    }

    if (activeTab.value === 'liked') {
      await loadLikes()
    }
  }
)

onMounted(() => {
  activeTab.value = normalizeTab(route.query.tab)
  loadAccount()
})
</script>