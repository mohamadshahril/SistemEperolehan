<script setup lang="ts">
import { Eye, Paperclip, Pencil, Printer, Trash2 } from 'lucide-vue-next';
import IconButton from '@/components/IconButton.vue';
import { computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';

type RequestRow = {
    id: number
    title: string
    budget: number
    status: string
    submitted_at: string | null
    purchase_ref_no?: string | null
    attachment_url?: string | null
}

const props = defineProps<{
    requests: {
        data: RequestRow[]
        links: Array<{ url: string | null; label: string; active: boolean }>
        // Include minimal paginator meta we need for row numbering
        meta?: {
            from?: number | null
            current_page?: number
            per_page?: number
        }
    }
    filters: {
        search?: string | null
        status?: string | null
        from_date?: string | null
        to_date?: string | null
        sort_by?: string | null
        sort_dir?: 'asc' | 'desc' | null
        per_page?: number | null
    }
    statuses?: string[]
}>()

const state = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    from_date: props.filters.from_date ?? '',
    to_date: props.filters.to_date ?? '',
    sort_by: props.filters.sort_by ?? 'submitted_at',
    sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
    per_page: props.filters.per_page ?? 10,
})

computed(() => props.statuses ?? ['Pending', 'Approved', 'Rejected'])

function applyFilters(extra: Record<string, unknown> = {}) {
    router.get('/purchase-requests', {
        search: state.search || undefined,
        status: state.status || undefined,
        from_date: state.from_date || undefined,
        to_date: state.to_date || undefined,
        sort_by: state.sort_by || undefined,
        sort_dir: state.sort_dir || undefined,
        per_page: state.per_page || undefined,
        ...extra,
    }, { preserveState: true, preserveScroll: true, replace: true })
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

// Map status text to Tailwind color classes
function statusClass(status: string | undefined | null): string {
    const s = (status || '').toLowerCase()
    if (s === 'approved') return 'bg-green-100 text-green-800 border border-green-200'
    if (s === 'rejected') return 'bg-red-100 text-red-800 border border-red-200'
    if (s === 'pending') return 'bg-yellow-100 text-yellow-800 border border-yellow-200'
    return 'bg-gray-100 text-gray-800 border border-gray-200'
}

// Note: previously showed row number; requirement changed to show ID directly.

function destroyRequest(id: number) {
    if (!confirm('Delete this purchase request? This action cannot be undone.')) return
    router.delete(`/purchase-requests/${id}`, { preserveScroll: true, preserveState: true })
}

</script>

<template>
    <div class="overflow-x-auto rounded-md border">
        <table class="min-w-full divide-y">
            <thead class="bg-muted/30">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        <button @click="sortBy('id')" class="hover:underline">
                            ID
                        </button>
                    </th>
                    <!-- Ref No column is now sortable by purchase_ref_no -->
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        <button
                            @click="sortBy('purchase_ref_no')"
                            class="hover:underline"
                        >
                            Ref No
                        </button>
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        <button
                            @click="sortBy('title')"
                            class="hover:underline"
                        >
                            Title
                        </button>
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        <button
                            @click="sortBy('submitted_at')"
                            class="hover:underline"
                        >
                            Submitted
                        </button>
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        <button
                            @click="sortBy('status')"
                            class="hover:underline"
                        >
                            Status
                        </button>
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        <button
                            @click="sortBy('budget')"
                            class="hover:underline"
                        >
                            Budget (RM)
                        </button>
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        Attachment
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr
                    v-for="req in props.requests.data"
                    :key="req.id"
                    class="odd:bg-white even:bg-muted/10"
                >
                    <td class="px-4 py-2">
                        <div class="text-xs text-muted-foreground">
                            #{{ req.id }}
                        </div>
                    </td>
                    <!-- Ref No column -->
                    <td class="px-4 py-2">{{ req.purchase_ref_no || '-' }}</td>
                    <td class="px-4 py-2">
                        <div class="font-medium">{{ req.title }}</div>
                    </td>
                    <td class="px-4 py-2">
                        {{
                            req.submitted_at
                                ? new Date(req.submitted_at).toLocaleString(
                                      'en-MY',
                                  )
                                : '-'
                        }}
                    </td>
                    <td class="px-4 py-2">
                        <span
                            class="rounded px-2 py-1 text-xs"
                            :class="statusClass(req.status)"
                            >{{ req.status }}</span
                        >
                    </td>
                    <td class="px-4 py-2">
                        {{
                            Number(req.budget).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            })
                        }}
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center">
                            <!-- Show attachment icon only when an attachment exists -->
                            <IconButton
                                v-if="req.attachment_url"
                                :icon="Paperclip"
                                title="Attachment"
                                :href="req.attachment_url"
                                external
                            />
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            <IconButton
                                :icon="Eye"
                                title="View"
                                :href="`/purchase-requests/${req.id}`"
                            />
                            <IconButton
                                v-if="
                                    (req.status || '').toLowerCase() ===
                                    'pending'
                                "
                                :icon="Pencil"
                                title="Edit"
                                :href="`/purchase-requests/${req.id}/edit`"
                            />
                            <IconButton
                                :icon="Printer"
                                title="Print"
                                :href="`/purchase-requests/${req.id}?print=1`"
                            />
                            <IconButton
                                v-if="
                                    (req.status || '').toLowerCase() ===
                                    'pending'
                                "
                                :icon="Trash2"
                                title="Delete"
                                variant="danger"
                                @click="() => destroyRequest(req.id)"
                            />
                        </div>
                    </td>
                </tr>
                <tr v-if="props.requests.data.length === 0">
                    <td
                        colspan="7"
                        class="px-4 py-8 text-center text-sm text-muted-foreground"
                    >
                        No purchase requests found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
