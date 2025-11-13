<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  phone: '',
  address: '',
})

function submit() {
  form.post('/vendors', {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Create Vendor" />
  <AppLayout :breadcrumbs="[
    { title: 'Vendors', href: '/vendors' },
    { title: 'Create', href: '/vendors/create' }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Create New Vendor</h1>
      </div>

      <div class="max-w-2xl rounded-md border bg-white p-6">
        <form @submit.prevent="submit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Vendor Name <span class="text-red-600">*</span></label>
              <input
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.name }"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.email }"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                pattern="[0-9+\-\s()]+"
                placeholder="+60123456789"
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.phone }"
                title="Phone number can only contain numbers, +, -, spaces, and parentheses"
              />
              <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Address</label>
              <textarea
                v-model="form.address"
                rows="3"
                class="mt-1 block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.address }"
              ></textarea>
              <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
            </div>
          </div>

          <div class="mt-6 flex items-center gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50"
            >
              {{ form.processing ? 'Creating...' : 'Create Vendor' }}
            </button>
            <Link href="/vendors" class="rounded-md border px-4 py-2">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
