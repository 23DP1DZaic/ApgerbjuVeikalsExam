<template>
  <div class="settings-page">
    <div class="settings-layout">
      <SettingsSidebar />

      <main class="settings-main">
        <div class="settings-card">
          <h1>Profile Settings</h1>
          <p>Edit your public profile</p>

            <div class="profile-preview">
            <img
                v-if="user?.avatar_url"
                :src="user.avatar_url"
                alt="Avatar"
                class="profile-avatar"
            >
            <div v-else class="profile-avatar placeholder">
                {{ userInitial }}
            </div>

            <div>
                <h2>{{ user?.display_name || user?.name }}</h2>
                <p>{{ user?.bio || 'No bio yet' }}</p>
            </div>
            </div>

          <form @submit.prevent="saveProfile">
            <div class="form-group">
              <label>Display name</label>
              <input v-model="form.display_name" required>
            </div>

            <div class="form-group">
              <label>Bio</label>
              <textarea v-model="form.bio" rows="4"></textarea>
            </div>

            <div class="form-group">
              <label>Avatar</label>
              <input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @change="handleAvatarChange"
              >
            </div>

            <button type="submit" class="auth-button" :disabled="loading">
              <p>{{ loading ? 'Saving...' : 'Save Profile' }}</p>
            </button>

            <p v-if="message" class="success">{{ message }}</p>
            <p v-if="error" class="error">{{ error }}</p>
          </form>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import SettingsSidebar from '../components/SettingsSidebar.vue'
import { API_URL, fetchWithAuth } from '../services/api'
import { getCurrentUser, setUser, type AuthUser } from '../services/auth'

const user = ref<AuthUser | null>(null)
const loading = ref(false)
const message = ref('')
const error = ref('')
const avatarFile = ref<File | null>(null)

const form = reactive({
  display_name: '',
  bio: '',
})

const userInitial = computed(() => {
  const value = user.value?.display_name || user.value?.name || '?'
  return value.charAt(0).toUpperCase()
})

const loadProfile = async () => {
  user.value = await getCurrentUser()

  if (user.value) {
    form.display_name = user.value.display_name || user.value.name || ''
    form.bio = user.value.bio || ''
  }
}

const handleAvatarChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files || !input.files[0]) return
  avatarFile.value = input.files[0]
}

const saveProfile = async () => {
  message.value = ''
  error.value = ''
  loading.value = true

  try {
    const formData = new FormData()
    formData.append('display_name', form.display_name)
    formData.append('bio', form.bio)

    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value)
    }

    const response = await fetchWithAuth(`${API_URL}/api/profile`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-HTTP-Method-Override': 'PUT',
      },
      body: formData,
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to update profile'
      return
    }

    user.value = data.user
    setUser(data.user)
    message.value = data.message || 'Profile updated successfully'
    avatarFile.value = null
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  } finally {
    loading.value = false
  }
}

onMounted(loadProfile)
</script>