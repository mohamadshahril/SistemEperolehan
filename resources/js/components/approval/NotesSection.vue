<script setup lang="ts">
import { FileText } from 'lucide-vue-next'

interface Props {
  notes?: string | null
  title?: string
}

withDefaults(defineProps<Props>(), {
  notes: null,
  title: 'Request Notes',
})
</script>

<template>
  <div class="rounded-lg border p-4">
    <div class="mb-3 flex items-center gap-2">
      <FileText :size="18" class="text-gray-600" />
      <h3 class="font-semibold">{{ title }}</h3>
    </div>
    <div
      v-if="notes"
      class="whitespace-pre-wrap rounded bg-gray-50 p-3 text-sm leading-relaxed"
    >
      {{ notes }}
    </div>
    <div v-else class="text-sm text-muted-foreground italic">
      No notes provided
    </div>
  </div>
</template>

