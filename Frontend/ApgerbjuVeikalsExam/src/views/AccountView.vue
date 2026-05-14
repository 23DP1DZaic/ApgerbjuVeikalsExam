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
                <p class="account-joined">Joined in 2025</p>
              </div>
            </div>

            <!-- <button class="logout-btn" @click="logout">
              {{ t.logout }}
            </button> -->

              <!-- <button
              type="button"
              class="account-logout-btn"
              @click="logout"
            >
              Logout
            </button> -->

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
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getCurrentUser, type AuthUser, type Listing } from '../services/auth'
import { clearAuth } from '../services/auth'



const router = useRouter()

const logout = () => {
  clearAuth()
  router.push('/login')
}

const user = ref<AuthUser | null>(null)

const sellingListings = ref<Listing[]>([])
const favoriteListings = ref<Listing[]>([])
const likedListings = ref<Listing[]>([])

const loadingTab = ref(false)

const tabs = ['Selling', 'Favorites', 'Liked', 'Purchases', 'Reviews'] as const
type AccountTab = typeof tabs[number]

const getTabLabel = (tab: AccountTab) => {
  if (tab === 'Selling') {
    return sellingListings.value.length === 1 ? 'Your Listing' : 'Your Listings'
  }

  return tab
}

const activeTab = ref<AccountTab>('Selling')

const userInitial = computed(() => {
  const value = user.value?.display_name || user.value?.name || '?'
  return value.charAt(0).toUpperCase()
})

const activeTabTitle = computed(() => {
  if (activeTab.value === 'Selling') {
    return sellingListings.value.length === 1 ? 'Your Listing' : 'Your Listings'
  }

  if (activeTab.value === 'Favorites') return 'Favorite Listings'
  if (activeTab.value === 'Liked') return 'Liked Listings'
  if (activeTab.value === 'Purchases') return 'Purchases'

  return 'Reviews'
})

const activeListings = computed(() => {
  if (activeTab.value === 'Selling') return sellingListings.value
  if (activeTab.value === 'Favorites') return favoriteListings.value
  if (activeTab.value === 'Liked') return likedListings.value

  return []
})

const emptyMessage = computed(() => {
  if (activeTab.value === 'Selling') return 'No listings yet.'
  if (activeTab.value === 'Favorites') return 'No favorite listings yet.'
  if (activeTab.value === 'Liked') return 'No liked listings yet.'
  if (activeTab.value === 'Purchases') return 'No purchases yet.'
  return 'No reviews yet.'
})

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

const setActiveTab = async (tab: AccountTab) => {
  activeTab.value = tab

  if (tab === 'Favorites') {
    await loadFavorites()
  }

  if (tab === 'Liked') {
    await loadLikes()
  }
}

const goToListing = (id: number) => {
  router.push(`/listing/${id}`)
}

onMounted(() => {
  loadAccount()
})
</script>
