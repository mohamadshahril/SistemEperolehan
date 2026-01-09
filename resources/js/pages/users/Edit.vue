<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  user: { id: number; name: string; email: string; roles: number[]; permissions: number[]; location_iso_code?: string }
  allRoles: Array<{ id: number; name: string }>
  allPermissions: Array<{ id: number; name: string }>
  locations: Array<{ location_iso_code: string; location_name: string }>
  canManageDirectPermissions?: boolean
}>()

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  location_iso_code: props.user.location_iso_code || '',
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
          <h2 class="mb-3 text-lg font-medium">Profile Information</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium">Name</label>
              <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border p-2" />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium">Email</label>
              <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border p-2" />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium">Location</label>
              <select v-model="form.location_iso_code" class="mt-1 block w-full rounded-md border p-2">
                <option value="">Select Location</option>
                <option v-for="loc in locations" :key="loc.location_iso_code" :value="loc.location_iso_code">
                  {{ loc.location_name }} ({{ loc.location_iso_code }})
                </option>
              </select>
              <p v-if="form.errors.location_iso_code" class="mt-1 text-sm text-red-600">{{ form.errors.location_iso_code }}</p>
            </div>
          </div>
        </div>

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

        <div v-if="canManageDirectPermissions" class="rounded-md border bg-white p-6">
          <h2 class="mb-3 text-lg font-medium">Direct Permissions (Admin Only)</h2>
          <p class="mb-3 text-sm text-muted-foreground">
            Direct permissions override role-based permissions. Use this to grant or revoke specific permissions for this user.
          </p>
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
