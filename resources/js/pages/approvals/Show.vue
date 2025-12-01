<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import RequestHeader from '@/components/approval/RequestHeader.vue'
import BudgetStatusCard from '@/components/approval/BudgetStatusCard.vue'
import ItemsTable from '@/components/approval/ItemsTable.vue'
import NotesSection from '@/components/approval/NotesSection.vue'
import ApprovalSection from '@/components/approval/ApprovalSection.vue'
import BackButton from '@/components/approval/BackButton.vue'

interface Item {
  item_no: number
  details: string
  purpose?: string | null
  quantity: number
  price: number | string
}

interface PurchaseRequest {
  id: number
  title: string
  budget: number | string
  purchase_ref_no?: string | null
  items: Item[]
  note: string | null
  status: 'Pending' | 'Approved' | 'Rejected' | string
  submitted_at: string | null
  approval_remarks: string | null
  approved_at?: string | null
  approved_by?: { id: number; name: string }
  user: { id: number; name: string; email: string }
}

const props = defineProps<{
  request: PurchaseRequest
}>()

const state = reactive({
  remarks: props.request.approval_remarks || '',
  isLoading: false,
})

const submitAction = (action: 'approve' | 'reject') => {
  if (state.isLoading) return
  state.isLoading = true
  const url = action === 'approve' ? `/approvals/${props.request.id}/approve` : `/approvals/${props.request.id}/reject`
  const payload = { comment: state.remarks || undefined }
  router.post(url, payload, {
    preserveScroll: true,
    onSuccess: () => {
      state.remarks = ''
      state.isLoading = false
    },
    onError: () => {
      state.isLoading = false
    },
  })
}

const handleRemarksUpdate = (newRemarks: string) => {
  state.remarks = newRemarks
}
</script>

<template>
  <Head :title="`Approval PR #${props.request.id}`" />
  <AppLayout
    :breadcrumbs="[
      { title: 'Approval Purchase Requests', href: '/approvals' },
      { title: `#${props.request.id}`, href: `/approvals/${props.request.id}` },
    ]"
  >
    <div class="space-y-6 p-4">
      <!-- Page Header -->
      <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
          <h1 class="text-3xl font-bold">Purchase Request Details</h1>
          <p class="mt-1 text-muted-foreground">Review and approve this purchase request</p>
        </div>
        <BackButton href="/approvals" label="Back to Requests" />
      </div>

      <!-- Request Header Section -->
      <RequestHeader
        :id="props.request.id"
        :applicant-name="props.request.user.name"
        :applicant-email="props.request.user.email"
        :title="props.request.title"
        :purchase-ref-no="props.request.purchase_ref_no"
        :submitted-at="props.request.submitted_at"
      />

      <!-- Budget & Status Card -->
      <BudgetStatusCard
        :budget="props.request.budget"
        :status="props.request.status"
        :submitted-at="props.request.submitted_at"
        :approved-at="props.request.approved_at"
        :approved-by="props.request.approved_by"
      />

      <!-- Items Table Section -->
      <ItemsTable :items="props.request.items" title="Items" :show-total="true" />

      <!-- Notes Section -->
      <NotesSection :notes="props.request.note" title="Notes" />

      <!-- Approval Section -->
      <ApprovalSection
        :request-id="props.request.id"
        :status="props.request.status"
        :remarks="state.remarks"
        :loading="state.isLoading"
        @update:remarks="handleRemarksUpdate"
        @approve="submitAction('approve')"
        @reject="submitAction('reject')"
      />
    </div>
  </AppLayout>
</template>

