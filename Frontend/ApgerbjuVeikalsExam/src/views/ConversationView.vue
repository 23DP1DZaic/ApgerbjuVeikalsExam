<template>
  <div class="messages-page">
    <div class="conversation-layout">
      <div class="chat-card">
        <div class="chat-header">
          <button class="back-btn" @click="router.push('/messages')">
            ← Back
          </button>

          <div v-if="conversation" class="chat-header-content">
            <div
              class="chat-listing-preview clickable-listing-preview"
              @click="openListing"
            >
              <img
                v-if="listingImage"
                :src="listingImage"
                :alt="conversation.listing?.title || 'Listing'"
              >

              <div v-else class="chat-listing-placeholder">
                No image
              </div>

              <div>
                <h2>{{ conversation.listing?.title || 'Deleted listing' }}</h2>
                <p>Listing</p>
              </div>
            </div>

            <div class="chat-user-preview">
              <div class="chat-user-text">
                <h3>{{ getOtherUserName() }}</h3>
              </div>

              <div class="chat-user-avatar">
                <img
                  v-if="getOtherUserAvatar()"
                  :src="getOtherUserAvatar()"
                  :alt="getOtherUserName()"
                >

                <span v-else>
                  {{ getOtherUserInitial() }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <p v-if="loading" class="loading-text">
          Loading...
        </p>

        <p v-if="error" class="error">
          {{ error }}
        </p>

        <div v-if="conversation && !loading" class="chat-body">
          <div
            v-for="message in conversation.messages"
            :key="message.id"
            class="chat-message-row"
            :class="{ mine: message.sender_id === currentUser?.id }"
          >
            <div
              class="chat-message"
              :class="{ offer: isOfferMessage(message.body) }"
            >
              <div
                v-if="isOfferMessage(message.body)"
                class="chat-offer-card"
              >
                <template
                  v-for="offer in [getOfferFromMessage(message.body)]"
                  :key="offer?.id || getOfferIdFromMessage(message.body)"
                >
                  <template v-if="offer">
                    <strong>OFFER</strong>

                    <p>
                      Offer:
                      {{ formatPrice(offer.amount) }}
                    </p>

                    <small>
                      Status:
                      {{ formatOfferStatus(offer.status) }}
                    </small>

                    <small
                      v-if="offer.seller_expires_at && offer.status === 'pending'"
                    >
                      Seller has until:
                      {{ formatDateTime(offer.seller_expires_at) }}
                    </small>

                    <small
                      v-if="offer.buyer_expires_at && offer.status === 'accepted'"
                    >
                      Buyer has until:
                      {{ formatDateTime(offer.buyer_expires_at) }}
                    </small>

                    <div
                      v-if="
                        offer.status === 'pending' &&
                        offer.seller?.id === currentUser?.id
                      "
                      class="chat-offer-actions"
                    >
                      <button
                        type="button"
                        class="chat-offer-accept"
                        @click="acceptOffer(offer)"
                      >
                        Accept
                      </button>

                      <button
                        type="button"
                        class="chat-offer-decline"
                        @click="declineOffer(offer)"
                      >
                        Decline
                      </button>
                    </div>

                    <button
                      v-if="
                        offer.status === 'accepted' &&
                        offer.buyer?.id === currentUser?.id
                      "
                      type="button"
                      class="chat-offer-pay"
                      @click="goToOfferPayment(offer)"
                    >
                      Pay now
                    </button>
                  </template>

                  <template v-else>
                    Loading offer...
                  </template>
                </template>
              </div>

              <p v-else>
                {{ message.body }}
              </p>

              <span>{{ formatTime(message.created_at) }}</span>
            </div>
          </div>
        </div>

        <form
          v-if="conversation && !loading"
          class="chat-form"
          @submit.prevent="sendMessage"
        >
          <input
            v-model="newMessage"
            placeholder="Write a message..."
          >

          <button
            type="submit"
            :disabled="sending || !newMessage.trim()"
          >
            {{ sending ? 'Sending...' : 'Send' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getUser, getToken } from '../services/auth'

type User = {
  id: number
  name: string
  display_name?: string | null
  avatar_url?: string | null
}

type ListingImage = {
  id: number
  image_path: string
}

type Listing = {
  id: number
  title: string
  price?: number
  images?: ListingImage[]
}

type Message = {
  id: number
  conversation_id: number
  sender_id: number
  body: string
  created_at: string
  sender?: User
}

type Conversation = {
  id: number
  listing?: Listing | null
  buyer?: User | null
  seller?: User | null
  messages: Message[]
}

type OfferItem = {
  id: number
  amount: number | string
  status: string
  seller_expires_at: string | null
  buyer_expires_at: string | null
  listing: {
    id: number
    title: string
    price: number
    images?: {
      id: number
      image_path: string
    }[]
  }
  buyer?: {
    id: number
    name: string
    display_name: string | null
  }
  seller?: {
    id: number
    name: string
    display_name: string | null
  }
}

const route = useRoute()
const router = useRouter()
const currentUser = getUser()

const conversation = ref<Conversation | null>(null)
const loading = ref(true)
const sending = ref(false)
const error = ref('')
const newMessage = ref('')
const offerCache = ref<Record<number, OfferItem>>({})

const listingImage = computed(() => {
  const imagePath = conversation.value?.listing?.images?.[0]?.image_path

  if (!imagePath) return ''

  return `${API_URL}/storage/${imagePath}`
})

const openListing = () => {
  if (!conversation.value?.listing?.id) return

  router.push(`/listing/${conversation.value.listing.id}`)
}

const loadConversation = async () => {
  loading.value = true
  error.value = ''

  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await fetchWithAuth(
      `${API_URL}/api/conversations/${route.params.id}`,
      {
        method: 'GET',
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
      error.value = data.message || 'Failed to load conversation'
      return
    }

    conversation.value = data

    loadOffersFromMessages()

    window.dispatchEvent(new Event('messages-read'))
  } catch (err) {
    console.error('Load conversation error:', err)
    error.value = 'Server connection error'
  } finally {
    loading.value = false
  }
}

const sendMessage = async () => {
  if (!conversation.value || !newMessage.value.trim()) return

  sending.value = true
  error.value = ''

  try {
    const response = await fetchWithAuth(
      `${API_URL}/api/conversations/${conversation.value.id}/messages`,
      {
        method: 'POST',
        body: JSON.stringify({
          body: newMessage.value.trim(),
        }),
      }
    )

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to send message'
      return
    }

    conversation.value.messages.push(data)

    if (isOfferMessage(data.body)) {
      await loadOffer(getOfferIdFromMessage(data.body))
    }

    newMessage.value = ''
  } catch (err) {
    console.error('Send message error:', err)
    error.value = 'Server connection error'
  } finally {
    sending.value = false
  }
}

const getOtherUser = () => {
  if (!conversation.value || !currentUser) return null

  return conversation.value.buyer?.id === currentUser.id
    ? conversation.value.seller
    : conversation.value.buyer
}

const getOtherUserName = () => {
  const otherUser = getOtherUser()

  return otherUser?.display_name || otherUser?.name || 'User'
}

const getOtherUserAvatar = () => {
  const otherUser = getOtherUser()

  return otherUser?.avatar_url || ''
}

const getOtherUserInitial = () => {
  return getOtherUserName().charAt(0).toUpperCase()
}

const isOfferMessage = (body: string) => {
  return body.startsWith('__OFFER__:')
}

const getOfferIdFromMessage = (body: string) => {
  return Number(body.replace('__OFFER__:', ''))
}

const getOfferFromMessage = (body: string): OfferItem | null => {
  const offerId = getOfferIdFromMessage(body)

  return offerCache.value[offerId] || null
}

const loadOffer = async (offerId: number) => {
  if (!offerId || offerCache.value[offerId]) return

  try {
    const response = await fetchWithAuth(`${API_URL}/api/offers/${offerId}`, {
      method: 'GET',
    })

    const data = await response.json()

    if (response.ok) {
      offerCache.value[offerId] = data
    }
  } catch (err) {
    console.error('Load offer error:', err)
  }
}

const loadOffersFromMessages = () => {
  if (!conversation.value) return

  conversation.value.messages.forEach((message) => {
    if (isOfferMessage(message.body)) {
      loadOffer(getOfferIdFromMessage(message.body))
    }
  })
}

const acceptOffer = async (offer: OfferItem) => {
  try {
    const response = await fetchWithAuth(`${API_URL}/api/offers/${offer.id}/accept`, {
      method: 'POST',
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to accept offer.')
      return
    }

    offerCache.value[offer.id] = data
  } catch (err) {
    console.error('Accept offer error:', err)
    alert('Server connection error.')
  }
}

const declineOffer = async (offer: OfferItem) => {
  try {
    const response = await fetchWithAuth(`${API_URL}/api/offers/${offer.id}/decline`, {
      method: 'POST',
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to decline offer.')
      return
    }

    offerCache.value[offer.id] = data
  } catch (err) {
    console.error('Decline offer error:', err)
    alert('Server connection error.')
  }
}

const goToOfferPayment = (offer: OfferItem) => {
  router.push(`/offers/${offer.id}/pay`)
}

const formatOfferStatus = (status: string) => {
  if (status === 'pending') return 'Pending'
  if (status === 'accepted') return 'Accepted'
  if (status === 'declined') return 'Declined'
  if (status === 'paid') return 'Paid'
  if (status === 'expired') return 'Expired'

  return status
}

const formatPrice = (price: number | string) => {
  return `${Math.round(Number(price))} €`
}

const formatDateTime = (value: string) => {
  return new Date(value).toLocaleString('lv-LV', {
    timeZone: 'Europe/Riga',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatTime = (value: string) => {
  if (!value) return ''

  return new Intl.DateTimeFormat('lv-LV', {
    timeZone: 'Europe/Riga',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}

onMounted(loadConversation)
</script>