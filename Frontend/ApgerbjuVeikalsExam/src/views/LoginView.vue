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
          <p> Login</p>
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
      const response = await fetch('http://127.0.0.1:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(form),
      })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Login failed'
      return
    }

    localStorage.setItem('user', JSON.stringify(data.user))
    message.value = 'Login successful'

    console.log('Logged in user:', data.user)
  } catch {
    error.value = 'Server connection error'
  }
}
</script>