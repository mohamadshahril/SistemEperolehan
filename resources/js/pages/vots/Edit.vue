<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'

const props = defineProps<{
  vot: { id: number; vot_code: number; vot_description: string; status: number }
}>()

const form = useForm({
  vot_code: props.vot.vot_code,
  vot_description: props.vot.vot_description,
  status: props.vot.status as 1 | 2,
  _method: 'PUT' as const,
})

function submit() {
  form.post(`/vots/${props.vot.id}`)
}

function destroyVot() {
  if (!confirm('Delete this VOT?')) return
  router.delete(`/vots/${props.vot.id}`)
}
</script>

<template>
  <Head title="Edit VOT" />
  <AppLayout :breadcrumbs="[{ title: 'VOTs', href: '/vots' }, { title: 'Edit', href: `/vots/${props.vot.id}/edit` }]">
    <div class="mx-auto max-w-2xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Edit VOT</h1>
        <a href="/vots" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium">VOT Code</label>
          <input v-model="form.vot_code" type="number" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.vot_code" class="mt-1 text-sm text-red-600">{{ form.errors.vot_code }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">VOT Name</label>
          <input v-model.trim="form.vot_description" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.vot_description" class="mt-1 text-sm text-red-600">{{ form.errors.vot_description }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model.number="form.status" class="mt-1 block w-full rounded-md border p-2">
            <option :value="1">Active</option>
            <option :value="2">Inactive</option>
          </select>
          <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <button type="submit" :disabled="form.processing" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
          <a href="/vots" class="text-sm hover:underline">Cancel</a>
          <span class="mx-1 hidden md:inline">|</span>
          <button type="button" @click="destroyVot" class="text-red-600 hover:underline">Delete</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
