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
        <h2>{{ t.policyTitle }}</h2>

        <p>
          {{ t.policyText1 }}
        </p>

        <p>
          {{ t.policyText2 }}
        </p>

        <h2>{{ t.contactTitle }}</h2>

        <p>
          {{ t.contactText }}
        </p>

        <h2>{{ t.adviceTitle }}</h2>

        <p>
          {{ t.adviceText }}
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
    title: 'Refunds',
    subtitle: 'Basic information about returns and refunds.',
    policyTitle: 'Refund policy',
    policyText1:
      'Sunny is a user-to-user marketplace. This means that refund agreements are made directly between the buyer and the seller.',
    policyText2:
      'Before buying an item, the buyer should carefully check the listing photos, size, condition, description and ask additional questions if something is unclear.',
    contactTitle: 'When should users contact each other?',
    contactText:
      'If the received item is very different from the listing description, the buyer should contact the seller through messages and explain the situation.',
    adviceTitle: 'Our advice',
    adviceText:
      'To avoid misunderstandings, sellers should upload clear photos and write honest descriptions. Buyers should ask questions before making an offer or purchase.',
  },

  lv: {
    title: 'Atgriešana',
    subtitle: 'Pamatinformācija par preču atgriešanu un naudas atmaksu.',
    policyTitle: 'Atgriešanas noteikumi',
    policyText1:
      'Sunny ir lietotāju savstarpējas tirdzniecības platforma. Tas nozīmē, ka par preces atgriešanu vai naudas atmaksu pircējs un pārdevējs vienojas savā starpā.',
    policyText2:
      'Pirms preces iegādes pircējam rūpīgi jāapskata sludinājuma attēli, izmērs, stāvoklis, apraksts un jāuzdod papildu jautājumi, ja kaut kas nav skaidrs.',
    contactTitle: 'Kad lietotājiem jāsazinās?',
    contactText:
      'Ja saņemtā prece būtiski atšķiras no sludinājuma apraksta, pircējam jāsazinās ar pārdevēju, izmantojot ziņu sistēmu, un jāizskaidro situācija.',
    adviceTitle: 'Mūsu ieteikums',
    adviceText:
      'Lai izvairītos no pārpratumiem, pārdevējiem ieteicams augšupielādēt skaidrus attēlus un rakstīt godīgus aprakstus. Pircējiem pirms piedāvājuma vai pirkuma veikšanas ieteicams uzdot jautājumus.',
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