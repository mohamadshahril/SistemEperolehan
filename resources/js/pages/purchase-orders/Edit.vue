<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  purchaseOrder: {
    id: number
    order_number: string
    vendor_id: number
    vendor: {
      id: number
      name: string
    }
    details: string | null
    status: string
    attachment_path: string | null
  }
  vendors: Array<{
    id: number
    name: string
  }>
}>()

const form = useForm({
  vendor_id: props.purchaseOrder.vendor_id,
  details: props.purchaseOrder.details || '',
  status: props.purchaseOrder.status,
  attachment: null as File | null,
})

function submit() {
  form.transform((data: { vendor_id: number; details: string; status: string; attachment: File | null }) => {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('vendor_id', data.vendor_id.toString());
    formData.append('details', data.details);
    formData.append('status', data.status);
    if (data.attachment) {
      formData.append('attachment', data.attachment);
    }
    return formData;
  });
  form.post(`/purchase-orders/${props.purchaseOrder.id}`, {
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Edit Purchase Order" />
  <AppLayout :breadcrumbs="[
    { title: 'Purchase Orders', href: '/purchase-orders' },
    { title: 'Edit', href: `/purchase-orders/${purchaseOrder.id}/edit` }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Edit Purchase Order</h1>
        <p class="text-sm text-muted-foreground">Order Number: {{ purchaseOrder.order_number }}</p>
      </div>

      <div class="max-w-2xl rounded-md border bg-white p-6">
        <form @submit.prevent="submit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Vendor <span class="text-red-600">*</span></label>
              <select
                v-model="form.vendor_id"
                required
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.vendor_id }"
              >
                <option value="">Select a vendor</option>
                <option v-for="vendor in props.vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
              </select>
              <p v-if="form.errors.vendor_id" class="mt-1 text-sm text-red-600">{{ form.errors.vendor_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Details</label>
              <textarea
                v-model="form.details"
                rows="4"
                placeholder="Enter purchase order details..."
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.details }"
              ></textarea>
              <p v-if="form.errors.details" class="mt-1 text-sm text-red-600">{{ form.errors.details }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Status <span class="text-red-600">*</span></label>
              <select
                v-model="form.status"
                required
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.status }"
              >
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Completed">Completed</option>
              </select>
              <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Attachment</label>
              <div v-if="purchaseOrder.attachment_path" class="mb-2 rounded-md bg-gray-50 p-2 text-sm">
                <span class="text-gray-600">Current file: </span>
                <a 
                  :href="`/storage/${purchaseOrder.attachment_path}`" 
                  target="_blank"
                  class="text-blue-600 hover:underline"
                >
                  View attachment
                </a>
              </div>
              <input
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                @change="(e) => form.attachment = (e.target as HTMLInputElement).files?.[0] || null"
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.attachment }"
              />
              <p class="mt-1 text-xs text-gray-500">
                {{ purchaseOrder.attachment_path ? 'Upload a new file to replace the current one.' : 'Accepted: pdf, jpg, jpeg, png, doc, docx, xls, xlsx. Max 5 MB.' }}
              </p>
              <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</p>
            </div>
          </div>

          <div class="mt-6 flex items-center gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50"
            >
              {{ form.processing ? 'Updating...' : 'Update Purchase Order' }}
            </button>
            <Link href="/purchase-orders" class="rounded-md border px-4 py-2">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
