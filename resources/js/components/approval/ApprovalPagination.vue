<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface Props {
  links: PaginationLink[]
  perPage?: number
  onPerPageChange?: (value: number) => void
}

withDefaults(defineProps<Props>(), {
  perPage: 10,
})

interface Emits {
  (e: 'navigate', url: string): void
  (e: 'change-per-page', value: number): void
}

const emit = defineEmits<Emits>()

const getButtonContent = (label: string) => {
  if (label.includes('&laquo;')) return '<'
  if (label.includes('&raquo;')) return '>'
  return label
}

const isPrevious = (label: string) => label.includes('&laquo;')
const isNext = (label: string) => label.includes('&raquo;')

const handleNavigate = (url: string | null) => {
  if (url) {
    emit('navigate', url)
  }
}

const handlePerPageChange = (value: number) => {
  emit('change-per-page', value)
}
</script>

<template>
  <div class="mt-4 flex flex-wrap items-center gap-2">
    <div class="flex gap-1">
      <button
        v-for="link in links"
        :key="link.label"
        :disabled="!link.url"
        @click="handleNavigate(link.url)"
        :class="[
          'rounded-md border px-3 py-1 transition-colors',
          link.active ? 'bg-primary text-white' : 'hover:bg-gray-100',
          !link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
        ]"
      >
        <span v-if="isPrevious(link.label)" class="inline-flex items-center">
          <ChevronLeft :size="16" />
        </span>
        <span v-else-if="isNext(link.label)" class="inline-flex items-center">
          <ChevronRight :size="16" />
        </span>
        <span v-else v-html="link.label"></span>
      </button>
    </div>
    <div class="ml-auto flex items-center gap-2">
      <label class="text-sm font-medium">Per page:</label>
      <select
        :value="perPage"
        @change="handlePerPageChange(Number($event.target.value))"
        class="rounded-md border p-1"
      >
        <option :value="10">10</option>
        <option :value="25">25</option>
        <option :value="50">50</option>
      </select>
    </div>
  </div>
</template>


