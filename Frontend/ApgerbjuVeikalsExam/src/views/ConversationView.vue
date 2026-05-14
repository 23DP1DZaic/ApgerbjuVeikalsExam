<template>
  <div class="messages-page">
    <div class="conversation-layout">
      <div class="chat-card">
        <div class="chat-header">
          <button class="back-btn" @click="router.push('/messages')">
            ← Back
          </button>

        <div v-if="conversation" class="chat-header-content">
        <div class="chat-listing-preview">
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
            <!-- <p>User</p> -->
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

        <p v-if="loading" class="loading-text">Loading...</p>
        <p v-if="error" class="error">{{ error }}</p>

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
              <strong v-if="isOfferMessage(message.body)">
                Offer
              </strong>

              <p>{{ message.body }}</p>

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
import { getUser } from '../services/auth'

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

const route = useRoute()
const router = useRouter()
const currentUser = getUser()

const conversation = ref<Conversation | null>(null)
const loading = ref(true)
const sending = ref(false)
const error = ref('')
const newMessage = ref('')

const listingImage = computed(() => {
  const imagePath = conversation.value?.listing?.images?.[0]?.image_path

  if (!imagePath) return ''

  return `${API_URL}/storage/${imagePath}`
})

const loadConversation = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetchWithAuth(
      `${API_URL}/api/conversations/${route.params.id}`,
      {
        headers: {
          Accept: 'application/json',
        },
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
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
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
  return body.toLowerCase().startsWith('offer:')
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