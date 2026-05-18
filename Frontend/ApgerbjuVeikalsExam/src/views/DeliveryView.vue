<template>
  <div class="info-page">
    <section class="info-hero">
      <div class="container">
        <h1>{{ t.title }}</h1>
        <p>{{ t.subtitle }}</p>
      </div>
    </section>

    <section class="info-content container">
      <div class="info-card">
        <h2>{{ t.howTitle }}</h2>

        <p>
          {{ t.howText1 }}
        </p>

        <p>
          {{ t.howText2 }}
        </p>

        <h2>{{ t.optionsTitle }}</h2>

        <p>
          {{ t.optionsText }}
        </p>

        <h2>{{ t.importantTitle }}</h2>

        <p>
          {{ t.importantText }}
        </p>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

type Language = 'en' | 'lv'

const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

const translations = {
  en: {
    title: 'Delivery',
    subtitle: 'Information about shipping and delivery on Sunny.',
    howTitle: 'How delivery works',
    howText1:
      'Sunny is a marketplace where users sell items directly to other users. Delivery details are discussed between the buyer and the seller before completing the purchase.',
    howText2:
      'The seller is responsible for packing the item safely and sending it using an agreed delivery method. Buyers should always confirm the delivery price, shipping time and address before making a deal.',
    optionsTitle: 'Recommended delivery options',
    optionsText:
      'We recommend using trusted parcel services and keeping the tracking number until the item is received. This helps both sides feel safer during the transaction.',
    importantTitle: 'Important',
    importantText:
      'Sunny does not currently process delivery payments automatically. Shipping is arranged by users themselves.',
  },

  lv: {
    title: 'Piegāde',
    subtitle: 'Informācija par preču nosūtīšanu un piegādi platformā Sunny.',
    howTitle: 'Kā darbojas piegāde',
    howText1:
      'Sunny ir lietotāju savstarpējas tirdzniecības platforma, kur pircēji un pārdevēji vienojas tieši savā starpā. Piegādes detaļas tiek apspriestas starp pircēju un pārdevēju pirms darījuma pabeigšanas.',
    howText2:
      'Pārdevējs ir atbildīgs par preces drošu iepakošanu un nosūtīšanu ar iepriekš saskaņotu piegādes veidu. Pircējam pirms darījuma ieteicams precizēt piegādes cenu, nosūtīšanas laiku un adresi.',
    optionsTitle: 'Ieteicamās piegādes iespējas',
    optionsText:
      'Ieteicams izmantot uzticamus pakomātu vai kurjerpasta pakalpojumus un saglabāt sūtījuma izsekošanas numuru līdz brīdim, kad prece ir saņemta.',
    importantTitle: 'Svarīgi',
    importantText:
      'Sunny pašlaik neveic automātisku piegādes maksājumu apstrādi. Par piegādi lietotāji vienojas paši.',
  },
}

const t = computed(() => translations[language.value])

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