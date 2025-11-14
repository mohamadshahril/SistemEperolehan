<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Purchase Orders</h1>
    <div class="flex mb-3">
      <input v-model="filters.search" placeholder="Search..." class="border p-2 mr-2"/>
      <Link :href="route('purchase-orders.create')" class="bg-blue-500 text-white px-4 py-2 rounded">+ New PO</Link>
    </div>

    <table class="w-full border">
      <thead><tr><th>PO #</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <tr v-for="po in pos.data" :key="po.id">
          <td>{{ po.po_number }}</td>
          <td>RM {{ po.total_price }}</td>
          <td>{{ po.status }}</td>
          <td>
            <Link :href="route('purchase-orders.edit', po.id)" class="text-blue-600">Edit</Link> |
            <a :href="route('purchase-orders.download', po.id)" target="_blank" class="text-green-600">PDF</a>
          </td>
        </tr>
      </tbody>
    </table>

    <Pagination :links="pos.links" class="mt-4"/>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
defineProps({ pos: Object, filters: Object })
</script>
