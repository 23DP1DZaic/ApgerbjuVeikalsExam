<template>
  <form @submit.prevent="register">
    <input v-model="form.name" placeholder="Name">
    <input v-model="form.email" placeholder="Email">
    <input v-model="form.password" type="password" placeholder="Password">
    <button>Register</button>
  </form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'

const form = reactive({
  name: '',
  email: '',
  password: '',
})

const register = async () => {
  const res = await fetch('http://127.0.0.1:8000/api/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(form),
  })

  const data = await res.json()
  console.log(data)
}
</script>