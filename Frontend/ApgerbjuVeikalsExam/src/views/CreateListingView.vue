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

        <div class="form-group">
          <label>Color</label>
          <select v-model="form.color" required>
            <option value="">Select color</option>
            <option value="Black">Black</option>
            <option value="White">White</option>
            <option value="Blue">Blue</option>
            <option value="Red">Red</option>
            <option value="Green">Green</option>
            <option value="Brown">Brown</option>
            <option value="Grey">Grey</option>
            <option value="Beige">Beige</option>
          </select>
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
          <label>Image</label>
          <input
            type="file"
            accept="image/jpeg,image/png,image/webp"
            @change="handleImage"
            required
          >
        </div>

        <button class="auth-button" type="submit">
          Create listing
        </button>

        <p v-if="message" class="success">{{ message }}</p>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const message = ref('')
const error = ref('')
const imageFile = ref<File | null>(null)

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

const handleImage = (event: Event) => {
  const input = event.target as HTMLInputElement

  if (input.files && input.files[0]) {
    imageFile.value = input.files[0]
  } else {
    imageFile.value = null
  }
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
    !imageFile.value
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
  formData.append('image', imageFile.value)

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
</script>