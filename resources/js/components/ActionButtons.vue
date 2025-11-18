<script setup lang="ts">
import IconButton from '@/components/ui/IconButton.vue'

const props = defineProps<{
    viewUrl?: string | null
    attachUrl?: string | null
    printUrl?: string | null
    editUrl?: string | null
    canEdit?: boolean
    canDelete?: boolean
}>()

const emit = defineEmits<{
    (e: 'delete'): void
}>()
</script>

<template>
    <div class="flex items-center gap-1.5">
        <IconButton v-if="viewUrl" icon="eye" :to="viewUrl" title="View" variant="muted" />
        <IconButton v-if="attachUrl" icon="paperclip" :to="attachUrl" title="Attachment" variant="muted" target="_blank" />
        <IconButton v-if="printUrl" icon="printer" :to="printUrl" title="Print" variant="muted" />

        <template v-if="canEdit">
            <IconButton v-if="editUrl" icon="pencil" :to="editUrl" title="Edit" />
        </template>

        <template v-if="canDelete">
            <IconButton icon="trash" as="button" title="Delete" variant="danger" @click="$emit('delete')" />
        </template>
    </div>
</template>
