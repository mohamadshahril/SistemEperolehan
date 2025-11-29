<script setup lang="ts">
interface Props {
  requestId: number
  status: string
  loading?: boolean
}

interface Emits {
  (e: 'approve'): void
  (e: 'reject'): void
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

defineEmits<Emits>()
</script>

<template>
  <div class="action-buttons">
    <div class="flex justify-end gap-2" v-if="props.status === 'Pending'">
      <button
        class="rounded-md bg-green-600 px-3 py-2 text-white hover:bg-green-700 disabled:opacity-50"
        :disabled="props.loading"
        @click="$emit('approve')"
      >
        {{ props.loading ? 'Processing...' : 'Approve' }}
      </button>
      <button
        class="rounded-md bg-red-600 px-3 py-2 text-white hover:bg-red-700 disabled:opacity-50"
        :disabled="props.loading"
        @click="$emit('reject')"
      >
        {{ props.loading ? 'Processing...' : 'Reject' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.action-buttons {
  display: flex;
}
</style>

