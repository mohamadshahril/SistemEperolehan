<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = useForm({
  item_name: '',
  quantity: 1,
  price: '',
  purpose: '',
  attachment: null as File | null,
})

const submitting = ref(false)

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files && input.files.length > 0) {
    form.attachment = input.files[0]
  } else {
    form.attachment = null
  }
}

function submit() {
  submitting.value = true
  form.post('/purchase-requests', {
    forceFormData: true,
    onFinish: () => (submitting.value = false),
  })
}
</script>

<template>
  <Head title="Create Purchase Request" />
  <AppLayout :breadcrumbs="[{ title: 'Purchase Requests', href: '/purchase-requests' }, { title: 'Create', href: '/purchase-requests/create' }]">
    <div class="mx-auto max-w-3xl p-4">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Create Purchase Request</h1>
        <a href="/purchase-requests" class="text-sm text-primary hover:underline">Back to list</a>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium">Item Name</label>
          <input v-model="form.item_name" type="text" class="mt-1 block w-full rounded-md border p-2" />
          <div v-if="form.errors.item_name" class="mt-1 text-sm text-red-600">{{ form.errors.item_name }}</div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium">Quantity</label>
            <input v-model.number="form.quantity" type="number" min="1" class="mt-1 block w-full rounded-md border p-2" />
            <div v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">{{ form.errors.quantity }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium">Price</label>
            <input v-model="form.price" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border p-2" />
            <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium">Purpose</label>
          <textarea v-model="form.purpose" rows="4" class="mt-1 block w-full rounded-md border p-2"></textarea>
          <div v-if="form.errors.purpose" class="mt-1 text-sm text-red-600">{{ form.errors.purpose }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Attachment (optional)</label>
          <input type="file" @change="onFileChange" class="mt-1 block w-full" />
          <div class="mt-1 text-xs text-muted-foreground">Accepted: pdf, jpg, jpeg, png, doc, docx, xls, xlsx. Max 5 MB.</div>
          <div v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</div>
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" :disabled="form.processing || submitting" class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50">
            {{ form.processing || submitting ? 'Submitting...' : 'Submit Request' }}
          </button>
          <a href="/purchase-requests" class="text-sm hover:underline">Cancel</a>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
