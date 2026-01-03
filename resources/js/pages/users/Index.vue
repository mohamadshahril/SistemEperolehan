<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps<{
  users: {
    data: Array<{ id: number; name: string; email: string; roles?: Array<{ id: number; name: string }>; permissions?: Array<{ id: number; name: string }> }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
}>()

const state = reactive({
  search: props.filters.search ?? '',
  sort_by: props.filters.sort_by ?? 'created_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  per_page: props.filters.per_page ?? 10,
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/users', {
    search: state.search || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function sortBy(column: string) {
  if (state.sort_by === column) {
    state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    state.sort_by = column
    state.sort_dir = 'asc'
  }
  applyFilters()
}

function goTo(url: string | null) {
  if (!url) return
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head title="Users" />
  <AppLayout :breadcrumbs="[{ title: 'Users', href: '/users' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Users</h1>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input v-model="state.search" type="text" placeholder="Name or email" @keyup.enter="applyFilters({ page: 1 })" class="mt-1 block w-full rounded-md border p-2" />
        </div>
        <div class="flex items-end">
          <button class="rounded-md border px-3 py-2" @click="applyFilters({ page: 1 })">Apply</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y">
          <thead>
            <tr>
              <th class="px-3 py-2 text-left"><button @click="sortBy('name')">Name</button></th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('email')">Email</button></th>
              <th class="px-3 py-2 text-left">Roles</th>
              <th class="px-3 py-2 text-left">Permissions</th>
              <th class="px-3 py-2 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in props.users.data" :key="u.id" class="border-b">
              <td class="px-3 py-2 font-medium">{{ u.name }}</td>
              <td class="px-3 py-2">{{ u.email }}</td>
              <td class="px-3 py-2">
                <div class="flex flex-wrap gap-1">
                  <span v-for="r in (u.roles || [])" :key="r.id" class="rounded bg-gray-100 px-2 py-0.5 text-xs">{{ r.name }}</span>
                  <span v-if="!u.roles || u.roles.length === 0" class="text-sm text-muted-foreground">-</span>
                </div>
              </td>
              <td class="px-3 py-2">
                <div class="flex flex-wrap gap-1">
                  <span v-for="p in (u.permissions || [])" :key="p.id" class="rounded bg-gray-100 px-2 py-0.5 text-xs">{{ p.name }}</span>
                  <span v-if="!u.permissions || u.permissions.length === 0" class="text-sm text-muted-foreground">-</span>
                </div>
              </td>
              <td class="px-3 py-2 text-right">
                <a :href="`/users/${u.id}/edit`" class="text-primary hover:underline">Manage</a>
              </td>
            </tr>
            <tr v-if="props.users.data.length === 0">
              <td colspan="5" class="px-3 py-6 text-center text-sm text-muted-foreground">No users found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button v-for="l in props.users.links" :key="l.label + (l.url || '')" :disabled="!l.url" @click="goTo(l.url)" class="rounded-md border px-3 py-1 disabled:opacity-50" :class="{ 'bg-primary text-white': l.active }" v-html="l.label" />
        <div class="ml-auto flex items-center gap-2">
          <label class="text-sm">Per page:</label>
          <select v-model.number="state.per_page" @change="applyFilters({ page: 1 })" class="rounded-md border p-1">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
