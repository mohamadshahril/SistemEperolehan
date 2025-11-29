<script setup lang="ts">
import { Search } from 'lucide-vue-next'

interface Props {
  message?: string
}

withDefaults(defineProps<Props>(), {
  message: 'No requests found.',
})
</script>

<template>
  <div class="flex flex-col items-center justify-center py-12 text-center">
    <Search :size="48" class="mb-4 text-gray-300" />
    <p class="text-sm text-muted-foreground">{{ message }}</p>
  </div>
</template>

