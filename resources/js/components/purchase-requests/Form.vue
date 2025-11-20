<script setup lang="ts">
import { computed } from 'vue'

type Option = { id: number; [k: string]: unknown }

const props = defineProps<{
  modelValue: {
    id?: number
    title: string
    type_procurement_id: string | number | null
    file_reference_id: string | number | null
    vot_id: string | number | null
    location_iso_code?: string | null
    budget: number | string
    note?: string | null
    // legacy purpose supported by backend
    purpose?: string | null
    item: Array<{
      item_no?: number | null
      details: string
      purpose?: string | null
      quantity: number | string
      price: number | string
      item_code?: string | null
      unit?: string | null
    }>
    attachment?: File | null
  }
  options: {
    type_procurements: Option[]
    file_references: Option[]
    vots: Option[]
  }
  readOnly?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: typeof props.modelValue): void
}>()

const total = computed(() => {
  return (props.modelValue.item || []).reduce((sum, it) => {
    const qty = Number(it.quantity || 0)
    const price = Number(it.price || 0)
    return sum + qty * price
  }, 0)
})

function addItem() {
  if (props.readOnly) return
  emit('update:modelValue', {
    ...props.modelValue,
    item: [...props.modelValue.item, { details: '', quantity: 1, price: 0 }],
  })
}

function removeItem(idx: number) {
  if (props.readOnly) return
  const items = props.modelValue.item.slice()
  items.splice(idx, 1)
  emit('update:modelValue', { ...props.modelValue, item: items })
}

function updateField<K extends keyof typeof props.modelValue>(key: K, value: (typeof props.modelValue)[K]) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}

function updateItem(idx: number, key: keyof (typeof props.modelValue.item)[number], value: unknown) {
  const items = props.modelValue.item.slice()
  items[idx] = { ...items[idx], [key]: value }
  emit('update:modelValue', { ...props.modelValue, item: items })
}
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div>
        <label class="block text-sm font-medium">Title <span class="text-red-600">*</span></label>
        <input
          :disabled="readOnly"
          v-model="(props.modelValue as any).title"
          @input="(e:any)=>updateField('title', e.target.value)"
          type="text"
          class="mt-1 block w-full rounded-md border p-2"
        />
      </div>
      <div>
        <label class="block text-sm font-medium">Budget (RM) <span class="text-red-600">*</span></label>
        <input
          :disabled="readOnly"
          v-model.number="(props.modelValue as any).budget"
          @input="(e:any)=>updateField('budget', e.target.value)"
          type="number" min="0" step="0.01"
          class="mt-1 block w-full rounded-md border p-2"
        />
        <p class="mt-1 text-xs text-gray-600">Total items: RM {{ total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium">Type Procurement <span class="text-red-600">*</span></label>
        <select
          :disabled="readOnly"
          :value="(props.modelValue as any).type_procurement_id ?? ''"
          @change="(e:any)=>updateField('type_procurement_id', Number(e.target.value) || '')"
          class="mt-1 block w-full rounded-md border p-2"
        >
          <option value="">Select...</option>
          <option v-for="o in props.options.type_procurements" :key="o.id" :value="o.id">{{ (o as any).procurement_code }} - {{ (o as any).procurement_description }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">File Reference <span class="text-red-600">*</span></label>
        <select
          :disabled="readOnly"
          :value="(props.modelValue as any).file_reference_id ?? ''"
          @change="(e:any)=>updateField('file_reference_id', Number(e.target.value) || '')"
          class="mt-1 block w-full rounded-md border p-2"
        >
          <option value="">Select...</option>
          <option v-for="o in props.options.file_references" :key="o.id" :value="o.id">{{ (o as any).file_code }} - {{ (o as any).file_description }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Vot <span class="text-red-600">*</span></label>
        <select
          :disabled="readOnly"
          :value="(props.modelValue as any).vot_id ?? ''"
          @change="(e:any)=>updateField('vot_id', Number(e.target.value) || '')"
          class="mt-1 block w-full rounded-md border p-2"
        >
          <option value="">Select...</option>
          <option v-for="o in props.options.vots" :key="o.id" :value="o.id">{{ (o as any).vot_code }} - {{ (o as any).vot_description }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Notes</label>
        <textarea
          :disabled="readOnly"
          :value="(props.modelValue as any).note ?? (props.modelValue as any).purpose ?? ''"
          @input="(e:any)=>updateField('note', e.target.value)"
          rows="3"
          class="mt-1 block w-full rounded-md border p-2"
        ></textarea>
      </div>
    </div>

    <div>
      <div class="mb-2 flex items-center justify-between">
        <h3 class="text-lg font-medium">Items</h3>
        <button v-if="!readOnly" type="button" class="rounded-md border px-3 py-1" @click="addItem">Add item</button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y">
          <thead>
            <tr>
              <th class="px-2 py-1 text-left">#</th>
              <th class="px-2 py-1 text-left">Details</th>
              <th class="px-2 py-1 text-left">Qty</th>
              <th class="px-2 py-1 text-left">Unit Price (RM)</th>
              <th class="px-2 py-1 text-left">Purpose</th>
              <th class="px-2 py-1 text-right">Amount</th>
              <th v-if="!readOnly" class="px-2 py-1 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(it, idx) in props.modelValue.item" :key="idx" class="border-b">
              <td class="px-2 py-1">{{ idx + 1 }}</td>
              <td class="px-2 py-1">
                <input :disabled="readOnly" class="w-full rounded-md border p-1" :value="it.details" @input="(e:any)=>updateItem(idx,'details', e.target.value)" />
              </td>
              <td class="px-2 py-1">
                <input :disabled="readOnly" type="number" min="1" step="1" class="w-24 rounded-md border p-1" :value="it.quantity" @input="(e:any)=>updateItem(idx,'quantity', e.target.value)" />
              </td>
              <td class="px-2 py-1">
                <input :disabled="readOnly" type="number" min="0" step="0.01" class="w-32 rounded-md border p-1" :value="it.price" @input="(e:any)=>updateItem(idx,'price', e.target.value)" />
              </td>
              <td class="px-2 py-1">
                <input :disabled="readOnly" class="w-full rounded-md border p-1" :value="it.purpose || ''" @input="(e:any)=>updateItem(idx,'purpose', e.target.value)" />
              </td>
              <td class="px-2 py-1 text-right">
                {{ (Number(it.quantity||0) * Number(it.price||0)).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
              <td v-if="!readOnly" class="px-2 py-1 text-right">
                <button type="button" class="text-red-600 hover:underline" @click="removeItem(idx)">Remove</button>
              </td>
            </tr>
            <tr v-if="props.modelValue.item.length === 0">
              <td colspan="7" class="px-3 py-4 text-center text-sm text-muted-foreground">No items. Click "Add item" to start.</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" class="px-2 py-2 text-right font-medium">Total</td>
              <td class="px-2 py-2 text-right font-medium">RM {{ total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}</td>
              <td v-if="!readOnly" />
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>
