<script setup lang="ts">
import { ArrowLeft } from 'lucide-vue-next'

interface Props {
  href?: string
  label?: string
}

withDefaults(defineProps<Props>(), {
  href: '/approvals',
  label: 'Back to Requests',
})
</script>

<template>
  <a
    :href="href"
    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-50"
  >
    <ArrowLeft :size="18" />
    {{ label }}
  </a>
</template>

