<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  location: { id: number; location_iso_code: string; location_name: string; parent_iso_code: string | null; status?: number }
}>()

const form = useForm({
  location_iso_code: props.location.location_iso_code,
  location_name: props.location.location_name,
  parent_iso_code: props.location.parent_iso_code ?? '',
  status: (props.location.status ?? 1) as 1 | 2,
})

const submitting = ref(false)

function submit() {
  submitting.value = true
  form.put(`/locations/${props.location.id}`, {
    onFinish: () => (submitting.value = false),
  })
}

// Delete action temporarily disabled on UI
</script>

<template>
  <Head title="Edit Location" />
  <AppLayout :breadcrumbs="[{ title: 'Locations', href: '/locations' }, { title: 'Edit', href: `/locations/${props.location.id}/edit` }]">
    <div class="mx-auto max-w-2xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Edit Location</h1>
        <a href="/locations" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium">ISO Code</label>
          <input v-model="form.location_iso_code" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.location_iso_code" class="mt-1 text-sm text-red-600">{{ form.errors.location_iso_code }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Name</label>
          <input v-model="form.location_name" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.location_name" class="mt-1 text-sm text-red-600">{{ form.errors.location_name }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Parent ISO Code (optional)</label>
          <input v-model="form.parent_iso_code" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.parent_iso_code" class="mt-1 text-sm text-red-600">{{ form.errors.parent_iso_code }}</div>
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
          <button type="submit" :disabled="form.processing || submitting" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing || submitting ? 'Saving...' : 'Save Changes' }}
          </button>
          <a href="/locations" class="text-sm hover:underline">Cancel</a>
<!--          <button type="button" @click="destroyLocation" class="ml-auto rounded-md bg-red-600 px-4 py-2 text-white">Delete</button>-->
        </div>
      </form>
    </div>
  </AppLayout>
</template>
