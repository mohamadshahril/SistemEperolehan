<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  permissions: Array<{ id: number; name: string }>
}>()

const form = useForm({
  name: '',
  description: '',
  permission_ids: [] as number[],
})

function submit() {
  form.post('/roles')
}
</script>

<template>
  <Head title="Create Role" />
  <AppLayout :breadcrumbs="[
    { title: 'Roles', href: '/roles' },
    { title: 'Create', href: '/roles/create' }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Create Role</h1>
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

            <div>
              <label class="block text-sm font-medium">Permissions</label>
              <div class="mt-2 grid grid-cols-1 gap-2 md:grid-cols-2">
                <label v-for="p in props.permissions" :key="p.id" class="flex items-center gap-2">
                  <input type="checkbox" :value="p.id" v-model="form.permission_ids" />
                  <span>{{ p.name }}</span>
                </label>
              </div>
              <p v-if="form.errors.permission_ids" class="mt-1 text-sm text-red-600">{{ form.errors.permission_ids }}</p>
            </div>
          </div>

          <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="rounded-md bg-primary px-4 py-2 text-white" :disabled="form.processing">Save</button>
            <a href="/roles" class="rounded-md border px-4 py-2">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
