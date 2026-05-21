<template>
  <div class="settings-page">
    <div class="settings-layout">
      <SettingsSidebar />

      <main class="settings-main">
        <div class="settings-card">
          <h1>{{ st.accountSettings }}</h1>
          <p>{{ st.privateAccountSettings }}</p>

          <form @submit.prevent="updateAccount">
            <div class="form-group">
              <label>{{ st.accountName }}</label>
              <input
                v-model="form.name"
                required
              >
            </div>

            <div class="form-group">
              <label>{{ st.email }}</label>
              <input
                v-model="form.email"
                type="email"
                required
              >
            </div>

            <div class="form-group">
              <label>{{ st.newPassword }}</label>
              <input
                v-model="form.password"
                type="password"
                :placeholder="st.passwordPlaceholder"
              >
            </div>

            <div class="form-group">
              <label>{{ st.confirmPassword }}</label>
              <input
                v-model="form.password_confirmation"
                type="password"
              >
            </div>

            <button
              type="submit"
              class="auth-button"
              :disabled="loading"
            >
              <p>{{ loading ? st.saving : st.saveAccount }}</p>
            </button>

            <p v-if="message" class="success">
              {{ message }}
            </p>

            <p v-if="error" class="error">
              {{ error }}
            </p>
          </form>

          <hr class="account-divider">

          <div class="danger-zone">
            <h2>{{ st.accountManagement }}</h2>
            <p>{{ st.deleteWarning }}</p>

            <button
              class="delete-account-btn"
              :disabled="deleting"
              @click="deleteAccount"
            >
              {{ deleting ? st.deleting : st.deleteAccount }}
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import SettingsSidebar from '../components/SettingsSidebar.vue'
import { API_URL, fetchWithAuth } from '../services/api'
import { clearAuth, getCurrentUser, setUser } from '../services/auth'

type Language = 'en' | 'lv'

const router = useRouter()

const loading = ref(false)
const deleting = ref(false)
const message = ref('')
const error = ref('')

const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const settingsTranslations = {
  en: {
    accountSettings: 'Account Settings',
    privateAccountSettings: 'Private account settings',
    accountName: 'Account name',
    email: 'Email',
    newPassword: 'New password',
    confirmPassword: 'Confirm password',
    passwordPlaceholder: "Leave empty if you don't want to change it",
    saveAccount: 'Save Account',
    saving: 'Saving...',
    accountUpdated: 'Account updated successfully',
    updateError: 'Failed to update account',
    serverError: 'Server connection error',
    accountManagement: 'Account Management',
    deleteWarning: 'This action permanently deletes your account and listings.',
    deleteAccount: 'Delete account',
    deleting: 'Deleting...',
    deleteConfirm: 'Are you sure you want to delete your account?',
    deleteError: 'Failed to delete account',
  },

  lv: {
    accountSettings: 'Konta iestatījumi',
    privateAccountSettings: 'Privāti konta iestatījumi',
    accountName: 'Konta vārds',
    email: 'E-pasts',
    newPassword: 'Jaunā parole',
    confirmPassword: 'Apstiprini paroli',
    passwordPlaceholder: 'Atstāj tukšu, ja nevēlies mainīt paroli',
    saveAccount: 'Saglabāt kontu',
    saving: 'Saglabā...',
    accountUpdated: 'Konts veiksmīgi atjaunināts',
    updateError: 'Neizdevās atjaunināt kontu',
    serverError: 'Servera savienojuma kļūda',
    accountManagement: 'Konta pārvaldība',
    deleteWarning: 'Šī darbība neatgriezeniski dzēsīs tavu kontu un sludinājumus.',
    deleteAccount: 'Dzēst kontu',
    deleting: 'Dzēš...',
    deleteConfirm: 'Vai tiešām vēlies dzēst savu kontu?',
    deleteError: 'Neizdevās dzēst kontu',
  },
}

const st = computed(() => settingsTranslations[language.value])

const updateLanguage = () => {
  language.value = (localStorage.getItem('language') as Language) || 'en'
}

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
      error.value = data.message || st.value.updateError
      return
    }

    setUser(data.user)
    message.value = data.message || st.value.accountUpdated
    form.password = ''
    form.password_confirmation = ''
  } catch (err) {
    console.error(err)
    error.value = st.value.serverError
  } finally {
    loading.value = false
  }
}

const deleteAccount = async () => {
  if (!confirm(st.value.deleteConfirm)) return

  deleting.value = true
  error.value = ''
  message.value = ''

  try {
    const response = await fetchWithAuth(`${API_URL}/api/me`, {
      method: 'DELETE',
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || st.value.deleteError
      return
    }

    clearAuth()
    router.push('/register')
  } catch (err) {
    console.error(err)
    error.value = st.value.serverError
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  loadAccount()
  window.addEventListener('language-changed', updateLanguage)
})

onBeforeUnmount(() => {
  window.removeEventListener('language-changed', updateLanguage)
})
</script>