<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  deliveryOrder: {
    id: number
    do_number: string
    delivery_date: string
    file_path: string | null
    is_received: boolean
    notes?: string | null
    purchase_order: { order_number: string; vendor: { name: string } }
  }
}>()

function printPage() {
  window.print()
}
</script>

<template>
  <Head title="Delivery Order Summary" />
  <AppLayout :breadcrumbs="[{ title: 'Delivery Orders', href: '/delivery-orders' }, { title: 'Print', href: `/delivery-orders/${props.deliveryOrder.id}/print` }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Delivery Order Summary</h1>
        <button @click="printPage" class="rounded-md bg-primary px-3 py-2 text-white">Print</button>
      </div>

      <div class="max-w-3xl rounded-md border bg-white p-6">
        <div class="space-y-3">
          <div>
            <h2 class="text-lg font-medium">DO: {{ props.deliveryOrder.do_number }}</h2>
            <div class="text-sm text-muted-foreground">#{{ props.deliveryOrder.id }}</div>
          </div>

          <div>
            <strong>Purchase Order:</strong> {{ props.deliveryOrder.purchase_order.order_number }}
          </div>

          <div>
            <strong>Vendor:</strong> {{ props.deliveryOrder.purchase_order.vendor.name }}
          </div>

          <div>
            <strong>Delivery Date:</strong> {{ props.deliveryOrder.delivery_date }}
          </div>

          <div>
            <strong>Status:</strong> <span class="ml-2">{{ props.deliveryOrder.is_received ? 'Received' : 'Pending' }}</span>
          </div>

          <div>
            <strong>Notes:</strong>
            <div class="mt-1">{{ props.deliveryOrder.notes || '-' }}</div>
          </div>

          <div v-if="props.deliveryOrder.file_path">
            <strong>Attachment:</strong>
            <div class="mt-1"><a :href="`/storage/${props.deliveryOrder.file_path}`" target="_blank" class="text-blue-600">View Document</a></div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
