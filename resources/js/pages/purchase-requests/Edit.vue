<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps<{
  request: {
    id: number
    item_name: string
    quantity: number
    price: string | number
    purpose?: string | null
    status: string
    submitted_at: string | null
    attachment_path?: string | null
    attachment_url?: string | null
  }
  canEdit: boolean
}>()

const form = useForm({
  item_name: props.request.item_name ?? '',
  quantity: props.request.quantity ?? 1,
  price: props.request.price ?? '',
  purpose: props.request.purpose ?? '',
  attachment: null as File | null,
})

// Coerce values to prevent FormData from dropping keys and to ensure numeric types
form.transform((data: any) => {
  const payload: any = {
    ...data,
    item_name: (data.item_name ?? '').toString().trim(),
    quantity: Number(data.quantity ?? 0),
    price: Number(data.price ?? 0),
    purpose: (data.purpose ?? '').toString(),
    _method: 'PUT',
  }
  // Only include attachment if a real File is provided; avoid sending null/empty which breaks Laravel 'file' validation
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
    // Use a single space so some browsers don't fall back to URL
    document.title = ' '
    const restore = () => {
      if (originalTitle !== null) document.title = originalTitle
      originalTitle = null
      window.removeEventListener('afterprint', restore)
    }
    window.addEventListener('afterprint', restore)
    // Fallback in case afterprint doesn't fire (some browsers/user settings)
    setTimeout(() => restore(), 2000)
  }
  run()
}

function printRequest() {
  if (typeof window !== 'undefined' && window.print) {
    withTemporaryTitleForPrint(() => window.print())
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    const params = new URLSearchParams(window.location.search)
    if (params.get('print') === '1') {
      // Delay slightly to allow page to render fully before printing
      withTemporaryTitleForPrint(() => {
        setTimeout(() => window.print(), 200)
      })
    }
  }
})

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
    <div class="mx-auto max-w-3xl p-4">
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
          <div><span class="font-medium">Item Name:</span> {{ props.request.item_name }}</div>
          <div><span class="font-medium">Quantity:</span> {{ props.request.quantity }}</div>
          <div><span class="font-medium">Price:</span> {{ 'RM' + Number(props.request.price).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
          <div v-if="props.request.purpose"><span class="font-medium">Purpose:</span> {{ props.request.purpose }}</div>
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
        <div>
          <label class="block text-sm font-medium">Item Name</label>
          <input name="item_name" v-model.trim="form.item_name" type="text" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit" />
          <div v-if="form.errors.item_name" class="mt-1 text-sm text-red-600">{{ form.errors.item_name }}</div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium">Quantity</label>
            <input name="quantity" v-model.number="form.quantity" type="number" min="1" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit" />
            <div v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">{{ form.errors.quantity }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">Price</label>
            <input name="price" v-model.number="form.price" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit" />
            <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium">Purpose</label>
          <textarea v-model="form.purpose" rows="4" class="mt-1 block w-full rounded-md border p-2" :disabled="!props.canEdit"></textarea>
          <div v-if="form.errors.purpose" class="mt-1 text-sm text-red-600">{{ form.errors.purpose }}</div>
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

        <div class="flex flex-wrap items-center gap-3">
          <button type="submit" :disabled="form.processing || submitting || !props.canEdit" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing || submitting ? 'Saving...' : 'Save Changes' }}
          </button>
          <a href="/purchase-requests" class="text-sm hover:underline">Cancel</a>
          <span class="mx-2 hidden md:inline">|</span>
          <button type="button" @click="printRequest" class="text-primary hover:underline">Print</button>
          <span class="mx-2 hidden md:inline">|</span>
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
  /* Hide interactive elements and form */
  .no-print { display: none !important; }
  /* Show summary only */
  .print-only { display: block !important; }

  /* Make page background white and text dark for clarity */
  html, body { background: #fff !important; color: #000 !important; }

  /* Avoid page breaks inside summary blocks */
  .print-only .space-y-2 > div { break-inside: avoid; }
}
</style>
