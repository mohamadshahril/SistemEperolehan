<script setup lang="ts">
import { reactive } from 'vue'
import { RotateCcw } from 'lucide-vue-next'

interface Props {
  search?: string
  fromDate?: string
  toDate?: string
  status?: string
  statuses?: string[]
}

interface Emits {
  (e: 'apply', filters: Record<string, unknown>): void
  (e: 'reset'): void
}

const props = withDefaults(defineProps<Props>(), {
  search: '',
  fromDate: '',
  toDate: '',
  status: 'Pending',
  statuses: () => ['All', 'Pending', 'Approved', 'Rejected'],
})

const emit = defineEmits<Emits>()

const filters = reactive({
  search: props.search,
  fromDate: props.fromDate,
  toDate: props.toDate,
  status: props.status,
})

const handleApply = () => {
  emit('apply', {
    search: filters.search || undefined,
    from_date: filters.fromDate || undefined,
    to_date: filters.toDate || undefined,
    status: filters.status || undefined,
    page: 1,
  })
}

const handleStatusChange = () => {
  handleApply()
}

const handleReset = () => {
  filters.search = ''
  filters.fromDate = ''
  filters.toDate = ''
  filters.status = 'Pending'
  emit('reset')
}
</script>

<template>
  <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-6">
    <div class="md:col-span-2">
      <label class="block text-sm font-medium">Search</label>
      <input
        v-model="filters.search"
        type="text"
        placeholder="Ref, employee, title, code, status or date (YYYY-MM-DD)"
        class="mt-1 block w-full rounded-md border p-2"
        @keyup.enter="handleApply"
        @change="handleStatusChange"
      />
    </div>
    <div>
      <label class="block text-sm font-medium">Status</label>
      <select v-model="filters.status" class="mt-1 block w-full rounded-md border p-2" @change="handleStatusChange">

        <option v-for="s in props.statuses" :key="s" :value="s">
          {{ s }}
        </option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium">From date</label>
      <input v-model="filters.fromDate" type="date" class="mt-1 block w-full rounded-md border p-2"
             @change="handleStatusChange"/>
    </div>
    <div>
      <label class="block text-sm font-medium">To date</label>
      <input v-model="filters.toDate" type="date" class="mt-1 block w-full rounded-md border p-2"
             @change="handleStatusChange"/>
    </div>
    <div class="flex items-end gap-2">
      <button
        @click="handleApply"
        class="rounded-md border px-3 py-2 hover:bg-gray-100 active:bg-gray-200"
      >
        Apply
      </button>
      <button
        @click="handleReset"
        class="inline-flex items-center gap-2 rounded-md border px-3 py-2 hover:bg-gray-100 active:bg-gray-200"
        title="Reset filters"
      >
        <RotateCcw :size="16" />
      </button>
    </div>
  </div>
</template>
