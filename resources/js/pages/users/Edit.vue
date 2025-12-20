<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  user: { id: number; name: string; email: string; roles: number[]; permissions: number[] }
  allRoles: Array<{ id: number; name: string }>
  allPermissions: Array<{ id: number; name: string }>
}>()

const form = useForm({
  role_ids: (props.user.roles || []) as number[],
  permission_ids: (props.user.permissions || []) as number[],
})

function submit() {
  form.put(`/users/${props.user.id}`)
}
</script>

<template>
  <Head title="Manage User" />
  <AppLayout :breadcrumbs="[
    { title: 'Users', href: '/users' },
    { title: 'Manage', href: `/users/${props.user.id}/edit` }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Manage User</h1>
        <p class="text-sm text-muted-foreground">{{ props.user.name }} — {{ props.user.email }}</p>
      </div>

      <div class="max-w-3xl space-y-6">
        <div class="rounded-md border bg-white p-6">
          <h2 class="mb-3 text-lg font-medium">Roles</h2>
          <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
            <label v-for="r in allRoles" :key="r.id" class="flex items-center gap-2">
              <input type="checkbox" :value="r.id" v-model="form.role_ids" />
              <span>{{ r.name }}</span>
            </label>
          </div>
          <p v-if="form.errors.role_ids" class="mt-1 text-sm text-red-600">{{ form.errors.role_ids }}</p>
        </div>

        <div class="rounded-md border bg-white p-6">
          <h2 class="mb-3 text-lg font-medium">Direct Permissions</h2>
          <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
            <label v-for="p in allPermissions" :key="p.id" class="flex items-center gap-2">
              <input type="checkbox" :value="p.id" v-model="form.permission_ids" />
              <span>{{ p.name }}</span>
            </label>
          </div>
          <p v-if="form.errors.permission_ids" class="mt-1 text-sm text-red-600">{{ form.errors.permission_ids }}</p>
        </div>

        <div class="flex items-center gap-2">
          <button class="rounded-md bg-primary px-4 py-2 text-white" :disabled="form.processing" @click="submit">Save Changes</button>
          <a href="/users" class="rounded-md border px-4 py-2">Back</a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
