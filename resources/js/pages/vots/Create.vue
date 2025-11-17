<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  vot_code: '' as string | number,
  vot_name: '',
  status: 1 as 1 | 2,
})

function submit() {
  form.post('/vots')
}
</script>

<template>
  <Head title="New VOT" />
  <AppLayout :breadcrumbs="[{ title: 'VOTs', href: '/vots' }, { title: 'Create', href: '/vots/create' }]">
    <div class="mx-auto max-w-2xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Create VOT</h1>
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
          <input v-model.trim="form.vot_name" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.vot_name" class="mt-1 text-sm text-red-600">{{ form.errors.vot_name }}</div>
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
          <button type="submit" :disabled="form.processing" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing ? 'Saving...' : 'Create VOT' }}
          </button>
          <a href="/vots" class="text-sm hover:underline">Cancel</a>
        </div>
      </form>
    </div>
  </AppLayout>

</template>
