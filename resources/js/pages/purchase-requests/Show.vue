<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Form from '@/components/purchase-requests/Form.vue';
import IconButton from '@/components/IconButton.vue';
import { Pencil, Paperclip, Printer, ArrowLeft } from 'lucide-vue-next';
import { onMounted } from 'vue';
import RequestInfoCard from '@/components/purchase-requests/RequestInfoCard.vue';

const props = defineProps<{
    request: {
        id: number;
        title: string;
        type_procurement_id: number | string;
        file_reference_id: number | string;
        vot_id: number | string;
        location_iso_code?: string | null;
        budget: number | string;
        note?: string | null;
        purpose?: string | null;
        item: Array<{
            details: string;
            purpose?: string | null;
            quantity: number | string;
            price: number | string;
            item_code?: string | null;
            unit?: string | null;
        }>;
        attachment_url?: string | null;
        status?: string;
        purchase_ref_no?: string | null;
        submitted_at?: string | null;
    };
    options: {
        type_procurements: Array<{ id: number; procurement_code?: string; procurement_description?: string }>;
        file_references: Array<{ id: number; file_code?: string; file_description?: string }>;
        vots: Array<{ id: number; vot_code?: string; vot_description?: string }>;
        item_units: Array<{ id: number; unit_code?: string; unit_description?: string }>;
    };
    current_user?: { name?: string | null; location_iso_code?: string | null };
    today?: string;
}>();

const readonlyModel = {
    title: props.request.title,
    type_procurement_id: props.request.type_procurement_id,
    file_reference_id: props.request.file_reference_id,
    vot_id: props.request.vot_id,
    budget: props.request.budget,
    note: props.request.note ?? props.request.notes ?? props.request.purpose ?? '',
    item: (props.request.items || props.request.item || []).map((it: any) => ({
        details: it.details,
        purpose: it.purpose ?? '',
        quantity: it.quantity ?? 1,
        price: it.price ?? 0,
        item_code: it.item_code ?? '',
        unit: it.unit ?? '',
    })),
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('print') === '1') {
        setTimeout(() => window.print(), 200);
    }
});
</script>

<template>
    <Head title="View Purchase Request" />
    <AppLayout
        :breadcrumbs="[
      { title: 'Purchase Requests', href: '/purchase-requests' },
      { title: `#${props.request.id}`, href: `/purchase-requests/${props.request.id}` },
    ]"
    >
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h1 class="p-3 text-2xl font-semibold">
                        Purchase Request #{{ props.request.id }}
                    </h1>

                    <RequestInfoCard
                        :request="props.request"
                        :current-user="props.current_user"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <IconButton
                        v-if="(props.request.status || '').toLowerCase() === 'pending'"
                        :icon="Pencil"
                        title="Edit"
                        :href="`/purchase-requests/${props.request.id}/edit`"
                    />
                    <IconButton
                        v-if="props.request.attachment_url"
                        :icon="Paperclip"
                        title="Attachment"
                        :href="props.request.attachment_url || undefined"
                        external
                    />
                    <IconButton
                        :icon="Printer"
                        title="Print"
                        :href="`/purchase-requests/${props.request.id}?print=1`"
                    />
                    <Link
                        href="/purchase-requests"
                        class="inline-flex items-center gap-1 rounded-md border px-3 py-2"
                    >
                        <ArrowLeft class="h-4 w-4" /> Back
                    </Link>
                </div>
            </div>

            <div class="rounded-md border bg-white p-6">
                <div class="mb-4" v-if="props.request.purchase_ref_no">
                    <label class="text-lg font-bold">Ref No: </label>
                    {{ props.request.purchase_ref_no }}
                </div>

                <Form
                    :model-value="readonlyModel as any"
                    :options="props.options as any"
                    :read-only="true"
                />

                <div class="mb-4" v-if="props.request.attachment_url">
                    <label class="block text-sm font-medium">Attachment</label>
                    <a
                        :href="props.request.attachment_url"
                        target="_blank"
                        class="text-primary hover:underline text-sm"
                    >
                        View Attachment
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
