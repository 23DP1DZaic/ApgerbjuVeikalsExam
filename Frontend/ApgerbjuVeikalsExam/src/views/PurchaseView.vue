<template>
  <div class="purchase-page">
    <div class="purchase-layout">
      <section class="checkout-card">
        <button class="back-btn" type="button" @click="goBack">
          ← {{ t.back }}
        </button>

        <h1>{{ t.checkout }}</h1>
        <p class="checkout-subtitle">
          {{ t.checkoutSubtitle }}
        </p>

        <p class="checkout-warning">
          {{ t.demoWarning }}
        </p>

        <form class="checkout-form" @submit.prevent="submitPurchase">
          <h2>{{ t.deliveryAddress }}</h2>

          <div class="form-row">
            <div class="form-group">
              <label>{{ t.firstName }}</label>
              <input
                v-model.trim="form.firstName"
                type="text"
                :placeholder="t.firstName"
                :class="{ 'input-error': errors.firstName }"
              >
              <p v-if="errors.firstName" class="field-error">
                {{ errors.firstName }}
              </p>
            </div>

            <div class="form-group">
              <label>{{ t.lastName }}</label>
              <input
                v-model.trim="form.lastName"
                type="text"
                :placeholder="t.lastName"
                :class="{ 'input-error': errors.lastName }"
              >
              <p v-if="errors.lastName" class="field-error">
                {{ errors.lastName }}
              </p>
            </div>
          </div>

          <div class="form-group">
            <label>{{ t.address }}</label>
            <input
              v-model.trim="form.address"
              type="text"
              :placeholder="t.addressPlaceholder"
              :class="{ 'input-error': errors.address }"
            >
            <p v-if="errors.address" class="field-error">
              {{ errors.address }}
            </p>
          </div>

          <div class="form-row">
            <div class="form-group">
            <label>{{ t.city }}</label>
            <input
              v-model.trim="form.city"
              type="text"
              :placeholder="t.city"
              :class="{ 'input-error': errors.city }"
            >
              <p v-if="errors.city" class="field-error">
                {{ errors.city }}
              </p>
            </div>

            <div class="form-group">
              <label>{{ t.postalCode }}</label>
              <input
                v-model.trim="form.postalCode"
                type="text"
                placeholder="LV-1000"
                :class="{ 'input-error': errors.postalCode }"
              >
              <p v-if="errors.postalCode" class="field-error">
                {{ errors.postalCode }}
              </p>
            </div>
          </div>

          <div class="form-group">
            <label>{{ t.phoneNumber }}</label>
            <input
              v-model.trim="form.phone"
              type="text"
              inputmode="tel"
              placeholder="+371 20000000"
              :class="{ 'input-error': errors.phone }"
              @input="formatPhone"
            >
            <p v-if="errors.phone" class="field-error">
              {{ errors.phone }}
            </p>
          </div>

          <h2>{{ t.paymentCard }}</h2>

          <div class="form-group">
            <label>{{ t.cardholderName }}</label>
            <input
              v-model.trim="form.cardName"
              type="text"
              :placeholder="t.cardholderPlaceholder"
              :class="{ 'input-error': errors.cardName }"
            >
            <p v-if="errors.cardName" class="field-error">
              {{ errors.cardName }}
            </p>
          </div>

          <div class="form-group">
            <label>{{ t.cardNumber }}</label>
            <input
              v-model="form.cardNumber"
              type="text"
              inputmode="numeric"
              maxlength="19"
              placeholder="1234 5678 9012 3456"
              :class="{ 'input-error': errors.cardNumber }"
              @input="formatCardNumber"
            >
            <p v-if="errors.cardNumber" class="field-error">
              {{ errors.cardNumber }}
            </p>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>{{ t.expiryDate }}</label>
              <input
                v-model="form.expiry"
                type="text"
                maxlength="5"
                placeholder="MM/YY"
                :class="{ 'input-error': errors.expiry }"
                @input="formatExpiry"
              >
              <p v-if="errors.expiry" class="field-error">
                {{ errors.expiry }}
              </p>
            </div>

            <div class="form-group">
              <label>{{ t.cvv }}</label>
              <input
                v-model="form.cvv"
                type="password"
                inputmode="numeric"
                maxlength="4"
                placeholder="123"
                :class="{ 'input-error': errors.cvv }"
                @input="formatCvv"
              >
              <p v-if="errors.cvv" class="field-error">
                {{ errors.cvv }}
              </p>
            </div>
          </div>

          <button class="purchase-submit-btn" type="submit">
            {{ t.completePurchase }}
          </button>

          <p v-if="successMessage" class="purchase-success">
            {{ successMessage }}
          </p>
        </form>
      </section>

      <aside class="purchase-summary">
        <h2>{{ t.orderSummary }}</h2>

        <div v-if="loading" class="summary-loading">
          {{ t.loading }}
        </div>

        <div v-else-if="error" class="error">
          {{ error }}
        </div>

        <div v-else-if="listing" class="summary-listing">
          <div class="summary-image-wrap">
            <img
              v-if="listingImage"
              :src="listingImage"
              :alt="listing.title"
            >

            <div v-else class="no-image">
              {{ t.noImage }}
            </div>
          </div>

          <div class="summary-info">
            <h3>{{ listing.title }}</h3>
            <p>{{ listing.brand || t.unknownBrand }}</p>
            <p>
              {{ listing.category }}
              <span v-if="listing.size"> · {{ listing.size }}</span>
              <span v-if="listing.condition"> · {{ formatText(listing.condition) }}</span>
            </p>
          </div>

          <div class="summary-line">
            <span>{{ t.itemPrice }}</span>
            <strong>{{ listing.price }} €</strong>
          </div>

          <div class="summary-line">
            <span>{{ t.delivery }}</span>
            <strong>0 €</strong>
          </div>

          <div class="summary-total">
            <span>{{ t.total }}</span>
            <strong>{{ listing.price }} €</strong>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL } from '../services/api'
import { getToken } from '../services/auth'

type ListingImage = {
  id: number
  image_path: string
}

type Listing = {
  id: number
  title: string
  description: string
  price: number
  category: string
  brand: string | null
  color: string | null
  size: string | null
  condition: string
  images: ListingImage[]
}

type CheckoutErrors = {
  firstName?: string
  lastName?: string
  address?: string
  city?: string
  postalCode?: string
  phone?: string
  cardName?: string
  cardNumber?: string
  expiry?: string
  cvv?: string
}

const route = useRoute()
const router = useRouter()

const listing = ref<Listing | null>(null)
const loading = ref(true)
const error = ref('')
const successMessage = ref('')

const form = reactive({
  firstName: '',
  lastName: '',
  address: '',
  city: '',
  postalCode: '',
  phone: '',
  cardName: '',
  cardNumber: '',
  expiry: '',
  cvv: '',
})

const errors = reactive<CheckoutErrors>({})

const listingImage = computed(() => {
  const firstImage = listing.value?.images?.[0]

  if (!firstImage) return ''

  return `${API_URL}/storage/${firstImage.image_path}`
})

const clearErrors = () => {
  Object.keys(errors).forEach((key) => {
    delete errors[key as keyof CheckoutErrors]
  })
}

const fetchListing = async () => {
  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await fetch(`${API_URL}/api/listings/${route.params.id}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = t.value.loadError
      return
    }

    listing.value = data
  } catch (err) {
    console.error('Load listing error:', err)
    error.value = 'Failed to load listing'
  } finally {
    loading.value = false
  }
}

const formatCardNumber = () => {
  const digits = form.cardNumber.replace(/\D/g, '').slice(0, 16)
  form.cardNumber = digits.replace(/(.{4})/g, '$1 ').trim()
}

const formatExpiry = () => {
  const digits = form.expiry.replace(/\D/g, '').slice(0, 4)

  if (digits.length >= 3) {
    form.expiry = `${digits.slice(0, 2)}/${digits.slice(2)}`
    return
  }

  form.expiry = digits
}

const formatCvv = () => {
  form.cvv = form.cvv.replace(/\D/g, '').slice(0, 4)
}

const formatPhone = () => {
  let value = form.phone.replace(/[^\d+]/g, '')

  if (value.includes('+')) {
    value = '+' + value.replace(/\+/g, '')
  }

  form.phone = value
}


const isValidExpiry = (value: string) => {
  const match = value.match(/^(\d{2})\/(\d{2})$/)

  if (!match) return false

  const month = Number(match[1])
  const year = Number(`20${match[2]}`)

  if (month < 1 || month > 12) return false

  const now = new Date()
  const currentYear = now.getFullYear()
  const currentMonth = now.getMonth() + 1

  if (year < currentYear) return false
  if (year === currentYear && month < currentMonth) return false

  return true
}

const validateForm = () => {
  clearErrors()

if (!form.firstName) {
  errors.firstName = t.value.firstNameRequired
}

if (!form.lastName) {
  errors.lastName = t.value.lastNameRequired
}

if (form.address.length < 5) {
  errors.address = t.value.addressError
}

if (form.city.length < 2) {
  errors.city = t.value.cityError
}

if (!/^[A-Za-z]{2}-?\d{4}$|^\d{4,6}$/.test(form.postalCode)) {
  errors.postalCode = t.value.postalCodeError
}

if (!/^\+?\d{8,15}$/.test(form.phone.replace(/\s/g, ''))) {
  errors.phone = t.value.phoneError
}

if (form.cardName.length < 3) {
  errors.cardName = t.value.cardNameError
}

const cardDigits = form.cardNumber.replace(/\D/g, '')

if (!/^\d{16}$/.test(cardDigits)) {
  errors.cardNumber = t.value.cardNumberError
}

if (!isValidExpiry(form.expiry)) {
  errors.expiry = t.value.expiryError
}

if (!/^\d{3,4}$/.test(form.cvv)) {
  errors.cvv = t.value.cvvError
}

  return Object.keys(errors).length === 0
}

const submitPurchase = async () => {
  successMessage.value = ''

  if (!validateForm()) {
    return
  }

  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await fetch(`${API_URL}/api/listings/${route.params.id}/purchase`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Purchase failed'
      return
    }

    listing.value = data.listing
    successMessage.value = t.value.success
  } catch (err) {
    console.error('Purchase error:', err)
    error.value = 'Server connection error'
  }
}

const goBack = () => {
  router.back()
}

const formatText = (value?: string | null) => {
  if (!value) return ''

  return value.charAt(0).toUpperCase() + value.slice(1)
}

onMounted(() => {
  fetchListing()
  window.addEventListener('language-changed', updateLanguage)
})

onBeforeUnmount(() => {
  window.removeEventListener('language-changed', updateLanguage)
})

type Language = 'en' | 'lv'

const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

const translations = {
  en: {
    back: 'Back',
    checkout: 'Checkout',
    checkoutSubtitle: 'Enter your delivery and payment information.',
    demoWarning: 'Demo checkout only. Card data is not saved or charged.',

    deliveryAddress: 'Delivery address',
    firstName: 'First name',
    lastName: 'Last name',
    address: 'Address',
    addressPlaceholder: 'Street, house, apartment',
    city: 'City',
    postalCode: 'Postal code',
    phoneNumber: 'Phone number',

    paymentCard: 'Payment card',
    cardholderName: 'Cardholder name',
    cardholderPlaceholder: 'Name on card',
    cardNumber: 'Card number',
    expiryDate: 'Expiry date',
    cvv: 'CVV',

    completePurchase: 'Complete purchase',

    orderSummary: 'Order summary',
    loading: 'Loading...',
    noImage: 'No image',
    unknownBrand: 'Unknown brand',
    itemPrice: 'Item price',
    delivery: 'Delivery',
    total: 'Total',

    firstNameRequired: 'First name is required',
    lastNameRequired: 'Last name is required',
    addressError: 'Address must be at least 5 characters',
    cityError: 'City is required',
    postalCodeError: 'Enter a valid postal code',
    phoneError: 'Enter a valid phone number',
    cardNameError: 'Cardholder name is required',
    cardNumberError: 'Card number must contain 16 digits',
    expiryError: 'Enter a valid future date',
    cvvError: 'CVV must contain 3 or 4 digits',

    success: 'Information is correct. Purchase completed successfully.',
    listingNotFound: 'Listing not found',
    loadError: 'Failed to load listing',
  },

  lv: {
    back: 'Atpakaļ',
    checkout: 'Pirkuma noformēšana',
    checkoutSubtitle: 'Ievadi piegādes un maksājuma informāciju.',
    demoWarning: 'Šī ir demo pirkuma forma. Kartes dati netiek saglabāti un maksājums netiek veikts.',

    deliveryAddress: 'Piegādes adrese',
    firstName: 'Vārds',
    lastName: 'Uzvārds',
    address: 'Adrese',
    addressPlaceholder: 'Iela, mājas numurs, dzīvoklis',
    city: 'Pilsēta',
    postalCode: 'Pasta indekss',
    phoneNumber: 'Tālruņa numurs',

    paymentCard: 'Maksājumu karte',
    cardholderName: 'Kartes īpašnieka vārds',
    cardholderPlaceholder: 'Vārds uz kartes',
    cardNumber: 'Kartes numurs',
    expiryDate: 'Derīguma termiņš',
    cvv: 'CVV',

    completePurchase: 'Pabeigt pirkumu',

    orderSummary: 'Pasūtījuma kopsavilkums',
    loading: 'Ielādējas...',
    noImage: 'Nav attēla',
    unknownBrand: 'Nezināms zīmols',
    itemPrice: 'Preces cena',
    delivery: 'Piegāde',
    total: 'Kopā',

    firstNameRequired: 'Vārds ir obligāts',
    lastNameRequired: 'Uzvārds ir obligāts',
    addressError: 'Adresei jābūt vismaz 5 rakstzīmēm',
    cityError: 'Pilsēta ir obligāta',
    postalCodeError: 'Ievadi derīgu pasta indeksu',
    phoneError: 'Ievadi derīgu tālruņa numuru',
    cardNameError: 'Kartes īpašnieka vārds ir obligāts',
    cardNumberError: 'Kartes numuram jābūt 16 cipariem',
    expiryError: 'Ievadi derīgu nākotnes datumu',
    cvvError: 'CVV jābūt 3 vai 4 cipariem',

    success: 'Informācija ir pareiza. Pirkums veiksmīgi noformēts.',
    listingNotFound: 'Sludinājums nav atrasts',
    loadError: 'Neizdevās ielādēt sludinājumu',
  },
}

const t = computed(() => translations[language.value])

const updateLanguage = () => {
  language.value = (localStorage.getItem('language') as Language) || 'en'
}



</script>