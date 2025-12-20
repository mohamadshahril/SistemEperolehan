<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  permission: { id: number; name: string; description?: string | null }
}>()

const form = useForm({
  name: props.permission.name || '',
  description: props.permission.description || '',
})

function submit() {
  form.put(`/permissions/${props.permission.id}`)
}
</script>

<template>
  <Head title="Edit Permission" />
  <AppLayout :breadcrumbs="[
    { title: 'Permissions', href: '/permissions' },
    { title: 'Edit', href: `/permissions/${props.permission.id}/edit` }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Edit Permission</h1>
      </div>

      <div class="max-w-2xl rounded-md border bg-white p-6">
        <form @submit.prevent="submit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Name <span class="text-red-600">*</span></label>
              <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border p-2" :class="{ 'border-red-500': form.errors.name }" />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Description</label>
              <input v-model="form.description" type="text" class="mt-1 block w-full rounded-md border p-2" :class="{ 'border-red-500': form.errors.description }" />
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>
          </div>

          <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="rounded-md bg-primary px-4 py-2 text-white" :disabled="form.processing">Update</button>
            <a href="/permissions" class="rounded-md border px-4 py-2">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
