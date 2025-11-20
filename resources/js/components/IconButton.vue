<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  title: string
  icon: any
  href?: string
  external?: boolean
  disabled?: boolean
  size?: 'sm' | 'md'
  variant?: 'default' | 'danger'
}>()

const emit = defineEmits<{ (e: 'click', ev: MouseEvent): void }>()

const classes = computed(() => {
  const base = 'inline-flex items-center justify-center rounded-md border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2'
  const size = props.size === 'sm' ? ' h-8 w-8 text-[14px] px-1.5 py-1.5' : ' h-9 w-9 text-[16px] px-2 py-2'
  const variant = props.variant === 'danger'
    ? ' border-red-200 text-red-600 hover:bg-red-50'
    : ' border-gray-200 text-primary hover:bg-muted/30'
  const disabled = props.disabled ? ' opacity-50 pointer-events-none' : ''
  return base + size + ' ' + variant + disabled
})

function onClick(ev: MouseEvent) {
  if (props.disabled) {
    ev.preventDefault()
    return
  }
  emit('click', ev)
}
</script>

<template>
  <a v-if="href" :href="href" :title="title" :class="classes" :target="external ? '_blank' : undefined" :rel="external ? 'noopener noreferrer' : undefined">
    <component :is="icon" />
    <span class="sr-only">{{ title }}</span>
  </a>
  <button v-else type="button" :title="title" :class="classes" :disabled="disabled" @click="onClick">
    <component :is="icon" />
    <span class="sr-only">{{ title }}</span>
  </button>

</template>
