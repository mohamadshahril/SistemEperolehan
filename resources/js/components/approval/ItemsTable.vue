<script setup lang="ts">
import { Package } from 'lucide-vue-next'

interface Item {
  item_no: number
  details: string
  purpose?: string | null
  quantity: number
  price: number | string
}

interface Props {
  items: Item[]
  title?: string
  showTotal?: boolean
}

withDefaults(defineProps<Props>(), {
  title: 'Purchase Items',
  showTotal: true,
  items: () => [],
})

const calculateTotal = (items?: Item[]): number => {
  if (!items || !Array.isArray(items)) {
    return 0
  }
  return items.reduce((sum, item) => {
    return sum + Number(item.price) * item.quantity
  }, 0)
}

const formatCurrency = (amount: number | string): string => {
  return Number(amount).toLocaleString('en-MY', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2">
      <Package :size="18" class="text-gray-600" />
      <h3 class="font-semibold">{{ title }}</h3>
    </div>

    <div class="overflow-x-auto rounded-lg border">
      <table class="min-w-full divide-y">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold">Item No</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Details</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Purpose</th>
            <th class="px-4 py-3 text-right text-sm font-semibold">Qty</th>
            <th class="px-4 py-3 text-right text-sm font-semibold">Price (RM)</th>
            <th class="px-4 py-3 text-right text-sm font-semibold">Subtotal (RM)</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="(item, idx) in items" :key="idx" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-mono">{{ item.item_no }}</td>
            <td class="px-4 py-3 text-sm">{{ item.details }}</td>
            <td class="px-4 py-3 text-sm text-muted-foreground">
              {{ item.purpose || '-' }}
            </td>
            <td class="px-4 py-3 text-right text-sm font-semibold">{{ item.quantity }}</td>
            <td class="px-4 py-3 text-right text-sm">{{ formatCurrency(item.price) }}</td>
            <td class="px-4 py-3 text-right text-sm font-semibold">
              {{ formatCurrency(Number(item.price) * item.quantity) }}
            </td>
          </tr>
        </tbody>
        <tfoot v-if="showTotal" class="bg-blue-50 font-semibold">
          <tr>
            <td colspan="5" class="px-4 py-3 text-right text-sm">Total Amount:</td>
            <td class="px-4 py-3 text-right text-sm text-blue-900">
              RM {{ formatCurrency(calculateTotal(items)) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
      <div class="rounded-lg bg-blue-50 p-3">
        <p class="text-xs font-medium text-muted-foreground">Total Items</p>
        <p class="text-2xl font-bold text-blue-900">{{ items?.length || 0 }}</p>
      </div>
      <div class="rounded-lg bg-green-50 p-3">
        <p class="text-xs font-medium text-muted-foreground">Total Quantity</p>
        <p class="text-2xl font-bold text-green-900">
          {{ items?.reduce((sum, item) => sum + item.quantity, 0) || 0 }}
        </p>
      </div>
      <div class="rounded-lg bg-purple-50 p-3">
        <p class="text-xs font-medium text-muted-foreground">Total Amount</p>
        <p class="text-2xl font-bold text-purple-900">
          RM {{ formatCurrency(calculateTotal(items)) }}
        </p>
      </div>
    </div>
  </div>
</template>

