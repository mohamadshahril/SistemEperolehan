<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps<{
  requests: {
    data: Array<{
      id: number
      item_name: string
      quantity: number
      price: string | number
      status: string
      submitted_at: string | null
      attachment_path?: string | null
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    status?: string | null
    from_date?: string | null
    to_date?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
  statuses: string[]
}>()

const state = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? '',
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  sort_by: props.filters.sort_by ?? 'submitted_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/purchase-requests', {
    search: state.search || undefined,
    status: state.status || undefined,
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
  state.search = ''
  state.status = ''
  state.from_date = ''
  state.to_date = ''
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

function destroyRequest(id: number) {
  if (!confirm('Are you sure you want to delete this purchase request? This action cannot be undone.')) return
  router.delete(`/purchase-requests/${id}`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {},
  })
}
</script>

<template>
  <Head title="Purchase Requests" />
  <AppLayout :breadcrumbs="[{ title: 'Purchase Requests', href: '/purchase-requests' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Purchase Requests</h1>
        <a href="/purchase-requests/create" class="rounded-md bg-primary px-3 py-2 text-white">New Request</a>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-5">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input
            v-model="state.search"
            type="text"
            placeholder="Item, purpose, ID, or date (YYYY-MM-DD)"
            @keyup.enter="applyFilters({ page: 1 })"
            class="mt-1 block w-full rounded-md border p-2"
          />
        </div>
        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model="state.status" class="mt-1 block w-full rounded-md border p-2">
            <option value="">All</option>
            <option v-for="s in props.statuses" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">From</label>
          <input v-model="state.from_date" type="date" class="mt-1 block w-full rounded-md border p-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">To</label>
          <input v-model="state.to_date" type="date" class="mt-1 block w-full rounded-md border p-2" />
        </div>
      </div>

      <div class="mb-4 flex items-center gap-3">
        <button @click="applyFilters({ page: 1 })" class="rounded-md border px-3 py-2">Apply</button>
        <button @click="resetFilters" class="rounded-md border px-3 py-2">Reset</button>
        <div class="ml-auto flex items-center gap-2">
          <label class="text-sm">Per page:</label>
          <select v-model.number="state.per_page" @change="applyFilters({ page: 1 })" class="rounded-md border p-1">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto rounded-md border">
        <table class="min-w-full divide-y">
          <thead class="bg-muted/30">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('item_name')" class="hover:underline">Item Name</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('quantity')" class="hover:underline">Qty</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('price')" class="hover:underline">Price</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('submitted_at')" class="hover:underline">Date</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('status')" class="hover:underline">Status</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">Attachment</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in props.requests.data" :key="row.id" class="odd:bg-white even:bg-muted/10">
              <td class="px-4 py-2">
                <div class="font-medium">{{ row.item_name }}</div>
                <div class="text-xs text-muted-foreground">#{{ row.id }}</div>
              </td>
              <td class="px-4 py-2">{{ row.quantity }}</td>
                <td class="px-4 py-2">{{ 'RM' + Number(row.price).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                <td class="px-4 py-2">{{ row.submitted_at ? new Date(row.submitted_at).toLocaleDateString('en-GB', { timeZone: 'UTC' }) : '-' }}</td>
              <td class="px-4 py-2">
                <span
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs"
                  :class="{
                    'bg-yellow-100 text-yellow-800': row.status === 'Pending',
                    'bg-green-100 text-green-800': row.status === 'Approved',
                    'bg-red-100 text-red-800': row.status === 'Rejected',
                  }"
                >
                  {{ row.status }}
                </span>
              </td>
              <td class="px-4 py-2">
                <a v-if="row.attachment_path" :href="`/storage/${row.attachment_path}`" target="_blank" class="text-primary hover:underline">View</a>
                <span v-else class="text-muted-foreground">-</span>
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-3">
                  <a :href="`/purchase-requests/${row.id}/edit?print=1`" class="text-primary hover:underline">Print</a>
                  <template v-if="row.status === 'Pending'">
                    <span class="text-muted-foreground">|</span>
                    <a :href="`/purchase-requests/${row.id}/edit`" class="text-primary hover:underline">Edit</a>
                    <button @click="destroyRequest(row.id)" class="text-red-600 hover:underline">Delete</button>
                  </template>
                  <span v-else class="text-muted-foreground">—</span>
                </div>
              </td>
            </tr>
            <tr v-if="props.requests.data.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No purchase requests found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div class="text-sm text-muted-foreground">
          Sort: <strong>{{ state.sort_by }}</strong> ({{ state.sort_dir?.toUpperCase() }})
        </div>
        <nav class="flex flex-wrap gap-1">
          <button
            v-for="link in props.requests.links"
            :key="link.label"
            class="rounded border px-3 py-1 text-sm"
            :class="{ 'bg-primary text-white border-primary': link.active }"
            v-html="link.label"
            :disabled="!link.url"
            @click="goTo(link.url)"
          />
        </nav>
      </div>
    </div>
  </AppLayout>
</template>
