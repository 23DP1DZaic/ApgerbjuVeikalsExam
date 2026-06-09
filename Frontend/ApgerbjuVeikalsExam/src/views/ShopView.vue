<template>
  <div class="shop">
    <div class="container shop-container">
      <!-- Sidebar filters -->
      <aside class="shop-sidebar">
        <!-- Brand filter -->
        <div class="filter-section">
          <h3>{{ st.brand }}</h3>

          <input
            v-model="brandFilter"
            type="text"
            class="filter-input"
            :placeholder="st.brandPlaceholder"
          >
        </div>

        <!-- Size filter -->
        <div class="filter-section">
          <h3>{{ st.size }}</h3>

          <select v-model="sizeFilter" class="sort-select">
            <option value="">{{ st.allSizes }}</option>

            <option value="XXS">XXS</option>
            <option value="XS">XS</option>
            <option value="S">S</option>
            <option value="M">M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>
            <option value="XXL">XXL</option>

            <option value="W26">W26</option>
            <option value="W28">W28</option>
            <option value="W30">W30</option>
            <option value="W32">W32</option>
            <option value="W34">W34</option>
            <option value="W36">W36</option>
            <option value="W38">W38</option>

            <option value="35">35</option>
            <option value="36">36</option>
            <option value="37">37</option>
            <option value="38">38</option>
            <option value="39">39</option>
            <option value="40">40</option>
            <option value="41">41</option>
            <option value="42">42</option>
            <option value="43">43</option>
            <option value="44">44</option>
            <option value="45">45</option>
            <option value="46">46</option>
            <option value="47">47</option>
          </select>
        </div>

        <!-- Color filter -->
        <div
          ref="colorFilterDropdownRef"
          class="filter-section color-dropdown-wrapper"
        >
          <h3>{{ st.color }}</h3>

          <button
            type="button"
            class="custom-color-select shop-color-select"
            @click="isColorFilterOpen = !isColorFilterOpen"
          >
            <span
              v-if="selectedFilterColor"
              class="color-dot"
              :class="{ 'white-dot': selectedFilterColor.name === 'White' }"
              :style="{ background: selectedFilterColor.value }"
            ></span>

            <span>
              {{ selectedFilterColor ? colorLabel(selectedFilterColor.name) : st.allColors }}
            </span>

            <span class="custom-select-arrow">⌄</span>
          </button>

          <div
            v-if="isColorFilterOpen"
            class="custom-color-menu shop-color-menu"
          >
            <button
              type="button"
              class="custom-color-option"
              @click="selectFilterColor('')"
            >
              <span class="color-dot empty-color-dot"></span>
              <span>{{ st.allColors }}</span>
            </button>

            <button
              v-for="color in colors"
              :key="color.name"
              type="button"
              class="custom-color-option"
              @click="selectFilterColor(color.name)"
            >
              <span
                class="color-dot"
                :class="{ 'white-dot': color.name === 'White' }"
                :style="{ background: color.value }"
              ></span>

              <span>{{ colorLabel(color.name) }}</span>
            </button>
          </div>
        </div>

        <!-- Condition filter -->
        <div class="filter-section">
          <h3>{{ st.condition }}</h3>

          <select v-model="conditionFilter" class="sort-select">
            <option value="">{{ st.allConditions }}</option>
            <option value="new">{{ st.new }}</option>
            <option value="used">{{ st.used }}</option>
          </select>
        </div>

        <!-- Price filter -->
        <div class="filter-section">
          <h3>{{ st.price }}</h3>

          <div class="price-inputs">
            <input
              v-model="minPrice"
              type="number"
              min="0"
              class="filter-input"
              :placeholder="st.min"
            >

            <input
              v-model="maxPrice"
              type="number"
              min="0"
              class="filter-input"
              :placeholder="st.max"
            >
          </div>
        </div>

        <!-- Sort filter -->
        <div class="filter-section">
          <h3>{{ st.sortBy }}</h3>

          <select v-model="sortOption" class="sort-select">
            <option value="newest">{{ st.newArrivals }}</option>
            <option value="price-low">{{ st.priceLow }}</option>
            <option value="price-high">{{ st.priceHigh }}</option>
            <option value="title-az">{{ st.titleAz }}</option>
          </select>
        </div>

        <!-- Filter buttons -->
        <div class="filter-actions">
          <button
            type="button"
            class="apply-filters"
            @click="applyFilters"
          >
            {{ st.applyFilters }}
          </button>

          <button
            type="button"
            class="reset-filters"
            @click="resetFilters"
          >
            {{ st.resetFilters }}
          </button>
        </div>
      </aside>

      <!-- Main shop content -->
      <main class="shop-main">
        <!-- Products count / loading / empty message -->
        <div class="products-header">
          <p v-if="loading">
            {{ st.loading }}
          </p>

          <p v-else-if="products.length > 0">
            {{ products.length }} {{ products.length === 1 ? st.item : st.items }}
          </p>

          <div v-else class="empty-shop-message">
            <p>
              {{ st.noItems }}
            </p>
          </div>
        </div>

        <!-- Backend error -->
        <p v-if="error" class="error">
          {{ error }}
        </p>

        <!-- Product grid -->
        <div v-if="!loading" class="products-grid">
          <div
            v-for="product in products"
            :key="product.id"
            class="product-card"
            @click="viewProduct(product)"
          >
            <!-- Product image -->
            <div class="product-image">
              <img
                v-if="product.images && product.images.length"
                :src="`${API_URL}/storage/${product.images?.[0]?.image_path || ''}`"
                :alt="product.title"
              >

              <div v-else class="no-image">
                {{ st.noImage }}
              </div>

              <div v-if="product.status === 'sold'" class="sold-badge">
                {{ st.sold }}
              </div>
            </div>

            <!-- Product text info -->
            <div class="product-info">
              <h3>{{ product.title }}</h3>

              <p>
                {{ categoryLabel(product.category) }}
                <span v-if="product.size"> · {{ product.size }}</span>
                <span v-if="product.condition"> · {{ conditionLabel(product.condition) }}</span>
              </p>

              <div class="product-price-row">
                <span
                  v-if="product.original_price && Number(product.original_price) > Number(product.price)"
                  class="old-price"
                >
                  {{ formatPrice(product.original_price) }}
                </span>

                <span class="price">
                  {{ formatPrice(product.price) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
// Imports: Vue helpers, router and API helpers
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'

// Router: used to read filters from URL and open listing pages
const router = useRouter()
const route = useRoute()

// Type: website language
type Language = 'en' | 'lv'

// Type: listing image from backend
type ListingImage = {
  id: number
  image_path: string
}

// Type: product/listing object from backend
type Product = {
  id: number
  title: string
  description: string
  price: number
  category: string
  brand: string | null
  color: string | null
  size: string | null
  condition: string
  gender?: string | null
  images: ListingImage[]
  user_id: number
  likes_count?: number
  favorites_count?: number
  liked_by_me?: boolean
  favorited_by_me?: boolean
  status?: 'available' | 'sold' | string
  original_price?: number | string | null
}

// Products state: list of listings shown in shop
const products = ref<Product[]>([])

// Page state: loading and error messages
const loading = ref(true)
const error = ref<string | null>(null)

// Filter state: values from sidebar filters
const brandFilter = ref('')
const sizeFilter = ref('')
const colorFilter = ref('')
const conditionFilter = ref('')
const minPrice = ref('')
const maxPrice = ref('')
const onlyWithImage = ref(false)
const sortOption = ref('newest')
const categoryFilter = ref('')

// Color dropdown state
const isColorFilterOpen = ref(false)
const colorFilterDropdownRef = ref<HTMLElement | null>(null)

// Language state: reads language from localStorage
const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

// Translations: all shop/sidebar text
const shopTranslations = {
  en: {
    item: 'item',
    items: 'items',
    noItems: 'No items found. Try another category.',
    loading: 'Loading...',
    noImage: 'No image',
    sold: 'SOLD',

    brand: 'Brand',
    brandPlaceholder: 'Nike, Adidas...',
    size: 'Size',
    allSizes: 'All sizes',
    color: 'Color',
    allColors: 'All colors',
    condition: 'Condition',
    allConditions: 'All conditions',
    new: 'New',
    used: 'Used',
    price: 'Price',
    min: 'Min',
    max: 'Max',
    sortBy: 'Sort by',
    newArrivals: 'New arrivals',
    priceLow: 'Price (Low)',
    priceHigh: 'Price (High)',
    titleAz: 'Title A-Z',
    applyFilters: 'Apply filters',
    resetFilters: 'Reset filters',

    loadError: 'Failed to load listings',
    connectionError: 'Could not connect to backend',

    colors: {
      Black: 'Black',
      White: 'White',
      Gray: 'Gray',
      Brown: 'Brown',
      Beige: 'Beige',
      Yellow: 'Yellow',
      Red: 'Red',
      Orange: 'Orange',
      Pink: 'Pink',
      Purple: 'Purple',
      Blue: 'Blue',
      Green: 'Green',
      Multi: 'Multi',
      Silver: 'Silver',
      Gold: 'Gold',
    },
  },

  lv: {
    item: 'prece',
    items: 'preces',
    noItems: 'Netika atrasta neviena prece. Izmēģini citu kategoriju.',
    loading: 'Ielāde...',
    noImage: 'Nav attēla',
    sold: 'PĀRDOTS',

    brand: 'Zīmols',
    brandPlaceholder: 'Nike, Adidas...',
    size: 'Izmērs',
    allSizes: 'Visi izmēri',
    color: 'Krāsa',
    allColors: 'Visas krāsas',
    condition: 'Stāvoklis',
    allConditions: 'Visi stāvokļi',
    new: 'Jauns',
    used: 'Lietots',
    price: 'Cena',
    min: 'No',
    max: 'Līdz',
    sortBy: 'Kārtot pēc',
    newArrivals: 'Jaunākās preces',
    priceLow: 'Cena zemākā',
    priceHigh: 'Cena augstākā',
    titleAz: 'Nosaukums A-Z',
    applyFilters: 'Pielietot filtrus',
    resetFilters: 'Atiestatīt filtrus',

    loadError: 'Neizdevās ielādēt sludinājumus',
    connectionError: 'Neizdevās savienoties ar serveri',

    colors: {
      Black: 'Melna',
      White: 'Balta',
      Gray: 'Pelēka',
      Brown: 'Brūna',
      Beige: 'Bēša',
      Yellow: 'Dzeltena',
      Red: 'Sarkana',
      Orange: 'Oranža',
      Pink: 'Rozā',
      Purple: 'Violeta',
      Blue: 'Zila',
      Green: 'Zaļa',
      Multi: 'Daudzkrāsaina',
      Silver: 'Sudraba',
      Gold: 'Zelta',
    },
  },
}

// Current translation object
const st = computed(() => shopTranslations[language.value])

// Category translations: only labels are translated, backend values stay English
const lvCategoryLabels: Record<string, string> = {
  Accessories: 'Aksesuāri',
  Bottoms: 'Apakšdaļa',
  Footwear: 'Apavi',
  Outerwear: 'Virsdrēbes',
  Tailoring: 'Klasiskais apģērbs',
  Tops: 'Augšdaļa',

  'Long Sleeve T-Shirts': 'T-krekli ar garām piedurknēm',
  Polos: 'Polo krekli',
  'Shirts (Button Ups)': 'Krekli ar pogām',
  'Short Sleeve T-Shirts': 'T-krekli ar īsām piedurknēm',
  'Sweaters & Knitwear': 'Džemperi un trikotāža',
  'Sweatshirts & Hoodies': 'Džemperi un hūdiji',
  'Tank Tops & Sleeveless': 'Krekli bez piedurknēm',
  Jerseys: 'Sporta krekli',

  'Casual Pants': 'Ikdienas bikses',
  'Cropped Pants': 'Saīsinātas bikses',
  Denim: 'Džinsi',
  Leggings: 'Legingi',
  'Overalls & Jumpsuits': 'Kombinezoni',
  Shorts: 'Šorti',
  'Sweatpants & Joggers': 'Sporta bikses un džogeri',
  Swimwear: 'Peldapģērbs',

  Bombers: 'Bomberi',
  'Cloaks & Capes': 'Apmetņi',
  'Denim Jackets': 'Džinsu jakas',
  'Heavy Coats': 'Silti mēteļi',
  'Leather Jackets': 'Ādas jakas',
  'Light Jackets': 'Vieglas jakas',
  Parkas: 'Parkas',
  Raincoats: 'Lietusmēteļi',
  Vests: 'Vestes',

  Boots: 'Zābaki',
  'Casual Leather Shoes': 'Ikdienas ādas apavi',
  'Formal Shoes': 'Klasiskie apavi',
  'Hi-Top Sneakers': 'Augstās kedas',
  'High-Top Sneakers': 'Augstās kedas',
  'Low-Top Sneakers': 'Zemās kedas',
  Sandals: 'Sandales',
  'Slip Ons': 'Slip-on apavi',
  Flats: 'Zempapēžu apavi',
  Heels: 'Augstpapēžu kurpes',

  'Bags & Luggage': 'Somas un bagāža',
  Bags: 'Somas',
  Belts: 'Jostas',
  Glasses: 'Brilles',
  'Gloves & Scarves': 'Cimdi un šalles',
  Hats: 'Cepures',
  'Jewelry & Watches': 'Rotaslietas un pulksteņi',
  Jewelry: 'Rotaslietas',
  Scarves: 'Šalles',
  Wallets: 'Maki',
  Watches: 'Pulksteņi',
  'Socks & Underwear': 'Zeķes un apakšveļa',
  Sunglasses: 'Saulesbrilles',

  Blazers: 'Žaketes',
  'Formal Shirting': 'Klasiskie krekli',
  'Formal Trousers': 'Klasiskās bikses',
  'Formal Dresses': 'Klasiskas kleitas',
  'Formal Pants': 'Klasiskas bikses',
  Sets: 'Komplekti',
  Suits: 'Uzvalki',
  Tuxedos: 'Smokingu kostīmi',

  Blouses: 'Blūzes',
  'Crop Tops': 'Īsie topi',
  'Long Sleeve Tops': 'Topi ar garām piedurknēm',
  'Short Sleeve Tops': 'Topi ar īsām piedurknēm',
  'Tank Tops': 'Topi bez piedurknēm',
  Jeans: 'Džinsi',
  Pants: 'Bikses',
  Skirts: 'Svārki',
  Coats: 'Mēteļi',
  'Puffer Jackets': 'Pufīgās jakas',
}

// Colors: filter color options
const colors = [
  { name: 'Black', value: '#000000' },
  { name: 'White', value: '#ffffff' },
  { name: 'Gray', value: '#e5e5e5' },
  { name: 'Brown', value: '#6b4a3a' },
  { name: 'Beige', value: '#e6cf91' },
  { name: 'Yellow', value: '#ffd91a' },
  { name: 'Red', value: '#f10b0b' },
  { name: 'Orange', value: '#ff6500' },
  { name: 'Pink', value: '#ec5aaa' },
  { name: 'Purple', value: '#5f1fd6' },
  { name: 'Blue', value: '#1177bd' },
  { name: 'Green', value: '#4faf0b' },
  {
    name: 'Multi',
    value: 'linear-gradient(135deg, red, orange, yellow, green, blue, purple)',
  },
  {
    name: 'Silver',
    value: 'linear-gradient(135deg, #777, #eee, #aaa)',
  },
  {
    name: 'Gold',
    value: 'linear-gradient(135deg, #b99b22, #f3e37c, #c8a600)',
  },
]

// Selected color object for color filter button
const selectedFilterColor = computed(() => {
  return colors.find((color) => color.name === colorFilter.value) || null
})

// Language update: called when header language button changes language
const updateLanguage = () => {
  language.value = (localStorage.getItem('language') as Language) || 'en'
}

// Color label: translates color names in LV mode
const colorLabel = (colorName: string) => {
  return st.value.colors[colorName as keyof typeof st.value.colors] || colorName
}

// Category label: translates category names in LV mode
const categoryLabel = (categoryName: string) => {
  if (language.value === 'lv') {
    return lvCategoryLabels[categoryName] || categoryName
  }

  return categoryName
}

// Condition label: translates new/used status
const conditionLabel = (value?: string | null) => {
  if (!value) return ''

  if (value === 'new') return st.value.new
  if (value === 'used') return st.value.used

  return formatText(value)
}

// Sync filters: reads current filters from URL query
const syncFiltersFromRoute = () => {
  brandFilter.value = String(route.query.brand || '')
  sizeFilter.value = String(route.query.size || '')
  colorFilter.value = String(route.query.color || '')
  conditionFilter.value = String(route.query.condition || '')
  minPrice.value = String(route.query.min_price || '')
  maxPrice.value = String(route.query.max_price || '')
  sortOption.value = String(route.query.sort || 'newest')
  onlyWithImage.value = String(route.query.has_images || '') === '1'
  categoryFilter.value = String(route.query.category || '')
}

// Update URL: puts sidebar filter values into route query
const updateRouteWithFilters = () => {
  router.replace({
    path: '/shop',
    query: {
      search: route.query.search || undefined,
      gender: route.query.gender || undefined,
      section: route.query.section || undefined,
      parent_category: route.query.parent_category || undefined,

      category: categoryFilter.value || undefined,
      brand: brandFilter.value || undefined,
      size: sizeFilter.value || undefined,
      color: colorFilter.value || undefined,
      condition: conditionFilter.value || undefined,
      min_price: minPrice.value || undefined,
      max_price: maxPrice.value || undefined,
      has_images: onlyWithImage.value ? '1' : undefined,
      sort: sortOption.value !== 'newest' ? sortOption.value : undefined,
    },
  })
}

// Apply filters: updates URL, watcher will reload products
const applyFilters = () => {
  updateRouteWithFilters()
}

// Fetch products: loads listings from backend using URL query filters
const fetchProducts = async () => {
  loading.value = true
  error.value = null

  try {
    const params = new URLSearchParams()

    if (route.query.search) {
      params.append('search', String(route.query.search))
    }

    if (route.query.gender) {
      params.append('gender', String(route.query.gender))
    }

    if (route.query.category) {
      params.append('category', String(route.query.category))
    }

    if (route.query.parent_category) {
      params.append('parent_category', String(route.query.parent_category))
    }

    if (route.query.section) {
      params.append('section', String(route.query.section))
    }

    if (route.query.brand) {
      params.append('brand', String(route.query.brand))
    }

    if (route.query.size) {
      params.append('size', String(route.query.size))
    }

    if (route.query.color) {
      params.append('color', String(route.query.color))
    }

    if (route.query.condition) {
      params.append('condition', String(route.query.condition))
    }

    if (route.query.min_price) {
      params.append('min_price', String(route.query.min_price))
    }

    if (route.query.max_price) {
      params.append('max_price', String(route.query.max_price))
    }

    if (route.query.has_images) {
      params.append('has_images', String(route.query.has_images))
    }

    if (route.query.sort) {
      params.append('sort', String(route.query.sort))
    }

    const queryString = params.toString()

    const url = queryString
      ? `${API_URL}/api/listings?${queryString}`
      : `${API_URL}/api/listings`

    const response = await fetchWithAuth(url, {
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
      error.value = data.message || st.value.loadError
      products.value = []
      return
    }

    products.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Fetch listings error:', err)
    error.value = st.value.connectionError
    products.value = []
  } finally {
    loading.value = false
  }
}

// Reset filters: clears sidebar filters but keeps main category/search navigation
const resetFilters = () => {
  brandFilter.value = ''
  sizeFilter.value = ''
  colorFilter.value = ''
  conditionFilter.value = ''
  minPrice.value = ''
  maxPrice.value = ''
  onlyWithImage.value = false
  sortOption.value = 'newest'
  categoryFilter.value = ''

  router.replace({
    path: '/shop',
    query: {
      search: route.query.search || undefined,
      gender: route.query.gender || undefined,
      section: route.query.section || undefined,
      parent_category: route.query.parent_category || undefined,
      category: route.query.category || undefined,
    },
  })
}

// Open listing: navigates to listing details page
const viewProduct = (product: Product) => {
  router.push(`/listing/${product.id}`)
}

// Select color: saves selected color and closes dropdown
const selectFilterColor = (colorName: string) => {
  colorFilter.value = colorName
  isColorFilterOpen.value = false
}

// Format text: fallback formatter for unknown backend values
const formatText = (value?: string | null) => {
  if (!value) return ''

  return value.charAt(0).toUpperCase() + value.slice(1)
}

// Format price: displays rounded euro price
const formatPrice = (price: number | string) => {
  return `${Math.round(Number(price))} €`
}

// Click outside: closes color dropdown when user clicks outside
const handleColorFilterClickOutside = (event: MouseEvent) => {
  const target = event.target as Node

  if (
    colorFilterDropdownRef.value &&
    !colorFilterDropdownRef.value.contains(target)
  ) {
    isColorFilterOpen.value = false
  }
}

// Route watcher: reloads products when URL filters change
watch(
  () => route.query,
  () => {
    syncFiltersFromRoute()
    fetchProducts()
  }
)

// Mounted: sync filters, load products and attach listeners
onMounted(() => {
  syncFiltersFromRoute()
  fetchProducts()

  window.addEventListener('language-changed', updateLanguage)
  document.addEventListener('click', handleColorFilterClickOutside)
})

// Before unmount: remove listeners
onBeforeUnmount(() => {
  window.removeEventListener('language-changed', updateLanguage)
  document.removeEventListener('click', handleColorFilterClickOutside)
})
</script>