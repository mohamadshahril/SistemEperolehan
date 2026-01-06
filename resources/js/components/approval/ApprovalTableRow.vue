<script setup lang="ts">
import { Eye } from 'lucide-vue-next'
import StatusBadge from './StatusBadge.vue'

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
  request: Request
}

defineProps<Props>()

interface Emits {
  (e: 'view', id: number): void
}

defineEmits<Emits>()

const formatCurrency = (amount: number | string): string => {
  return 'RM ' + Number(amount).toLocaleString('en-MY', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const formatDate = (dateString: string | null): string => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-GB', { timeZone: 'UTC' })
}
</script>

<template>
  <tr class="border-b align-top">
    <td class="px-3 py-2 font-mono">
        <a class="text-primary">
            #{{ request.id }}
        </a>
    </td>
    <td class="px-3 py-2">{{ request.user.name }}</td>
    <td class="px-3 py-2">{{ request.title }}</td>
    <td class="px-3 py-2 font-mono">{{ request.purchase_ref_no || '-' }}</td>
    <td class="px-3 py-2">{{ formatCurrency(request.budget) }}</td>
    <td class="px-3 py-2">{{ formatDate(request.submitted_at) }}</td>
    <td class="px-3 py-2">
      <StatusBadge :status="request.status" />
    </td>
      <td class="px-3 py-2">
          <button
              @click="$emit('view', request.id)"
              class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-sm hover:bg-gray-100"
              title="View details"
          >
              <Eye :size="16" />
          </button>
      </td>
  </tr>
</template>

