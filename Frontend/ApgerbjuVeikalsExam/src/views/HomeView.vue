<template>
  <div class="home">
    <!-- Hero -->
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <h1>New Collection</h1>
          <p>Minimalism and functionality in every detail.</p>
          <router-link to="/shop" class="hero-link">Shop →</router-link>
        </div>
      </div>
    </section>

    <!-- Featured -->
    <section class="featured">
      <div class="container">
        <h2 class="section-title">Featured </h2>
        <div class="products-grid">
          <div class="product-card" v-for="product in featuredProducts" :key="product.id">
            <div class="product-image">
              <img :src="product.image" :alt="product.name">
            </div>
            <div class="product-info">
              <h3>{{ product.name }}</h3>
              <p>{{ product.price }} €</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!--About -->
    <section class="about">
      <div class="container">
        <div class="about-content">
          <h2>About Us</h2>
          <p>
            Name creates clothes you can feel like yourself in. <br>
            We focus on quality materials, thoughtful cuts, and timeless style.
          </p>
          <p>
            Each piece is a balance between aesthetics and comfort, designed for urban life.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
const featuredProducts = [
  { id: 1, name: '1', price: 29, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80' },
  { id: 2, name: '2', price: 89, image: 'https://images.unsplash.com/photo-1542272604-787c3835535d?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80' },
  { id: 3, name: '3', price: 65, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80' },
]
</script>
