<template>
  <form @submit.prevent="login">
    <input v-model="form.email" placeholder="Email">
    <input v-model="form.password" type="password" placeholder="Password">
    <button>Login</button>
  </form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'

const form = reactive({
  email: '',
  password: '',
})

const login = async () => {
  const res = await fetch('http://127.0.0.1:8000/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(form),
  })

  const data = await res.json()
  console.log(data)
}
</script>