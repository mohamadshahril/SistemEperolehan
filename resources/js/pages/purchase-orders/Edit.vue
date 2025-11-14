<template>
  <div>
    <h1 class="text-2xl mb-4 font-bold">Edit Purchase Order</h1>

    <form @submit.prevent="form.put(route('purchase-orders.update', po.id))" class="space-y-4">
      <!-- Approved Request -->
      <div>
        <label class="block mb-1 font-semibold">Approved Request</label>
        <input
          v-model="form.approved_request_id"
          disabled
          class="border p-2 w-full bg-gray-100"
        />
      </div>

      <!-- Vendor -->
      <div>
        <label class="block mb-1 font-semibold">Vendor ID</label>
        <input v-model="form.vendor_id" class="border p-2 w-full" />
        <div v-if="form.errors.vendor_id" class="text-red-600 text-sm">
          {{ form.errors.vendor_id }}
        </div>
      </div>

      <!-- Items -->
      <div>
        <h3 class="font-semibold mb-2">Items</h3>
        <table class="w-full border">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 text-left">Item</th>
              <th class="p-2 text-left w-24">Qty</th>
              <th class="p-2 text-left w-32">Unit Price</th>
              <th class="p-2 text-left w-16"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in form.items" :key="i">
              <td><input v-model="item.item_name" class="border p-1 w-full" /></td>
              <td><input type="number" v-model.number="item.quantity" class="border p-1 w-full" /></td>
              <td><input type="number" v-model.number="item.unit_price" class="border p-1 w-full" /></td>
              <td>
                <button
                  type="button"
                  @click="removeItem(i)"
                  class="text-red-600 hover:underline"
                >x</button>
              </td>
            </tr>
          </tbody>
        </table>

        <button
          type="button"
          @click="addItem"
          class="bg-gray-200 px-3 py-1 mt-2 rounded"
        >
          + Add Item
        </button>
      </div>

      <!-- Total -->
      <div class="font-semibold">
        Total: RM {{ totalPrice.toFixed(2) }}
      </div>

      <!-- Status -->
      <div>
        <label class="block mb-1 font-semibold">Status</label>
        <select v-model="form.status" class="border p-2 w-full">
          <option value="Created">Created</option>
          <option value="Approved">Approved</option>
          <option value="Completed">Completed</option>
        </select>
      </div>

      <!-- Buttons -->
      <div class="flex gap-2">
        <button
          type="submit"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          :disabled="form.processing"
        >
          Update
        </button>
        <Link
          :href="route('purchase-orders.index')"
          class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
        >
          Cancel
        </Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

defineProps({ po: Object })

const form = useForm({
  approved_request_id: props.po.approved_request_id,
  vendor_id: props.po.vendor_id,
  items: props.po.items.map(i => ({
    item_name: i.item_name,
    quantity: i.quantity,
    unit_price: i.unit_price
  })),
  status: props.po.status,
  total_price: props.po.total_price
})

const addItem = () => form.items.push({ item_name: '', quantity: 1, unit_price: 0 })
const removeItem = (i) => form.items.splice(i, 1)

const totalPrice = computed(() =>
  form.items.reduce((sum, item) => sum + item.quantity * item.unit_price, 0)
)

watch(totalPrice, (v) => (form.total_price = v))
</script>
