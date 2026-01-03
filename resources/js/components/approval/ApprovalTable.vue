<script setup lang="ts">
import ApprovalTableHeader from './ApprovalTableHeader.vue'
import ApprovalTableRow from './ApprovalTableRow.vue'
import EmptyState from './EmptyState.vue'

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

interface Props {
  requests: Request[]
  sortBy?: string
  sortDir?: 'asc' | 'desc'
}

withDefaults(defineProps<Props>(), {
  sortBy: 'submitted_at',
  sortDir: 'desc',
})

interface Emits {
  (e: 'sort', column: string): void
  (e: 'view', id: number): void
}

defineEmits<Emits>()

const columns = [
  { key: 'view', label: 'View', sortable: false, width: '60px' },
  { key: 'id', label: 'Ref ID', sortable: true, width: '100px' },
  { key: 'user', label: "Applicant's Name", sortable: false },
  { key: 'title', label: 'Title', sortable: true },
  { key: 'purchase_ref_no', label: 'Purchase Ref No', sortable: false },
  { key: 'budget', label: 'Budget', sortable: true },
  { key: 'submitted_at', label: 'Submitted', sortable: true },
  { key: 'status', label: 'Status', sortable: true, width: '120px' },
]
</script>

<template>
  <div class="overflow-x-auto rounded-lg border">
    <table class="min-w-full divide-y">
      <ApprovalTableHeader :columns="columns" :sort-by="sortBy" :sort-dir="sortDir" @sort="$emit('sort', $event)" />
      <tbody>
        <ApprovalTableRow
          v-for="request in requests"
          :key="request.id"
          :request="request"
          @view="$emit('view', $event)"
        />
        <tr v-if="requests.length === 0">
          <td colspan="8" class="px-3 py-6">
            <EmptyState />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

