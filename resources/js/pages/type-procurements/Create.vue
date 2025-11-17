<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  procurement_code: '' as string | number,
  procurement_description: '',
  status: 1 as 1 | 2,
})

function submit() {
  form.post('/type-procurements')
}
</script>

<template>
  <Head title="New Type Procurement" />
  <AppLayout :breadcrumbs="[{ title: 'Type Procurements', href: '/type-procurements' }, { title: 'Create', href: '/type-procurements/create' }]">
    <div class="mx-auto max-w-2xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Create Type Procurement</h1>
        <a href="/type-procurements" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium">Procurement Code</label>
          <input v-model="form.procurement_code" type="number" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.procurement_code" class="mt-1 text-sm text-red-600">{{ form.errors.procurement_code }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Description</label>
          <input v-model.trim="form.procurement_description" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.procurement_description" class="mt-1 text-sm text-red-600">{{ form.errors.procurement_description }}</div>
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
            {{ form.processing ? 'Saving...' : 'Create' }}
          </button>
          <a href="/type-procurements" class="text-sm hover:underline">Cancel</a>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
