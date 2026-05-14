<template>
  <div class="messages-page">
    <div class="messages-layout">
      <div class="messages-card">
        <h1>Messages</h1>

        <p v-if="loading" class="loading-text">Loading...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading && !conversations.length" class="empty-text">
          No messages yet.
        </div>

        <div v-if="!loading && conversations.length" class="conversation-list">
          <div
            v-for="conversation in conversations"
            :key="conversation.id"
            class="conversation-item"
            @click="openConversation(conversation.id)"
          >
            <div class="conversation-image">
              <img
                v-if="getListingImage(conversation)"
                :src="getListingImage(conversation)"
                :alt="conversation.listing?.title || 'Listing'"
              >

              <div v-else class="no-image">
                No image
              </div>
            </div>

            <div class="conversation-info">
              <div class="conversation-top">
                <h3>
                  {{ getOtherUserName(conversation) }}
                </h3>

                <span>
                  {{ formatDate(conversation.updated_at) }}
                </span>
              </div>

              <p class="conversation-listing">
                {{ conversation.listing?.title || 'Deleted listing' }}
              </p>

              <p class="conversation-last-message">
                {{ conversation.latest_message?.body || 'No messages yet' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getUser } from '../services/auth'

type User = {
  id: number
  name: string
  display_name?: string | null
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
  body: string
  created_at: string
  sender?: User
}

type Conversation = {
  id: number
  listing?: Listing | null
  buyer?: User | null
  seller?: User | null
  latest_message?: Message | null
  updated_at: string
}

const router = useRouter()
const currentUser = getUser()

const conversations = ref<Conversation[]>([])
const loading = ref(true)
const error = ref('')

const loadConversations = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetchWithAuth(`${API_URL}/api/conversations`, {
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
      error.value = data.message || 'Failed to load messages'
      return
    }

    conversations.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Load conversations error:', err)
    error.value = 'Server connection error'
  } finally {
    loading.value = false
  }
}

const openConversation = (id: number) => {
  router.push(`/messages/${id}`)
}

const getListingImage = (conversation: Conversation) => {
  const imagePath = conversation.listing?.images?.[0]?.image_path

  if (!imagePath) return ''

  return `${API_URL}/storage/${imagePath}`
}

const getOtherUserName = (conversation: Conversation) => {
  if (!currentUser) return 'User'

  const otherUser =
    conversation.buyer?.id === currentUser.id
      ? conversation.seller
      : conversation.buyer

  return otherUser?.display_name || otherUser?.name || 'User'
}

const formatDate = (value: string) => {
  if (!value) return ''

  return new Intl.DateTimeFormat('lv-LV', {
    timeZone: 'Europe/Riga',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(new Date(value))
}

onMounted(loadConversations)
</script>