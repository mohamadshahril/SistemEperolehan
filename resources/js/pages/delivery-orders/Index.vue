<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

interface DeliveryOrderItem {
    id: number
    do_number: string
    delivery_date: string
    file_path: string | null
    is_received: boolean
    purchase_order: {
        order_number: string
        vendor: {
            name: string
        }
    }
}

const props = defineProps<{
    deliveryOrders: {
        data: DeliveryOrderItem[]
        links: Array<{ url: string | null; label: string; active: boolean }>
    }
    filters: {
        from_date?: string | null
        to_date?: string | null
        vendor_id?: number | null
        status?: string | null
        sort_by?: string | null
        sort_dir?: 'asc' | 'desc' | null
    }
    vendors: Array<{ id: number; name: string }>
}>()

import { reactive } from 'vue'

const state = reactive({
    from_date: props.filters.from_date ?? '',
    to_date: props.filters.to_date ?? '',
    vendor_id: props.filters.vendor_id ?? '',
    status: props.filters.status ?? '',
    sort_by: props.filters.sort_by ?? 'delivery_date',
    sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
})

// Function to handle Confirm Delivery (PATCH request)
const confirmDelivery = (id: number) => {
    if (confirm('Are you sure you want to confirm receipt for this Delivery Order? This action cannot be undone.')) {
        router.patch(`/delivery-orders/${id}/confirm`, {}, {
            preserveScroll: true,
        })
    }
}

// Function to view the uploaded file
const viewFile = (filePath: string | null) => {
    if (filePath) {
        window.open('/storage/' + filePath, '_blank')
    }
}

// Function to delete a delivery order
const deleteDeliveryOrder = (id: number, doNumber: string) => {
    if (confirm(`Are you sure you want to delete Delivery Order ${doNumber}? This action cannot be undone.`)) {
        router.delete(`/delivery-orders/${id}`, {
            preserveScroll: true,
        })
    }
}

// Function to format date in Malaysia format (DD/MM/YYYY)
const formatDateMalaysia = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('ms-MY', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

function applyFilters(extra: Record<string, unknown> = {}) {
    router.get('/delivery-orders', {
        from_date: state.from_date || undefined,
        to_date: state.to_date || undefined,
        vendor_id: state.vendor_id || undefined,
        status: state.status || undefined,
        sort_by: state.sort_by || undefined,
        sort_dir: state.sort_dir || undefined,
        ...extra,
    }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
    state.from_date = ''
    state.to_date = ''
    state.vendor_id = ''
    state.status = ''
    applyFilters({ page: 1 })
}

function sortBy(column: string) {
    if (state.sort_by === column) {
        state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc'
    } else {
        state.sort_by = column
        state.sort_dir = 'asc'
    }
    applyFilters()
}

function goTo(url: string | null) {
    if (!url) return
    router.get(url, {}, { preserveState: true, preserveScroll: true })
}
</script>

<template>
    <Head title="Delivery Orders" />
    <AppLayout :breadcrumbs="[{ title: 'Delivery Orders', href: '/delivery-orders' }]">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Delivery Orders</h1>
                <Link href="/delivery-orders/create" class="rounded-md bg-primary px-3 py-2 text-white">
                    + Upload New DO
                </Link>
            </div>

            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 gap-3 rounded-md border bg-white p-4 md:grid-cols-5">
                <div>
                    <label class="block text-sm font-medium">From Date</label>
                    <input v-model="state.from_date" type="date" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })" />
                </div>
                <div>
                    <label class="block text-sm font-medium">To Date</label>
                    <input v-model="state.to_date" type="date" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Vendor</label>
                    <select v-model="state.vendor_id" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })">
                        <option value="">All Vendors</option>
                        <option v-for="vendor in props.vendors" :key="vendor.id" :value="String(vendor.id)">{{ vendor.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Status</label>
                    <select v-model="state.status" class="mt-1 block w-full rounded-md border p-2" @change="applyFilters({ page: 1 })">
                        <option value="">All</option>
                        <option value="received">Received</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button @click="applyFilters({ page: 1 })" class="rounded-md border px-3 py-2">Apply</button>
                    <button @click="resetFilters" class="rounded-md border px-3 py-2">Reset</button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-md border">
                <table class="min-w-full divide-y">
                    <thead class="bg-muted/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button @click="sortBy('do_number')" class="hover:underline">DO Number</button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button @click="sortBy('delivery_date')" class="hover:underline">Delivery Date</button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button @click="sortBy('is_received')" class="hover:underline">Status</button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="doItem in props.deliveryOrders.data" :key="doItem.id" class="odd:bg-white even:bg-muted/10">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ doItem.do_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ doItem.purchase_order.order_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ doItem.purchase_order.vendor.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDateMalaysia(doItem.delivery_date) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{'bg-green-100 text-green-800': doItem.is_received, 'bg-yellow-100 text-yellow-800': !doItem.is_received}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ doItem.is_received ? 'Received' : 'Pending' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <button @click="viewFile(doItem.file_path)" class="text-indigo-600 hover:text-indigo-900 disabled:opacity-50" :disabled="!doItem.file_path">
                                    View File
                                </button>
                                
                                <button v-if="!doItem.is_received" @click="confirmDelivery(doItem.id)" class="text-green-600 hover:text-green-900">
                                    Confirm
                                </button>
                                <span v-else class="text-gray-500">Confirmed</span>

                                <Link :href="`/delivery-orders/${doItem.id}/edit`" class="text-yellow-600 hover:text-yellow-900">
                                    Edit
                                </Link>

                                <Link :href="`/delivery-orders/${doItem.id}/print`" target="_blank" class="text-purple-600 hover:text-purple-900">
                                    Print
                                </Link>

                                <button @click="deleteDeliveryOrder(doItem.id, doItem.do_number)" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="props.deliveryOrders.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No delivery orders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                <nav class="flex flex-wrap gap-1">
                    <button
                        v-for="link in props.deliveryOrders.links"
                        :key="link.label"
                        class="rounded border px-3 py-1 text-sm"
                        :class="{ 'bg-primary text-white border-primary': link.active }"
                        :disabled="!link.url"
                        @click="goTo(link.url)"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </AppLayout>
</template>