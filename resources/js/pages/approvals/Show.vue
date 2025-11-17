<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  request: {
    id: number
    title: string
    budget: number | string
    purchase_code?: string | null
    items: Array<{ item_no: number; details: string; purpose?: string | null; quantity: number; price: number | string }>
    purpose: string | null
    status: 'Pending' | 'Approved' | 'Rejected' | string
    submitted_at: string | null
    approval_comment: string | null
    approved_at?: string | null
    approved_by?: number | null
    user: { id: number; name: string; email: string }
  }
}>()

function badgeClasses(status: string) {
  return {
    'bg-amber-100 text-amber-800': status === 'Pending',
    'bg-green-100 text-green-800': status === 'Approved',
    'bg-red-100 text-red-800': status === 'Rejected',
  }
}
</script>

<template>
  <Head :title="`Approval PR #${props.request.id}`" />
  <AppLayout :breadcrumbs="[
    { title: 'Approval Purchase Requests', href: '/approvals' },
    { title: `#${props.request.id}`, href: `/approvals/${props.request.id}` }
  ]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Approval PR #{{ props.request.id }}</h1>
        <a href="/approvals" class="rounded-md border px-3 py-2">Back to list</a>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-3">
          <div>
            <div class="text-sm text-muted-foreground">Reference</div>
            <div class="font-mono">#{{ props.request.id }}</div>
          </div>
          <div>
            <div class="text-sm text-muted-foreground">Employee</div>
            <div>{{ props.request.user.name }} <span class="text-muted-foreground">({{ props.request.user.email }})</span></div>
          </div>
          <div>
            <div class="text-sm text-muted-foreground">Title</div>
            <div>{{ props.request.title }}</div>
          </div>
          <div>
            <div class="text-sm text-muted-foreground">Budget</div>
            <div>{{ 'RM' + Number(props.request.budget).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
          </div>
          <div v-if="props.request.purchase_code">
            <div class="text-sm text-muted-foreground">Code</div>
            <div class="font-mono">{{ props.request.purchase_code }}</div>
          </div>
        </div>

        <div class="space-y-3">
          <div>
            <div class="text-sm text-muted-foreground">Status</div>
            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                  :class="badgeClasses(props.request.status)">
              {{ props.request.status }}
            </span>
          </div>
          <div>
            <div class="text-sm text-muted-foreground">Submitted</div>
            <div>{{ props.request.submitted_at ? new Date(props.request.submitted_at).toLocaleString() : '-' }}</div>
          </div>
          <div v-if="props.request.status !== 'Pending'">
            <div class="text-sm text-muted-foreground">Approved/Rejected At</div>
            <div>{{ props.request.approved_at ? new Date(props.request.approved_at).toLocaleString() : '-' }}</div>
          </div>
        </div>
      </div>

      <div class="mt-6">
        <div class="text-sm text-muted-foreground">Purpose</div>
        <div class="whitespace-pre-wrap">{{ props.request.purpose || '-' }}</div>
      </div>

      <div class="mt-6">
        <div class="text-sm text-muted-foreground">Items</div>
        <div class="overflow-x-auto rounded-md border">
          <table class="min-w-full divide-y">
            <thead>
              <tr>
                <th class="px-3 py-2 text-left">No.</th>
                <th class="px-3 py-2 text-left">Details</th>
                <th class="px-3 py-2 text-left">Purpose</th>
                <th class="px-3 py-2 text-right">Qty</th>
                <th class="px-3 py-2 text-right">Price (RM)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(it, idx) in props.request.items" :key="idx" class="border-b">
                <td class="px-3 py-2">{{ it.item_no }}</td>
                <td class="px-3 py-2">{{ it.details }}</td>
                <td class="px-3 py-2">{{ it.purpose || '-' }}</td>
                <td class="px-3 py-2 text-right">{{ it.quantity }}</td>
                <td class="px-3 py-2 text-right">{{ Number(it.price).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-6">
        <div class="text-sm text-muted-foreground">Approval Comment</div>
        <div class="whitespace-pre-wrap">{{ props.request.approval_comment || '-' }}</div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.text-muted-foreground { color: #6b7280; }
</style>
