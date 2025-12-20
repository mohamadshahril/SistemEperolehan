<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  tender: {
    id: number
    tender_number: string
    title: string
    description: string
    estimated_budget: number | null
    opening_date: string
    closing_date: string
    status: 'Draft' | 'Published' | 'Closed' | 'Awarded' | 'Cancelled'
    requirements: string | null
    terms_conditions: string | null
  }
}>()

const form = useForm({
  title: props.tender.title,
  description: props.tender.description,
  estimated_budget: props.tender.estimated_budget,
  opening_date: props.tender.opening_date,
  closing_date: props.tender.closing_date,
  status: props.tender.status,
  requirements: props.tender.requirements || '',
  terms_conditions: props.tender.terms_conditions || '',
})

function submit() {
  form.put(`/tenders/${props.tender.id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="`Edit Tender - ${tender.tender_number}`" />
  <AppLayout :breadcrumbs="[
    { title: 'Tenders', href: '/tenders' },
    { title: tender.tender_number, href: `/tenders/${tender.id}` },
    { title: 'Edit', href: `/tenders/${tender.id}/edit` }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Edit Tender: {{ tender.tender_number }}</h1>
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
              <option value="Closed">Closed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-500">{{ form.errors.status }}</p>
          </div>
        </div>

        <div class="rounded-md border bg-white p-6 space-y-4">
          <h2 class="text-lg font-semibold border-b pb-2">Additional Details</h2>

          <div>
            <label class="block text-sm font-medium mb-1">Requirements</label>
            <textarea
              v-model="form.requirements"
              rows="6"
              placeholder="List the requirements for vendors"
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
              placeholder="Specify the terms and conditions"
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
            {{ form.processing ? 'Updating...' : 'Update Tender' }}
          </button>
          <a :href="`/tenders/${tender.id}`" class="rounded-md border px-4 py-2">Cancel</a>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
