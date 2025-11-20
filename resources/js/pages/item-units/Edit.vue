<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{ unit: { id: number; code: string; name: string; description?: string | null; status: number } }>()

const form = useForm({
  code: props.unit.code,
  name: props.unit.name,
  description: props.unit.description ?? '',
  status: (props.unit.status as 1 | 2) ?? 1,
})

const submitting = ref(false)

function submit() {
  submitting.value = true
  form.put(`/item-units/${props.unit.id}`, {
    onFinish: () => (submitting.value = false),
  })
}
</script>

<template>
  <Head title="Edit Item Unit" />
  <AppLayout :breadcrumbs="[{ title: 'Item Units', href: '/item-units' }, { title: `Edit: ${props.unit.code}`, href: `/item-units/${props.unit.id}/edit` }]">
    <div class="mx-auto max-w-2xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Edit Item Unit</h1>
        <a href="/item-units" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium">Code</label>
          <input v-model="form.code" type="text" class="mt-1 block w-full rounded-md border p-2" @input="form.code = form.code.toUpperCase()" />
          <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Name</label>
          <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Description</label>
          <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model.number="form.status" class="mt-1 block w-full rounded-md border p-2">
            <option :value="1">Active</option>
            <option :value="2">Inactive</option>
          </select>
          <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" :disabled="form.processing || submitting" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing || submitting ? 'Saving...' : 'Save' }}
          </button>
          <a href="/item-units" class="text-sm hover:underline">Cancel</a>
        </div>
      </form>
    </div>
  </AppLayout>
  </template>
