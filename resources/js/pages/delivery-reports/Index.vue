<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

interface DeliveryOrderItem {
  id: number
  do_number: string
  delivery_date: string
  is_received: boolean
  purchase_order: {
    order_number: string
    vendor: { name: string }
  }
}

const props = defineProps<{
  deliveryOrders: {
    data: DeliveryOrderItem[]
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    from_date?: string | null
    to_date?: string | null
    vendor_id?: number | null
    status?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
  }
  stats: {
    total: number
    received: number
    pending: number
    received_percentage: number
  }
  vendors: Array<{ id: number; name: string }>
}>()

const state = reactive({
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  vendor_id: props.filters.vendor_id ?? '',
  status: props.filters.status ?? '',
  sort_by: props.filters.sort_by ?? 'delivery_date',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/delivery-reports', {
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    vendor_id: state.vendor_id || undefined,
    status: state.status || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
  state.from_date = ''
  state.to_date = ''
  state.vendor_id = ''
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


// Function to format date in Malaysia format (DD/MM/YYYY)
const formatDateMalaysia = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('ms-MY', { year: 'numeric', month: '2-digit', day: '2-digit' })
}
</script>

<template>
  <Head title="Delivery Reports" />
  <AppLayout :breadcrumbs="[{ title: 'Delivery Reports', href: '/delivery-reports' }]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Delivery Reports</h1>
      </div>

      <!-- Stats Cards -->
      <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-md border bg-white p-4">
          <div class="text-sm text-gray-600">Total Deliveries</div>
          <div class="text-3xl font-bold text-primary">{{ props.stats.total }}</div>
        </div>
        <div class="rounded-md border bg-white p-4">
          <div class="text-sm text-gray-600">Received</div>
          <div class="text-3xl font-bold text-green-600">{{ props.stats.received }}</div>
        </div>
        <div class="rounded-md border bg-white p-4">
          <div class="text-sm text-gray-600">Pending</div>
          <div class="text-3xl font-bold text-yellow-600">{{ props.stats.pending }}</div>
        </div>
        <div class="rounded-md border bg-white p-4">
          <div class="text-sm text-gray-600">Received Rate</div>
          <div class="text-3xl font-bold">{{ props.stats.received_percentage }}%</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-4 grid grid-cols-1 gap-3 rounded-md border bg-white p-4 md:grid-cols-5">
        <div>
          <label class="block text-sm font-medium">From Date</label>
          <input v-model="state.from_date" type="date" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })" />
        </div>
        <div>
          <label class="block text-sm font-medium">To Date</label>
          <input v-model="state.to_date" type="date" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })" />
        </div>
        <div>
          <label class="block text-sm font-medium">Vendor</label>
          <select v-model="state.vendor_id" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })">
            <option value="">All Vendors</option>
            <option v-for="vendor in props.vendors" :key="vendor.id" :value="String(vendor.id)">{{ vendor.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model="state.status" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })">
            <option value="">All</option>
            <option value="received">Received</option>
            <option value="pending">Pending</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button @click="applyFilters({ page: 1 })" class="rounded-md border px-3 py-2">Apply</button>
          <button @click="resetFilters" class="rounded-md border px-3 py-2">Reset</button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-md border">
        <table class="min-w-full divide-y">
          <thead class="bg-muted/30">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('do_number')" class="hover:underline">DO Number</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">PO Number</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Vendor</th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('delivery_date')" class="hover:underline">Delivery Date</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('is_received')" class="hover:underline">Status</button>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="row in props.deliveryOrders.data" :key="row.id" class="odd:bg-white even:bg-muted/10">
              <td class="px-4 py-2 font-mono">{{ row.do_number }}</td>
              <td class="px-4 py-2">{{ row.purchase_order.order_number }}</td>
              <td class="px-4 py-2">{{ row.purchase_order.vendor.name }}</td>
              
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDateMalaysia(row.delivery_date) }}</td>
              <td class="px-4 py-2">
                <span :class="row.is_received ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                  {{ row.is_received ? 'Received' : 'Pending' }}
                </span>
              </td>
            </tr>
            <tr v-if="props.deliveryOrders.data.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No delivery orders found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <nav class="flex flex-wrap gap-1">
          <button
            v-for="link in props.deliveryOrders.links"
            :key="link.label"
            class="rounded border px-3 py-1 text-sm"
            :class="{ 'bg-primary text-white border-primary': link.active }"
            :disabled="!link.url"
            @click="goTo(link.url)"
            v-html="link.label"
          />
        </nav>
      </div>
    </div>
  </AppLayout>
</template>
