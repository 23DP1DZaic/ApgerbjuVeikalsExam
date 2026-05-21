<template>
  <div class="messages-page">
    <div class="messages-layout">
      <div class="messages-card">
        <h1>Messages</h1>

        <p v-if="loading" class="loading-text">
          Loading...
        </p>

        <p v-if="error" class="error">
          {{ error }}
        </p>

        <div
          v-if="!loading && conversations.length"
          class="conversation-list"
        >
          <div
            v-for="conversation in conversations"
            :key="conversation.id"
            class="conversation-item"
            :class="{ unread: Number(conversation.unread_count || 0) > 0 }"
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
                <h3 class="conversation-listing-title">
                  {{ conversation.listing?.title || 'Deleted listing' }}
                </h3>

                <div class="conversation-meta">
                  <span>
                    {{ formatDate(getLastMessageDate(conversation)) }}
                  </span>

                  <span
                    v-if="Number(conversation.unread_count || 0) > 0"
                    class="chat-unread-badge"
                  >
                    {{ Number(conversation.unread_count || 0) > 99 ? '99+' : conversation.unread_count }}
                  </span>
                </div>
              </div>

              <div class="conversation-user-row">
                <div class="conversation-avatar">
                  <img
                    v-if="getOtherUserAvatar(conversation)"
                    :src="getOtherUserAvatar(conversation)"
                    :alt="getOtherUserName(conversation)"
                  >

                  <span v-else>
                    {{ getOtherUserInitial(conversation) }}
                  </span>
                </div>

                <div class="conversation-message-content">
                  <p class="conversation-user-name">
                    {{ getOtherUserName(conversation) }}:
                  </p>

                  <p class="conversation-last-message">
                    {{ formatLastMessage(getLastMessageBody(conversation)) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <p
          v-if="!loading && !conversations.length && !error"
          class="empty-text"
        >
          No messages yet.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getToken, getUser } from '../services/auth'

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
  body: string
  created_at: string
  sender_id?: number
  sender?: User
}

type Conversation = {
  id: number
  listing?: Listing | null
  buyer?: User | null
  seller?: User | null
  latest_message?: Message | null
  last_message?: Message | null
  unread_count?: number
  messages_max_created_at?: string | null
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

  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await fetchWithAuth(`${API_URL}/api/conversations`, {
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
      error.value = data.message || 'Failed to load messages'
      conversations.value = []
      return
    }

    conversations.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Load conversations error:', err)
    error.value = 'Server connection error'
    conversations.value = []
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

const getOtherUser = (conversation: Conversation) => {
  if (!currentUser) return null

  return conversation.buyer?.id === currentUser.id
    ? conversation.seller
    : conversation.buyer
}

const getOtherUserName = (conversation: Conversation) => {
  const otherUser = getOtherUser(conversation)

  return otherUser?.display_name || otherUser?.name || 'User'
}

const getOtherUserAvatar = (conversation: Conversation) => {
  const otherUser = getOtherUser(conversation)

  return otherUser?.avatar_url || ''
}

const getOtherUserInitial = (conversation: Conversation) => {
  return getOtherUserName(conversation).charAt(0).toUpperCase()
}

const getLastMessage = (conversation: Conversation) => {
  return conversation.last_message || conversation.latest_message || null
}

const getLastMessageBody = (conversation: Conversation) => {
  return getLastMessage(conversation)?.body || 'No messages yet'
}

const getLastMessageDate = (conversation: Conversation) => {
  return (
    conversation.messages_max_created_at ||
    getLastMessage(conversation)?.created_at ||
    conversation.updated_at ||
    ''
  )
}

const formatLastMessage = (body: string) => {
  if (!body) return ''

  if (body.startsWith('__OFFER__:')) {
    return 'Offer sent'
  }

  return body
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