<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps<{
  purchaseOrders: {
    data: Array<{
      id: number
      order_number: string
      vendor: {
        id: number
        name: string
      }
      details: string | null
      status: string
      attachment_path: string | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  vendors: Array<{
    id: number
    name: string
  }>
  filters: {
    search?: string | null
    status?: string | null
    vendor_id?: number | null
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
  vendor_id: props.filters.vendor_id ?? '',
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  sort_by: props.filters.sort_by ?? 'created_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/purchase-orders', {
    search: state.search || undefined,
    status: state.status || undefined,
    vendor_id: state.vendor_id || undefined,
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
  state.vendor_id = ''
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

function destroyPurchaseOrder(id: number, status: string) {
  if (status !== 'Pending') {
    alert('Only pending purchase orders can be deleted.')
    return
  }
  if (!confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) return
  router.delete(`/purchase-orders/${id}`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {},
  })
}
</script>

<template>
  <Head title="Purchase Orders" />
  <AppLayout :breadcrumbs="[{ title: 'Purchase Orders', href: '/purchase-orders' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Purchase Orders</h1>
        <Link href="/purchase-orders/create" class="rounded-md bg-primary px-3 py-2 text-white">New Purchase Order</Link>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-5">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input
            v-model="state.search"
            type="text"
            placeholder="Order number, details, or ID"
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
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Vendor</label>
          <select v-model="state.vendor_id" class="mt-1 block w-full rounded-md border p-2">
            <option value="">All</option>
            <option v-for="v in props.vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
          </select>
        </div>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-5">
        <div>
          <label class="block text-sm font-medium">From Date</label>
          <input v-model="state.from_date" type="date" class="mt-1 block w-full rounded-md border p-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">To Date</label>
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
                <button @click="sortBy('order_number')" class="hover:underline">Order Number</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">Vendor</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Details</th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('status')" class="hover:underline">Status</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('created_at')" class="hover:underline">Created</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in props.purchaseOrders.data" :key="row.id" class="odd:bg-white even:bg-muted/10">
              <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                  <div>
                    <div class="font-medium">{{ row.order_number }}</div>
                    <div class="text-xs text-muted-foreground">#{{ row.id }}</div>
                  </div>
                  <a 
                    v-if="row.attachment_path" 
                    :href="`/storage/${row.attachment_path}`" 
                    target="_blank"
                    class="text-blue-600 hover:text-blue-800"
                    title="View attachment"
                  >
                    📎
                  </a>
                </div>
              </td>
              <td class="px-4 py-2">{{ row.vendor.name }}</td>
              <td class="px-4 py-2">
                <div class="max-w-xs truncate" :title="row.details || ''">
                  {{ row.details || '-' }}
                </div>
              </td>
              <td class="px-4 py-2">
                <span
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs"
                  :class="{
                    'bg-yellow-100 text-yellow-800': row.status === 'Pending',
                    'bg-green-100 text-green-800': row.status === 'Approved',
                    'bg-blue-100 text-blue-800': row.status === 'Completed',
                  }"
                >
                  {{ row.status }}
                </span>
              </td>
              <td class="px-4 py-2">{{ new Date(row.created_at).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-3">
                  <Link :href="`/purchase-orders/${row.id}/edit`" class="text-primary hover:underline">Edit</Link>
                  <button 
                    @click="destroyPurchaseOrder(row.id, row.status)" 
                    class="hover:underline"
                    :class="row.status !== 'Pending' ? 'text-gray-400 cursor-not-allowed' : 'text-red-600'"
                    :title="row.status !== 'Pending' ? 'Only pending orders can be deleted' : 'Delete purchase order'"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="props.purchaseOrders.data.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No purchase orders found.</td>
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
            v-for="link in props.purchaseOrders.links"
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
