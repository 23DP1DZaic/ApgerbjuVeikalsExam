<template>
  <div class="admin-page">
    <div class="admin-card">
      <h1>Admin Categories</h1>
      <p>Manage marketplace categories</p>

      <form class="category-form" @submit.prevent="createCategory">
        <input
          v-model="newCategoryName"
          type="text"
          placeholder="New category name"
          required
        >
        <button type="submit" :disabled="loadingCreate">
          {{ loadingCreate ? 'Adding...' : 'Add Category' }}
        </button>
      </form>

      <p v-if="message" class="success">{{ message }}</p>
      <p v-if="error" class="error">{{ error }}</p>

      <div class="category-list">
        <div
          v-for="category in categories"
          :key="category.id"
          class="category-item"
        >
          <div>
            <strong>{{ category.name }}</strong>
            <p>{{ category.slug }}</p>
          </div>

          <button
            class="delete-btn"
            :disabled="deletingId === category.id"
            @click="deleteCategory(category.id)"
          >
            {{ deletingId === category.id ? 'Deleting...' : 'Delete' }}
          </button>
        </div>

        <p v-if="!categories.length" class="empty-text">No categories yet.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getUser } from '../services/auth'

type Category = {
  id: number
  name: string
  slug: string
}

const router = useRouter()

const categories = ref<Category[]>([])
const newCategoryName = ref('')
const message = ref('')
const error = ref('')
const loadingCreate = ref(false)
const deletingId = ref<number | null>(null)

const loadCategories = async () => {
  try {
    const response = await fetch(`${API_URL}/api/categories`, {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to load categories'
      return
    }

    categories.value = data
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  }
}

const createCategory = async () => {
  message.value = ''
  error.value = ''
  loadingCreate.value = true

  try {
    const response = await fetchWithAuth(`${API_URL}/api/categories`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: newCategoryName.value,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to create category'
      return
    }

    message.value = data.message || 'Category created successfully'
    newCategoryName.value = ''
    loadCategories()
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  } finally {
    loadingCreate.value = false
  }
}

const deleteCategory = async (id: number) => {
  if (!confirm('Delete this category?')) return

  message.value = ''
  error.value = ''
  deletingId.value = id

  try {
    const response = await fetchWithAuth(`${API_URL}/api/categories/${id}`, {
      method: 'DELETE',
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to delete category'
      return
    }

    message.value = data.message || 'Category deleted successfully'
    loadCategories()
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  } finally {
    deletingId.value = null
  }
}

onMounted(() => {
  const user = getUser()

  if (!user || user.role !== 'admin') {
    router.push('/')
    return
  }

  loadCategories()
})
</script>
