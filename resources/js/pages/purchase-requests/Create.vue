<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Form from '@/components/purchase-requests/Form.vue'

const props = defineProps<{
  options: {
    type_procurements: Array<{ id: number; procurement_code: string; procurement_description: string }>
    file_references: Array<{ id: number; file_code: string; file_description: string }>
    vots: Array<{ id: number; vot_code: string; vot_description: string }>
  }
  current_user?: { name?: string | null; location_iso_code?: string | null }
  today?: string
}>()

const form = useForm({
  title: '',
  type_procurement_id: '',
  file_reference_id: '',
  vot_id: '',
  budget: 0,
  note: '',
  item: [{ details: '', quantity: 1, price: 0, purpose: '' }],
  attachment: null as File | null,
})

const modelProxy = computed({
  get: () => form as any,
  set: (v: any) => { Object.assign(form, v) },
})

function submit() {
  form.post('/purchase-requests', {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Create Purchase Request" />
  <AppLayout :breadcrumbs="[
    { title: 'Purchase Requests', href: '/purchase-requests' },
    { title: 'Create', href: '/purchase-requests/create' }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">New Purchase Request</h1>
        <p class="text-sm text-muted-foreground" v-if="props.current_user?.name">
          Applicant: {{ props.current_user.name }} ({{ props.current_user.location_iso_code || '-' }})
        </p>
      </div>

      <div class="rounded-md border bg-white p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <Form v-model="modelProxy" :options="props.options" />

          <div>
            <label class="block text-sm font-medium">Attachment (optional)</label>
            <input
              type="file"
              accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
              @change="(e) => (form.attachment = (e.target as HTMLInputElement).files?.[0] || null)"
              class="mt-1 block w-full rounded-md border p-2"
              :class="{ 'border-red-500': form.errors.attachment }"
            />
            <p class="mt-1 text-xs text-gray-500">Accepted: pdf, jpg, jpeg, png, doc, docx, xls, xlsx. Max 5 MB.</p>
            <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</p>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit" :disabled="form.processing" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
              {{ form.processing ? 'Submitting...' : 'Submit Request' }}
            </button>
            <Link href="/purchase-requests" class="rounded-md border px-4 py-2">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
