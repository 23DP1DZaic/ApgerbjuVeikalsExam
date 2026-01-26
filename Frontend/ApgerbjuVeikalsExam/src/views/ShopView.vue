<template>
  <section>
    <h1>test</h1>

    <ul>
      <li v-for="product in products" :key="product.id">
        {{ product.name }} — {{ product.price }} €
      </li>
    </ul>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const products = ref([
  { id: 1, name: 'A', price: 20 },
  { id: 2, name: 'V', price: 50 },
  { id: 3, name: '67', price: 120 }
])

const res = await fetch('http://127.0.0.1:8000/api/products')
</script>
