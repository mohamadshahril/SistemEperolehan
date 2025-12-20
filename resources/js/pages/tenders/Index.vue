<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

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
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
}>()

const state = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? '',
  sort_by: props.filters.sort_by ?? 'created_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

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
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">
                  {{ row.bids_count }} {{ row.bids_count === 1 ? 'bid' : 'bids' }}
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
