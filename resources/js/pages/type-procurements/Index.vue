<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps<{
  typeProcurements: {
    data: Array<{ id: number; procurement_code: number; procurement_description: string; status: number }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    status?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
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
  router.get('/type-procurements', {
    search: state.search || undefined,
    status: state.status || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
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

function destroyItem(id: number) {
  if (!confirm('Delete this type procurement?')) return
  router.delete(`/type-procurements/${id}`, { preserveScroll: true, preserveState: true })
}
</script>

<template>
  <Head title="Type Procurements" />
  <AppLayout :breadcrumbs="[{ title: 'Type Procurements', href: '/type-procurements' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Type Procurements</h1>
        <a href="/type-procurements/create" class="rounded-md bg-primary px-3 py-2 text-white">New Type</a>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-5">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input
            v-model="state.search"
            type="text"
            placeholder="Code or description"
            @keyup.enter="applyFilters({ page: 1 })"
            class="mt-1 block w-full rounded-md border p-2"
          />
        </div>
        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model="state.status" class="mt-1 block w-full rounded-md border p-2">
            <option value="">All</option>
            <option v-for="s in props.statuses" :key="s.value" :value="String(s.value)">{{ s.label }}</option>
          </select>
        </div>
        <div class="flex items-end">
          <button class="rounded-md border px-3 py-2" @click="applyFilters({ page: 1 })">Apply</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y">
          <thead>
            <tr>
              <th class="px-3 py-2 text-left"><button @click="sortBy('procurement_code')">Code</button></th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('procurement_description')">Description</button></th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('status')">Status</button></th>
              <th class="px-3 py-2 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in props.typeProcurements.data" :key="row.id" class="border-b">
              <td class="px-3 py-2 font-mono">{{ row.procurement_code }}</td>
              <td class="px-3 py-2">{{ row.procurement_description }}</td>
              <td class="px-3 py-2">
                <span :class="row.status === 1 ? 'text-green-700' : 'text-gray-500'">{{ row.status === 1 ? 'Active' : 'Inactive' }}</span>
              </td>
              <td class="px-3 py-2 text-right">
                <a :href="`/type-procurements/${row.id}/edit`" class="text-primary hover:underline">Edit</a>
                <button class="ml-3 text-red-600 hover:underline" @click="destroyItem(row.id)">Delete</button>
              </td>
            </tr>
            <tr v-if="props.typeProcurements.data.length === 0">
              <td colspan="4" class="px-3 py-6 text-center text-sm text-muted-foreground">No type procurements found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button
          v-for="l in props.typeProcurements.links"
          :key="l.label + (l.url || '')"
          :disabled="!l.url"
          @click="goTo(l.url)"
          class="rounded-md border px-3 py-1 disabled:opacity-50"
          :class="{ 'bg-primary text-white': l.active }"
          v-html="l.label"
        />
        <div class="ml-auto flex items-center gap-2">
          <label class="text-sm">Per page:</label>
          <select v-model.number="state.per_page" @change="applyFilters({ page: 1 })" class="rounded-md border p-1">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
