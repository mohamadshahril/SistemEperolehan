<script setup lang="ts">
interface Props {
  modelValue: string
  status: string
  readonly?: boolean
  placeholder?: string
  rows?: number
}

interface Emits {
  (e: 'update:modelValue', value: string): void
  (e: 'focus'): void
}

const props = withDefaults(defineProps<Props>(), {
  readonly: false,
  placeholder: 'Add remarks (optional)',
  rows: 2,
})
defineEmits<Emits>()
</script>

<template>
  <div class="remarks-field">
    <template v-if="props.status === 'Pending' && !props.readonly">
      <textarea
        :value="props.modelValue"
        :placeholder="props.placeholder"
        :rows="props.rows"
        class="mt-1 block w-full rounded-md border p-2"
        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        @focus="$emit('focus')"
      ></textarea>
    </template>
    <template v-else>
      <div class="mt-1 whitespace-pre-wrap text-sm text-muted-foreground">
        {{ props.modelValue || '-' }}
      </div>
    </template>
  </div>
</template>

<style scoped>
.text-muted-foreground {
  color: #6b7280;
}
</style>

