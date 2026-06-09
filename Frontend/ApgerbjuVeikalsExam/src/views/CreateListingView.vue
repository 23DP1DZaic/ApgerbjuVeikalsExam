<template>
  <div class="auth-page create-listing-page">
    <div class="auth-card create-listing-card">
      <!-- Header: page title and short description -->
      <div class="create-listing-header">
        <div>
          <h1>{{ t.pageTitle }}</h1>
          <p>{{ t.pageSubtitle }}</p>
        </div>
      </div>

      <!-- Main form: novalidate disables ugly browser validation messages -->
      <form class="create-listing-form" novalidate @submit.prevent="createListing">
        <div class="form-section-title full-width">
          {{ t.details }}
        </div>

        <!-- Department select -->
        <div class="form-group">
          <label>{{ t.department }}</label>

        <div :class="{ 'field-error-control': hasFieldError('gender') }">
          <CustomSelect
            v-model="form.gender"
            :placeholder="t.departmentPlaceholder"
            :options="genderOptions"
          />
        </div>
        </div>

        <!-- Parent category select -->
        <div class="form-group">
          <label>{{ t.category }}</label>

        <div :class="{ 'field-error-control': hasFieldError('parentCategory') }">
          <CustomSelect
            v-model="form.parentCategory"
            :placeholder="form.gender ? t.categoryPlaceholder : t.selectDepartmentFirst"
            :options="parentCategoryOptions"
            :disabled="!form.gender"
          />
        </div>
        </div>

        <!-- Sub-category select -->
        <div class="form-group">
          <label>{{ t.subcategory }}</label>

        <div :class="{ 'field-error-control': hasFieldError('category') }">
          <CustomSelect
            v-model="form.category"
            :placeholder="form.parentCategory ? t.subcategoryPlaceholder : t.selectCategoryFirst"
            :options="subcategoryOptions"
            :disabled="!form.parentCategory"
          />
        </div>
        </div>

        <!-- Brand select -->
        <div class="form-group">
          <label>{{ t.brand }}</label>

        <div :class="{ 'field-error-control': hasFieldError('brand') }">
          <CustomSelect
            v-model="form.brand"
            :placeholder="form.category ? t.brandPlaceholder : t.selectSubcategoryFirst"
            :options="brandOptions"
            :disabled="!form.category"
          />
        </div>

          <p class="field-help">
            {{ t.brandHelp }}
          </p>
        </div>

        <!-- Size select: hidden for accessories, changes options by category -->
        <div v-if="!isAccessoryCategory" class="form-group">
          <label>{{ t.size }}</label>

        <div :class="{ 'field-error-control': hasFieldError('size') }">
          <CustomSelect
            v-model="form.size"
            :placeholder="
              isFootwearCategory
                ? t.shoeSizePlaceholder
                : isBottomsCategory
                  ? t.pantsSizePlaceholder
                  : t.clothingSizePlaceholder
            "
            :options="sizeOptions"
            :disabled="!form.category"
          />
        </div>
        </div>

        <!-- Item title input -->
        <div class="form-group">
          <label>{{ t.itemName }}</label>

        <input
          v-model="form.title"
          :placeholder="t.itemNamePlaceholder"
          :class="{ 'field-error-input': hasFieldError('title') }"
        >
        </div>

        <!-- Price input -->
        <div class="form-group">
          <label>{{ t.price }}</label>

        <input
          v-model="form.price"
          type="text"
          inputmode="numeric"
          :placeholder="t.pricePlaceholder"
          :class="{ 'field-error-input': hasFieldError('price') }"
          @input="onlyNumbers"
        >
        </div>

        <!-- Custom color dropdown -->
        <div
          ref="colorDropdownRef"
          class="form-group color-dropdown-wrapper"
        >
          <label>{{ t.color }}</label>

          <button
            type="button"
            class="custom-color-select"
            :class="{ 'field-error-input': hasFieldError('color') }"
            @click="isColorDropdownOpen = !isColorDropdownOpen"
          >
            <span
              v-if="selectedColor"
              class="color-dot"
              :class="{ 'white-dot': selectedColor.name === 'White' }"
              :style="{ background: selectedColor.value }"
            ></span>

            <span>
              {{ selectedColor ? colorLabel(selectedColor.name) : t.colorPlaceholder }}
            </span>

            <span class="custom-select-arrow">⌄</span>
          </button>

          <div v-if="isColorDropdownOpen" class="custom-color-menu">
            <button
              v-for="color in colors"
              :key="color.name"
              type="button"
              class="custom-color-option"
              @click="selectColor(color.name)"
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

        <!-- Condition select -->
        <div class="form-group">
          <label>{{ t.condition }}</label>

        <div :class="{ 'field-error-control': hasFieldError('condition') }">
          <CustomSelect
            v-model="form.condition"
            :placeholder="t.conditionPlaceholder"
            :options="conditionOptions"
          />
        </div>
        </div>

        <!-- Description textarea -->
        <div class="form-group full-width">
          <label>{{ t.description }}</label>

          <textarea
            v-model="form.description"
            :placeholder="t.descriptionPlaceholder"
          ></textarea>
        </div>

        <!-- Photo upload section -->
        <div class="form-group full-width">
          <label>{{ t.photos }}</label>

          <!-- Browse input box -->
          <div
            class="image-upload-box"
            :class="{ 'field-error-input': hasFieldError('photos') }"
          >

            <label class="browse-button">
              <span>{{ t.browse }}</span>

              <input
                ref="imageInput"
                type="file"
                multiple
                accept="image/jpeg,image/png,image/webp"
                class="hidden-file-input"
                @change="handleImagesChange"
              >
            </label>

            <span class="file-status">
              {{ imageFiles.length ? t.filesSelected(imageFiles.length) : t.noFileSelected }}
            </span>
          </div>

          <!-- Photo preview slots -->
          <div class="photo-grid">
            <div
              v-for="index in 5"
              :key="index"
              class="photo-slot"
              :class="{ 'photo-slot-error': hasFieldError('photos') }"
              role="button"
              tabindex="0"
              @click="openImagePicker"
              @keydown.enter="openImagePicker"
            >
              <img
                v-if="imagePreviews[index - 1]"
                :src="imagePreviews[index - 1]"
                alt="Listing photo preview"
                class="photo-preview"
              >

              <span v-else class="photo-placeholder">
                +
              </span>

              <button
                v-if="imagePreviews[index - 1]"
                type="button"
                class="photo-remove-btn"
                @click.stop="removeImage(index - 1)"
              >
                ×
              </button>
            </div>
          </div>

          <small>{{ t.photosHelp }}</small>
        </div>

        <!-- Success message -->
        <p v-if="message" class="success full-width">
          {{ message }}
        </p>

        <!-- Custom error message -->
        <p v-if="error" class="error full-width">
          {{ error }}
        </p>

        <!-- Submit button -->
        <div class="submit-row">
          <button class="auth-button" type="submit">
            <p>{{ t.createListing }}</p>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
// Imports: Vue, router, custom components, auth helpers and API helpers
import { reactive, ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import CustomSelect from '../components/CustomSelect.vue'
import { getUser, getToken, clearAuth } from '../services/auth'
import { API_URL, fetchWithAuth } from '../services/api'

// Type: category object from backend
type Category = {
  id: number
  name: string
  slug: string
  department?: string | null
  parent_id?: number | null
  parent?: {
    id: number
    name: string
  } | null
}

// Type: language values used by language switcher
type Language = 'en' | 'lv'

// Router: used after listing creation
const router = useRouter()

// Page messages: custom success and error blocks
const message = ref('')
const error = ref('')

const submitted = ref(false)

type FieldName =
  | 'gender'
  | 'parentCategory'
  | 'category'
  | 'brand'
  | 'size'
  | 'title'
  | 'price'
  | 'color'
  | 'condition'
  | 'photos'

const missingFields = computed<FieldName[]>(() => {
  const fields: FieldName[] = []

  if (!form.gender) fields.push('gender')
  if (!form.parentCategory) fields.push('parentCategory')
  if (!form.category) fields.push('category')
  if (!form.brand) fields.push('brand')
  if (!isAccessoryCategory.value && !form.size) fields.push('size')
  if (!form.title.trim()) fields.push('title')
  if (!form.price || Number(form.price) <= 0) fields.push('price')
  if (!form.color) fields.push('color')
  if (!form.condition) fields.push('condition')
  if (imageFiles.value.length < 3) fields.push('photos')

  return fields
})

const hasFieldError = (field: FieldName) => {
  return submitted.value && missingFields.value.includes(field)
}

// Language state: reads current language from localStorage
const language = ref<Language>(
  (localStorage.getItem('language') as Language) || 'en'
)

// Translations: all visible text for Add Listing page
const translations = {
  en: {
    pageTitle: 'Add a new listing',
    pageSubtitle: 'Fill in the details below to create your marketplace listing.',
    details: 'Details',

    department: 'Department',
    departmentPlaceholder: 'Men / Women',
    category: 'Category',
    categoryPlaceholder: 'Select category',
    selectDepartmentFirst: 'Select department first',
    subcategory: 'Sub-category',
    subcategoryPlaceholder: 'Select sub-category',
    selectCategoryFirst: 'Select category first',

    brand: 'Designer / Brand',
    brandPlaceholder: 'Select designer / brand',
    selectSubcategoryFirst: 'Select sub-category first',
    brandHelp: "Choose a brand from the list. If you do not know it, choose 'Other'.",

    size: 'Size',
    clothingSizePlaceholder: 'Select clothing size',
    shoeSizePlaceholder: 'Select shoe size',
    pantsSizePlaceholder: 'Select pants size',

    itemName: 'Item name',
    itemNamePlaceholder: 'Item name',
    price: 'Price',
    pricePlaceholder: 'Enter price',
    color: 'Color',
    colorPlaceholder: 'Select color',
    condition: 'Condition',
    conditionPlaceholder: 'Item condition',
    description: 'Description',
    descriptionPlaceholder: 'Add details about condition, fit, measurements, shipping, retail price, etc. Optional.',

    photos: 'Photos',
    browse: 'Browse',
    noFileSelected: 'No file selected',
    filesSelected: (count: number) => `${count} file(s) selected`,
    photosHelp: 'Add at least 3 images. Max 5 images. JPG, PNG, WEBP. Max 2MB each.',

    createListing: 'Create Listing',

    fillRequired: 'Fill in all required fields and add at least 3 photos.',
    loginAgain: 'You need to login again.',
    invalidBrand: 'Please choose a brand from the list.',
    invalidPrice: 'Price must be a valid number.',
    maxImages: 'You can upload maximum 5 images.',
    invalidImages: 'Images must be JPG, PNG or WEBP and max 2MB each.',
    serverError: 'Server connection error.',
    created: 'Listing created successfully.',

    men: 'Men',
    women: 'Women',
    new: 'New',
    used: 'Used',
  },

  lv: {
    pageTitle: 'Pievienot jaunu sludinājumu',
    pageSubtitle: 'Aizpildi informāciju, lai izveidotu sludinājumu.',
    details: 'Informācija',

    department: 'Nodaļa',
    departmentPlaceholder: 'Vīriešiem / Sievietēm',
    category: 'Kategorija',
    categoryPlaceholder: 'Izvēlies kategoriju',
    selectDepartmentFirst: 'Vispirms izvēlies nodaļu',
    subcategory: 'Apakškategorija',
    subcategoryPlaceholder: 'Izvēlies apakškategoriju',
    selectCategoryFirst: 'Vispirms izvēlies kategoriju',

    brand: 'Dizaineris / zīmols',
    brandPlaceholder: 'Izvēlies dizaineri / zīmolu',
    selectSubcategoryFirst: 'Vispirms izvēlies apakškategoriju',
    brandHelp: "Izvēlies zīmolu no saraksta. Ja nezini zīmolu, izvēlies 'Other'.",

    size: 'Izmērs',
    clothingSizePlaceholder: 'Izvēlies apģērba izmēru',
    shoeSizePlaceholder: 'Izvēlies apavu izmēru',
    pantsSizePlaceholder: 'Izvēlies bikšu izmēru',

    itemName: 'Preces nosaukums',
    itemNamePlaceholder: 'Preces nosaukums',
    price: 'Cena',
    pricePlaceholder: 'Ievadi cenu',
    color: 'Krāsa',
    colorPlaceholder: 'Izvēlies krāsu',
    condition: 'Stāvoklis',
    conditionPlaceholder: 'Preces stāvoklis',
    description: 'Apraksts',
    descriptionPlaceholder: 'Pievieno informāciju par stāvokli, izmēru, piegādi, sākotnējo cenu utt. Nav obligāti.',

    photos: 'Fotogrāfijas',
    browse: 'Izvēlēties',
    noFileSelected: 'Fails nav izvēlēts',
    filesSelected: (count: number) => `Izvēlēti faili: ${count}`,
    photosHelp: 'Pievieno vismaz 3 attēlus. Maksimums 5 attēli. JPG, PNG, WEBP. Maks. 2MB katrs.',

    createListing: 'Izveidot sludinājumu',

    fillRequired: 'Aizpildi visus obligātos laukus un pievieno vismaz 3 fotogrāfijas.',
    loginAgain: 'Tev jāpieslēdzas vēlreiz.',
    invalidBrand: 'Lūdzu, izvēlies zīmolu no saraksta.',
    invalidPrice: 'Cenai jābūt derīgam skaitlim.',
    maxImages: 'Var pievienot maksimums 5 attēlus.',
    invalidImages: 'Attēliem jābūt JPG, PNG vai WEBP formātā un līdz 2MB katram.',
    serverError: 'Servera savienojuma kļūda.',
    created: 'Sludinājums veiksmīgi izveidots.',

    men: 'Vīriešiem',
    women: 'Sievietēm',
    new: 'Jauns',
    used: 'Lietots',
  },
}

const t = computed(() => translations[language.value])

// Latvian color labels: used only when LV is selected
const lvColorLabels: Record<string, string> = {
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
}

// Language event: listens to language switcher from header
const handleLanguageChanged = (event: Event) => {
  const nextLanguage = (event as CustomEvent<Language>).detail

  if (nextLanguage === 'en' || nextLanguage === 'lv') {
    language.value = nextLanguage
  }
}

// User: used to check if user is logged in before creating listing
const user = getUser()

// Form state: all listing fields
const form = reactive({
  title: '',
  description: '',
  price: '',
  parentCategory: '',
  category: '',
  brand: '',
  color: '',
  size: '',
  condition: '',
  gender: '',
})

// Images state: real files and browser preview URLs
const imageFiles = ref<File[]>([])
const imageInput = ref<HTMLInputElement | null>(null)
const imagePreviews = ref<string[]>([])

// Categories state: loaded from backend
const categories = ref<Category[]>([])

// Color dropdown state
const isColorDropdownOpen = ref(false)
const colorDropdownRef = ref<HTMLElement | null>(null)

// Select options: department
const genderOptions = computed(() => [
  { label: t.value.men, value: 'men' },
  { label: t.value.women, value: 'women' },
])

// Select options: condition
const conditionOptions = computed(() => [
  { label: t.value.new, value: 'new' },
  { label: t.value.used, value: 'used' },
])

// Brand list: used for CustomSelect instead of browser datalist
const brands = [
  'A-Cold-Wall',
  'Acne Studios',
  'Adidas',
  'Alexander McQueen',
  'Amiri',
  "Arc'teryx",
  'Balenciaga',
  'Bape',
  'Bottega Veneta',
  'Burberry',
  'Carhartt',
  'Celine',
  'Chanel',
  'Chrome Hearts',
  'Comme des Garcons',
  'CP Company',
  'Diesel',
  'Dior',
  'Dries Van Noten',
  'Enfants Riches Déprimés',
  'Fear of God',
  'Fendi',
  'Ganni',
  'Givenchy',
  'Gucci',
  'Helmut Lang',
  'Issey Miyake',
  'Jacquemus',
  'Jil Sander',
  'Jordan',
  'Kapital',
  'Kiko Kostadinov',
  'Loewe',
  'Louis Vuitton',
  'Maison Margiela',
  'Miu Miu',
  'Moncler',
  'New Balance',
  'Nike',
  'Off-White',
  'Our Legacy',
  'Palace',
  'Polo Ralph Lauren',
  'Prada',
  'Raf Simons',
  'Rick Owens',
  'Saint Laurent Paris',
  'Salomon',
  'Stone Island',
  'Stussy',
  'Supreme',
  'The North Face',
  'Undercover',
  'Valentino',
  'Vetements',
  'Vintage',
  'Vivienne Westwood',
  'Y-3',
  'Yohji Yamamoto',
  'Zara',
  'Other',
].sort((a, b) => a.localeCompare(b))

const brandOptions = computed(() => {
  return brands.map((brand) => ({
    label: brand,
    value: brand,
  }))
})

// Category groups: used to change size field behavior
const footwearCategoryNames = [
  'Boots',
  'Casual Leather Shoes',
  'Formal Shoes',
  'Hi-Top Sneakers',
  'Low-Top Sneakers',
  'High-Top Sneakers',
  'Sandals',
  'Slip Ons',
  'Sneakers',
  'Footwear',
]

const accessoryCategoryNames = [
  'Accessories',
  'Bags',
  'Jewelry',
]

const bottomsCategoryNames = [
  'Bottoms',
  'Denim',
  'Pants',
  'Shorts',
  'Skirts',
]

// Colors: shown in custom color dropdown
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

// Selected color: finds full color object by selected name
const selectedColor = computed(() => {
  return colors.find((color) => color.name === form.color) || null
})

// Color label: translates colors when Latvian is active
const colorLabel = (colorName: string) => {
  if (language.value === 'lv') {
    return lvColorLabels[colorName] || colorName
  }

  return colorName
}

// Parent categories: top-level categories for selected department
const parentCategories = computed(() => {
  if (!form.gender) return []

  return categories.value
    .filter((category) => {
      return category.department === form.gender && category.parent_id === null
    })
    .sort((a, b) => a.name.localeCompare(b.name))
})

// Parent category options: CustomSelect format
const parentCategoryOptions = computed(() => {
  return parentCategories.value.map((category) => ({
    label: category.name,
    value: String(category.id),
  }))
})

// Subcategories: children of selected parent category
const subcategories = computed(() => {
  if (!form.parentCategory) return []

  return categories.value
    .filter((category) => {
      return String(category.parent_id) === String(form.parentCategory)
    })
    .sort((a, b) => a.name.localeCompare(b.name))
})

// Subcategory options: CustomSelect format
const subcategoryOptions = computed(() => {
  return subcategories.value.map((category) => ({
    label: category.name,
    value: category.name,
  }))
})

// Selected parent category: used for checking category group
const selectedParentCategory = computed(() => {
  return parentCategories.value.find((category) => {
    return String(category.id) === String(form.parentCategory)
  })
})

// Selected category names: normalized for comparisons
const selectedCategoryName = computed(() => {
  return form.category.toLowerCase()
})

const selectedParentCategoryName = computed(() => {
  return selectedParentCategory.value?.name?.toLowerCase() || ''
})

// Category matching helper: checks selected category or parent category
const categoryMatches = (names: string[]) => {
  return names.some((name) => {
    const normalizedName = name.toLowerCase()

    return (
      normalizedName === selectedCategoryName.value ||
      normalizedName === selectedParentCategoryName.value
    )
  })
}

// Category checks: control size field and size options
const isFootwearCategory = computed(() => {
  return categoryMatches(footwearCategoryNames)
})

const isAccessoryCategory = computed(() => {
  return categoryMatches(accessoryCategoryNames)
})

const isBottomsCategory = computed(() => {
  return categoryMatches(bottomsCategoryNames)
})

// Size options: clothing, shoes, or pants depending on selected category
const clothingSizeOptions = [
  { value: 'XXS', label: 'XXS' },
  { value: 'XS', label: 'XS' },
  { value: 'S', label: 'S' },
  { value: 'M', label: 'M' },
  { value: 'L', label: 'L' },
  { value: 'XL', label: 'XL' },
  { value: 'XXL', label: 'XXL' },
]

const shoeSizeOptions = [
  { value: '35', label: '35' },
  { value: '36', label: '36' },
  { value: '37', label: '37' },
  { value: '38', label: '38' },
  { value: '39', label: '39' },
  { value: '40', label: '40' },
  { value: '41', label: '41' },
  { value: '42', label: '42' },
  { value: '43', label: '43' },
  { value: '44', label: '44' },
  { value: '45', label: '45' },
  { value: '46', label: '46' },
  { value: '47', label: '47' },
]

const pantsSizeOptions = [
  { value: 'W26', label: 'W26' },
  { value: 'W28', label: 'W28' },
  { value: 'W30', label: 'W30' },
  { value: 'W32', label: 'W32' },
  { value: 'W34', label: 'W34' },
  { value: 'W36', label: 'W36' },
  { value: 'W38', label: 'W38' },
]

const sizeOptions = computed(() => {
  if (isFootwearCategory.value) {
    return shoeSizeOptions
  }

  if (isBottomsCategory.value) {
    return pantsSizeOptions
  }

  return clothingSizeOptions
})

// Watchers: reset dependent fields when higher-level selection changes
watch(
  () => form.gender,
  () => {
    form.parentCategory = ''
    form.category = ''
    form.size = ''
    form.brand = ''
  }
)

watch(
  () => form.parentCategory,
  () => {
    form.category = ''
    form.size = ''
    form.brand = ''
  }
)

watch(
  () => form.category,
  () => {
    form.size = ''
    form.brand = ''
  }
)

// Price input helper: only allows digits
const onlyNumbers = () => {
  form.price = form.price.replace(/\D/g, '')
}

// Categories loader: loads categories from backend API
const loadCategories = async () => {
  try {
    const response = await fetch(`${API_URL}/api/categories`, {
      headers: {
        Accept: 'application/json',
      },
    })

    const rawText = await response.text()

    let data: any = null

    try {
      data = JSON.parse(rawText)
    } catch {
      data = []
    }

    if (!response.ok) {
      console.error('Failed to load categories:', data)
      return
    }

    categories.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Load categories error:', err)
  }
}

// Image picker: opens hidden file input
const openImagePicker = () => {
  imageInput.value?.click()
}

// Image upload: validates, limits to 5 and creates previews
const handleImagesChange = (event: Event) => {
  const input = event.target as HTMLInputElement

  if (!input.files) return

  error.value = ''

  const newFiles = Array.from(input.files)
  const totalFiles = imageFiles.value.length + newFiles.length

  if (totalFiles > 5) {
    error.value = t.value.maxImages
    input.value = ''
    return
  }

  const invalidFile = newFiles.find((file) => {
    const isValidType = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)
    const isValidSize = file.size <= 2 * 1024 * 1024

    return !isValidType || !isValidSize
  })

  if (invalidFile) {
    error.value = t.value.invalidImages
    input.value = ''
    return
  }

  newFiles.forEach((file) => {
    imageFiles.value.push(file)
    imagePreviews.value.push(URL.createObjectURL(file))
  })

  input.value = ''
}

// Image remove: deletes selected file and preview, then empty plus slot returns
const removeImage = (index: number) => {
  const previewUrl = imagePreviews.value[index]

  if (previewUrl) {
    URL.revokeObjectURL(previewUrl)
  }

  imageFiles.value.splice(index, 1)
  imagePreviews.value.splice(index, 1)
}

// Color select: saves selected color and closes dropdown
const selectColor = (colorName: string) => {
  form.color = colorName
  isColorDropdownOpen.value = false
}

// Click outside: closes color dropdown if user clicks outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as Node

  if (
    colorDropdownRef.value &&
    !colorDropdownRef.value.contains(target)
  ) {
    isColorDropdownOpen.value = false
  }
}

// Create listing: validates fields and sends FormData to backend
const createListing = async () => {
  message.value = ''
  error.value = ''
  submitted.value = true

  const token = getToken()

  if (!user || !token) {
    error.value = t.value.loginAgain
    clearAuth()
    router.push('/login')
    return
  }

  if (missingFields.value.length > 0) {
    error.value = t.value.fillRequired
    return
  }

  const normalizedBrand = form.brand.trim().toLowerCase()

  const selectedBrand = brands.find((brand) => {
    return brand.toLowerCase() === normalizedBrand
  })

  if (!selectedBrand) {
    error.value = t.value.invalidBrand
    return
  }

  const priceNumber = Number(form.price)

  if (Number.isNaN(priceNumber) || priceNumber <= 0) {
    error.value = t.value.invalidPrice
    return
  }

  const formData = new FormData()

  formData.append('title', form.title)
  formData.append('description', form.description || '')
  formData.append('price', String(priceNumber))
  formData.append('category', form.category)
  formData.append('gender', form.gender)
  formData.append('brand', selectedBrand)
  formData.append('color', form.color)
  formData.append('size', isAccessoryCategory.value ? 'One Size' : form.size)
  formData.append('condition', form.condition)

  imageFiles.value.forEach((file) => {
    formData.append('images[]', file)
  })

  try {
    const response = await fetchWithAuth(`${API_URL}/api/listings`, {
      method: 'POST',
      body: formData,
    })

    const rawText = await response.text()

    let data: any = null

    try {
      data = JSON.parse(rawText)
    } catch {
      data = { message: rawText }
    }

    if (!response.ok) {
      error.value = data.message || `Request failed with status ${response.status}`
      console.error('Create listing backend error:', data)
      return
    }

    message.value = t.value.created
    submitted.value = false

    setTimeout(() => {
      router.push('/shop')
    }, 1000)
  } catch (err) {
    console.error('Create listing fetch error:', err)
    error.value = t.value.serverError
  }
}

// Mounted: add listeners and load categories
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('language-changed', handleLanguageChanged)
  loadCategories()
})

// Before unmount: remove listeners and clean preview URLs
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('language-changed', handleLanguageChanged)

  imagePreviews.value.forEach((previewUrl) => {
    URL.revokeObjectURL(previewUrl)
  })
})
</script>
