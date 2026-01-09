<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'

defineProps<{
  user: {
    id: number
    name: string
    email: string
    created_at: string
    roles: Array<{ id: number; name: string }>
    permissions: Array<{ id: number; name: string }>
    user_location?: { location_iso_code: string } | null
  }
}>()

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-MY', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <Head :title="`User Profile - ${user.name}`" />

  <AppLayout :breadcrumbs="[
    { title: 'Users', href: '/users' },
    { title: user.name, href: `/users/${user.id}` }
  ]">
    <div class="p-6">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">{{ user.name }}</h1>
          <p class="text-muted-foreground">{{ user.email }}</p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" asChild>
            <Link href="/users">Back to List</Link>
          </Button>
          <Button asChild>
            <Link :href="`/users/${user.id}/edit`" class="flex items-center gap-2">
              Edit User
            </Link>
          </Button>
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border bg-card p-6 shadow-sm">
          <h2 class="mb-4 text-lg font-semibold">Account Information</h2>
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Full Name</dt>
              <dd class="text-base font-medium">{{ user.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Email Address</dt>
              <dd class="text-base font-medium">{{ user.email }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Joined On</dt>
              <dd class="text-base font-medium">{{ formatDate(user.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Location</dt>
              <dd class="text-base font-medium">{{ user.user_location?.location_iso_code || 'N/A' }}</dd>
            </div>
          </dl>
        </div>

        <div class="space-y-6">
          <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Roles</h2>
            <div class="flex flex-wrap gap-2">
              <Badge v-for="role in user.roles" :key="role.id" variant="secondary">
                {{ role.name }}
              </Badge>
              <p v-if="user.roles.length === 0" class="text-sm text-muted-foreground">No roles assigned.</p>
            </div>
          </div>

          <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Direct Permissions</h2>
            <div class="flex flex-wrap gap-2">
              <Badge v-for="permission in user.permissions" :key="permission.id" variant="outline">
                {{ permission.name }}
              </Badge>
              <p v-if="user.permissions.length === 0" class="text-sm text-muted-foreground">No direct permissions assigned.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
