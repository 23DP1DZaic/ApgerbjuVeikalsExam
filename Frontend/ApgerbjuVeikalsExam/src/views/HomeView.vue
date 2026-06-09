<template>
  <main class="home-page">
    <!-- Hero section: first big block on the home page -->
    <section class="home-hero">
      <div class="home-hero-content">
        <p class="home-hero-kicker">
          {{ ht.kicker }}
        </p>

        <h1>
          {{ ht.heroTitle }}
        </h1>

        <p class="home-hero-text">
          {{ ht.heroText }}
        </p>

        <div class="home-hero-actions">
          <button
            type="button"
            class="home-primary-btn"
            @click="goToShop"
          >
            {{ ht.shopNow }}
          </button>

          <button
            type="button"
            class="home-secondary-btn"
            @click="goToCreateListing"
          >
            {{ ht.sellItem }}
          </button>
        </div>
      </div>

      <!-- Hero side cards: simple visual preview of marketplace features -->
      <div class="home-hero-panel">
        <div class="home-stat-card">
          <strong>{{ ht.fast }}</strong>
          <span>{{ ht.fastText }}</span>
        </div>

        <div class="home-stat-card">
          <strong>{{ ht.offers }}</strong>
          <span>{{ ht.offersText }}</span>
        </div>

        <div class="home-stat-card">
          <strong>{{ ht.messages }}</strong>
          <span>{{ ht.messagesText }}</span>
        </div>
      </div>
    </section>

    <!-- Trending section: listings sorted by biggest likes count -->
    <section class="trending-section">
      <div class="home-section-header">
        <div>
          <p class="home-section-kicker">
            {{ ht.trendingKicker }}
          </p>

          <h2>
            {{ ht.trendingTitle }}
          </h2>
        </div>

        <button
          type="button"
          class="home-link-btn"
          @click="goToShop"
        >
          {{ ht.viewAll }}
        </button>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="home-message">
        {{ ht.loading }}
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="home-error">
        {{ error }}
      </div>

      <!-- Empty state -->
      <div v-else-if="trendingListings.length === 0" class="home-message">
        {{ ht.noTrending }}
      </div>

      <!-- Trending cards -->
      <div v-else class="trending-grid">
        <article
          v-for="listing in trendingListings"
          :key="listing.id"
          class="trending-card"
          @click="openListing(listing.id)"
        >
          <div class="trending-image-wrapper">
            <img
              v-if="firstImagePath(listing)"
              :src="`${API_URL}/storage/${firstImagePath(listing)}`"
              :alt="listing.title"
              class="trending-image"
            >

            <div v-else class="trending-placeholder">
              {{ ht.noImage }}
            </div>

            <div v-if="listing.status === 'sold'" class="sold-badge">
              {{ ht.sold }}
            </div>
          </div>

          <div class="trending-info">
            <h3>
              {{ listing.title }}
            </h3>

            <p>
              {{ listing.brand || ht.unknownBrand }}
            </p>

            <div class="trending-bottom-row">
              <strong>
                {{ formatPrice(listing.price) }}
              </strong>

              <span>
                ♥ {{ listing.likes_count || 0 }}
              </span>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- How it works section: explains main website process -->
    <section class="how-it-works-section">
      <div class="home-section-header centered">
        <div>
          <p class="home-section-kicker">
            {{ ht.howKicker }}
          </p>

          <h2>
            {{ ht.howTitle }}
          </h2>
        </div>
      </div>

      <div class="how-grid">
        <div class="how-card">
          <div class="how-number">
            1
          </div>

          <h3>
            {{ ht.stepOneTitle }}
          </h3>

          <p>
            {{ ht.stepOneText }}
          </p>
        </div>

        <div class="how-card">
          <div class="how-number">
            2
          </div>

          <h3>
            {{ ht.stepTwoTitle }}
          </h3>

          <p>
            {{ ht.stepTwoText }}
          </p>
        </div>

        <div class="how-card">
          <div class="how-number">
            3
          </div>

          <h3>
            {{ ht.stepThreeTitle }}
          </h3>

          <p>
            {{ ht.stepThreeText }}
          </p>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup lang="ts">
// Imports: Vue helpers, router, API URL and auth token helper
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getToken } from '../services/auth'

// Router: used for navigation buttons and listing cards
const router = useRouter()

// Type: website language
type Language = 'en' | 'lv'

// Type: listing image from backend
type ListingImage = {
  id: number
  image_path: string
}

// Type: listing object used on home page
type Listing = {
  id: number
  title: string
  price: number | string
  brand: string | null
  status?: string | null
  likes_count?: number
  images: ListingImage[]
}

// Page state: listings, loading and error
const listings = ref<Listing[]>([])
const loading = ref(true)
const error = ref('')

// Language state: uses same localStorage language as the rest of the site
const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

// Translations: all text used on home page
const homeTranslations = {
  en: {
    kicker: 'Modern clothing marketplace',
    heroTitle: 'Buy and sell designer clothing in one place.',
    heroText:
      'Discover unique pieces, publish your own listings, send offers, message sellers and build your personal style.',
    shopNow: 'Shop now',
    sellItem: 'Sell item',

    fast: 'Fast listings',
    fastText: 'Upload photos and publish items in minutes.',
    offers: 'Offers',
    offersText: 'Buyers can send price offers to sellers.',
    messages: 'Messages',
    messagesText: 'Chat with sellers before buying.',

    trendingKicker: 'Most liked',
    trendingTitle: 'Trending',
    viewAll: 'View all',
    loading: 'Loading...',
    noTrending: 'No trending listings yet.',
    noImage: 'No image',
    sold: 'SOLD',
    unknownBrand: 'Unknown brand',

    howKicker: 'Simple process',
    howTitle: 'How it works',
    stepOneTitle: 'Create a listing',
    stepOneText:
      'Add photos, choose a category, set the price and publish your item.',
    stepTwoTitle: 'Get likes and offers',
    stepTwoText:
      'Other users can like your item, save it, message you or send an offer.',
    stepThreeTitle: 'Buy or sell',
    stepThreeText:
      'Complete the purchase, keep track of your items and leave a review.',
    loadError: 'Failed to load trending listings.',
    connectionError: 'Could not connect to backend.',
  },

  lv: {
    kicker: 'Mūsdienīga apģērbu platforma',
    heroTitle: 'Pērc un pārdod dizaineru apģērbu vienā vietā.',
    heroText:
      'Atrodi unikālas preces, publicē savus sludinājumus, sūti piedāvājumus, raksti pārdevējiem un veido savu stilu.',
    shopNow: 'Apskatīt preces',
    sellItem: 'Pārdot preci',

    fast: 'Ātri sludinājumi',
    fastText: 'Pievieno attēlus un publicē preci dažu minūšu laikā.',
    offers: 'Piedāvājumi',
    offersText: 'Pircēji var nosūtīt cenu piedāvājumus pārdevējiem.',
    messages: 'Ziņas',
    messagesText: 'Sazinies ar pārdevēju pirms pirkuma.',

    trendingKicker: 'Visvairāk patīk',
    trendingTitle: 'Populāri',
    viewAll: 'Skatīt visu',
    loading: 'Ielāde...',
    noTrending: 'Vēl nav populāru sludinājumu.',
    noImage: 'Nav attēla',
    sold: 'PĀRDOTS',
    unknownBrand: 'Nezināms zīmols',

    howKicker: 'Vienkāršs process',
    howTitle: 'Kā tas darbojas',
    stepOneTitle: 'Izveido sludinājumu',
    stepOneText:
      'Pievieno fotogrāfijas, izvēlies kategoriju, norādi cenu un publicē preci.',
    stepTwoTitle: 'Saņem patīk un piedāvājumus',
    stepTwoText:
      'Citi lietotāji var atzīmēt preci ar patīk, saglabāt to, rakstīt tev vai nosūtīt piedāvājumu.',
    stepThreeTitle: 'Pērc vai pārdod',
    stepThreeText:
      'Noformē pirkumu, seko savām precēm un pēc darījuma atstāj atsauksmi.',
    loadError: 'Neizdevās ielādēt populāros sludinājumus.',
    connectionError: 'Neizdevās savienoties ar serveri.',
  },
}

// Current translation object
const ht = computed(() => homeTranslations[language.value])

// Trending listings: sorted by likes count from biggest to smallest
const trendingListings = computed(() => {
  return [...listings.value]
    .sort((a, b) => {
      return Number(b.likes_count || 0) - Number(a.likes_count || 0)
    })
    .slice(0, 3)
})

// Fetch listings: loads all available listings and then frontend sorts by likes
const fetchTrendingListings = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings`, {
      method: 'GET',
    })

    const rawText = await response.text()

    let data: any = null

    try {
      data = JSON.parse(rawText)
    } catch {
      data = { message: rawText }
    }

    if (!response.ok) {
      error.value = data.message || ht.value.loadError
      listings.value = []
      return
    }

    listings.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Fetch trending listings error:', err)
    error.value = ht.value.connectionError
    listings.value = []
  } finally {
    loading.value = false
  }
}

// Navigation: open shop page
const goToShop = () => {
  router.push('/shop')
}

// Navigation: open create listing page, login first if user is not authenticated
const goToCreateListing = () => {
  const token = getToken()

  if (!token) {
    router.push('/login')
    return
  }

  router.push('/create-listing')
}

// Navigation: open listing details page
const openListing = (id: number) => {
  router.push(`/listing/${id}`)
}

// Image helper: safely gets first listing image path
const firstImagePath = (listing: Listing) => {
  return listing.images?.[0]?.image_path || ''
}


// Format price: displays price in euros
const formatPrice = (price: number | string) => {
  return `${Math.round(Number(price))} €`
}

// Language update: called when language button in header changes language
const updateLanguage = () => {
  language.value = (localStorage.getItem('language') as Language) || 'en'
}

// Mounted: load listings and listen for language changes
onMounted(() => {
  fetchTrendingListings()
  window.addEventListener('language-changed', updateLanguage)
})

// Before unmount: remove language listener
onBeforeUnmount(() => {
  window.removeEventListener('language-changed', updateLanguage)
})
</script>