<template>
  <div class="settings-page">
    <div class="settings-layout">
      <SettingsSidebar />

      <main class="settings-main">
        <div class="settings-card">
          <h1>Account Settings</h1>
          <p>Private account settings</p>

          <form @submit.prevent="updateAccount">
            <div class="form-group">
              <label>Account name</label>
              <input v-model="form.name" required>
            </div>

            <div class="form-group">
              <label>Email</label>
              <input v-model="form.email" type="email" required>
            </div>

            <div class="form-group">
              <label>New password</label>
              <input
                v-model="form.password"
                type="password"
                placeholder="Leave empty if you don't want to change it"
              >
            </div>

            <div class="form-group">
              <label>Confirm password</label>
              <input
                v-model="form.password_confirmation"
                type="password"
              >
            </div>

            <button type="submit" class="auth-button" :disabled="loading">
              <p>{{ loading ? 'Saving...' : 'Save Account' }}</p>
            </button>

            <p v-if="message" class="success">{{ message }}</p>
            <p v-if="error" class="error">{{ error }}</p>
          </form>

          <hr class="account-divider">

          <div class="danger-zone">
            <h2>Danger zone</h2>
            <p>This action permanently deletes your account and listings.</p>

            <button
              class="delete-account-btn"
              :disabled="deleting"
              @click="deleteAccount"
            >
              {{ deleting ? 'Deleting...' : 'Delete account' }}
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SettingsSidebar from '../components/SettingsSidebar.vue'
import { API_URL, fetchWithAuth } from '../services/api'
import { clearAuth, getCurrentUser, setUser } from '../services/auth'

const router = useRouter()

const loading = ref(false)
const deleting = ref(false)
const message = ref('')
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const loadAccount = async () => {
  const user = await getCurrentUser()

  if (!user) {
    router.push('/login')
    return
  }

  form.name = user.name || ''
  form.email = user.email || ''
}

const updateAccount = async () => {
  message.value = ''
  error.value = ''
  loading.value = true

  try {
    const payload: Record<string, string> = {
      name: form.name,
      email: form.email,
    }

    if (form.password) {
      payload.password = form.password
      payload.password_confirmation = form.password_confirmation
    }

    const response = await fetchWithAuth(`${API_URL}/api/me`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to update account'
      return
    }

    setUser(data.user)
    message.value = data.message || 'Account updated successfully'
    form.password = ''
    form.password_confirmation = ''
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  } finally {
    loading.value = false
  }
}

const deleteAccount = async () => {
  if (!confirm('Are you sure you want to delete your account?')) return

  deleting.value = true
  error.value = ''
  message.value = ''

  try {
    const response = await fetchWithAuth(`${API_URL}/api/me`, {
      method: 'DELETE',
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to delete account'
      return
    }

    clearAuth()
    router.push('/register')
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  } finally {
    deleting.value = false
  }
}

onMounted(loadAccount)
</script>
