<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Add Listing</h1>

      <form @submit.prevent="createListing">
        <div class="form-group">
          <label>Title</label>
          <input v-model="form.title" required>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea v-model="form.description" required></textarea>
        </div>

        <div class="form-group">
          <label>Price</label>
          <input v-model="form.price" type="number" min="1" required>
        </div>

        <div class="form-group">
          <label>Category</label>
          <select v-model="form.category" required>
            <option value="">Select category</option>
            <option value="Tshirt">Tshirt</option>
            <option value="Jeans">Jeans</option>
            <option value="Hoodie">Hoodie</option>
            <option value="Jacket">Jacket</option>
            <option value="Shirt">Shirt</option>
          </select>
        </div>

        <div class="form-group">
          <label>Brand</label>
          <input v-model="form.brand" placeholder="Nike, Adidas, Zara..." required>
        </div>

        <div class="form-group">
          <label>Condition</label>
          <select v-model="form.condition" required>
            <option value="">Select condition</option>
            <option value="new">New</option>
            <option value="used">Used</option>
          </select>
        </div>

<div class="form-group color-dropdown-wrapper"
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
      :style="{ background: selectedColor.value }"
    ></span>

    <span>{{ selectedColor ? selectedColor.name : 'Select color' }}</span>
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
        :style="{ background: color.value }"
      ></span>

      <span>{{ color.name }}</span>
    </button>
  </div>
</div>

        <div class="form-group">
          <label>Size</label>
          <select v-model="form.size" required>
            <option value="">Select size</option>
            <option value="XS">XS</option>
            <option value="S">S</option>
            <option value="M">M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>
          </select>
        </div>

        <!-- add brand list -->
<!-- список потому что пользователи могут напечатать не правилньо -->

<div class="form-group">
  <label>Images</label>

  <input
    type="file"
    accept="image/jpeg,image/png,image/webp"
    @change="addImage"
  >

  <p class="image-help">
    Add 1–5 images. JPG, PNG, WEBP. Max 2MB each.
  </p>

  <div v-if="imageFiles.length" class="selected-images">
    <div
      v-for="(file, index) in imageFiles"
      :key="index"
      class="selected-image"
    >
      <span>{{ file.name }}</span>
      <button type="button" @click="removeImage(index)">Remove</button>
    </div>
  </div>
</div>

        <button class="auth-button" type="submit">
        <p>Create Listing</p>
        </button>

        <p v-if="message" class="success">{{ message }}</p>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'


const router = useRouter()

const message = ref('')
const error = ref('')
const imageFiles = ref<File[]>([])

const savedUser = localStorage.getItem('user')
const user = savedUser ? JSON.parse(savedUser) : null

const form = reactive({
  title: '',
  description: '',
  price: '',
  category: '',
  brand: '',
  color: '',
  size: '',
  condition: '',
})

const addImage = (event: Event) => {
  const input = event.target as HTMLInputElement

  if (!input.files || !input.files[0]) return

  if (imageFiles.value.length >= 5) {
    error.value = 'You can add maximum 5 images'
    input.value = ''
    return
  }

  const file = input.files[0]

  if (file.size > 2 * 1024 * 1024) {
    error.value = 'Image must be less than 2MB'
    input.value = ''
    return
  }

  imageFiles.value.push(file)
  error.value = ''
  input.value = ''
}

const removeImage = (index: number) => {
  imageFiles.value.splice(index, 1)
}

const createListing = async () => {
  message.value = ''
  error.value = ''

  if (!user) {
    error.value = 'You need to login first'
    router.push('/login')
    return
  }

  if (
    !form.title ||
    !form.description ||
    !form.price ||
    !form.category ||
    !form.brand ||
    !form.color ||
    !form.size ||
    !form.condition ||
    !imageFiles.value
  ) {
    error.value = 'Fill in all fields'
    return
  }

  const formData = new FormData()

  formData.append('user_id', String(user.id))
  formData.append('title', form.title)
  formData.append('description', form.description)
  formData.append('price', form.price)
  formData.append('category', form.category)
  formData.append('brand', form.brand)
  formData.append('color', form.color)
  formData.append('size', form.size)
  formData.append('condition', form.condition)


  imageFiles.value.forEach((file) => {
    formData.append('images[]', file)
  })

  // const response = await fetch(`${import.meta.env.VITE_API_URL}/api/listings`, {
  //   method: 'POST',
  //   body: formData,
  // })

  const response = await fetch('http://127.0.0.1:8000/api/listings', {
  method: 'POST',
  body: formData,
})

  const data = await response.json()

  if (!response.ok) {
    error.value = data.message || 'Failed to create listing'
    return
  }

  message.value = 'Success'

  setTimeout(() => {
    router.push('/shop')
  }, 1200)
}


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
  { name: 'Multi', value: 'linear-gradient(135deg, red, orange, yellow, green, blue, purple)' },
  { name: 'Silver', value: 'linear-gradient(135deg, #777, #eee, #aaa)' },
  { name: 'Gold', value: 'linear-gradient(135deg, #b99b22, #f3e37c, #c8a600)' },
]

const selectedColor = computed(() => {
  return colors.find(color => color.name === form.color) || null
})

const selectColor = (colorName: string) => {
  form.color = colorName
  isColorDropdownOpen.value = false
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
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

</script>