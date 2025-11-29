<script setup lang="ts">
import { ArrowUp, ArrowDown } from 'lucide-vue-next'

interface Column {
  key: string
  label: string
  sortable?: boolean
  width?: string
}

interface Props {
  columns: Column[]
  sortBy?: string
  sortDir?: 'asc' | 'desc'
}

defineProps<Props>()

interface Emits {
  (e: 'sort', column: string): void
}

defineEmits<Emits>()
</script>

<template>
  <thead>
    <tr>
      <th v-for="col in columns" :key="col.key" class="px-3 py-2 text-left" :style="col.width ? { width: col.width } : {}">
        <button
          v-if="col.sortable"
          @click="$emit('sort', col.key)"
          class="inline-flex items-center gap-1 hover:underline"
        >
          {{ col.label }}
          <span v-if="sortBy === col.key">
            <ArrowUp v-if="sortDir === 'asc'" :size="14" />
            <ArrowDown v-else :size="14" />
          </span>
        </button>
        <span v-else>{{ col.label }}</span>
      </th>
    </tr>
  </thead>
</template>

