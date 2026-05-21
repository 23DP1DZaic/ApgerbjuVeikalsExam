<template>
  <div class="purchase-page">
    <div class="purchase-layout">
      <main class="purchase-form-card">
        <button class="back-btn" @click="router.back()">
          ← Back
        </button>

        <h1>Checkout</h1>

        <p class="purchase-subtitle">
          Complete your accepted offer purchase.
        </p>

        <div class="purchase-demo-note">
          Demo checkout only. Card data is not saved or charged.
        </div>

        <p v-if="loading" class="loading-text">
          Loading...
        </p>

        <p v-if="error" class="error">
          {{ error }}
        </p>

        <form v-if="offer && !success" @submit.prevent="submitPayment">
          <h2>Delivery address</h2>

          <div class="checkout-grid">
            <div class="form-group">
              <label>First name</label>
              <input
                v-model.trim="form.firstName"
                :class="{ 'input-error': errors.firstName }"
                placeholder="First name"
              >
              <p v-if="errors.firstName" class="field-error">
                {{ errors.firstName }}
              </p>
            </div>

            <div class="form-group">
              <label>Last name</label>
              <input
                v-model.trim="form.lastName"
                :class="{ 'input-error': errors.lastName }"
                placeholder="Last name"
              >
              <p v-if="errors.lastName" class="field-error">
                {{ errors.lastName }}
              </p>
            </div>
          </div>

          <div class="form-group">
            <label>Address</label>
            <input
              v-model.trim="form.address"
              :class="{ 'input-error': errors.address }"
              placeholder="Street, house, apartment"
            >
            <p v-if="errors.address" class="field-error">
              {{ errors.address }}
            </p>
          </div>

          <div class="checkout-grid">
            <div class="form-group">
              <label>City</label>
              <input
                v-model.trim="form.city"
                :class="{ 'input-error': errors.city }"
                placeholder="City"
              >
              <p v-if="errors.city" class="field-error">
                {{ errors.city }}
              </p>
            </div>

            <div class="form-group">
              <label>Postal code</label>
              <input
                v-model.trim="form.postalCode"
                :class="{ 'input-error': errors.postalCode }"
                placeholder="LV-1000"
              >
              <p v-if="errors.postalCode" class="field-error">
                {{ errors.postalCode }}
              </p>
            </div>
          </div>

          <div class="form-group">
            <label>Phone number</label>
            <input
              v-model="form.phone"
              :class="{ 'input-error': errors.phone }"
              placeholder="+37120000000"
              @input="form.phone = form.phone.replace(/[^+\d]/g, '')"
            >
            <p v-if="errors.phone" class="field-error">
              {{ errors.phone }}
            </p>
          </div>

          <h2>Payment card</h2>

          <div class="form-group">
            <label>Cardholder name</label>
            <input
              v-model.trim="form.cardholder"
              :class="{ 'input-error': errors.cardholder }"
              placeholder="Name on card"
            >
            <p v-if="errors.cardholder" class="field-error">
              {{ errors.cardholder }}
            </p>
          </div>

          <div class="form-group">
            <label>Card number</label>
            <input
              v-model="form.cardNumber"
              :class="{ 'input-error': errors.cardNumber }"
              placeholder="1234 5678 9012 3456"
              maxlength="19"
              @input="formatCardNumber"
            >
            <p v-if="errors.cardNumber" class="field-error">
              {{ errors.cardNumber }}
            </p>
          </div>

          <div class="checkout-grid">
            <div class="form-group">
              <label>Expiry date</label>
              <input
                v-model="form.expiry"
                :class="{ 'input-error': errors.expiry }"
                placeholder="MM/YY"
                maxlength="5"
                @input="formatExpiry"
              >
              <p v-if="errors.expiry" class="field-error">
                {{ errors.expiry }}
              </p>
            </div>

            <div class="form-group">
              <label>CVV</label>
              <input
                v-model="form.cvv"
                :class="{ 'input-error': errors.cvv }"
                placeholder="123"
                maxlength="4"
                @input="form.cvv = form.cvv.replace(/\D/g, '')"
              >
              <p v-if="errors.cvv" class="field-error">
                {{ errors.cvv }}
              </p>
            </div>
          </div>

          <button
            type="submit"
            class="purchase-submit-btn"
            :disabled="submitting"
          >
            {{ submitting ? 'Processing...' : 'Complete purchase' }}
          </button>
        </form>

        <div v-if="success" class="purchase-success-box">
          <h2>Purchased successfully!</h2>
          <p>Your offer purchase has been completed.</p>

          <button class="purchase-submit-btn" @click="goToListing">
            Open listing
          </button>
        </div>
      </main>

      <aside v-if="offer" class="purchase-summary-card">
        <h2>Order summary</h2>

        <div class="purchase-listing-preview">
          <img
            v-if="offer.listing.images?.length"
            :src="`${API_URL}/storage/${offer?.listing?.images[0]?.image_path}`"
            :alt="offer.listing.title"
          >

          <div v-else class="no-image">
            No image
          </div>

          <div>
            <h3>{{ offer.listing.title }}</h3>
            <p>Status: {{ offer.status }}</p>
            <strong>{{ formatPrice(offer.amount) }}</strong>
          </div>
        </div>

        <div class="purchase-summary-row">
          <span>Offer price</span>
          <strong>{{ formatPrice(offer.amount) }}</strong>
        </div>

        <div class="purchase-summary-row total">
          <span>Total</span>
          <strong>{{ formatPrice(offer.amount) }}</strong>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getToken } from '../services/auth'

type OfferItem = {
  id: number
  amount: number | string
  status: string
  buyer_expires_at: string | null
  listing: {
    id: number
    title: string
    price: number
    images?: {
      id: number
      image_path: string
    }[]
  }
}

const route = useRoute()
const router = useRouter()

const offer = ref<OfferItem | null>(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const success = ref(false)

const form = reactive({
  firstName: '',
  lastName: '',
  address: '',
  city: '',
  postalCode: '',
  phone: '',
  cardholder: '',
  cardNumber: '',
  expiry: '',
  cvv: '',
})

const errors = reactive<Record<string, string>>({})

const loadOffer = async () => {
  loading.value = true
  error.value = ''

  if (!getToken()) {
    router.push('/login')
    return
  }

  try {
    const response = await fetchWithAuth(`${API_URL}/api/offers/${route.params.id}`, {
      method: 'GET',
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to load offer'
      return
    }

    offer.value = data

    if (data.status !== 'accepted') {
      error.value = 'This offer is not available for payment.'
    }
  } catch (err) {
    console.error('Load offer error:', err)
    error.value = 'Server connection error'
  } finally {
    loading.value = false
  }
}

const validate = () => {
  Object.keys(errors).forEach((key) => {
    delete errors[key]
  })

  if (!form.firstName) errors.firstName = 'First name is required'
  if (!form.lastName) errors.lastName = 'Last name is required'
  if (form.address.length < 5) errors.address = 'Address must be at least 5 characters'
  if (!form.city) errors.city = 'City is required'

  if (!/^LV-\d{4}$/.test(form.postalCode)) {
    errors.postalCode = 'Enter a valid postal code'
  }

  if (!/^\+?\d{8,15}$/.test(form.phone)) {
    errors.phone = 'Enter a valid phone number'
  }

  if (!form.cardholder) errors.cardholder = 'Cardholder name is required'

  const cleanCard = form.cardNumber.replace(/\s/g, '')
  if (!/^\d{16}$/.test(cleanCard)) {
    errors.cardNumber = 'Card number must contain 16 digits'
  }

  if (!isValidExpiry(form.expiry)) {
    errors.expiry = 'Enter a valid future date'
  }

  if (!/^\d{3,4}$/.test(form.cvv)) {
    errors.cvv = 'CVV must contain 3 or 4 digits'
  }

  return Object.keys(errors).length === 0
}

const isValidExpiry = (value: string) => {
  if (!/^\d{2}\/\d{2}$/.test(value)) return false

  const [monthRaw, yearRaw] = value.split('/')
  const month = Number(monthRaw)
  const year = Number(`20${yearRaw}`)

  if (month < 1 || month > 12) return false

  const expiryDate = new Date(year, month)
  const now = new Date()

  return expiryDate > now
}

const submitPayment = async () => {
  if (!offer.value) return

  if (!validate()) return

  submitting.value = true
  error.value = ''

  try {
    const response = await fetchWithAuth(`${API_URL}/api/offers/${offer.value.id}/pay`, {
      method: 'POST',
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to complete payment'
      return
    }

    success.value = true
    offer.value.status = 'paid'
  } catch (err) {
    console.error('Offer payment error:', err)
    error.value = 'Server connection error'
  } finally {
    submitting.value = false
  }
}

const formatCardNumber = () => {
  const cleaned = form.cardNumber.replace(/\D/g, '').slice(0, 16)
  form.cardNumber = cleaned.replace(/(.{4})/g, '$1 ').trim()
}

const formatExpiry = () => {
  const cleaned = form.expiry.replace(/\D/g, '').slice(0, 4)

  if (cleaned.length >= 3) {
    form.expiry = `${cleaned.slice(0, 2)}/${cleaned.slice(2)}`
  } else {
    form.expiry = cleaned
  }
}

const goToListing = () => {
  if (!offer.value?.listing?.id) return

  router.push(`/listing/${offer.value.listing.id}`)
}

const formatPrice = (price: number | string) => {
  return `${Math.round(Number(price))} €`
}

onMounted(loadOffer)
</script>