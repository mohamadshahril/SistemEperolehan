<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps<{
  options: {
    type_procurements: Array<{ id: number; procurement_code: number; procurement_description: string }>
    file_references: Array<{ id: number; file_code: string; file_description: string }>
    vots: Array<{ id: number; vot_code: number; vot_description: string }>
  }
  current_user: { name?: string | null; location_iso_code?: string | null }
  today: string
}>()

type Item = {
  item_no: number
  item_name: string
  item_code?: string
  unit?: string
  purpose?: string
  quantity: number
  unit_price: number | string
}

const form = useForm({
  title: '',
  type_procurement_id: '' as any,
  file_reference_id: '' as any,
  vot_id: '' as any,
  budget: '' as any,
  // UI uses `note` (singular); backend expects `notes` -> we map in transform below
  note: '',
  // Align with purchase_items schema (frontend)
  item: [{ item_no: 1, item_name: '', item_code: '', unit: '', purpose: '', quantity: 1, unit_price: '' }] as Item[],
  attachment: null as File | null,
})

const submitting = ref(false)

// Transform payload to match backend validation while keeping UI aligned to purchase_items
// Backend expects: items[].details, items[].price. We map from item_name -> details, unit_price -> price
form.transform((data: any) => {
  const mappedItems = (data.item || []).map((it: any) => ({
    item_no: it.item_no,
    details: it.item_name,
    purpose: it.purpose,
    quantity: it.quantity,
    price: it.unit_price,
    // extra fields kept for potential backend future use
    item_code: it.item_code,
    unit: it.unit,
  }))
  return { ...data, item: mappedItems }
})

// Live total and budget check
const totalCost = computed(() => {
  try {
    return (form.item || []).reduce((sum: number, it: any) => {
      const qty = Number(it.quantity || 0)
      const price = Number(it.unit_price || 0)
      return sum + qty * price
    }, 0)
  } catch {
    return 0
  }
})
const overBudget = computed(() => {
  const budgetNum = Number(form.budget || 0)
  return totalCost.value > budgetNum && budgetNum > 0
})

function addItem() {
  const nextNo = (form.item?.length || 0) + 1
  form.item.push({ item_no: nextNo, item_name: '', item_code: '', unit: '', purpose: '', quantity: 1, unit_price: '' })
}
function removeItem(index: number) {
  if (form.item.length <= 1) return
  form.item.splice(index, 1)
  // Re-number
  form.item.forEach((it, i) => (it.item_no = i + 1))
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
  submitting.value = true
  form.post('/purchase-requests', {
    forceFormData: true,
    onFinish: () => (submitting.value = false),
  })
}
</script>

<template>
  <Head title="Create Purchase Request" />
  <AppLayout :breadcrumbs="[{ title: 'Purchase Requests', href: '/purchase-requests' }, { title: 'Create', href: '/purchase-requests/create' }]">
    <div class="mx-auto max-w-4xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Create Purchase Request</h1>
        <a href="/purchase-requests" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <form @submit.prevent="submit" class="space-y-6">

          <div class="rounded-md border p-3 text-sm text-muted-foreground">
              <div><strong>User:</strong> {{ props.current_user?.name || '-' }}</div>
              <div><strong>Location:</strong> {{ props.current_user?.location_iso_code || '-' }}</div>
              <div><strong>Date:</strong> {{ props.today }}</div>
          </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium">Title</label>
            <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border p-2" />
            <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">Budget (RM)</label>
            <input v-model="form.budget" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border p-2" />
            <div v-if="form.errors.budget" class="mt-1 text-sm text-red-600">{{ form.errors.budget }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="block text-sm font-medium">Type Procurement</label>
            <select v-model="form.type_procurement_id" class="mt-1 block w-full rounded-md border p-2">
              <option value="">-- Select --</option>
              <option v-for="opt in props.options.type_procurements" :key="opt.id" :value="opt.id">
                {{ opt.procurement_code }} - {{ opt.procurement_description }}
              </option>
            </select>
            <div v-if="form.errors.type_procurement_id" class="mt-1 text-sm text-red-600">{{ form.errors.type_procurement_id }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">File Reference</label>
            <select v-model="form.file_reference_id" class="mt-1 block w-full rounded-md border p-2">
              <option value="">-- Select --</option>
              <option v-for="opt in props.options.file_references" :key="opt.id" :value="opt.id">
                {{ opt.file_code }} - {{ opt.file_description }}
              </option>
            </select>
            <div v-if="form.errors.file_reference_id" class="mt-1 text-sm text-red-600">{{ form.errors.file_reference_id }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">VOT</label>
            <select v-model="form.vot_id" class="mt-1 block w-full rounded-md border p-2">
              <option value="">-- Select --</option>
              <option v-for="opt in props.options.vots" :key="opt.id" :value="opt.id">
                {{ opt.vot_code }} - {{ opt.vot_description }}
              </option>
            </select>
            <div v-if="form.errors.vot_id" class="mt-1 text-sm text-red-600">{{ form.errors.vot_id }}</div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium">Notes</label>
          <textarea v-model="form.note" rows="3" class="mt-1 block w-full rounded-md border p-2"></textarea>
          <div v-if="form.errors.note" class="mt-1 text-sm text-red-600">{{ form.errors.note }}</div>
        </div>

        <div>
          <div class="mb-2 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Items</h2>
            <button type="button" @click="addItem" class="rounded-md border px-3 py-1 text-sm">Add Item</button>
          </div>
          <div class="overflow-x-auto rounded-md border">
            <table class="min-w-full divide-y">
              <thead class="bg-muted/30">
                <tr>
                  <th class="px-2 py-2 text-left text-sm">No.</th>
                  <th class="px-2 py-2 text-left text-sm">Item Name</th>
                  <th class="px-2 py-2 text-left text-sm">Item Code</th>
                  <th class="px-2 py-2 text-left text-sm">Unit</th>
                  <th class="px-2 py-2 text-left text-sm">Purpose</th>
                  <th class="px-2 py-2 text-left text-sm">Qty</th>
                  <th class="px-2 py-2 text-left text-sm">Unit Price (RM)</th>
                  <th class="px-2 py-2 text-left text-sm">Total (RM)</th>
                  <th class="px-2 py-2 text-left text-sm"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(it, idx) in form.item" :key="idx" class="odd:bg-white even:bg-muted/10">
                  <td class="px-2 py-2 w-12">{{ idx + 1 }}</td>
                  <td class="px-2 py-2">
                    <input v-model="it.item_name" type="text" class="block w-full rounded-md border p-2" />
                    <div v-if="form.errors[`item.${idx}.details`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`item.${idx}.details`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-36">
                    <input v-model="it.item_code" type="text" class="block w-full rounded-md border p-2" />
                    <div v-if="form.errors[`item.${idx}.item_code`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`item.${idx}.item_code`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-28">
                    <input v-model="it.unit" type="text" class="block w-full rounded-md border p-2" />
                    <div v-if="form.errors[`item.${idx}.unit`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`item.${idx}.unit`] }}</div>
                  </td>
                  <td class="px-2 py-2">
                    <input v-model="it.purpose" type="text" class="block w-full rounded-md border p-2" />
                    <div v-if="form.errors[`item.${idx}.purpose`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`item.${idx}.purpose`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-24">
                    <input v-model.number="it.quantity" type="number" min="1" class="block w-full rounded-md border p-2" />
                    <div v-if="form.errors[`item.${idx}.quantity`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`item.${idx}.quantity`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-40">
                    <input v-model="it.unit_price" type="number" min="0" step="0.01" class="block w-full rounded-md border p-2" />
                    <div v-if="form.errors[`item.${idx}.price`]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[`item.${idx}.price`] }}</div>
                  </td>
                  <td class="px-2 py-2 w-40 text-right">
                    {{ (Number(it.quantity||0) * Number(it.unit_price||0)).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                  </td>
                  <td class="px-2 py-2 w-20 text-right">
                    <button type="button" @click="removeItem(idx)" class="text-sm text-red-600" :disabled="form.item.length <= 1">Remove</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="form.errors.item" class="mt-1 text-sm text-red-600">{{ form.errors.item }}</div>
          <div class="mt-3 flex items-center justify-between text-sm">
            <div>
              <span class="text-muted-foreground">Total:</span>
              <strong>RM{{ Number(totalCost).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</strong>
            </div>
            <div v-if="overBudget" class="text-red-600">
              Total exceeds budget.
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium">Attachment (optional)</label>
          <input type="file" @change="onFileChange" class="mt-1 block w-full" />
          <div class="mt-1 text-xs text-muted-foreground">Accepted: pdf, jpg, jpeg, png, doc, docx, xls, xlsx. Max 5 MB.</div>
          <div v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</div>
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" :disabled="form.processing || submitting || overBudget" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing || submitting ? 'Submitting...' : 'Submit Request' }}
          </button>
          <a href="/purchase-requests" class="text-sm hover:underline">Cancel</a>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
