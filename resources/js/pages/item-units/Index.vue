<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps<{
  units: {
    data: Array<{ id: number; code: string; name: string; status: number; description?: string | null }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: { search?: string | null; status?: string | null; sort_by?: string | null; sort_dir?: 'asc' | 'desc' | null; per_page?: number | null }
  statuses: Array<{ value: number; label: string }>
}>()

const state = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? '',
  sort_by: props.filters.sort_by ?? 'created_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/item-units', {
    search: state.search || undefined,
    status: state.status || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
  state.search = ''
  state.status = ''
  applyFilters({ page: 1 })
}

function sortBy(column: string) {
  if (state.sort_by === column) {
    state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    state.sort_by = column
    state.sort_dir = 'asc'
  }
  applyFilters()
}

function goTo(url: string | null) {
  if (!url) return
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

function destroyUnit(id: number) {
  if (!confirm('Delete this item unit?')) return
  router.delete(`/item-units/${id}`, { preserveScroll: true, preserveState: true })
}
</script>

<template>
  <Head title="Item Units" />
  <AppLayout :breadcrumbs="[{ title: 'Item Units', href: '/item-units' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Item Units</h1>
        <a href="/item-units/create" class="rounded-md bg-primary px-3 py-2 text-white">New Unit</a>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input v-model="state.search" type="text" placeholder="Code or name" @keyup.enter="applyFilters({ page: 1 })" class="mt-1 block w-full rounded-md border p-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model="state.status" class="mt-1 block w-full rounded-md border p-2">
            <option value="">All</option>
            <option v-for="s in props.statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="applyFilters({ page: 1 })" class="h-10 rounded-md border px-3">Apply</button>
          <button @click="resetFilters" class="ml-2 h-10 rounded-md border px-3">Reset</button>
        </div>
      </div>

      <div class="overflow-x-auto rounded-md border">
        <table class="min-w-full divide-y">
          <thead class="bg-muted/30">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium"><button @click="sortBy('code')" class="hover:underline">Code</button></th>
              <th class="px-4 py-2 text-left text-sm font-medium"><button @click="sortBy('name')" class="hover:underline">Name</button></th>
              <th class="px-4 py-2 text-left text-sm font-medium">Description</th>
              <th class="px-4 py-2 text-left text-sm font-medium"><button @click="sortBy('status')" class="hover:underline">Status</button></th>
              <th class="px-4 py-2 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in props.units.data" :key="row.id" class="odd:bg-white even:bg-muted/10">
              <td class="px-4 py-2 font-mono">{{ row.code }}</td>
              <td class="px-4 py-2">{{ row.name }}</td>
              <td class="px-4 py-2 text-sm text-muted-foreground">{{ row.description || '-' }}</td>
              <td class="px-4 py-2">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs" :class="{ 'bg-green-100 text-green-800': row.status === 1, 'bg-gray-200 text-gray-700': row.status !== 1 }">
                  {{ row.status === 1 ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-3">
                  <a :href="`/item-units/${row.id}/edit`" class="text-primary hover:underline">Edit</a>
                  <button @click="destroyUnit(row.id)" class="text-red-600 hover:underline">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="props.units.data.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No item units found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div class="text-sm text-muted-foreground">Sort: <strong>{{ state.sort_by }}</strong> ({{ state.sort_dir?.toUpperCase() }})</div>
        <nav class="flex flex-wrap gap-1">
          <button v-for="link in props.units.links" :key="link.label" class="rounded border px-3 py-1 text-sm" :class="{ 'bg-primary text-white border-primary': link.active }" v-html="link.label" :disabled="!link.url" @click="goTo(link.url)" />
        </nav>
      </div>
    </div>
  </AppLayout>
  </template>
