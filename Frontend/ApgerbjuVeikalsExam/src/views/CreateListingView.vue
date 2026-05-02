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
          <textarea v-model="form.description"></textarea>
        </div>

        <div class="form-group">
          <label>Price</label>
          <input v-model="form.price" type="number" required>
        </div>

        <div class="form-group">
          <label>Category</label>
          <select v-model="form.category" required>
            <option value="Tshirt">Tshirt</option>
            <option value="Jeans">Jeans</option>
            <option value="Hoodie">Hoodie</option>
            <option value="Jacket">Jacket</option>
            <option value="Shirt">Shirt</option>
          </select>
        </div>

        <div class="form-group">
          <label>Condition</label>
          <select v-model="form.condition" required>
            <option value="new">New</option>
            <option value="used">Used</option>
          </select>
        </div>

        <div class="form-group">
            <label>Color</label>
            <select v-model="form.color">
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
            <select v-model="form.size">
                <option value="">Select size</option>
                <option value="XS">XS</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
            </select>
        </div>

        <div class="form-group">
            <label>Image</label>
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    @change="handleImage">
    </div>

        <!-- add brand list -->
<!-- список потому что пользователи могут напечатать не правилньо -->

        <button class="auth-button">Create listing</button>

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
  condition: 'used',
})

const createListing = async () => {
  message.value = ''
  error.value = ''

  if (!user) {
    error.value = 'You need to login first'
    router.push('/login')
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

    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const response = await fetch('http://127.0.0.1:8000/api/listings', {
      method: 'POST',
      body: formData,
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to create listing'
      return
    }

  }

  message.value = 'Listing created successfully'
  // router.push('/shop')


const imageFile = ref<File | null>(null)

const handleImage = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files && input.files[0]) {
    imageFile.value = input.files[0]
  }
}
</script>