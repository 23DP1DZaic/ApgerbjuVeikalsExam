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

  <div v-if="listing.status === 'sold'" class="sold-badge detail-sold-badge">
    SOLD
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
        <button
          v-if="listing.user"
          type="button"
          class="seller-profile-card"
          @click="openSellerProfile"
        >
          <img
            v-if="listing.user.avatar_url"
            :src="listing.user.avatar_url"
            alt="Seller avatar"
            class="seller-profile-avatar"
          >

          <div v-else class="seller-profile-avatar seller-profile-placeholder">
            {{ sellerInitial }}
          </div>

          <div class="seller-profile-info">
            <span>Seller</span>
            <strong>{{ listing.user.display_name || listing.user.name }}</strong>
          </div>
        </button>

        <h2>{{ listing.brand || 'Unknown brand' }}</h2>

        <p class="meta">
        <p><strong>Condition:</strong> {{ formatText(listing.condition) }}</p>
        </p>

        <div class="price-row">
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

<div v-if="isSold" class="sold-info-box">
  SOLD
</div>

<div v-else-if="isOwnListing" class="owner-listing-actions">
  <button
    type="button"
    class="delete-listing-detail-btn"
    @click="deleteListing"
  >
    Delete listing
  </button>

  <div class="change-price-box">
    <label>Change price</label>

    <div class="change-price-row">
      <input
        v-model="newPrice"
        type="text"
        inputmode="numeric"
        placeholder="New price"
        :class="{ 'input-error': priceUpdateError }"
        @input="newPrice = newPrice.replace(/\D/g, '')"
      >

      <button
        type="button"
        @click="updatePrice"
      >
        Save
      </button>
    </div>

    <p v-if="priceUpdateError" class="change-price-message error">
      {{ priceUpdateError }}
    </p>

    <p v-if="priceUpdateSuccess" class="change-price-message success">
      {{ priceUpdateSuccess }}
    </p>
  </div>
</div>

<template v-else>
  <button class="purchase-btn" @click="goToPurchase">
    Purchase
  </button>

  <button class="secondary-btn" @click="openOfferModal">
    Offer
  </button>

  <button class="secondary-btn" @click="openMessageModal">
    Message
  </button>
</template>

        <div class="details-section">
          <h3>Seller Description</h3>
          <p>{{ listing.description }}</p>
        </div>

        <div class="details-section">
          <h3>Details</h3>
          <p><strong>Color:</strong> {{ listing.color || 'Not specified' }}</p>
          <p><strong>Size:</strong> {{ listing.size || 'Not specified' }}</p>
          <p><strong>Condition:</strong> {{ formatText(listing.condition) }}</p>
          <p><strong>Category:</strong> {{ listing.category }}</p>
        </div>
      </aside>
    </div>
  </div>

<div
  v-if="isMessageModalOpen"
  class="modal-overlay"
  @click.self="closeModals"
>
  <div class="message-modal">
    <button
      type="button"
      class="modal-close"
      @click="closeModals"
    >
      ×
    </button>

    <h2>Ask A Question</h2>

    <div class="modal-listing-preview" v-if="listing">
      <img
        v-if="imageUrls.length"
        :src="imageUrls[0]"
        :alt="listing.title"
      >

      <div class="modal-listing-info">
        <strong>{{ listing.brand || 'Unknown brand' }}</strong>
        <p>{{ listing.title }}</p>
        <span>{{ formatPrice(listing.price) }}</span>
      </div>
    </div>

    <label class="modal-label">Send a message</label>

    <textarea
      v-model="messageText"
      class="modal-textarea"
      placeholder="Send a message to request more details or discuss price."
    ></textarea>

    <p v-if="modalError" class="error">{{ modalError }}</p>
    <p v-if="modalSuccess" class="purchase-success">
      {{ modalSuccess }}
    </p>

    <button
      class="modal-submit-btn"
      :disabled="isSendingMessage || !messageText.trim()"
      @click="sendMessage"
    >
      {{ isSendingMessage ? 'Sending...' : 'Send Message' }}
    </button>
  </div>
</div>

<div
  v-if="isOfferModalOpen"
  class="modal-overlay"
  @click.self="closeModals"
>
  <div class="message-modal offer-modal">
    <button
      type="button"
      class="modal-close"
      @click="closeModals"
    >
      ×
    </button>

    <h2>Make an Offer</h2>

    <div class="modal-listing-preview" v-if="listing">
      <img
        v-if="imageUrls.length"
        :src="imageUrls[0]"
        :alt="listing.title"
      >

      <div class="modal-listing-info">
        <strong>{{ listing.brand || 'Unknown brand' }}</strong>
        <p>{{ listing.title }}</p>
        <span>{{ formatPrice(listing.price) }}</span>
      </div>
    </div>

    <label class="modal-label">Offer Price</label>

    <div class="offer-input-wrapper">
      <span>€</span>

      <input
        v-model="offerPrice"
        type="text"
        inputmode="numeric"
        placeholder="0"
        @input="onlyOfferNumbers"
      >
    </div>

    <p class="modal-help">
      The seller can accept or decline your offer.
    </p>

    <p v-if="modalError" class="error">{{ modalError }}</p>
    <p v-if="modalSuccess" class="purchase-success">
      {{ modalSuccess }}
    </p>

    <button
      class="modal-submit-btn"
      :disabled="isSendingMessage || !offerPrice"
      @click="sendOffer"
    >
      {{ isSendingMessage ? 'Sending...' : 'Send Offer' }}
    </button>
  </div>
</div>

</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getToken } from '../services/auth'
import { getUser } from '../services/auth'

const isMessageModalOpen = ref(false)
const isOfferModalOpen = ref(false)
const isSendingMessage = ref(false)

const messageText = ref('')
const offerPrice = ref('')
const modalError = ref('')
const modalSuccess = ref('')
const newPrice = ref('')
const priceUpdateError = ref('')
const priceUpdateSuccess = ref('')

const openMessageModal = () => {
  modalError.value = ''
  messageText.value = ''
  isMessageModalOpen.value = true
}

const openOfferModal = () => {
  modalError.value = ''
  modalSuccess.value = ''
  offerPrice.value = ''
  isOfferModalOpen.value = true
}

const closeModals = () => {
  isMessageModalOpen.value = false
  isOfferModalOpen.value = false
  modalError.value = ''
  modalSuccess.value = ''
}

const onlyOfferNumbers = () => {
  offerPrice.value = offerPrice.value.replace(/\D/g, '')
}

const startConversation = async () => {
  if (!listing.value) {
    throw new Error('Listing not found')
  }

  const token = getToken()

  if (!token) {
    router.push('/login')
    throw new Error('You need to login first')
  }

  const response = await fetchWithAuth(
    `${API_URL}/api/listings/${listing.value.id}/conversation`,
    {
      method: 'POST',
    }
  )

  const rawText = await response.text()

  let data: any = null

  try {
    data = JSON.parse(rawText)
  } catch {
    data = { message: rawText }
  }

  if (!response.ok) {
    throw new Error(data.message || 'Failed to start conversation')
  }

  return data
}

const sendMessage = async () => {
  modalError.value = ''

  if (!messageText.value.trim()) {
    modalError.value = 'Message cannot be empty'
    return
  }

  isSendingMessage.value = true

  try {
    const conversation = await startConversation()

    const response = await fetchWithAuth(
      `${API_URL}/api/conversations/${conversation.id}/messages`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          body: messageText.value.trim(),
        }),
      }
    )

    const data = await response.json()

    if (!response.ok) {
      modalError.value = data.message || 'Failed to send message'
      return
    }

    closeModals()
    router.push(`/messages/${conversation.id}`)
  } catch (error: any) {
    modalError.value = error.message || 'Failed to send message'
  } finally {
    isSendingMessage.value = false
  }
}

const sendOffer = async () => {
  modalError.value = ''
  modalSuccess.value = ''

  if (!listing.value) return

  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  const amount = Number(offerPrice.value)

  if (!offerPrice.value || Number.isNaN(amount) || amount <= 0) {
    modalError.value = 'Enter a valid offer price.'
    return
  }

  if (amount >= Number(listing.value.price)) {
    modalError.value = 'Offer must be lower than current price.'
    return
  }

  isSendingMessage.value = true

  try {
    const offerResponse = await fetchWithAuth(`${API_URL}/api/listings/${listing.value.id}/offers`, {
      method: 'POST',
      body: JSON.stringify({
        amount,
      }),
    })

    const offerData = await offerResponse.json()

    if (!offerResponse.ok) {
      modalError.value = offerData.message || 'Failed to send offer.'
      return
    }

    const conversation = await startConversation()

    const messageResponse = await fetchWithAuth(
      `${API_URL}/api/conversations/${conversation.id}/messages`,
      {
        method: 'POST',
        body: JSON.stringify({
          body: `__OFFER__:${offerData.id}`,
        }),
      }
    )

    const messageData = await messageResponse.json()

    if (!messageResponse.ok) {
      modalError.value = messageData.message || 'Offer was created, but message was not sent.'
      return
    }

    modalSuccess.value = 'Offer sent. Seller has 24 hours to accept or decline.'
    offerPrice.value = ''

    setTimeout(() => {
      closeModals()
      router.push(`/messages/${conversation.id}`)
    }, 900)
  } catch (error) {
    console.error('Offer error:', error)
    modalError.value = 'Server connection error.'
  } finally {
    isSendingMessage.value = false
  }
}

type ListingImage = {
  id: number
  image_path: string
}

type ListingUser = {
  id: number
  name: string
  display_name: string | null
  avatar_url: string | null
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
  status?: 'available' | 'sold' | string
  user?: ListingUser | null
  images: ListingImage[]
  likes_count?: number
  favorites_count?: number
  liked_by_me?: boolean
  favorited_by_me?: boolean
  user_id: number
  original_price?: number | string | null
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

const formatText = (value?: string | null) => {
  if (!value) return 'Not specified'

  return value.charAt(0).toUpperCase() + value.slice(1)
}


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


const goToPurchase = () => {
  if (!listing.value?.id) return

  router.push(`/listing/${listing.value.id}/purchase`)
}

const sellerInitial = computed(() => {
  const value = listing.value?.user?.display_name || listing.value?.user?.name || '?'
  return value.charAt(0).toUpperCase()
})

const openSellerProfile = () => {
  if (!listing.value?.user?.id) return

  router.push(`/users/${listing.value.user.id}`)
}

const currentUser = computed(() => {
  return getUser()
})

const isOwnListing = computed(() => {
  if (!listing.value || !currentUser.value) return false

  return listing.value.user_id === currentUser.value.id
})

const isSold = computed(() => {
  return listing.value?.status === 'sold'
})

const deleteListing = async () => {
  if (!listing.value) return

  if (!confirm('Delete this listing?')) return

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${listing.value.id}`, {
      method: 'DELETE',
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to delete listing')
      return
    }

    router.push('/account?tab=listings')
  } catch (err) {
    console.error('Delete listing error:', err)
    alert('Server connection error')
  }
}

const updatePrice = async () => {
  priceUpdateError.value = ''
  priceUpdateSuccess.value = ''

  if (!listing.value) return

  const price = Number(newPrice.value)

  if (!newPrice.value || Number.isNaN(price) || price <= 0) {
    priceUpdateError.value = 'Enter a valid price.'
    return
  }

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings/${listing.value.id}`, {
      method: 'PUT',
      body: JSON.stringify({
        price,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      priceUpdateError.value = data.message || 'Failed to update price.'
      return
    }

    listing.value.price = data.price
    listing.value.original_price = data.original_price
    newPrice.value = ''
    priceUpdateSuccess.value = 'Price updated successfully.'
  } catch (err) {
    console.error('Update price error:', err)
    priceUpdateError.value = 'Server connection error.'
  }
}

const formatPrice = (price: number | string) => {
  return `${Math.round(Number(price))} €`
}

</script>