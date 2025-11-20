<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Form from '@/components/purchase-requests/Form.vue'

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
    purpose?: string | null
    item: Array<{ details: string; purpose?: string | null; quantity: number | string; price: number | string; item_code?: string | null; unit?: string | null }>
    items?: Array<{ details: string; purpose?: string | null; quantity: number | string; price: number | string; item_code?: string | null; unit?: string | null }>
    attachment_url?: string | null
    status?: string
  }
  canEdit: boolean
  options: {
    type_procurements: Array<{ id: number; procurement_code: string; procurement_description: string }>
    file_references: Array<{ id: number; file_code: string; file_description: string }>
    vots: Array<{ id: number; vot_code: string; vot_description: string }>
  }
}>()

const form = useForm({
  title: props.request.title || '',
  type_procurement_id: props.request.type_procurement_id ?? '',
  file_reference_id: props.request.file_reference_id ?? '',
  vot_id: props.request.vot_id ?? '',
  budget: props.request.budget ?? 0,
  note: props.request.note ?? props.request.purpose ?? '',
  item: (props.request.items || props.request.item || []).map((it:any)=>({
    details: it.details,
    purpose: it.purpose ?? '',
    quantity: it.quantity ?? 1,
    price: it.price ?? 0,
    item_code: it.item_code ?? '',
    unit: it.unit ?? '',
  })),
  attachment: null as File | null,
})

const modelProxy = computed({
  get: () => form as any,
  set: (v: any) => { Object.assign(form, v) },
})

function submit() {
  form.post(`/purchase-requests/${props.request.id}`, {
    method: 'put',
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Edit Purchase Request" />
  <AppLayout :breadcrumbs="[
    { title: 'Purchase Requests', href: '/purchase-requests' },
    { title: `Edit #${props.request.id}`, href: `/purchase-requests/${props.request.id}/edit` }
  ]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">Edit Purchase Request</h1>
          <p class="text-sm text-muted-foreground">Status: <span class="rounded bg-gray-100 px-2 py-0.5">{{ props.request.status }}</span></p>
        </div>
        <a :href="`/purchase-requests/${props.request.id}`" class="rounded-md border px-3 py-2">View</a>
      </div>

      <div class="rounded-md border bg-white p-6">
        <div v-if="!props.canEdit" class="mb-4 rounded-md bg-amber-50 p-3 text-amber-800">
          This request is not editable because it is not in Pending status.
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <Form v-model="modelProxy" :options="props.options" :read-only="!props.canEdit" />

          <div>
            <label class="block text-sm font-medium">Replace Attachment (optional)</label>
            <input
              :disabled="!props.canEdit"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
              @change="(e) => (form.attachment = (e.target as HTMLInputElement).files?.[0] || null)"
              class="mt-1 block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.attachment }"
            />
            <p v-if="props.request.attachment_url" class="mt-1 text-xs">Current: <a :href="props.request.attachment_url" target="_blank" class="text-primary hover:underline">Download</a></p>
            <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</p>
          </div>

          <div class="flex items-center gap-3">
            <button v-if="props.canEdit" type="submit" :disabled="form.processing" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
            <Link :href="`/purchase-requests/${props.request.id}`" class="rounded-md border px-4 py-2">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>

</template>
