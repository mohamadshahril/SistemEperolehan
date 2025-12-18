<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  tender: {
    id: number
    tender_number: string
    title: string
    description: string
    estimated_budget: number | null
    opening_date: string
    closing_date: string
    status: 'Draft' | 'Published' | 'Closed' | 'Awarded' | 'Cancelled'
    requirements: string | null
    terms_conditions: string | null
    creator: { id: number; name: string; email: string } | null
    bids: Array<{
      id: number
      bid_amount: number
      proposal: string | null
      technical_specifications: string | null
      delivery_timeline_days: number | null
      status: string
      vendor: { id: number; name: string; email: string; phone: string | null }
      submitted_at: string
    }>
    awarded_bid: {
      id: number
      vendor: { id: number; name: string; email: string; phone: string | null }
    } | null
    awarded_at: string | null
    created_at: string
  }
}>()

const showAwardModal = ref(false)
const selectedBidId = ref<number | null>(null)

const awardForm = useForm({
  bid_id: null as number | null,
})

function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    Draft: 'bg-gray-100 text-gray-800',
    Published: 'bg-blue-100 text-blue-800',
    Closed: 'bg-yellow-100 text-yellow-800',
    Awarded: 'bg-green-100 text-green-800',
    Cancelled: 'bg-red-100 text-red-800',
    Submitted: 'bg-blue-100 text-blue-800',
    'Under Review': 'bg-yellow-100 text-yellow-800',
    Accepted: 'bg-green-100 text-green-800',
    Rejected: 'bg-red-100 text-red-800',
    Withdrawn: 'bg-gray-100 text-gray-800',
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

function openAwardModal(bidId: number) {
  selectedBidId.value = bidId
  awardForm.bid_id = bidId
  showAwardModal.value = true
}

function closeAwardModal() {
  showAwardModal.value = false
  selectedBidId.value = null
  awardForm.reset()
}

function awardTender() {
  awardForm.post(`/tenders/${props.tender.id}/award`, {
    preserveScroll: true,
    onSuccess: () => {
      closeAwardModal()
    },
  })
}
</script>

<template>
  <Head :title="`Tender - ${tender.tender_number}`" />
  <AppLayout :breadcrumbs="[
    { title: 'Tenders', href: '/tenders' },
    { title: tender.tender_number, href: `/tenders/${tender.id}` }
  ]">
    <div class="p-4 space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold">{{ tender.title }}</h1>
          <p class="text-sm text-muted-foreground">{{ tender.tender_number }}</p>
        </div>
        <div class="flex items-center gap-2">
          <span 
            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
            :class="getStatusColor(tender.status)"
          >
            {{ tender.status }}
          </span>
          <a 
            v-if="tender.status !== 'Awarded' && tender.status !== 'Cancelled'"
            :href="`/tenders/${tender.id}/edit`" 
            class="rounded-md border px-3 py-2 text-sm"
          >
            Edit
          </a>
        </div>
      </div>

      <!-- Tender Details -->
      <div class="rounded-md border bg-white p-6 space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Tender Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium text-muted-foreground">Estimated Budget</p>
            <p class="text-lg font-semibold">{{ formatCurrency(tender.estimated_budget) }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-muted-foreground">Created By</p>
            <p class="text-lg">{{ tender.creator?.name || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-muted-foreground">Opening Date</p>
            <p class="text-lg">{{ new Date(tender.opening_date).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-muted-foreground">Closing Date</p>
            <p class="text-lg">{{ new Date(tender.closing_date).toLocaleDateString('en-GB', { timeZone: 'UTC' }) }}</p>
          </div>
        </div>

        <div>
          <p class="text-sm font-medium text-muted-foreground mb-2">Description</p>
          <p class="text-base whitespace-pre-wrap">{{ tender.description }}</p>
        </div>

        <div v-if="tender.requirements">
          <p class="text-sm font-medium text-muted-foreground mb-2">Requirements</p>
          <p class="text-base whitespace-pre-wrap">{{ tender.requirements }}</p>
        </div>

        <div v-if="tender.terms_conditions">
          <p class="text-sm font-medium text-muted-foreground mb-2">Terms & Conditions</p>
          <p class="text-base whitespace-pre-wrap">{{ tender.terms_conditions }}</p>
        </div>

        <div v-if="tender.awarded_bid">
          <p class="text-sm font-medium text-muted-foreground mb-2">Awarded To</p>
          <div class="flex items-center gap-2">
            <p class="text-lg font-semibold">{{ tender.awarded_bid.vendor.name }}</p>
            <span class="text-sm text-muted-foreground">
              on {{ tender.awarded_at ? new Date(tender.awarded_at).toLocaleDateString('en-GB', { timeZone: 'UTC' }) : 'N/A' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Bids Section -->
      <div class="rounded-md border bg-white p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-2">
          <h2 class="text-lg font-semibold">Bids ({{ tender.bids.length }})</h2>
        </div>

        <div v-if="tender.bids.length === 0" class="text-center py-8 text-muted-foreground">
          No bids submitted yet.
        </div>

        <div v-else class="space-y-4">
          <div 
            v-for="bid in tender.bids" 
            :key="bid.id"
            class="border rounded-lg p-4 space-y-3"
            :class="{ 'border-green-500 bg-green-50': bid.id === tender.awarded_bid?.id }"
          >
            <div class="flex items-start justify-between">
              <div>
                <h3 class="font-semibold text-lg">{{ bid.vendor.name }}</h3>
                <p class="text-sm text-muted-foreground">{{ bid.vendor.email }}</p>
                <p v-if="bid.vendor.phone" class="text-sm text-muted-foreground">{{ bid.vendor.phone }}</p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-primary">{{ formatCurrency(bid.bid_amount) }}</p>
                <span 
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium mt-1"
                  :class="getStatusColor(bid.status)"
                >
                  {{ bid.status }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div v-if="bid.delivery_timeline_days">
                <p class="font-medium text-muted-foreground">Delivery Timeline</p>
                <p>{{ bid.delivery_timeline_days }} days</p>
              </div>
              <div>
                <p class="font-medium text-muted-foreground">Submitted</p>
                <p>{{ new Date(bid.submitted_at).toLocaleString('en-GB', { timeZone: 'UTC' }) }}</p>
              </div>
            </div>

            <div v-if="bid.proposal">
              <p class="font-medium text-sm text-muted-foreground mb-1">Proposal</p>
              <p class="text-sm whitespace-pre-wrap">{{ bid.proposal }}</p>
            </div>

            <div v-if="bid.technical_specifications">
              <p class="font-medium text-sm text-muted-foreground mb-1">Technical Specifications</p>
              <p class="text-sm whitespace-pre-wrap">{{ bid.technical_specifications }}</p>
            </div>

            <div 
              v-if="tender.status === 'Published' || tender.status === 'Closed'" 
              class="flex items-center gap-2 pt-2 border-t"
            >
              <button
                v-if="bid.status === 'Submitted' || bid.status === 'Under Review'"
                @click="openAwardModal(bid.id)"
                class="rounded-md bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700"
              >
                Award This Bid
              </button>
              <span v-if="bid.id === tender.awarded_bid?.id" class="text-sm font-medium text-green-600">
                ✓ Awarded Bid
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Award Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="showAwardModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        @click.self="closeAwardModal"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold mb-4">Award Tender</h3>
          <p class="text-sm text-muted-foreground mb-6">
            Are you sure you want to award this tender to the selected vendor? This action will:
          </p>
          <ul class="list-disc list-inside text-sm text-muted-foreground mb-6 space-y-1">
            <li>Mark the tender as "Awarded"</li>
            <li>Accept the selected bid</li>
            <li>Reject all other bids</li>
          </ul>
          <div class="flex items-center gap-3">
            <button
              @click="awardTender"
              :disabled="awardForm.processing"
              class="flex-1 rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
            >
              {{ awardForm.processing ? 'Awarding...' : 'Confirm Award' }}
            </button>
            <button
              @click="closeAwardModal"
              :disabled="awardForm.processing"
              class="flex-1 rounded-md border px-4 py-2 disabled:opacity-50"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
