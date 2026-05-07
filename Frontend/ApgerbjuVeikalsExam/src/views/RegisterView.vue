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
      const response = await fetch('http://127.0.0.1:8000/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(form),
      })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Registration failed'
      return
    }

    message.value = 'Registration successful'

    console.log('Registered user:', data.user)


    localStorage.setItem('user', JSON.stringify(data.user))
  } catch {
    error.value = 'Server connection error'
  }
}
</script>