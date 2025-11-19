<script setup lang="ts">
const props = defineProps<{
    icon: 'eye' | 'printer' | 'pencil' | 'trash' | 'paperclip'
    to?: string | null
    as?: 'a' | 'button'
    title?: string
    disabled?: boolean
    variant?: 'default' | 'danger' | 'muted'
}>()

const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void
}>()

const classes = () => {
    const base = 'inline-flex h-8 w-8 items-center justify-center rounded-md transition focus:outline-none focus:ring-2 focus:ring-offset-2';
    const palette = {
        default: 'text-slate-700 hover:bg-slate-100 focus:ring-primary',
        danger: 'text-red-600 hover:bg-red-50 focus:ring-red-500',
        muted: 'text-slate-500 hover:bg-slate-100 focus:ring-slate-400',
    } as const
    const disabled = props.disabled ? 'opacity-50 pointer-events-none' : ''
    return [base, palette[props.variant ?? 'default'], disabled].join(' ')
}
</script>

<template>
    <component
        :is="(to && !disabled) ? 'a' : (as || 'button')"
        :href="to || undefined"
        :title="title"
        :aria-label="title"
        :class="classes()"
        @click="(e: MouseEvent) => emit('click', e)"
    >
        <!-- Eye -->
        <svg v-if="icon==='eye'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"/>
            <circle cx="12" cy="12" r="3" />
        </svg>

        <!-- Printer -->
        <svg v-else-if="icon==='printer'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H5a3 3 0 01-3-3v-3a3 3 0 013-3h14a3 3 0 013 3v3a3 3 0 01-3 3h-1"/>
            <path d="M7 15h10v6H7z"/>
        </svg>

        <!-- Pencil -->
        <svg v-else-if="icon==='pencil'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487l3.651 3.651-10.9 10.9-4.758 1.107 1.107-4.758 10.9-10.9z"/>
        </svg>

        <!-- Trash -->
        <svg v-else-if="icon==='trash'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6m-9 4h12m-1 0l-.867 12.142A2 2 0 0114.138 21H9.862a2 2 0 01-1.995-1.858L7 7"/>
            <path d="M10 11v6M14 11v6" stroke-linecap="round"/>
        </svg>

        <!-- Paperclip -->
        <svg v-else-if="icon==='paperclip'" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip">
            <path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"/>
        </svg>

    </component>
</template>

