<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Login</h1>
      <p>Sign in to your account</p>

      <form @submit.prevent="login">
        <div class="form-group">
          <label>Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="Enter your email"
            required
          >
        </div>

        <div class="form-group">
          <label>Password</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="Enter your password"
            required
          >
        </div>

        <button type="submit" class="auth-button">
          <p>Login</p>
        </button>

        <p v-if="message" class="success">{{ message }}</p>
        <p v-if="error" class="error">{{ error }}</p>
      </form>

      <p class="auth-link">
        Don't have an account?
        <router-link to="/register">Register</router-link>
      </p>
    </div>
  </div>
</template>



<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL } from '../services/api'
import { saveAuth } from '../services/auth'

const router = useRouter()

const form = reactive({
  email: '',
  password: '',
})

const message = ref('')
const error = ref('')

const login = async () => {
  message.value = ''
  error.value = ''

  try {
    const response = await fetch(`${API_URL}/api/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        email: form.email,
        password: form.password,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Login failed'
      return
    }

    if (!data.token) {
      error.value = 'Token not received from server'
      return
    }

    saveAuth(data.user, data.token)

    message.value = 'Login successful'

    router.push('/')

    setTimeout(() => {
      window.location.reload()
    }, 100)
  } catch (err) {
    console.error('Login error:', err)
    error.value = 'Server connection error'
  }
}
</script>