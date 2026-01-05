<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

interface DocumentFile {
  file: File
  description: string
}

const form = useForm({
  title: '',
  description: '',
  estimated_budget: null as number | null,
  opening_date: '',
  closing_date: '',
  status: 'Draft' as 'Draft' | 'Published',
  requirements: '',
  terms_conditions: '',
  documents: [] as File[],
  document_descriptions: [] as string[],
})

const documentFiles = ref<DocumentFile[]>([])

function handleFileSelect(e: Event) {
  const target = e.target as HTMLInputElement
  const files = target.files
  
  if (files) {
    Array.from(files).forEach(file => {
      documentFiles.value.push({
        file,
        description: ''
      })
    })
  }
  
  // Reset input
  target.value = ''
}

function removeDocument(index: number) {
  documentFiles.value.splice(index, 1)
}

function submit() {
  // Create FormData manually for file uploads
  const formData = new FormData()
  
  // Add basic fields
  formData.append('title', form.title)
  formData.append('description', form.description)
  if (form.estimated_budget) {
    formData.append('estimated_budget', form.estimated_budget.toString())
  }
  formData.append('opening_date', form.opening_date)
  formData.append('closing_date', form.closing_date)
  formData.append('status', form.status)
  if (form.requirements) {
    formData.append('requirements', form.requirements)
  }
  if (form.terms_conditions) {
    formData.append('terms_conditions', form.terms_conditions)
  }
  
  // Add documents
  documentFiles.value.forEach((doc, index) => {
    formData.append(`documents[${index}]`, doc.file)
    if (doc.description) {
      formData.append(`document_descriptions[${index}]`, doc.description)
    }
  })
  
  // Submit using Inertia's post with FormData
  form.post('/tenders', {
    data: formData,
    preserveScroll: true,
    forceFormData: true,
  })
}

function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
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

        <!-- Document Upload Section -->
        <div class="rounded-md border bg-white p-6 space-y-4">
          <h2 class="text-lg font-semibold border-b pb-2">Tender Documents</h2>
          <p class="text-sm text-muted-foreground">
            Upload supporting documents for vendors to review (specifications, drawings, terms, etc.)
          </p>

          <div>
            <label class="block text-sm font-medium mb-2">Add Documents</label>
            <input
              type="file"
              @change="handleFileSelect"
              multiple
              accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
              class="block w-full rounded-md border p-2"
            />
            <p class="mt-1 text-sm text-muted-foreground">
              Accepted formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max: 10MB per file)
            </p>
          </div>

          <!-- Document List -->
          <div v-if="documentFiles.length > 0" class="space-y-3">
            <h3 class="text-sm font-medium">Selected Documents ({{ documentFiles.length }})</h3>
            <div
              v-for="(doc, index) in documentFiles"
              :key="index"
              class="flex items-start gap-3 p-3 border rounded-md bg-gray-50"
            >
              <div class="flex-1 space-y-2">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium">{{ doc.file.name }}</span>
                  <span class="text-xs text-muted-foreground">({{ formatFileSize(doc.file.size) }})</span>
                </div>
                <input
                  v-model="doc.description"
                  type="text"
                  placeholder="Document description (optional)"
                  class="block w-full rounded-md border p-2 text-sm"
                />
              </div>
              <button
                type="button"
                @click="removeDocument(index)"
                class="text-red-600 hover:text-red-800 text-sm font-medium"
              >
                Remove
              </button>
            </div>
          </div>

          <div v-else class="text-center py-8 text-muted-foreground border-2 border-dashed rounded-md">
            <p class="text-sm">No documents selected</p>
            <p class="text-xs mt-1">Click "Add Documents" above to upload files</p>
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
