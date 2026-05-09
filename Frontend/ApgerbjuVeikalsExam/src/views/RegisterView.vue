<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Register</h1>
      <p>Create your account</p>

      <form @submit.prevent="register">
        <div class="form-group">
          <label>Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Enter your name"
            required
          >
        </div>

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
          <p>Register</p>
        </button>

        <p v-if="message" class="success">{{ message }}</p>
        <p v-if="error" class="error">{{ error }}</p>
      </form>

      <p class="auth-link">
        Already have an account?
        <router-link to="/login">Login</router-link>
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
  name: '',
  email: '',
  password: '',
})

const message = ref('')
const error = ref('')

const register = async () => {
  message.value = ''
  error.value = ''

  try {
    const response = await fetch(`${API_URL}/api/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        name: form.name,
        email: form.email,
        password: form.password,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Registration failed'
      return
    }

    if (!data.token) {
      error.value = 'Token not received from server'
      return
    }

    saveAuth(data.user, data.token)

    message.value = 'Registration successful'

    router.push('/')

    setTimeout(() => {
      window.location.reload()
    }, 100)
  } catch (err) {
    console.error('Register error:', err)
    error.value = 'Server connection error'
  }
}
</script>