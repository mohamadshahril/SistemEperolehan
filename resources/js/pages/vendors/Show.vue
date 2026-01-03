<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps<{
  vendor: {
    id: number
    name: string
    email: string | null
    phone: string | null
    address: string | null
    address_line1: string
    address_line2: string | null
    city: string
    state: string
    postcode: string
    country: string
    created_at: string
    purchase_orders: Array<{
      id: number
      order_number: string
      status: string
      created_at: string
    }>
  }
}>()

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    Draft: 'bg-gray-100 text-gray-800',
    Pending: 'bg-yellow-100 text-yellow-800',
    Approved: 'bg-green-100 text-green-800',
    Rejected: 'bg-red-100 text-red-800',
    Completed: 'bg-blue-100 text-blue-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <Head :title="`Vendor: ${props.vendor.name}`" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Vendors', href: '/vendors' },
      { title: props.vendor.name, href: `/vendors/${props.vendor.id}` },
    ]"
  >
    <div class="p-4 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">{{ props.vendor.name }}</h1>
          <p class="text-sm text-muted-foreground">Vendor Details</p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="`/vendors/${props.vendor.id}/edit`"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
          >
            Edit Vendor
          </Link>
          <Link
            href="/vendors"
            class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
          >
            Back to List
          </Link>
        </div>
      </div>

      <!-- Vendor Information -->
      <div class="rounded-lg border bg-white p-6">
        <h2 class="text-lg font-semibold mb-4">Vendor Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium text-muted-foreground">Name</p>
            <p class="text-base">{{ props.vendor.name }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-muted-foreground">Email</p>
            <p class="text-base">{{ props.vendor.email || '-' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-muted-foreground">Phone</p>
            <p class="text-base">{{ props.vendor.phone || '-' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-muted-foreground">Created At</p>
            <p class="text-base">{{ formatDate(props.vendor.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Address Information -->
      <div class="rounded-lg border bg-white p-6">
        <h2 class="text-lg font-semibold mb-4">Address</h2>
        <div class="space-y-2">
          <p class="text-base">{{ props.vendor.address_line1 }}</p>
          <p v-if="props.vendor.address_line2" class="text-base">{{ props.vendor.address_line2 }}</p>
          <p class="text-base">
            {{ props.vendor.postcode }} {{ props.vendor.city }}, {{ props.vendor.state }}
          </p>
          <p class="text-base">{{ props.vendor.country }}</p>
        </div>
      </div>

      <!-- Purchase Orders -->
      <div class="rounded-lg border bg-white p-6">
        <h2 class="text-lg font-semibold mb-4">
          Purchase Orders ({{ props.vendor.purchase_orders.length }})
        </h2>
        
        <div v-if="props.vendor.purchase_orders.length === 0" class="text-center py-8 text-muted-foreground">
          No purchase orders found for this vendor.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="order in props.vendor.purchase_orders"
            :key="order.id"
            class="flex items-center justify-between border rounded-lg p-4 hover:bg-accent/50 transition-colors"
          >
            <div>
              <Link
                :href="`/purchase-orders/${order.id}`"
                class="text-base font-medium hover:underline"
              >
                {{ order.order_number }}
              </Link>
              <p class="text-sm text-muted-foreground">
                Created: {{ formatDate(order.created_at) }}
              </p>
            </div>
            <span
              class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
              :class="getStatusColor(order.status)"
            >
              {{ order.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
