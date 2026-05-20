Исправил: в `Reviews` у тебя был неправильный `listing.title` вместо `purchase.listing.title`, из-за этого ломался блок. Также убрал двойной `€` и сделал цены через `formatPrice`. 

```vue
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
              <strong>{{ purchasedItems.length }}</strong>
              <span>Purchases</span>
            </div>

            <div class="stat-box">
              <strong>{{ reviewedCount }}</strong>
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

          <div v-if="activeTab === 'reviews'" class="review-section">
            <div v-if="purchasedItems.length" class="listing-grid">
              <div
                v-for="purchase in purchasedItems"
                :key="purchase.id"
                class="review-listing-item"
              >
                <div
                  class="listing-card"
                  :class="{ disabled: purchase.already_reviewed }"
                  @click="!purchase.already_reviewed && openReviewModal(purchase)"
                >
                  <div class="listing-image-wrap">
                    <img
                      v-if="purchase.listing.images?.length"
                      :src="`${API_URL}/storage/${purchase.listing.images?.[0]?.image_path || ''}`"
                      :alt="purchase.listing.title"
                      class="listing-card-image"
                    >

                    <div v-else class="no-image">
                      No image
                    </div>

                    <div v-if="purchase.listing.status === 'sold'" class="sold-badge">
                      SOLD
                    </div>
                  </div>

                  <div class="listing-card-info">
                    <h3>{{ purchase.listing.title }}</h3>
                    <p>{{ purchase.listing.category }}</p>
                    <span>{{ formatPrice(purchase.listing.price) }}</span>

                    <small v-if="purchase.already_reviewed" class="review-card-note">
                      Already reviewed
                    </small>

                    <small v-else class="review-card-note">
                      Click to write review
                    </small>
                  </div>
                </div>

                <div
                  v-if="purchase.already_reviewed && purchase.review"
                  class="written-review-box"
                >
                  <div class="written-review-stars">
                    {{ '★'.repeat(purchase.review.rating) }}{{ '☆'.repeat(5 - purchase.review.rating) }}
                  </div>

                  <p>
                    {{ purchase.review.text }}
                  </p>
                </div>
              </div>
            </div>

            <p v-else-if="!loadingTab" class="empty-text">
              You have no purchased listings to review.
            </p>
          </div>

          <div
            v-else-if="activeTab === 'purchases'"
            class="listing-grid"
          >
            <div
              v-for="purchase in purchasedItems"
              :key="purchase.id"
              class="listing-card"
              @click="goToListing(purchase.listing.id)"
            >
              <div class="listing-image-wrap">
                <img
                  v-if="purchase.listing.images?.length"
                  :src="`${API_URL}/storage/${purchase.listing.images?.[0]?.image_path || ''}`"
                  :alt="purchase.listing.title"
                  class="listing-card-image"
                >

                <div v-else class="no-image">
                  No image
                </div>

                <div v-if="purchase.listing.status === 'sold'" class="sold-badge">
                  SOLD
                </div>
              </div>

              <div class="listing-card-info">
                <h3>{{ purchase.listing.title }}</h3>
                <p>{{ purchase.listing.category }}</p>
                <span>{{ formatPrice(purchase.listing.price) }}</span>
                <small class="review-card-note">Purchased item</small>
              </div>
            </div>

            <p v-if="!purchasedItems.length && !loadingTab" class="empty-text">
              No purchases yet.
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
                  No image
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

          <p v-else-if="!loadingTab" class="empty-text">
            {{ emptyMessage }}
          </p>
        </section>
      </main>
    </div>

    <div
      v-if="isReviewModalOpen && selectedPurchase"
      class="modal-overlay"
    >
      <div class="review-modal">
        <button
          type="button"
          class="modal-close"
          @click="closeReviewModal"
        >
          ×
        </button>

        <div class="review-modal-layout">
          <div class="review-form-side">
            <h2>Write review</h2>

            <label class="modal-label">Rating</label>

            <div class="star-rating">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                class="star-btn"
                :class="{ active: star <= reviewRating }"
                @click.stop="reviewRating = star"
              >
                ★
              </button>
            </div>

            <label class="modal-label">Review</label>

            <textarea
              v-model="reviewText"
              class="modal-textarea"
              placeholder="Write your review about the seller..."
            ></textarea>

            <p v-if="reviewError" class="field-error">
              {{ reviewError }}
            </p>

            <p v-if="reviewSuccess" class="purchase-success">
              {{ reviewSuccess }}
            </p>

            <button
              type="button"
              class="modal-submit-btn"
              @click="submitReview"
            >
              Submit review
            </button>
          </div>

          <aside class="review-listing-side">
            <h3>Purchased listing</h3>

            <img
              v-if="selectedPurchase.listing.images?.length"
              :src="`${API_URL}/storage/${selectedPurchase.listing.images?.[0]?.image_path || ''}`"
              :alt="selectedPurchase.listing.title"
            >

            <div v-else class="no-image">
              No image
            </div>

            <h4>{{ selectedPurchase.listing.title }}</h4>
            <p>{{ selectedPurchase.listing.category }}</p>
            <strong>{{ formatPrice(selectedPurchase.listing.price) }}</strong>
          </aside>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { clearAuth, getCurrentUser, type AuthUser, type Listing } from '../services/auth'

type PurchaseReview = {
  id: number
  rating: number
  text: string
  created_at: string
}

type PurchaseItem = {
  id: number
  listing: Listing
  seller: {
    id: number
    name: string
    display_name: string | null
  }
  already_reviewed: boolean
  review?: PurchaseReview | null
}

const router = useRouter()
const route = useRoute()

const user = ref<AuthUser | null>(null)

const sellingListings = ref<Listing[]>([])
const favoriteListings = ref<Listing[]>([])
const likedListings = ref<Listing[]>([])
const purchasedItems = ref<PurchaseItem[]>([])

const selectedPurchase = ref<PurchaseItem | null>(null)
const isReviewModalOpen = ref(false)
const reviewText = ref('')
const reviewRating = ref(5)
const reviewError = ref('')
const reviewSuccess = ref('')

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

const reviewedCount = computed(() => {
  return purchasedItems.value.filter((purchase) => purchase.already_reviewed).length
})

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

const loadPurchases = async () => {
  loadingTab.value = true

  try {
    const response = await fetchWithAuth(`${API_URL}/api/me/purchases`, {
      method: 'GET',
    })

    const data = await response.json()

    if (response.ok) {
      purchasedItems.value = Array.isArray(data) ? data : []
    }
  } catch (err) {
    console.error('Load purchases error:', err)
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
  await loadPurchases()
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

  if (tab === 'purchases' || tab === 'reviews') {
    await loadPurchases()
  }
}

const openReviewModal = (purchase: PurchaseItem) => {
  selectedPurchase.value = purchase
  reviewText.value = ''
  reviewRating.value = 5
  reviewError.value = ''
  reviewSuccess.value = ''
  isReviewModalOpen.value = true
}

const closeReviewModal = () => {
  isReviewModalOpen.value = false
  selectedPurchase.value = null
}

const submitReview = async () => {
  reviewError.value = ''
  reviewSuccess.value = ''

  if (!selectedPurchase.value) return

  if (!selectedPurchase.value.listing?.id) {
    reviewError.value = 'Listing was not found for this purchase.'
    return
  }

  if (!reviewText.value.trim() || reviewText.value.trim().length < 5) {
    reviewError.value = 'Review must be at least 5 characters.'
    return
  }

  try {
    const response = await fetchWithAuth(`${API_URL}/api/reviews`, {
      method: 'POST',
      body: JSON.stringify({
        listing_id: selectedPurchase.value.listing.id,
        rating: reviewRating.value,
        text: reviewText.value.trim(),
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      reviewError.value = data.message || 'Failed to submit review'
      return
    }

    reviewSuccess.value = 'Review submitted successfully.'
    selectedPurchase.value.already_reviewed = true
    selectedPurchase.value.review = {
      id: data.id,
      rating: data.rating,
      text: data.text,
      created_at: data.created_at,
    }

    setTimeout(() => {
      closeReviewModal()
    }, 800)
  } catch (err) {
    console.error('Review submit error:', err)
    reviewError.value = 'Server connection error'
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

    if (activeTab.value === 'purchases' || activeTab.value === 'reviews') {
      await loadPurchases()
    }
  }
)

onMounted(() => {
  activeTab.value = normalizeTab(route.query.tab)
  loadAccount()
})

const formatPrice = (price: number | string) => {
  return `${Math.round(Number(price))} €`
}
</script>
