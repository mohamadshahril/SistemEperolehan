<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'

const props = defineProps<{ fileReference: { id: number; file_code: string; file_description: string; parent_file_code: string; status: number } }>()

const form = useForm({
  file_code: props.fileReference.file_code,
  file_description: props.fileReference.file_description,
  parent_file_code: props.fileReference.parent_file_code,
  status: props.fileReference.status as 1 | 2,
  _method: 'PUT' as const,
})

function submit() {
  form.post(`/file-references/${props.fileReference.id}`)
}

function destroyItem() {
  if (!confirm('Delete this file reference?')) return
  router.delete(`/file-references/${props.fileReference.id}`)
}
</script>

<template>
  <Head title="Edit File Reference" />
  <AppLayout :breadcrumbs="[{ title: 'File References', href: '/file-references' }, { title: 'Edit', href: `/file-references/${props.fileReference.id}/edit` }]">
    <div class="mx-auto max-w-2xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Edit File Reference</h1>
        <a href="/file-references" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium">File Code</label>
          <input v-model.trim="form.file_code" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.file_code" class="mt-1 text-sm text-red-600">{{ form.errors.file_code }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Description</label>
          <input v-model.trim="form.file_description" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.file_description" class="mt-1 text-sm text-red-600">{{ form.errors.file_description }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Parent File Code</label>
          <input v-model.trim="form.parent_file_code" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.parent_file_code" class="mt-1 text-sm text-red-600">{{ form.errors.parent_file_code }}</div>
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
          <a href="/file-references" class="text-sm hover:underline">Cancel</a>
          <span class="mx-1 hidden md:inline">|</span>
          <button type="button" @click="destroyItem" class="text-red-600 hover:underline">Delete</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
