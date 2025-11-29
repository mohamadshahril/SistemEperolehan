<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import ApprovalFilters from '@/components/approval/ApprovalFilters.vue'
import ApprovalTable from '@/components/approval/ApprovalTable.vue'
import ApprovalPagination from '@/components/approval/ApprovalPagination.vue'

interface Request {
  id: number
  title: string
  budget: number | string
  purchase_ref_no?: string | null
  purpose?: string | null
  submitted_at: string | null
  status: string
  approval_remarks: string | null
  user: { id: number; name: string; email: string }
}

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface Filters {
  search?: string | null
  from_date?: string | null
  to_date?: string | null
  status?: string | null
  sort_by?: string | null
  sort_dir?: 'asc' | 'desc' | null
  per_page?: number | null
}

const props = defineProps<{
  requests: {
    data: Request[]
    links: PaginationLink[]
  }
  filters: Filters
  statuses?: string[]
}>()

const state = reactive({
  search: props.filters.search ?? '',
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  status: props.filters.status ?? 'Pending',
  per_page: props.filters.per_page ?? 10,
  sort_by: props.filters.sort_by ?? 'submitted_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
})

const handleApplyFilters = (filters: Record<string, unknown>) => {
  state.search = (filters.search as string) || ''
  state.from_date = (filters.from_date as string) || ''
  state.to_date = (filters.to_date as string) || ''
  state.status = (filters.status as string) || 'Pending'

  router.get('/approvals', {
    search: state.search || undefined,
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    status: state.status || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...filters,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

const handleResetFilters = () => {
  state.search = ''
  state.from_date = ''
  state.to_date = ''
  state.status = 'Pending'
  state.per_page = 10
  state.sort_by = 'submitted_at'
  state.sort_dir = 'desc'

  router.get('/approvals', {
    page: 1,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

const handleSort = (column: string) => {
  if (state.sort_by === column) {
    state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    state.sort_by = column
    state.sort_dir = 'asc'
  }

  router.get('/approvals', {
    search: state.search || undefined,
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    status: state.status || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

const handleNavigatePage = (url: string) => {
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const handleViewRequest = (id: number) => {
  router.get(`/approvals/${id}`)
}

const handlePerPageChange = (value: number) => {
  state.per_page = value
  router.get('/approvals', {
    search: state.search || undefined,
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    status: state.status || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: value,
    page: 1,
  }, { preserveState: true, preserveScroll: true, replace: true })
}
</script>

<template>
  <Head title="Approval Purchase Requests" />
  <AppLayout :breadcrumbs="[{ title: 'Approval Purchase Requests', href: '/approvals' }]">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight">Approval Purchase Requests</h1>
        <p class="mt-2 text-sm text-muted-foreground">Manage and approve pending purchase requests</p>
      </div>

      <!-- Filters Component -->
      <ApprovalFilters
        :search="state.search"
        :from-date="state.from_date"
        :to-date="state.to_date"
        :status="state.status"
        :statuses="props.statuses || ['All', 'Pending', 'Approved', 'Rejected']"
        @apply="handleApplyFilters"
        @reset="handleResetFilters"
      />

      <!-- Table Component -->
      <ApprovalTable
        :requests="props.requests.data"
        :sort-by="state.sort_by"
        :sort-dir="state.sort_dir"
        @sort="handleSort"
        @view="handleViewRequest"
      />

      <!-- Pagination Component -->
      <ApprovalPagination
        :links="props.requests.links"
        :per-page="state.per_page"
        @navigate="handleNavigatePage"
        @change-per-page="handlePerPageChange"
      />
    </div>
  </AppLayout>
</template>
