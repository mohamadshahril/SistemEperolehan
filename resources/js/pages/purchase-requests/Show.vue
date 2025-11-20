<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import Form from '@/components/purchase-requests/Form.vue'
import IconButton from '@/components/IconButton.vue'
import { Pencil, Paperclip, Printer, ArrowLeft } from 'lucide-vue-next'
import { onMounted } from 'vue'

const props = defineProps<{
  request: {
    id: number
    title: string
    type_procurement_id: number | string
    file_reference_id: number | string
    vot_id: number | string
    location_iso_code?: string | null
    budget: number | string
    note?: string | null
    notes?: string | null
    purpose?: string | null
    item: Array<{ details: string; purpose?: string | null; quantity: number | string; price: number | string; item_code?: string | null; unit?: string | null }>
    items?: Array<{ details: string; purpose?: string | null; quantity: number | string; price: number | string; item_code?: string | null; unit?: string | null }>
    attachment_url?: string | null
    status?: string
    purchase_ref_no?: string | null
    submitted_at?: string | null
  }
  options: {
    type_procurements: Array<{ id: number; procurement_code?: string; procurement_description?: string }>
    file_references: Array<{ id: number; file_code?: string; file_description?: string }>
    vots: Array<{ id: number; vot_code?: string; vot_description?: string }>
  }
}>()

const readonlyModel = {
  title: props.request.title,
  type_procurement_id: props.request.type_procurement_id,
  file_reference_id: props.request.file_reference_id,
  vot_id: props.request.vot_id,
  budget: props.request.budget,
  // Support both `note` and legacy/plural `notes` from backend
  note: props.request.note ?? props.request.notes ?? props.request.purpose ?? '',
  item: (props.request.items || props.request.item || []).map((it:any)=>({
    details: it.details,
    purpose: it.purpose ?? '',
    quantity: it.quantity ?? 1,
    price: it.price ?? 0,
    item_code: it.item_code ?? '',
    unit: it.unit ?? '',
  })),
}

// Options are provided by the backend (controller@show) with the selected values

// Map status text to Tailwind color classes (same as Index.vue)
function statusClass(status: string | undefined | null): string {
  const s = (status || '').toLowerCase()
  if (s === 'approved') return 'bg-green-100 text-green-800 border border-green-200'
  if (s === 'rejected') return 'bg-red-100 text-red-800 border border-red-200'
  if (s === 'pending') return 'bg-yellow-100 text-yellow-800 border border-yellow-200'
  return 'bg-gray-100 text-gray-800 border border-gray-200'
}

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('print') === '1') {
    // slight delay to ensure content rendered
    setTimeout(() => window.print(), 200)
  }
})
</script>

<template>
  <Head title="View Purchase Request" />
  <AppLayout :breadcrumbs="[
    { title: 'Purchase Requests', href: '/purchase-requests' },
    { title: `#${props.request.id}`, href: `/purchase-requests/${props.request.id}` }
  ]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">Purchase Request #{{ props.request.id }}</h1>
          <div class="text-sm text-muted-foreground flex gap-3 flex-wrap">
            <span>
              Status:
              <span class="rounded px-2 py-0.5" :class="statusClass(props.request.status)">{{ props.request.status }}</span>
            </span>
            <span v-if="props.request.purchase_ref_no">Ref: {{ props.request.purchase_ref_no }}</span>
            <span v-if="props.request.submitted_at">Submitted: {{ new Date(props.request.submitted_at).toLocaleString() }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <IconButton v-if="(props.request.status||'').toLowerCase() === 'pending'" :icon="Pencil" title="Edit" :href="`/purchase-requests/${props.request.id}/edit`" />
          <IconButton v-if="props.request.attachment_url" :icon="Paperclip" title="Attachment" :href="props.request.attachment_url || undefined" external />
          <IconButton :icon="Printer" title="Print" :href="`/purchase-requests/${props.request.id}?print=1`" external />
          <Link href="/purchase-requests" class="inline-flex items-center gap-1 rounded-md border px-3 py-2"><ArrowLeft class="h-4 w-4" /> Back</Link>
        </div>
      </div>

      <div class="rounded-md border bg-white p-6">
        <div class="mb-4" v-if="props.request.attachment_url">
          <label class="block text-sm font-medium">Attachment</label>
          <a :href="props.request.attachment_url" target="_blank" class="text-primary hover:underline">Download file</a>
        </div>

        <Form :model-value="readonlyModel as any" :options="props.options as any" :read-only="true" />
      </div>
    </div>
  </AppLayout>
</template>
