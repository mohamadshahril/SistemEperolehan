<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

type Item = { item_no: number; details: string; purpose?: string | null; quantity: number; price: number | string }

const props = defineProps<{
  request: {
    id: number
    title: string
    type_procurement_id: number | string
    file_reference_id: number | string
    vot_id: number | string
    location_iso_code?: string | null
    budget: number | string
    items: Item[]
    purpose?: string | null
    status: string
    submitted_at: string | null
    attachment_path?: string | null
    attachment_url?: string | null
    purchase_code?: string | null
  }
  canEdit: boolean
  options: {
    type_procurements: Array<{ id: number; procurement_code: number; procurement_description: string }>
    file_references: Array<{ id: number; file_code: string; file_description: string }>
    vots: Array<{ id: number; vot_code: number; vot_description: string }>
  }
}>()

const form = useForm({
  title: props.request.title ?? '',
  type_procurement_id: props.request.type_procurement_id ?? ('' as any),
  file_reference_id: props.request.file_reference_id ?? ('' as any),
  vot_id: props.request.vot_id ?? ('' as any),
  budget: props.request.budget ?? ('' as any),
  items: (props.request.items && props.request.items.length > 0 ? props.request.items : [{ item_no: 1, details: '', purpose: '', quantity: 1, price: '' }]) as Item[],
  purpose: props.request.purpose ?? '',
  attachment: null as File | null,
})

form.transform((data: any) => {
  const payload: any = {
    ...data,
    title: (data.title ?? '').toString().trim(),
    type_procurement_id: Number(data.type_procurement_id || 0) || data.type_procurement_id,
    file_reference_id: Number(data.file_reference_id || 0) || data.file_reference_id,
    vot_id: Number(data.vot_id || 0) || data.vot_id,
    budget: Number(data.budget ?? 0),
    // Normalize item fields
    items: (Array.isArray(data.items) ? data.items : []).map((it: any, i: number) => ({
      item_no: Number(it.item_no ?? i + 1),
      details: (it.details ?? '').toString(),
      purpose: (it.purpose ?? '').toString(),
      quantity: Number(it.quantity ?? 1),
      price: Number(it.price ?? 0),
    })),
    _method: 'PUT',
  }
  if (!(data.attachment instanceof File)) {
    delete payload.attachment
  }
  return payload
})

const submitting = ref(false)
let originalTitle: string | null = null

function withTemporaryTitleForPrint(run: () => void) {
  if (typeof document !== 'undefined') {
    originalTitle = document.title
    document.title = ' '
    const restore = () => {
      if (originalTitle !== null) document.title = originalTitle
      originalTitle = null
      window.removeEventListener('afterprint', restore)
    }
    window.addEventListener('afterprint', restore)
    setTimeout(() => restore(), 2000)
  }
  run()
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    const params = new URLSearchParams(window.location.search)
    if (params.get('print') === '1') {
      const navigateBack = () => {
        window.removeEventListener('afterprint', navigateBack)
        document.removeEventListener('visibilitychange', onVisibilityChange)
        try {
          if (window.opener && !window.opener.closed) {
            window.close()
            return
          }
        } catch {}
        try {
          router.get('/purchase-requests', {}, { replace: true })
        } catch {
          window.location.href = '/purchase-requests'
        }
      }

      const onVisibilityChange = () => {
        if (document.visibilityState === 'visible') {
          navigateBack()
        }
      }

      window.addEventListener('afterprint', navigateBack)
      document.addEventListener('visibilitychange', onVisibilityChange)

      withTemporaryTitleForPrint(() => {
        setTimeout(() => window.print(), 200)
      })
    }
  }
})

function addItem() {
  const nextNo = (form.items?.length || 0) + 1
  form.items.push({ item_no: nextNo, details: '', purpose: '', quantity: 1, price: '' })
}

function removeItem(index: number) {
  if (form.items.length <= 1) return
  form.items.splice(index, 1)
  form.items.forEach((it, i) => (it.item_no = i + 1))
}

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files && input.files.length > 0) {
    form.attachment = input.files[0]
  } else {
    form.attachment = null
  }
}

function submit() {
  if (!props.canEdit) return
  submitting.value = true
  form.post(`/purchase-requests/${props.request.id}`, {
    forceFormData: true,
    onFinish: () => (submitting.value = false),
  })
}

function destroyRequest() {
  if (!props.canEdit) return
  if (!confirm('Are you sure you want to delete this purchase request? This action cannot be undone.')) return
  router.delete(`/purchase-requests/${props.request.id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Edit Purchase Request" />
  <AppLayout :breadcrumbs="[{ title: 'Purchase Requests', href: '/purchase-requests' }, { title: `#${props.request.id} Edit`, href: `/purchase-requests/${props.request.id}/edit` }]">
    <div class="mx-auto max-w-4xl p-4">
      <div class="mb-6 flex items-center justify-between no-print">
        <h1 class="text-2xl font-semibold">Edit Purchase Request</h1>
        <a href="/purchase-requests" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <!-- Print-only summary -->
      <div class="print-only">
        <div class="mb-4">
          <h2 class="text-xl font-semibold">Purchase Request #{{ props.request.id }}</h2>
          <div class="text-sm text-gray-500">Printed on: {{ new Date().toLocaleString() }}</div>
        </div>
        <div class="space-y-2">
          <div><span class="font-medium">Title:</span> {{ props.request.title }}</div>
          <div><span class="font-medium">Code:</span> {{ props.request.purchase_code || '-' }}</div>
          <div><span class="font-medium">Location:</span> {{ props.request.location_iso_code || '-' }}</div>
          <div><span class="font-medium">Budget:</span> {{ 'RM' + Number(props.request.budget).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
          <div v-if="props.request.purpose"><span class="font-medium">Purpose:</span> {{ props.request.purpose }}</div>
          <div><span class="font-medium">Items:</span>
            <ul class="list-disc ml-6">
              <li v-for="(it, idx) in props.request.items" :key="idx">
                {{ it.item_no }}. {{ it.details }} — Qty: {{ it.quantity }}, Price: {{ 'RM' + Number(it.price).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                <template v-if="it.purpose"> ({{ it.purpose }})</template>
              </li>
            </ul>
          </div>
          <div><span class="font-medium">Status:</span> {{ props.request.status }}</div>
          <div><span class="font-medium">Submitted Date:</span> {{ props.request.submitted_at ? new Date(props.request.submitted_at).toLocaleDateString('en-GB', { timeZone: 'UTC' }) : '-' }}</div>
          <div>
            <span class="font-medium">Attachment:</span>
            <template v-if="props.request.attachment_url">
              {{ props.request.attachment_url }}
            </template>
            <template v-else>-</template>
          </div>
        </div>
      </div>

      <div v-if="!props.canEdit" class="mb-4 rounded-md border border-yellow-300 bg-yellow-50 p-3 text-sm text-yellow-800 no-print">
        This request is <strong>{{ props.request.status }}</strong> and cannot be edited.
      </div>

      <form @submit.prevent="submit" class="space-y-6 no-print">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium">Title</label>
            <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit" />
            <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">Budget (RM)</label>
            <input v-model="form.budget" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit" />
            <div v-if="form.errors.budget" class="mt-1 text-sm text-red-600">{{ form.errors.budget }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="block text-sm font-medium">Type Procurement</label>
            <select v-model="form.type_procurement_id" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit">
              <option value="">-- Select --</option>
              <option v-for="opt in props.options.type_procurements" :key="opt.id" :value="opt.id">
                {{ opt.procurement_code }} - {{ opt.procurement_description }}
              </option>
            </select>
            <div v-if="form.errors.type_procurement_id" class="mt-1 text-sm text-red-600">{{ form.errors.type_procurement_id }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">File Reference</label>
            <select v-model="form.file_reference_id" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit">
              <option value="">-- Select --</option>
              <option v-for="opt in props.options.file_references" :key="opt.id" :value="opt.id">
                {{ opt.file_code }} - {{ opt.file_description }}
              </option>
            </select>
            <div v-if="form.errors.file_reference_id" class="mt-1 text-sm text-red-600">{{ form.errors.file_reference_id }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">VOT</label>
            <select v-model="form.vot_id" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit">
              <option value="">-- Select --</option>
              <option v-for="opt in props.options.vots" :key="opt.id" :value="opt.id">
                {{ opt.vot_code }} - {{ opt.vot_description }}
              </option>
            </select>
            <div v-if="form.errors.vot_id" class="mt-1 text-sm text-red-600">{{ form.errors.vot_id }}</div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium">Purpose / Remarks</label>
          <textarea v-model="form.purpose" rows="3" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit"></textarea>
          <div v-if="form.errors.purpose" class="mt-1 text-sm text-red-600">{{ form.errors.purpose }}</div>
        </div>

        <div>
          <div class="mb-2 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Items</h2>
            <button type="button" @click="addItem" class="rounded-md border px-3 py-1 text-sm" :disabled="!props.canEdit">Add Item</button>
          </div>
          <div class="overflow-x-auto rounded-md border">
            <table class="min-w-full divide-y">
              <thead class="bg-muted/30">
                <tr>
                  <th class="px-2 py-2 text-left text-sm">No.</th>
                  <th class="px-2 py-2 text-left text-sm">Details</th>
                  <th class="px-2 py-2 text-left text-sm">Purpose</th>
                  <th class="px-2 py-2 text-left text-sm">Qty</th>
                  <th class="px-2 py-2 text-left text-sm">Price (RM)</th>
                  <th class="px-2 py-2 text-left text-sm"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(it, idx) in form.items" :key="idx" class="odd:bg-white even:bg-muted/10">
                  <td class="px-2 py-2 w-12">{{ idx + 1 }}</td>
                  <td class="px-2 py-2">
                    <input v-model="it.details" type="text" class="block w-full rounded-md border p-2" :disabled="!props.canEdit" />
                    <div v-if="form.errors[`items.${idx}.details`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`items.${idx}.details`] }}</div>
                  </td>
                  <td class="px-2 py-2">
                    <input v-model="it.purpose" type="text" class="block w-full rounded-md border p-2" :disabled="!props.canEdit" />
                    <div v-if="form.errors[`items.${idx}.purpose`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`items.${idx}.purpose`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-24">
                    <input v-model.number="it.quantity" type="number" min="1" class="block w-full rounded-md border p-2" :disabled="!props.canEdit" />
                    <div v-if="form.errors[`items.${idx}.quantity`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`items.${idx}.quantity`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-40">
                    <input v-model="it.price" type="number" min="0" step="0.01" class="block w-full rounded-md border p-2" :disabled="!props.canEdit" />
                    <div v-if="form.errors[`items.${idx}.price`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`items.${idx}.price`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-20 text-right">
                    <button type="button" @click="removeItem(idx)" class="text-sm text-red-600" :disabled="form.items.length <= 1 || !props.canEdit">Remove</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="form.errors.items" class="mt-1 text-sm text-red-600">{{ form.errors.items }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Attachment</label>
          <div class="mt-1">
            <a v-if="props.request.attachment_url" :href="props.request.attachment_url" target="_blank" class="text-primary hover:underline">View current attachment</a>
            <span v-else class="text-muted-foreground text-sm">No attachment</span>
          </div>
          <input type="file" @change="onFileChange" class="mt-2 block w-full" :disabled="!props.canEdit" />
          <div class="mt-1 text-xs text-muted-foreground">Accepted: pdf, jpg, jpeg, png, doc, docx, xls, xlsx. Max 5 MB.</div>
          <div v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</div>
        </div>

        <div class="rounded-md border p-3 text-sm text-muted-foreground">
          <div><strong>Code:</strong> {{ props.request.purchase_code || '-' }}</div>
          <div><strong>Location:</strong> {{ props.request.location_iso_code || '-' }}</div>
          <div><strong>Date:</strong> {{ props.request.submitted_at ? new Date(props.request.submitted_at).toLocaleDateString('en-GB', { timeZone: 'UTC' }) : '-' }}</div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <button type="submit" :disabled="form.processing || submitting || !props.canEdit" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing || submitting ? 'Saving...' : 'Save Changes' }}
          </button>
          <a href="/purchase-requests" class="text-sm hover:underline">Cancel</a>
          <span class="mx-1 hidden md:inline">|</span>
          <button type="button" v-if="props.canEdit" @click="destroyRequest" class="text-red-600 hover:underline">Delete Request</button>
        </div>
      </form>
    </div>
  </AppLayout>
  </template>


<style>
/***** Printing behavior for Edit.vue *****/
.print-only { display: none; }
.no-print { display: block; }

@media print {
  .no-print { display: none !important; }
  .print-only { display: block !important; }
  html, body { background: #fff !important; color: #000 !important; }
  .print-only .space-y-2 > div { break-inside: avoid; }
}
</style>
