<template>
  <div class="auth-page create-listing-page">
    <div class="auth-card create-listing-card">
      <div class="create-listing-header">
        <div>
          <h1>Add a new listing</h1>
          <p>Fill in the details below to create your marketplace listing.</p>
        </div>

        <!-- <router-link to="/about" class="sell-guide-link">
          How to sell guide →
        </router-link> -->
      </div>

      <form class="create-listing-form" @submit.prevent="createListing">
        <div class="form-section-title full-width">
          Details
        </div>

        <div class="form-group">
          <label>Department</label>

          <CustomSelect
            v-model="form.gender"
            placeholder="Men / Women"
            :options="genderOptions"
          />
        </div>

        <div class="form-group">
          <label>Category</label>

          <CustomSelect
            v-model="form.parentCategory"
            :placeholder="form.gender ? 'Select category' : 'Select department first'"
            :options="parentCategoryOptions"
            :disabled="!form.gender"
          />
        </div>

        <div class="form-group">
          <label>Sub-category</label>

          <CustomSelect
            v-model="form.category"
            :placeholder="form.parentCategory ? 'Select sub-category' : 'Select category first'"
            :options="subcategoryOptions"
            :disabled="!form.parentCategory"
          />
        </div>

        <div class="form-group">
          <label>Designer / Brand</label>

          <input
            v-model="form.brand"
            list="brand-list"
            :placeholder="form.category ? 'Nike, Adidas, Zara, Other...' : 'Select sub-category first'"
            :disabled="!form.category"
            required
          >

          <datalist id="brand-list">
            <option
              v-for="brand in brands"
              :key="brand"
              :value="brand"
            />
          </datalist>

          <p class="field-help">
            Choose a brand from the list. If you do not know it, choose 'Other'.
          </p>
        </div>

        <div class="form-group">
          <label>Size</label>

          <CustomSelect
            v-model="form.size"
            :placeholder="isFootwearCategory ? 'Select shoe size' : 'Select clothing size'"
            :options="sizeOptions"
            :disabled="!form.category"
          />
        </div>

        <div class="form-group">
          <label>Item name</label>

          <input
            v-model="form.title"
            placeholder="Item name"
            required
          >
        </div>

        <div class="form-group">
          <label>Price</label>

          <input
            v-model="form.price"
            type="text"
            inputmode="numeric"
            placeholder="Enter price"
            required
            @input="onlyNumbers"
          >
        </div>

        <div
          class="form-group color-dropdown-wrapper"
          ref="colorDropdownRef"
        >
          <label>Color</label>

          <button
            type="button"
            class="custom-color-select"
            @click="isColorDropdownOpen = !isColorDropdownOpen"
          >
          <span
            v-if="selectedColor"
            class="color-dot"
            :class="{ 'white-dot': selectedColor.name === 'White' }"
            :style="{ background: selectedColor.value }"
          ></span>

            <span>{{ selectedColor ? selectedColor.name : 'Select color' }}</span>

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

            <span>{{ color.name }}</span>
          </button>
          </div>
        </div>

        <div class="form-group">
          <label>Condition</label>

          <CustomSelect
            v-model="form.condition"
            placeholder="Item condition"
            :options="conditionOptions"
          />
        </div>

        <div class="form-group full-width">
          <label>Description</label>

          <textarea
            v-model="form.description"
            placeholder="Add details about condition, fit, measurements, shipping, retail price, etc. Optional."
          ></textarea>
        </div>

        <div class="form-group full-width">
          <label>Images</label>

          <div class="file-upload-box">
            <input
              id="listing-images"
              class="file-input-hidden"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              @change="addImage"
            >

            <label for="listing-images" class="file-upload-btn">
              Browse
            </label>

            <span class="file-upload-text">
              {{ imageFiles.length ? `${imageFiles.length} image(s) selected` : 'No file selected' }}
            </span>
          </div>

          <p class="image-help">
            Add 1–5 images. JPG, PNG, WEBP. Max 2MB each.
          </p>

        <div v-if="imagePreviews.length" class="image-preview-grid">
          <div
            v-for="(preview, index) in imagePreviews"
            :key="preview"
            class="image-preview-card"
          >
            <img
              :src="preview"
              :alt="imageFiles[index]?.name || 'Listing image'"
            >

            <div class="image-preview-info">
              <p class="image-preview-name">
                {{ imageFiles[index]?.name || 'Listing image' }}
              </p>

              <button
                type="button"
                class="image-remove-btn"
                @click="removeImage(index)"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
        </div>

        <p v-if="message" class="success full-width">
          {{ message }}
        </p>

        <p v-if="error" class="error full-width">
          {{ error }}
        </p>

        <div class="submit-row">
          <button class="auth-button" type="submit">
            <p>Create Listing</p>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import CustomSelect from '../components/CustomSelect.vue'
import { getUser, getToken, clearAuth } from '../services/auth'
import { API_URL, fetchWithAuth } from '../services/api'

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

const router = useRouter()

const message = ref('')
const error = ref('')

const imageFiles = ref<File[]>([])
const imagePreviews = ref<string[]>([])
const categories = ref<Category[]>([])

const user = getUser()

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

const genderOptions = [
  { label: 'Men', value: 'men' },
  { label: 'Women', value: 'women' },
]

const conditionOptions = [
  { label: 'New', value: 'new' },
  { label: 'Used', value: 'used' },
]

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

const clothingSizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL']
const shoeSizes = [
  '35',
  '36',
  '37',
  '38',
  '39',
  '40',
  '41',
  '42',
  '43',
  '44',
  '45',
  '46',
  '47',
]

const footwearCategoryNames = [
  'Boots',
  'Casual Leather Shoes',
  'Formal Shoes',
  'Hi-Top Sneakers',
  'Low-Top Sneakers',
  'Sandals',
  'Slip Ons',
  'Sneakers',
  'Footwear',
]

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

const selectedColor = computed(() => {
  return colors.find((color) => color.name === form.color) || null
})

const isFootwearCategory = computed(() => {
  return footwearCategoryNames.some((name) => {
    return name.toLowerCase() === form.category.toLowerCase()
  })
})

const availableSizes = computed(() => {
  return isFootwearCategory.value ? shoeSizes : clothingSizes
})

const sizeOptions = computed(() => {
  return availableSizes.value.map((size) => ({
    label: size,
    value: size,
  }))
})

const parentCategories = computed(() => {
  if (!form.gender) return []

  return categories.value
    .filter((category) => {
      return category.department === form.gender && category.parent_id === null
    })
    .sort((a, b) => a.name.localeCompare(b.name))
})

const parentCategoryOptions = computed(() => {
  return parentCategories.value.map((category) => ({
    label: category.name,
    value: String(category.id),
  }))
})

const subcategories = computed(() => {
  if (!form.parentCategory) return []

  return categories.value
    .filter((category) => {
      return String(category.parent_id) === String(form.parentCategory)
    })
    .sort((a, b) => a.name.localeCompare(b.name))
})

const subcategoryOptions = computed(() => {
  return subcategories.value.map((category) => ({
    label: category.name,
    value: category.name,
  }))
})



watch(
  () => form.gender,
  () => {
    form.parentCategory = ''
    form.category = ''
    form.size = ''
  }
)

watch(
  () => form.parentCategory,
  () => {
    form.category = ''
    form.size = ''
  }
)

watch(
  () => form.category,
  () => {
    form.size = ''
  }
)

const onlyNumbers = () => {
  form.price = form.price.replace(/\D/g, '')
}

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

const addImage = (event: Event) => {
  const input = event.target as HTMLInputElement

  if (!input.files || !input.files[0]) return

  if (imageFiles.value.length >= 8) {
    error.value = 'You can add maximum 8 images'
    input.value = ''
    return
  }

  const file = input.files[0]

  if (file.size > 2 * 1024 * 1024) {
    error.value = 'Image must be less than 2MB'
    input.value = ''
    return
  }

  const previewUrl = URL.createObjectURL(file)

  imageFiles.value.push(file)
  imagePreviews.value.push(previewUrl)

  error.value = ''
  input.value = ''
}

const removeImage = (index: number) => {
  const previewUrl = imagePreviews.value[index]

  if (previewUrl) {
    URL.revokeObjectURL(previewUrl)
  }

  imageFiles.value.splice(index, 1)
  imagePreviews.value.splice(index, 1)
}

const selectColor = (colorName: string) => {
  form.color = colorName
  isColorDropdownOpen.value = false
}

const createListing = async () => {
  message.value = ''
  error.value = ''

  const token = getToken()

  if (!user || !token) {
    error.value = 'You need to login again'
    clearAuth()
    router.push('/login')
    return
  }

  const normalizedBrand = form.brand.trim().toLowerCase()

  const selectedBrand = brands.find(
    (brand) => brand.toLowerCase() === normalizedBrand
  )

  if (!selectedBrand) {
    error.value = 'Please choose a brand from the list'
    return
  }

  const priceNumber = Number(form.price)

  if (Number.isNaN(priceNumber) || priceNumber <= 0) {
    error.value = 'Price must be a valid number'
    return
  }

  if (
    !form.title ||
    !form.price ||
    !form.parentCategory ||
    !form.category ||
    !form.gender ||
    !form.brand ||
    !form.color ||
    !form.size ||
    !form.condition ||
    imageFiles.value.length === 0
  ) {
    error.value = 'Fill in all required fields'
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
  formData.append('size', form.size)
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

    message.value = 'Listing created successfully'

    setTimeout(() => {
      router.push('/shop')
    }, 1000)
  } catch (err) {
    console.error('Create listing fetch error:', err)
    error.value = 'Server connection error'
  }
}

const isColorDropdownOpen = ref(false)
const colorDropdownRef = ref<HTMLElement | null>(null)

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as Node

  if (
    colorDropdownRef.value &&
    !colorDropdownRef.value.contains(target)
  ) {
    isColorDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  loadCategories()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)

  imagePreviews.value.forEach((previewUrl) => {
    URL.revokeObjectURL(previewUrl)
  })
})
</script>