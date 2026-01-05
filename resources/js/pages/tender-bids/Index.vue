<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
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
      status: string
      bids_count: number
      creator: { id: number; name: string } | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  vendors: Array<{
    id: number
    name: string
    email: string
  }>
  filters: {
    search?: string | null
    status?: string | null
    year?: number | null
    opening_date_from?: string | null
    opening_date_to?: string | null
    closing_date_from?: string | null
    closing_date_to?: string | null
    budget_min?: number | null
    budget_max?: number | null
    quick_filter?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
}>()

const state = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? 'Published',
  year: props.filters.year ?? null,
  opening_date_from: props.filters.opening_date_from ?? '',
  opening_date_to: props.filters.opening_date_to ?? '',
  closing_date_from: props.filters.closing_date_from ?? '',
  closing_date_to: props.filters.closing_date_to ?? '',
  budget_min: props.filters.budget_min ?? null,
  budget_max: props.filters.budget_max ?? null,
  quick_filter: props.filters.quick_filter ?? '',
  sort_by: props.filters.sort_by ?? 'closing_date',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'asc',
  per_page: props.filters.per_page ?? 10,
  showAdvancedFilters: false,
})

// Generate year options (from 2000 to current year + 10)
const currentYear = new Date().getFullYear()
const yearOptions = Array.from({ length: currentYear - 2000 + 11 }, (_, i) => currentYear + 10 - i)

const showBidModal = ref(false)
const selectedTender = ref<typeof props.tenders.data[0] | null>(null)

const bidForm = useForm({
  tender_id: null as number | null,
  vendor_id: null as number | null,
  bid_amount: null as number | null,
  proposal: '',
  technical_specifications: '',
  delivery_timeline_days: null as number | null,
  attachment: null as File | null,
})

function formatCurrency(amount: number | null): string {
  if (!amount) return '-'
  return new Intl.NumberFormat('en-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(amount)
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

function isOpen(tender: typeof props.tenders.data[0]): boolean {
  const now = new Date()
  const opening = new Date(tender.opening_date)
  const closing = new Date(tender.closing_date)
  return tender.status === 'Published' && now >= opening && now <= closing
}

function daysRemaining(closingDate: string): number {
  const now = new Date()
  const closing = new Date(closingDate)
  const diff = closing.getTime() - now.getTime()
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

function openBidModal(tender: typeof props.tenders.data[0]) {
  selectedTender.value = tender
  bidForm.tender_id = tender.id
  bidForm.vendor_id = props.vendors[0]?.id || null
  showBidModal.value = true
}

function closeBidModal() {
  showBidModal.value = false
  selectedTender.value = null
  bidForm.reset()
}

function submitBid() {
  bidForm.post('/tender-bids', {
    preserveScroll: true,
    onSuccess: () => {
      closeBidModal()
    },
  })
}

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/tender-bids', {
    search: state.search || undefined,
    status: state.status || undefined,
    year: state.year || undefined,
    opening_date_from: state.opening_date_from || undefined,
    opening_date_to: state.opening_date_to || undefined,
    closing_date_from: state.closing_date_from || undefined,
    closing_date_to: state.closing_date_to || undefined,
    budget_min: state.budget_min || undefined,
    budget_max: state.budget_max || undefined,
    quick_filter: state.quick_filter || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
  state.search = ''
  state.status = 'Published'
  state.year = null
  state.opening_date_from = ''
  state.opening_date_to = ''
  state.closing_date_from = ''
  state.closing_date_to = ''
  state.budget_min = null
  state.budget_max = null
  state.quick_filter = ''
  applyFilters({ page: 1 })
}

function applyQuickFilter(filter: string) {
  state.quick_filter = filter
  state.opening_date_from = ''
  state.opening_date_to = ''
  state.closing_date_from = ''
  state.closing_date_to = ''
  applyFilters({ page: 1 })
}

function hasActiveFilters(): boolean {
  return !!(
    state.search ||
    (state.status && state.status !== 'Published') ||
    state.year ||
    state.opening_date_from ||
    state.opening_date_to ||
    state.closing_date_from ||
    state.closing_date_to ||
    state.budget_min ||
    state.budget_max ||
    state.quick_filter
  )
}

function goTo(url: string | null) {
  if (!url) return
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

function handleFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  bidForm.attachment = target.files?.[0] || null
}
</script>

<template>
  <Head title="Tender Opportunities" />
  <AppLayout :breadcrumbs="[{ title: 'Tender Opportunities', href: '/tender-bids' }]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Tender Opportunities</h1>
        <p class="text-sm text-muted-foreground">Browse and submit bids for available tenders</p>
      </div>

      <!-- Quick Filters -->
      <div class="mb-4 flex flex-wrap gap-2">
        <button
          @click="applyQuickFilter('open_now')"
          class="rounded-full px-4 py-1.5 text-sm border transition-colors"
          :class="state.quick_filter === 'open_now' ? 'bg-primary text-white border-primary' : 'bg-white hover:bg-gray-50'"
        >
          🟢 Open Now
        </button>
        <button
          @click="applyQuickFilter('closing_soon')"
          class="rounded-full px-4 py-1.5 text-sm border transition-colors"
          :class="state.quick_filter === 'closing_soon' ? 'bg-primary text-white border-primary' : 'bg-white hover:bg-gray-50'"
        >
          ⏰ Closing Soon (7 days)
        </button>
        <button
          @click="applyQuickFilter('recently_added')"
          class="rounded-full px-4 py-1.5 text-sm border transition-colors"
          :class="state.quick_filter === 'recently_added' ? 'bg-primary text-white border-primary' : 'bg-white hover:bg-gray-50'"
        >
          🆕 Recently Added
        </button>
        <button
          v-if="state.quick_filter"
          @click="state.quick_filter = ''; applyFilters({ page: 1 })"
          class="rounded-full px-4 py-1.5 text-sm border bg-gray-100 hover:bg-gray-200"
        >
          ✕ Clear Quick Filter
        </button>
      </div>

      <!-- Main Filters -->
      <div class="mb-4 rounded-md border bg-white p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4 mb-3">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Search</label>
            <input
              v-model="state.search"
              type="text"
              placeholder="Tender number, title, or description"
              @keyup.enter="applyFilters({ page: 1 })"
              class="block w-full rounded-md border p-2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Year</label>
            <select v-model.number="state.year" @change="applyFilters({ page: 1 })" class="block w-full rounded-md border p-2">
              <option :value="null">All Years</option>
              <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select v-model="state.status" @change="applyFilters({ page: 1 })" class="block w-full rounded-md border p-2">
              <option value="">All Statuses</option>
              <option value="Published">Published</option>
              <option value="Closed">Closed</option>
              <option value="Awarded">Awarded</option>
            </select>
          </div>
        </div>

        <!-- Advanced Filters Toggle -->
        <button
          @click="state.showAdvancedFilters = !state.showAdvancedFilters"
          class="text-sm text-primary hover:underline mb-3"
        >
          {{ state.showAdvancedFilters ? '▼ Hide' : '▶ Show' }} Advanced Filters
        </button>

        <!-- Advanced Filters -->
        <div v-if="state.showAdvancedFilters" class="space-y-3 pt-3 border-t">
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium mb-1">Opening Date From</label>
              <input
                v-model="state.opening_date_from"
                type="date"
                class="block w-full rounded-md border p-2"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Opening Date To</label>
              <input
                v-model="state.opening_date_to"
                type="date"
                class="block w-full rounded-md border p-2"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium mb-1">Closing Date From</label>
              <input
                v-model="state.closing_date_from"
                type="date"
                class="block w-full rounded-md border p-2"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Closing Date To</label>
              <input
                v-model="state.closing_date_to"
                type="date"
                class="block w-full rounded-md border p-2"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium mb-1">Budget Min (MYR)</label>
              <input
                v-model.number="state.budget_min"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="block w-full rounded-md border p-2"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Budget Max (MYR)</label>
              <input
                v-model.number="state.budget_max"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="block w-full rounded-md border p-2"
              />
            </div>
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex items-center gap-3 mt-4">
          <button @click="applyFilters({ page: 1 })" class="rounded-md bg-primary text-white px-4 py-2 hover:bg-primary/90">
            Apply Filters
          </button>
          <button @click="resetFilters" class="rounded-md border px-4 py-2 hover:bg-gray-50">
            Reset All
          </button>
          <div v-if="hasActiveFilters()" class="text-sm text-muted-foreground">
            {{ hasActiveFilters() ? '✓ Filters active' : '' }}
          </div>
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

      <!-- Tender List -->
      <div class="space-y-4">
        <div 
          v-for="tender in props.tenders.data" 
          :key="tender.id"
          class="rounded-md border bg-white p-6 space-y-3"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h2 class="text-xl font-semibold">{{ tender.title }}</h2>
                <span 
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="getStatusColor(tender.status)"
                >
                  {{ tender.status }}
                </span>
              </div>
              <p class="text-sm text-muted-foreground">{{ tender.tender_number }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-muted-foreground">Estimated Budget</p>
              <p class="text-lg font-semibold">{{ formatCurrency(tender.estimated_budget) }}</p>
            </div>
          </div>

          <p class="text-base line-clamp-2">{{ tender.description }}</p>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div>
              <p class="font-medium text-muted-foreground">Opening Date</p>
              <p>{{ new Date(tender.opening_date).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</p>
            </div>
            <div>
              <p class="font-medium text-muted-foreground">Closing Date</p>
              <p>{{ new Date(tender.closing_date).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</p>
            </div>
            <div>
              <p class="font-medium text-muted-foreground">Days Remaining</p>
              <p :class="{ 'text-red-600 font-semibold': daysRemaining(tender.closing_date) <= 3 }">
                {{ daysRemaining(tender.closing_date) > 0 ? `${daysRemaining(tender.closing_date)} days` : 'Closed' }}
              </p>
            </div>
            <div>
              <p class="font-medium text-muted-foreground">Total Bids</p>
              <p>{{ tender.bids_count }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 pt-2 border-t">
            <a :href="`/tenders/${tender.id}`" class="text-primary hover:underline text-sm">View Details</a>
            <button
              v-if="isOpen(tender)"
              @click="openBidModal(tender)"
              class="rounded-md bg-primary px-4 py-2 text-sm text-white hover:bg-primary/90"
            >
              Submit Bid
            </button>
            <span v-else-if="tender.status === 'Closed'" class="text-sm text-muted-foreground">
              Bidding Closed
            </span>
            <span v-else-if="tender.status === 'Awarded'" class="text-sm text-green-600 font-medium">
              Tender Awarded
            </span>
          </div>
        </div>

        <div v-if="props.tenders.data.length === 0" class="text-center py-12 text-muted-foreground">
          No tenders found matching your criteria.
        </div>
      </div>

      <!-- Pagination -->
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

    <!-- Submit Bid Modal -->
    <Teleport to="body">
      <div 
        v-if="showBidModal && selectedTender"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        @click.self="closeBidModal"
      >
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-semibold mb-4">Submit Bid for {{ selectedTender.tender_number }}</h3>
          
          <form @submit.prevent="submitBid" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Select Vendor <span class="text-red-500">*</span></label>
              <select
                v-model.number="bidForm.vendor_id"
                required
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': bidForm.errors.vendor_id }"
              >
                <option :value="null">Select a vendor</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }} ({{ vendor.email }})
                </option>
              </select>
              <p v-if="bidForm.errors.vendor_id" class="mt-1 text-sm text-red-500">{{ bidForm.errors.vendor_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Bid Amount (MYR) <span class="text-red-500">*</span></label>
              <input
                v-model.number="bidForm.bid_amount"
                type="number"
                step="0.01"
                min="0"
                required
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': bidForm.errors.bid_amount }"
              />
              <p v-if="bidForm.errors.bid_amount" class="mt-1 text-sm text-red-500">{{ bidForm.errors.bid_amount }}</p>
              <p v-if="selectedTender.estimated_budget" class="mt-1 text-sm text-muted-foreground">
                Estimated Budget: {{ formatCurrency(selectedTender.estimated_budget) }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Delivery Timeline (Days)</label>
              <input
                v-model.number="bidForm.delivery_timeline_days"
                type="number"
                min="1"
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': bidForm.errors.delivery_timeline_days }"
              />
              <p v-if="bidForm.errors.delivery_timeline_days" class="mt-1 text-sm text-red-500">{{ bidForm.errors.delivery_timeline_days }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Proposal</label>
              <textarea
                v-model="bidForm.proposal"
                rows="4"
                placeholder="Describe your proposal..."
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': bidForm.errors.proposal }"
              />
              <p v-if="bidForm.errors.proposal" class="mt-1 text-sm text-red-500">{{ bidForm.errors.proposal }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Technical Specifications</label>
              <textarea
                v-model="bidForm.technical_specifications"
                rows="4"
                placeholder="Provide technical details..."
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': bidForm.errors.technical_specifications }"
              />
              <p v-if="bidForm.errors.technical_specifications" class="mt-1 text-sm text-red-500">{{ bidForm.errors.technical_specifications }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Attachment</label>
              <input
                type="file"
                @change="handleFileChange"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': bidForm.errors.attachment }"
              />
              <p v-if="bidForm.errors.attachment" class="mt-1 text-sm text-red-500">{{ bidForm.errors.attachment }}</p>
              <p class="mt-1 text-sm text-muted-foreground">
                Accepted formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max: 10MB)
              </p>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t">
              <button
                type="submit"
                :disabled="bidForm.processing"
                class="flex-1 rounded-md bg-primary px-4 py-2 text-white hover:bg-primary/90 disabled:opacity-50"
              >
                {{ bidForm.processing ? 'Submitting...' : 'Submit Bid' }}
              </button>
              <button
                type="button"
                @click="closeBidModal"
                :disabled="bidForm.processing"
                class="flex-1 rounded-md border px-4 py-2 disabled:opacity-50"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
