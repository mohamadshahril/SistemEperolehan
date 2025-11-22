<script setup lang="ts">
import { computed } from 'vue'
import PRForm from '@/components/purchase-requests/Form.vue'

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
    item_units?: Array<{ id: number; unit_code: string; unit_description: string }>
  }
  readOnly?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: typeof props.modelValue): void
}>()

// Provide a v-model proxy so this wrapper can remain dumb and delegate to the shared component
const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})
</script>

<template>
  <PRForm v-model="modelProxy" :options="props.options" :readOnly="props.readOnly" />
</template>
