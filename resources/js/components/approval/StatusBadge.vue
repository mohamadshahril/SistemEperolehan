<script setup lang="ts">
interface Props {
  status: string
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
})

const statusClasses: Record<string, string> = {
  'Pending': 'bg-amber-100 text-amber-800',
  'Approved': 'bg-green-100 text-green-800',
  'Rejected': 'bg-red-100 text-red-800',
}

const sizeClasses: Record<string, string> = {
  'sm': 'px-2 py-0.5 text-xs',
  'md': 'px-2 py-1 text-xs',
  'lg': 'px-3 py-1.5 text-sm',
}

const getStatusClass = (status: string): string => {
  return statusClasses[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <span
    class="inline-flex items-center rounded-full font-medium"
    :class="[getStatusClass(status), sizeClasses[props.size]]"
  >
    {{ status }}
  </span>
</template>

