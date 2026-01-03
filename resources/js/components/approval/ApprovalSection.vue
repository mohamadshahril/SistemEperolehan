<script setup lang="ts">
import { MessageSquare, CheckCircle, XCircle } from 'lucide-vue-next'
import RemarksField from './RemarksField.vue'
import ActionButtons from './ActionButtons.vue'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

interface Props {
  requestId: number
  status: string
  remarks: string
  loading?: boolean
}

interface Emits {
  (e: 'update:remarks', value: string): void
  (e: 'approve'): void
  (e: 'reject'): void
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<Emits>()

// Permissions from Inertia shared props
const page = usePage()
const canMap = computed<Record<string, boolean>>(() => {
  const auth: any = (page as any).props?.auth || {}
  return (auth?.can as Record<string, boolean>) || {}
})

const canApprove = computed(() => !!canMap.value['approve purchase requests'])
const canReject = computed(() => !!canMap.value['reject purchase requests'])
</script>

<template>
  <div class="rounded-lg border bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center gap-2">
      <MessageSquare :size="20" class="text-gray-600" />
      <h3 class="text-lg font-semibold">
        {{ props.status === 'Pending' ? 'Approval Actions' : 'Approval Decision' }}
      </h3>
    </div>

    <!-- Status Message for Non-Pending Requests -->
    <div v-if="props.status !== 'Pending'" class="mb-4 rounded-md p-4" :class="{
      'bg-green-50 border border-green-200': props.status === 'Approved',
      'bg-red-50 border border-red-200': props.status === 'Rejected'
    }">
      <div class="flex items-center gap-2">
        <CheckCircle v-if="props.status === 'Approved'" :size="20" class="text-green-600" />
        <XCircle v-if="props.status === 'Rejected'" :size="20" class="text-red-600" />
        <span class="font-medium" :class="{
          'text-green-900': props.status === 'Approved',
          'text-red-900': props.status === 'Rejected'
        }">
          This request has been {{ props.status.toLowerCase() }}
        </span>
      </div>
    </div>

    <!-- Remarks Field -->
    <div class="space-y-2">
      <label class="block text-sm font-medium text-gray-700">
        {{ props.status === 'Pending' ? 'Approver Remarks (Optional)' : 'Approver Remarks' }}
      </label>
      <RemarksField
        :model-value="props.remarks"
        :status="props.status"
        :placeholder="props.status === 'Pending' ? 'Add remarks or comments for this approval decision...' : ''"
        :rows="4"
        @update:model-value="emit('update:remarks', $event)"
      />
    </div>

    <!-- Action Buttons (only for pending requests) -->
    <div v-if="props.status === 'Pending'" class="mt-6">
      <ActionButtons
        :request-id="props.requestId"
        :status="props.status"
        :loading="props.loading"
        :can-approve="canApprove"
        :can-reject="canReject"
        @approve="emit('approve')"
        @reject="emit('reject')"
      />
    </div>
  </div>
</template>
