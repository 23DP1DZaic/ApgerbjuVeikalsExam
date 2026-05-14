<template>
  <div
    class="custom-select-wrapper"
    ref="selectRef"
  >
    <button
      type="button"
      class="custom-select-button"
      :class="{ disabled }"
      :disabled="disabled"
      @click="toggleOpen"
    >
      <span :class="{ placeholder: !selectedLabel }">
        {{ selectedLabel || placeholder }}
      </span>

      <span class="custom-select-arrow">⌄</span>
    </button>

    <div
      v-if="isOpen"
      class="custom-select-menu"
    >
      <template v-if="groups && groups.length">
        <div
          v-for="group in groups"
          :key="group.label"
          class="custom-select-group"
        >
          <div class="custom-select-group-title">
            {{ group.label }}
          </div>

          <button
            v-for="option in group.options"
            :key="option.value"
            type="button"
            class="custom-select-option"
            :class="{ active: modelValue === option.value }"
            @click="selectOption(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </template>

      <template v-else>
        <button
          v-for="option in options"
          :key="option.value"
          type="button"
          class="custom-select-option"
          :class="{ active: modelValue === option.value }"
          @click="selectOption(option.value)"
        >
          {{ option.label }}
        </button>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

type SelectOption = {
  label: string
  value: string
}

type SelectGroup = {
  label: string
  options: SelectOption[]
}

const props = defineProps<{
  modelValue: string
  placeholder: string
  options?: SelectOption[]
  groups?: SelectGroup[]
  disabled?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const isOpen = ref(false)
const selectRef = ref<HTMLElement | null>(null)

const selectedLabel = computed(() => {
  const allOptions = [
    ...(props.options || []),
    ...(props.groups || []).flatMap((group) => group.options),
  ]

  return allOptions.find((option) => option.value === props.modelValue)?.label || ''
})

const toggleOpen = () => {
  if (props.disabled) return

  isOpen.value = !isOpen.value
}

const selectOption = (value: string) => {
  emit('update:modelValue', value)
  isOpen.value = false
}

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as Node

  if (selectRef.value && !selectRef.value.contains(target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>