<template>
  <div class="account-page">
    <div class="account-layout">
      <aside class="account-sidebar">
        <h3>Settings</h3>

        <router-link to="/settings/profile" class="settings-link">
          Profile
        </router-link>

        <router-link to="/settings/account" class="settings-link">
          Account
        </router-link>
      </aside>

      <main class="account-main">
        <div class="account-header-card">
          <div class="account-top">
            <div class="account-user-block">
              <img
                v-if="user?.avatar_url"
                :src="user.avatar_url"
                alt="Avatar"
                class="account-avatar-image"
              >
              <div v-else class="account-avatar">
                {{ userInitial }}
              </div>

              <div class="account-user-info">
                <h1>{{ user?.display_name || user?.name || 'User' }}</h1>
                <p class="account-subtext">
                  {{ user?.bio || 'No description yet' }}
                </p>
                <p class="account-joined">Joined in 2025</p>
              </div>
            </div>

            <router-link to="/settings/profile" class="edit-profile-btn">
              Edit Profile
            </router-link>
          </div>

          <div class="account-stats">
            <div class="stat-box">
              <strong>{{ user?.listings?.length || 0 }}</strong>
              <span>Listings</span>
            </div>

            <div class="stat-box">
              <strong>0</strong>
              <span>Favorites</span>
            </div>

            <div class="stat-box">
              <strong>0</strong>
              <span>Saved</span>
            </div>

            <div class="stat-box">
              <strong>0</strong>
              <span>Purchases</span>
            </div>

            <div class="stat-box">
              <strong>0</strong>
              <span>Reviews</span>
            </div>
          </div>

          <div class="account-tabs">
            <button
              v-for="tab in tabs"
              :key="tab"
              class="account-tab"
              :class="{ active: activeTab === tab }"
              @click="activeTab = tab"
            >
              {{ tab }}
            </button>
          </div>
        </div>

        <section class="account-content-card">
          <template v-if="activeTab === 'Selling'">
            <h2>My Listings</h2>

            <div v-if="user?.listings?.length" class="listing-grid">
              <div
                v-for="listing in user.listings"
                :key="listing.id"
                class="listing-card"
              >
                <img
                  v-if="listing.images?.length"
                  :src="`${API_URL}/storage/${listing.images?.[0]?.image_path || ''}`"
                  :alt="listing.title"
                  class="listing-card-image"
                >
                <div v-else class="no-image">No image</div>

                <div class="listing-card-info">
                  <h3>{{ listing.title }}</h3>
                  <p>{{ listing.category }}</p>
                  <span>{{ listing.price }} €</span>
                </div>
              </div>
            </div>

            <p v-else class="empty-text">No listings yet.</p>
          </template>

          <template v-else>
            <h2>{{ activeTab }}</h2>
            <p class="empty-text">
              123
            </p>
          </template>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { API_URL } from '../services/api'
import { getCurrentUser, type AuthUser } from '../services/auth'

const user = ref<AuthUser | null>(null)

const tabs = ['Selling', 'Favorites', 'Saved', 'Purchases', 'Reviews']
const activeTab = ref('Selling')

const userInitial = computed(() => {
  const value = user.value?.display_name || user.value?.name || '?'
  return value.charAt(0).toUpperCase()
})

const loadAccount = async () => {
  user.value = await getCurrentUser()
}

onMounted(loadAccount)
</script>
