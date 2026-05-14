<template>
  <div class="admin-page">
    <div class="admin-card">
      <h1>Admin Categories</h1>
      <p>Manage marketplace categories and subcategories</p>

      <form class="category-form-block" @submit.prevent="createCategory">
        <div class="form-group">
          <label>Name</label>
          <input
            v-model="newCategoryName"
            type="text"
            placeholder="Category name"
            required
          >
        </div>

        <div class="form-group">
          <label>Department</label>
          <select v-model="department" required>
            <option value="">Select department</option>
            <option value="men">Men</option>
            <option value="women">Women</option>
          </select>
        </div>

        <div class="form-group">
          <label>Parent category</label>
          <select v-model="parentId">
            <option value="">No parent</option>
            <option
              v-for="category in parentOptions"
              :key="category.id"
              :value="String(category.id)"
            >
              {{ category.name }}
            </option>
          </select>
        </div>

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
          <template v-if="editingId === category.id">
            <div class="edit-block">
              <div class="form-group">
                <label>Name</label>
                <input v-model="editForm.name" type="text">
              </div>

              <div class="form-group">
                <label>Department</label>
                <select v-model="editForm.department">
                  <option value="">Select department</option>
                  <option value="men">Men</option>
                  <option value="women">Women</option>
                </select>
              </div>

              <div class="form-group">
                <label>Parent category</label>
                <select v-model="editForm.parent_id">
                  <option value="">No parent</option>
                  <option
                    v-for="parent in editParentOptions(category.id)"
                    :key="parent.id"
                    :value="String(parent.id)"
                  >
                    {{ parent.name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="category-actions">
              <button
                class="save-btn"
                :disabled="updatingId === category.id"
                @click="updateCategory(category.id)"
              >
                {{ updatingId === category.id ? 'Saving...' : 'Save' }}
              </button>

              <button class="cancel-btn" @click="cancelEdit">
                Cancel
              </button>
            </div>
          </template>

          <template v-else>
            <div>
              <strong>{{ category.name }}</strong>
              <p>Slug: {{ category.slug }}</p>
              <p>Department: {{ category.department || '—' }}</p>
              <p>Parent: {{ category.parent?.name || 'None' }}</p>
            </div>

            <div class="category-actions">
              <button class="edit-btn" @click="startEdit(category)">
                Edit
              </button>

              <button
                class="delete-btn"
                :disabled="deletingId === category.id"
                @click="deleteCategory(category.id)"
              >
                {{ deletingId === category.id ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </template>
        </div>

        <p v-if="!categories.length" class="empty-text">No categories yet.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL, fetchWithAuth } from '../services/api'
import { getUser } from '../services/auth'

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

const categories = ref<Category[]>([])
const newCategoryName = ref('')
const department = ref('')
const parentId = ref('')
const message = ref('')
const error = ref('')
const loadingCreate = ref(false)
const deletingId = ref<number | null>(null)
const updatingId = ref<number | null>(null)
const editingId = ref<number | null>(null)

const editForm = reactive({
  name: '',
  department: '',
  parent_id: '',
})

const parentOptions = computed(() => {
  if (!department.value) return categories.value.filter((c) => !c.parent_id)

  return categories.value.filter(
    (c) => !c.parent_id && c.department === department.value
  )
})

const editParentOptions = (currentCategoryId: number) => {
  if (!editForm.department) {
    return categories.value.filter(
      (c) => !c.parent_id && c.id !== currentCategoryId
    )
  }

  return categories.value.filter(
    (c) =>
      !c.parent_id &&
      c.department === editForm.department &&
      c.id !== currentCategoryId
  )
}

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
        department: department.value,
        parent_id: parentId.value ? Number(parentId.value) : null,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to create category'
      return
    }

    message.value = data.message || 'Category created successfully'
    newCategoryName.value = ''
    department.value = ''
    parentId.value = ''
    loadCategories()
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  } finally {
    loadingCreate.value = false
  }
}

const startEdit = (category: Category) => {
  editingId.value = category.id
  editForm.name = category.name
  editForm.department = category.department || ''
  editForm.parent_id = category.parent_id ? String(category.parent_id) : ''
  message.value = ''
  error.value = ''
}

const cancelEdit = () => {
  editingId.value = null
  editForm.name = ''
  editForm.department = ''
  editForm.parent_id = ''
}

const updateCategory = async (id: number) => {
  message.value = ''
  error.value = ''
  updatingId.value = id

  try {
    const response = await fetchWithAuth(`${API_URL}/api/categories/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: editForm.name,
        department: editForm.department,
        parent_id: editForm.parent_id ? Number(editForm.parent_id) : null,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Failed to update category'
      return
    }

    message.value = data.message || 'Category updated successfully'
    cancelEdit()
    loadCategories()
  } catch (err) {
    console.error(err)
    error.value = 'Server connection error'
  } finally {
    updatingId.value = null
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
