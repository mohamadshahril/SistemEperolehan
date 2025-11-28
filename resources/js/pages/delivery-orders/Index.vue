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
}>()

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

            <div class="overflow-x-auto rounded-md border">
                <table class="min-w-full divide-y">
                    <thead class="bg-muted/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="doItem in props.deliveryOrders.data" :key="doItem.id" class="odd:bg-white even:bg-muted/10">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ doItem.do_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ doItem.purchase_order.order_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ doItem.purchase_order.vendor.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ doItem.delivery_date }}</td>
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
                        @click="link.url ? router.get(link.url, {}, { preserveState: true, preserveScroll: true }) : null"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </AppLayout>
</template>