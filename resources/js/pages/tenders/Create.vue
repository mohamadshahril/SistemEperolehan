<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  title: '',
  description: '',
  estimated_budget: null as number | null,
  opening_date: '',
  closing_date: '',
  status: 'Draft' as 'Draft' | 'Published',
  requirements: '',
  terms_conditions: '',
})

function submit() {
  form.post('/tenders', {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Create Tender" />
  <AppLayout :breadcrumbs="[
    { title: 'Tenders', href: '/tenders' },
    { title: 'Create', href: '/tenders/create' }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Create New Tender</h1>
      </div>

      <form @submit.prevent="submit" class="max-w-3xl space-y-6">
        <div class="rounded-md border bg-white p-6 space-y-4">
          <h2 class="text-lg font-semibold border-b pb-2">Basic Information</h2>

          <div>
            <label class="block text-sm font-medium mb-1">Title <span class="text-red-500">*</span></label>
            <input
              v-model="form.title"
              type="text"
              required
              class="block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.title }"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Description <span class="text-red-500">*</span></label>
            <textarea
              v-model="form.description"
              required
              rows="4"
              class="block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.description }"
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Estimated Budget (MYR)</label>
            <input
              v-model.number="form.estimated_budget"
              type="number"
              step="0.01"
              min="0"
              class="block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.estimated_budget }"
            />
            <p v-if="form.errors.estimated_budget" class="mt-1 text-sm text-red-500">{{ form.errors.estimated_budget }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Opening Date <span class="text-red-500">*</span></label>
              <input
                v-model="form.opening_date"
                type="date"
                required
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.opening_date }"
              />
              <p v-if="form.errors.opening_date" class="mt-1 text-sm text-red-500">{{ form.errors.opening_date }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Closing Date <span class="text-red-500">*</span></label>
              <input
                v-model="form.closing_date"
                type="date"
                required
                class="block w-full rounded-md border p-2"
                :class="{ 'border-red-500': form.errors.closing_date }"
              />
              <p v-if="form.errors.closing_date" class="mt-1 text-sm text-red-500">{{ form.errors.closing_date }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
            <select
              v-model="form.status"
              required
              class="block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.status }"
            >
              <option value="Draft">Draft</option>
              <option value="Published">Published</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-500">{{ form.errors.status }}</p>
            <p class="mt-1 text-sm text-muted-foreground">
              Draft: Tender is not visible to vendors. Published: Tender is open for bidding.
            </p>
          </div>
        </div>

        <div class="rounded-md border bg-white p-6 space-y-4">
          <h2 class="text-lg font-semibold border-b pb-2">Additional Details</h2>

          <div>
            <label class="block text-sm font-medium mb-1">Requirements</label>
            <textarea
              v-model="form.requirements"
              rows="6"
              placeholder="List the requirements for vendors (e.g., certifications, experience, etc.)"
              class="block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.requirements }"
            />
            <p v-if="form.errors.requirements" class="mt-1 text-sm text-red-500">{{ form.errors.requirements }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Terms & Conditions</label>
            <textarea
              v-model="form.terms_conditions"
              rows="6"
              placeholder="Specify the terms and conditions for this tender"
              class="block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.terms_conditions }"
            />
            <p v-if="form.errors.terms_conditions" class="mt-1 text-sm text-red-500">{{ form.errors.terms_conditions }}</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Tender' }}
          </button>
          <a href="/tenders" class="rounded-md border px-4 py-2">Cancel</a>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
