<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps<{
  vendors: {
    data: Array<{
      id: number
      name: string
      email: string | null
      phone: string | null
      address: string | null
      purchase_orders_count: number
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
}>()

const state = reactive({
  search: props.filters.search ?? '',
  sort_by: props.filters.sort_by ?? 'created_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/vendors', {
    search: state.search || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
  state.search = ''
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

function destroyVendor(id: number) {
  if (!confirm('Are you sure you want to delete this vendor? This action cannot be undone.')) return
  router.delete(`/vendors/${id}`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {},
  })
}
</script>

<template>
  <Head title="Vendors" />
  <AppLayout :breadcrumbs="[{ title: 'Vendors', href: '/vendors' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Vendors</h1>
        <Link href="/vendors/create" class="rounded-md bg-primary px-3 py-2 text-white">New Vendor</Link>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input
            v-model="state.search"
            type="text"
            placeholder="Name, email, phone, or address"
            @keyup.enter="applyFilters({ page: 1 })"
            class="mt-1 block w-full rounded-md border p-2"
          />
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
                <button @click="sortBy('name')" class="hover:underline">Vendor Name</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('email')" class="hover:underline">Email</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('phone')" class="hover:underline">Phone</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">Address</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Purchase Orders</th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('created_at')" class="hover:underline">Created</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in props.vendors.data" :key="row.id" class="odd:bg-white even:bg-muted/10">
              <td class="px-4 py-2">
                <div class="font-medium">{{ row.name }}</div>
                <div class="text-xs text-muted-foreground">#{{ row.id }}</div>
              </td>
              <td class="px-4 py-2">{{ row.email || '-' }}</td>
              <td class="px-4 py-2">{{ row.phone || '-' }}</td>
              <td class="px-4 py-2">
                <div class="max-w-xs truncate" :title="row.address || ''">
                  {{ row.address || '-' }}
                </div>
              </td>
              <td class="px-4 py-2">
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">
                  {{ row.purchase_orders_count }} orders
                </span>
              </td>
              <td class="px-4 py-2">{{ new Date(row.created_at).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-3">
                  <Link :href="`/vendors/${row.id}/edit`" class="text-primary hover:underline">Edit</Link>
                  <button 
                    @click="destroyVendor(row.id)" 
                    class="hover:underline"
                    :class="row.purchase_orders_count > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-red-600'"
                    :disabled="row.purchase_orders_count > 0"
                    :title="row.purchase_orders_count > 0 ? 'Cannot delete vendor with purchase orders' : 'Delete vendor'"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="props.vendors.data.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No vendors found.</td>
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
            v-for="link in props.vendors.links"
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
