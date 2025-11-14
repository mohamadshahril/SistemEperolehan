<template>
  <div>
    <h1 class="text-2xl mb-4 font-bold">Create Purchase Order</h1>

    <form @submit.prevent="form.post(route('purchase-orders.store'))" class="space-y-4">
      <div>
        <label>Approved Request</label>
        <select v-model="form.approved_request_id" class="border p-2 w-full">
          <option value="">-- Select --</option>
          <option v-for="r in approvedRequests" :key="r.id" :value="r.id">{{ r.request_number }}</option>
        </select>
      </div>

      <div>
        <label>Vendor ID</label>
        <input v-model="form.vendor_id" class="border p-2 w-full"/>
      </div>

      <div>
        <h3 class="font-semibold">Items</h3>
        <table class="w-full border">
          <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th></th></tr></thead>
          <tbody>
            <tr v-for="(item,i) in form.items" :key="i">
              <td><input v-model="item.item_name" class="border p-1 w-full"/></td>
              <td><input type="number" v-model.number="item.quantity" class="border p-1 w-20"/></td>
              <td><input type="number" v-model.number="item.unit_price" class="border p-1 w-24"/></td>
              <td><button type="button" @click="form.items.splice(i,1)">x</button></td>
            </tr>
          </tbody>
        </table>
        <button type="button" @click="addItem" class="bg-gray-200 px-3 py-1 mt-2">+ Add Item</button>
      </div>

      <p>Total: RM {{ totalPrice }}</p>

      <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Save PO</button>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
defineProps({ approvedRequests: Array })

const form = useForm({
  approved_request_id: '',
  vendor_id: '',
  total_price: 0,
  items: [{ item_name:'', quantity:1, unit_price:0 }]
})

const totalPrice = computed(()=> form.items.reduce((t,i)=>t + (i.quantity*i.unit_price),0))

const addItem = ()=> form.items.push({ item_name:'', quantity:1, unit_price:0 })

watch(totalPrice, (v)=> form.total_price = v)
</script>
