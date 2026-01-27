<template>
  <div id="app">

    <!-- Main header -->
    <header class="main-header">
      <div class="container">
        <div class="logo">
          <router-link to="/">Name</router-link>
        </div>
        <nav class="main-nav">
          <router-link to="/">Home</router-link>
          <router-link to="/shop">Shop</router-link>
          <router-link to="/cart">Cart ({{ cartCount }})</router-link>
        </nav>
      </div>
    </header>

    <!-- Content -->
    <main class="main-content">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="main-footer">
      <div class="container">
        <div class="footer-section">
          <h4>Name</h4>
          <p> description </p>
        </div>
        <div class="footer-section">
          <h4>Information</h4>
          <a href="#">Delivery</a>
          <a href="#">Refunds</a>
          <a href="#">About Us</a>
        </div>
        <div class="footer-section">
          <h4>Contact Us</h4>
          <p>email.com<br>+371 123</p>
        </div>
        <div class="footer-section">
          <h4>Subscribe to our newsletter</h4>
          <div class="newsletter">
            <input type="email" placeholder="Your email">
            <button>→</button>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2026 Name</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
const cartCount = ref(0)
</script>

