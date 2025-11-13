<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  vendors: Array<{
    id: number
    name: string
  }>
}>()

const form = useForm({
  vendor_id: '',
  details: '',
  status: 'Pending',
  attachment: null as File | null,
})

function submit() {
  form.post('/purchase-orders', {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Create Purchase Order" />
  <AppLayout :breadcrumbs="[
    { title: 'Purchase Orders', href: '/purchase-orders' },
    { title: 'Create', href: '/purchase-orders/create' }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Create New Purchase Order</h1>
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
              <label class="block text-sm font-medium">Attachment (optional)</label>
              <input
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                @change="(e) => form.attachment = (e.target as HTMLInputElement).files?.[0] || null"
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.attachment }"
              />
              <p class="mt-1 text-xs text-gray-500">Accepted: pdf, jpg, jpeg, png, doc, docx, xls, xlsx. Max 5 MB.</p>
              <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</p>
            </div>

            <div class="rounded-md bg-blue-50 p-3 text-sm text-blue-800">
              <strong>Note:</strong> Order number will be automatically generated upon creation.
            </div>
          </div>

          <div class="mt-6 flex items-center gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50"
            >
              {{ form.processing ? 'Creating...' : 'Create Purchase Order' }}
            </button>
            <Link href="/purchase-orders" class="rounded-md border px-4 py-2">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
