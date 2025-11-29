<script setup lang="ts">
interface Props {
  status: string
}

defineProps<Props>()

const statusClasses: Record<string, string> = {
  'Pending': 'bg-amber-100 text-amber-800',
  'Approved': 'bg-green-100 text-green-800',
  'Rejected': 'bg-red-100 text-red-800',
}

const getStatusClass = (status: string): string => {
  return statusClasses[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
    :class="getStatusClass(status)"
  >
    {{ status }}
  </span>
</template>

