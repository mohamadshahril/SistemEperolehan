<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import RequestTable from '@/components/purchase-requests/RequestTable.vue'

type RequestRow = {
  id: number
  title: string
  budget: number
  status: string
  submitted_at: string | null
  purchase_ref_no?: string | null
  attachment_url?: string | null
}

const props = defineProps<{
  requests: {
    data: RequestRow[]
    links: Array<{ url: string | null; label: string; active: boolean }>
    meta?: {
      from?: number | null
      current_page?: number
      per_page?: number
    }
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
  statuses?: string[]
}>()

const state = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? 'All',
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  sort_by: props.filters.sort_by ?? 'submitted_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

const statusOptions = computed(() => props.statuses ?? ['Pending', 'Approved', 'Rejected'])

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
  state.status = 'All'
  state.from_date = ''
  state.to_date = ''
  state.per_page = 10
  applyFilters({ page: 1 })
}

function goTo(url: string | null) {
  if (!url) return
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Note: previously showed row number; requirement changed to show ID directly.

function destroyRequest(id: number) {
  if (!confirm('Delete this purchase request? This action cannot be undone.')) return
  router.delete(`/purchase-requests/${id}`, { preserveScroll: true, preserveState: true })
}
</script>

<template>
  <Head title="My Purchase Requests" />
  <AppLayout :breadcrumbs="[{ title: 'Purchase Requests', href: '/purchase-requests' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Purchase Requests</h1>
        <a href="/purchase-requests/create" class="rounded-md bg-primary px-3 py-2 text-white">New Request</a>
      </div>

      <!-- Filters area (Vue 3 table design) -->
      <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-5">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input
            v-model="state.search"
            type="text"
            placeholder="Title, ref no, or date (YYYY-MM-DD)"
            @keyup.enter="applyFilters({ page: 1 })"
            class="mt-1 block w-full rounded-md border p-2"
          />
        </div>
        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model="state.status" class="mt-1 block w-full rounded-md border p-2">
            <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
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

        <RequestTable :requests="props.requests" :filters="state" @destroy="destroyRequest" />

      <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div class="text-sm text-muted-foreground">
          Sort: <strong>{{ state.sort_by }}</strong> ({{ state.sort_dir?.toUpperCase() }})
        </div>
        <nav class="flex flex-wrap gap-1">
          <button
            v-for="l in props.requests.links"
            :key="l.label + (l.url || '')"
            :disabled="!l.url"
            @click="goTo(l.url)"
            class="rounded border px-3 py-1 text-sm disabled:opacity-50"
            :class="{ 'bg-primary text-white border-primary': l.active }"
            v-html="l.label"
          />
        </nav>
      </div>
    </div>
  </AppLayout>
</template>
