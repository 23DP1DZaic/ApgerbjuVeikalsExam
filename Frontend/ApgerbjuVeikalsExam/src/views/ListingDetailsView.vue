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
        <p><strong>Condition:</strong> {{ formatText(listing.condition) }}</p>
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

          <button
            class="secondary-btn"
            @click="openOfferModal"
          >
            Offer
          </button>

          <button
            class="secondary-btn"
            @click="openMessageModal"
          >
            Message
          </button>

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
        <span>{{ listing.price }} €</span>
      </div>
    </div>

    <label class="modal-label">Send a message</label>

    <textarea
      v-model="messageText"
      class="modal-textarea"
      placeholder="Send a message to request more details or discuss price."
    ></textarea>

    <p v-if="modalError" class="error">{{ modalError }}</p>

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
        <span>{{ listing.price }} €</span>
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

const isMessageModalOpen = ref(false)
const isOfferModalOpen = ref(false)
const isSendingMessage = ref(false)

const messageText = ref('')
const offerPrice = ref('')
const modalError = ref('')

const openMessageModal = () => {
  modalError.value = ''
  messageText.value = ''
  isMessageModalOpen.value = true
}

const openOfferModal = () => {
  modalError.value = ''
  offerPrice.value = ''
  isOfferModalOpen.value = true
}

const closeModals = () => {
  isMessageModalOpen.value = false
  isOfferModalOpen.value = false
  modalError.value = ''
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

  const price = Number(offerPrice.value)

  if (!price || price <= 0) {
    modalError.value = 'Offer price must be valid'
    return
  }

  if (listing.value && price >= Number(listing.value.price)) {
    modalError.value = 'Offer must be lower than listing price'
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
          body: `Offer: ${price} €`,
        }),
      }
    )

    const data = await response.json()

    if (!response.ok) {
      modalError.value = data.message || 'Failed to send offer'
      return
    }

    closeModals()
    router.push(`/messages/${conversation.id}`)
  } catch (error: any) {
    modalError.value = error.message || 'Failed to send offer'
  } finally {
    isSendingMessage.value = false
  }
}

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

</script>