<template>
  <aside class="settings-sidebar">
    <h3>{{ st.settings }}</h3>

    <nav class="settings-nav">
      <router-link
        to="/settings/profile"
        class="settings-link"
      >
        {{ st.profile }}
      </router-link>

      <router-link
        to="/settings/account"
        class="settings-link"
      >
        {{ st.account }}
      </router-link>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

type Language = 'en' | 'lv'

const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

const sidebarTranslations = {
  en: {
    settings: 'Settings',
    profile: 'Profile',
    account: 'Account',
  },

  lv: {
    settings: 'Iestatījumi',
    profile: 'Profils',
    account: 'Konts',
  },
}

const st = computed(() => sidebarTranslations[language.value])

const updateLanguage = () => {
  language.value = (localStorage.getItem('language') as Language) || 'en'
}

onMounted(() => {
  window.addEventListener('language-changed', updateLanguage)
})

onBeforeUnmount(() => {
  window.removeEventListener('language-changed', updateLanguage)
})
</script>