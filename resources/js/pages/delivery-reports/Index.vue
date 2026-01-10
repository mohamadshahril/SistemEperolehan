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

interface PeriodData {
  period: string
  label: string
  total: number
  received: number
  pending: number
  percentage: number
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
    report_type?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
  }
  stats: {
    total: number
    received: number
    pending: number
    received_percentage: number
  }
  periodData: PeriodData[]
  vendors: Array<{ id: number; name: string }>
}>()

const state = reactive({
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  vendor_id: props.filters.vendor_id ?? '',
  status: props.filters.status ?? '',
  report_type: props.filters.report_type ?? 'list',
  sort_by: props.filters.sort_by ?? 'delivery_date',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  selected_period: (props.filters.selected_period as string | null) ?? null,
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/delivery-reports', {
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    vendor_id: state.vendor_id || undefined,
    status: state.status || undefined,
    report_type: state.report_type || undefined,
    selected_period: state.selected_period || undefined,
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
  state.report_type = 'list'
  state.selected_period = null
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

// Function to get delivery orders for selected period
function getOrdersForPeriod(): DeliveryOrderItem[] {
  return props.deliveryOrders.data
}

function selectPeriod(period: string) {
  state.selected_period = state.selected_period === period ? null : period
  applyFilters({ page: 1 })
}

function exportToPdf() {
  const params = new URLSearchParams({
    from_date: state.from_date || '',
    to_date: state.to_date || '',
    vendor_id: String(state.vendor_id) || '',
    status: state.status || '',
    report_type: state.report_type || '',
    selected_period: state.selected_period || '',
    sort_by: state.sort_by || '',
    sort_dir: state.sort_dir || '',
  })
  window.open(`/delivery-reports/export/pdf?${params.toString()}`, '_blank')
}
</script>

<template>
  <Head title="Delivery Reports" />
  <AppLayout :breadcrumbs="[{ title: 'Delivery Reports', href: '/delivery-reports' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Delivery Reports</h1>
        <button @click="exportToPdf" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">
          📄 Export to PDF
        </button>
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
      <div class="mb-4 rounded-md border bg-white p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
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

        <!-- Report Type Selector -->
        <div class="mt-4 border-t pt-4">
          <label class="block text-sm font-medium mb-2">Report Type</label>
          <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="state.report_type" value="list" @change="applyFilters({ page: 1 })" class="cursor-pointer" />
              <span class="text-sm">List View</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="state.report_type" value="monthly" @change="applyFilters({ page: 1 })" class="cursor-pointer" />
              <span class="text-sm">Monthly Report</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="state.report_type" value="quarterly" @change="applyFilters({ page: 1 })" class="cursor-pointer" />
              <span class="text-sm">Quarterly Report</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="state.report_type" value="yearly" @change="applyFilters({ page: 1 })" class="cursor-pointer" />
              <span class="text-sm">Yearly Report</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Period Report Table (Monthly/Quarterly/Yearly) -->
      <div v-if="state.report_type !== 'list'" class="space-y-4">
        <div class="overflow-x-auto rounded-md border">
          <table class="min-w-full divide-y">
            <thead class="bg-muted/30">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-medium">Period</th>
                <th class="px-4 py-2 text-left text-sm font-medium">Total Orders</th>
                <th class="px-4 py-2 text-left text-sm font-medium">Received</th>
                <th class="px-4 py-2 text-left text-sm font-medium">Pending</th>
                <th class="px-4 py-2 text-left text-sm font-medium">Received %</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="period in props.periodData" :key="period.period"
                  class="odd:bg-white even:bg-muted/10 cursor-pointer hover:bg-blue-50 transition-colors"
                  @click="selectPeriod(period.period)">
                <td class="px-4 py-2 font-medium" :class="{ 'text-blue-600 font-bold': state.selected_period === period.period }">
                  {{ period.label }}
                </td>
                <td class="px-4 py-2 text-center font-semibold">{{ period.total }}</td>
                <td class="px-4 py-2 text-center">
                  <span class="text-green-600 font-semibold">{{ period.received }}</span>
                </td>
                <td class="px-4 py-2 text-center">
                  <span class="text-yellow-600 font-semibold">{{ period.pending }}</span>
                </td>
                <td class="px-4 py-2">
                  <div class="flex items-center gap-2">
                    <div class="w-20 bg-gray-200 rounded-full h-2">
                      <div class="bg-green-600 h-2 rounded-full" :style="{ width: period.percentage + '%' }"></div>
                    </div>
                    <span class="text-sm">{{ period.percentage }}%</span>
                  </div>
                </td>
              </tr>
              <tr v-if="props.periodData.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No data available for this period.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Delivery Orders List for Selected Period -->
        <div v-if="state.selected_period" class="rounded-md border bg-white p-4">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Delivery Orders - {{ props.periodData.find(p => p.period === state.selected_period)?.label }}</h3>
            <button @click="selectPeriod(state.selected_period)" class="text-sm text-gray-500 hover:text-gray-700">Close</button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y">
              <thead class="bg-muted/30">
                <tr>
                  <th class="px-4 py-2 text-left text-sm font-medium">DO Number</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">PO Number</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Vendor</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Delivery Date</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="order in getOrdersForPeriod()" :key="order.id" class="odd:bg-white even:bg-muted/10">
                  <td class="px-4 py-2 font-mono text-sm">{{ order.do_number }}</td>
                  <td class="px-4 py-2 text-sm">{{ order.purchase_order.order_number }}</td>
                  <td class="px-4 py-2 text-sm">{{ order.purchase_order.vendor.name }}</td>
                  <td class="px-4 py-2 text-sm">{{ formatDateMalaysia(order.delivery_date) }}</td>
                  <td class="px-4 py-2">
                    <span :class="order.is_received ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                      {{ order.is_received ? 'Received' : 'Pending' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="getOrdersForPeriod().length === 0">
                  <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No delivery orders found for this period.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- List View Table -->
      <div v-else class="overflow-x-auto rounded-md border">
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
      <div v-if="state.report_type === 'list' || state.selected_period" class="mt-4 flex flex-wrap items-center justify-between gap-2">
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
