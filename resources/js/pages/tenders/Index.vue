<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'

const props = defineProps<{
  tenders: {
    data: Array<{
      id: number
      tender_number: string
      title: string
      description: string
      estimated_budget: number | null
      opening_date: string
      closing_date: string
      status: 'Draft' | 'Published' | 'Closed' | 'Awarded' | 'Cancelled'
      bids_count: number
      bids?: Array<{
        id: number
        vendor: { id: number; name: string }
        bid_amount: number
        status: string
        submitted_at: string
      }>
      creator: { id: number; name: string } | null
      awarded_bid: {
        id: number
        vendor: { id: number; name: string }
      } | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    status?: string | null
    from_date?: string | null
    to_date?: string | null
    date_filter_type?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
}>()

const state = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? '',
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  date_filter_type: props.filters.date_filter_type ?? 'created_at',
  sort_by: props.filters.sort_by ?? 'created_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

const hoveredTenderId = ref<number | null>(null)
const tooltipPosition = ref({ top: 0, left: 0 })

function showTooltip(event: MouseEvent, tenderId: number) {
  hoveredTenderId.value = tenderId
  const rect = (event.target as HTMLElement).getBoundingClientRect()
  tooltipPosition.value = {
    top: rect.bottom + window.scrollY + 8,
    left: rect.left + window.scrollX
  }
}

function hideTooltip() {
  hoveredTenderId.value = null
}

function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    Draft: 'bg-gray-100 text-gray-800',
    Published: 'bg-blue-100 text-blue-800',
    Closed: 'bg-yellow-100 text-yellow-800',
    Awarded: 'bg-green-100 text-green-800',
    Cancelled: 'bg-red-100 text-red-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

function formatCurrency(amount: number | null): string {
  if (!amount) return '-'
  return new Intl.NumberFormat('en-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(amount)
}

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/tenders', {
    search: state.search || undefined,
    status: state.status || undefined,
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    date_filter_type: state.date_filter_type || undefined,
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
  state.date_filter_type = 'created_at'
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

function destroyTender(id: number) {
  if (!confirm('Are you sure you want to delete this tender? This action cannot be undone.')) return
  router.delete(`/tenders/${id}`, {
    preserveScroll: true,
    preserveState: true,
  })
}
</script>

<template>
  <Head title="Tenders" />
  <AppLayout :breadcrumbs="[{ title: 'Tenders', href: '/tenders' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Tenders</h1>
        <Link href="/tenders/create" class="rounded-md bg-primary px-3 py-2 text-white">New Tender</Link>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input
            v-model="state.search"
            type="text"
            placeholder="Tender number, title, or description"
            @keyup.enter="applyFilters({ page: 1 })"
            class="mt-1 block w-full rounded-md border p-2"
          />
        </div>
        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model="state.status" @change="applyFilters({ page: 1 })" class="mt-1 block w-full rounded-md border p-2">
            <option value="">All Statuses</option>
            <option value="Draft">Draft</option>
            <option value="Published">Published</option>
            <option value="Closed">Closed</option>
            <option value="Awarded">Awarded</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
        <div>
          <label class="block text-sm font-medium">Date Filter Type</label>
          <select v-model="state.date_filter_type" class="mt-1 block w-full rounded-md border p-2">
            <option value="created_at">Created Date</option>
            <option value="opening_date">Opening Date</option>
            <option value="closing_date">Closing Date</option>
          </select>
        </div>
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
                <button @click="sortBy('tender_number')" class="hover:underline">Tender #</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('title')" class="hover:underline">Title</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">Budget</th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('opening_date')" class="hover:underline">Opening Date</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('closing_date')" class="hover:underline">Closing Date</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">
                <button @click="sortBy('status')" class="hover:underline">Status</button>
              </th>
              <th class="px-4 py-2 text-left text-sm font-medium">Bids</th>
              <th class="px-4 py-2 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in props.tenders.data" :key="row.id" class="odd:bg-white even:bg-muted/10">
              <td class="px-4 py-2">
                <Link :href="`/tenders/${row.id}`" class="font-medium text-primary hover:underline">
                  {{ row.tender_number }}
                </Link>
              </td>
              <td class="px-4 py-2">
                <div class="font-medium">{{ row.title }}</div>
                <div class="text-xs text-muted-foreground line-clamp-1">{{ row.description }}</div>
              </td>
              <td class="px-4 py-2">{{ formatCurrency(row.estimated_budget) }}</td>
              <td class="px-4 py-2">{{ new Date(row.opening_date).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</td>
              <td class="px-4 py-2">{{ new Date(row.closing_date).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</td>
              <td class="px-4 py-2">
                <span 
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="getStatusColor(row.status)"
                >
                  {{ row.status }}
                </span>
              </td>
              <td class="px-4 py-2">
                <div v-if="row.bids_count > 0" class="inline-block">
                  <button 
                    @mouseenter="showTooltip($event, row.id)"
                    @mouseleave="hideTooltip"
                    class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800 hover:bg-blue-200 transition-colors cursor-pointer"
                  >
                    {{ row.bids_count }} {{ row.bids_count === 1 ? 'bid' : 'bids' }}
                  </button>
                  
                  <!-- Hover Preview Tooltip - Fixed positioning to prevent clipping -->
                  <Teleport to="body">
                    <div 
                      v-if="hoveredTenderId === row.id && row.bids && row.bids.length > 0"
                      @mouseenter="hoveredTenderId = row.id"
                      @mouseleave="hideTooltip"
                      class="fixed w-80 bg-white border border-gray-200 rounded-lg shadow-xl p-4 z-[9999] transition-opacity duration-200"
                      :style="{
                        top: tooltipPosition.top + 'px',
                        left: tooltipPosition.left + 'px'
                      }"
                    >
                      <div class="space-y-2">
                        <h4 class="font-semibold text-sm text-gray-900 border-b border-gray-200 pb-2">Recent Bids</h4>
                        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                          <div 
                            v-for="bid in row.bids" 
                            :key="bid.id"
                            class="flex items-start justify-between gap-2 p-2 rounded-md bg-gray-50 hover:bg-gray-100 transition-colors"
                          >
                            <div class="flex-1 min-w-0">
                              <div class="font-medium text-sm text-gray-900">{{ bid.vendor.name }}</div>
                              <div class="text-xs text-gray-600 mt-0.5">
                                {{ formatCurrency(bid.bid_amount) }}
                              </div>
                              <div class="text-xs text-gray-500 mt-0.5">
                                {{ new Date(bid.submitted_at).toLocaleDateString('en-GB') }}
                              </div>
                            </div>
                            <span 
                              class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap"
                              :class="{
                                'bg-yellow-100 text-yellow-800': bid.status === 'Submitted',
                                'bg-blue-100 text-blue-800': bid.status === 'Under Review',
                                'bg-green-100 text-green-800': bid.status === 'Accepted',
                                'bg-red-100 text-red-800': bid.status === 'Rejected',
                              }"
                            >
                              {{ bid.status }}
                            </span>
                          </div>
                        </div>
                        <div v-if="row.bids_count > 10" class="pt-2 border-t border-gray-200">
                          <Link 
                            :href="`/tenders/${row.id}`" 
                            class="text-xs text-blue-600 hover:text-blue-800 hover:underline font-medium inline-block"
                          >
                            View all {{ row.bids_count }} bids →
                          </Link>
                        </div>
                      </div>
                      <!-- Arrow pointer -->
                      <div class="absolute -top-2 left-4 w-4 h-4 bg-white border-l border-t border-gray-200 transform rotate-45"></div>
                    </div>
                  </Teleport>
                </div>
                <span v-else class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                  0 bids
                </span>
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-3">
                  <Link :href="`/tenders/${row.id}`" class="text-primary hover:underline">View</Link>
                  <Link 
                    v-if="row.status !== 'Awarded' && row.status !== 'Cancelled'"
                    :href="`/tenders/${row.id}/edit`" 
                    class="text-primary hover:underline"
                  >
                    Edit
                  </Link>
                  <button 
                    @click="destroyTender(row.id)" 
                    class="hover:underline"
                    :class="row.status === 'Awarded' || row.bids_count > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-red-600'"
                    :disabled="row.status === 'Awarded' || row.bids_count > 0"
                    :title="row.status === 'Awarded' ? 'Cannot delete awarded tender' : row.bids_count > 0 ? 'Cannot delete tender with bids' : 'Delete tender'"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="props.tenders.data.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">No tenders found.</td>
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
            v-for="link in props.tenders.links"
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
