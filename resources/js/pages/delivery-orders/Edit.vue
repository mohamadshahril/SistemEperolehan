<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  deliveryOrder: {
    id: number
    do_number: string
    delivery_date: string
    file_path: string | null
    is_received: boolean
    notes?: string | null
    purchase_order: {
      id: number
      order_number: string
      vendor: { name: string }
    }
  }
  purchaseOrders: Array<{ id: number; order_number: string }>
}>()

// Format delivery_date to YYYY-MM-DD for date input
const formatDateForInput = (dateString: string) => {
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const form = useForm({
  purchase_order_id: String(props.deliveryOrder.purchase_order.id),
  do_number: props.deliveryOrder.do_number,
  delivery_date: formatDateForInput(props.deliveryOrder.delivery_date),
  delivery_file: null as File | null,
  notes: props.deliveryOrder.notes || '',
})

function submit() {
  form.post(`/delivery-orders/${props.deliveryOrder.id}`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {},
  })
}
</script>

<template>
  <Head title="Edit Delivery Order" />
  <AppLayout :breadcrumbs="[
    { title: 'Delivery Orders', href: '/delivery-orders' },
    { title: 'Edit', href: `/delivery-orders/${props.deliveryOrder.id}/edit` }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Edit Delivery Order</h1>
      </div>

      <div class="max-w-2xl rounded-md border bg-white p-6">
        <form @submit.prevent="submit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Purchase Order <span class="text-red-600">*</span></label>
              <select
                v-model="form.purchase_order_id"
                required
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.purchase_order_id }"
              >
                <option value="">Select a purchase order</option>
                <option v-for="po in props.purchaseOrders" :key="po.id" :value="String(po.id)">{{ po.order_number }}</option>
              </select>
              <p v-if="form.errors.purchase_order_id" class="mt-1 text-sm text-red-600">{{ form.errors.purchase_order_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Delivery Order Number <span class="text-red-600">*</span></label>
              <input
                v-model="form.do_number"
                type="text"
                required
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.do_number }"
              />
              <p v-if="form.errors.do_number" class="mt-1 text-sm text-red-600">{{ form.errors.do_number }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Delivery Date <span class="text-red-600">*</span></label>
              <input
                v-model="form.delivery_date"
                type="date"
                required
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.delivery_date }"
              />
              <p v-if="form.errors.delivery_date" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_date }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Replace Delivery Document</label>
              <input
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                @change="(e) => form.delivery_file = (e.target as HTMLInputElement).files?.[0] || null"
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.delivery_file }"
              />
              <p v-if="form.errors.delivery_file" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_file }}</p>
              <p v-if="props.deliveryOrder.file_path" class="mt-1 text-sm text-gray-600">Current file: <a :href="`/storage/${props.deliveryOrder.file_path}`" target="_blank" class="text-blue-600">View</a></p>
            </div>

            <div>
              <label class="block text-sm font-medium">Notes</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.notes }"
              ></textarea>
              <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
            </div>
          </div>

          <div class="mt-6 flex items-center gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
            <Link href="/delivery-orders" class="rounded-md border px-4 py-2">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
