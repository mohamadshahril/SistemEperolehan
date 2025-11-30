<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { countries, getStatesByCountry, type State } from '@/data/countries-states'

const props = defineProps<{
  vendor: {
    id: number
    name: string
    email: string | null
    phone: string | null
    address: string | null
    address_line1: string | null
    address_line2: string | null
    city: string | null
    state: string | null
    postcode: string | null
    country: string | null
  }
}>()

const form = useForm({
  name: props.vendor.name,
  email: props.vendor.email || '',
  phone: props.vendor.phone || '',
  address_line1: props.vendor.address_line1 || '',
  address_line2: props.vendor.address_line2 || '',
  city: props.vendor.city || '',
  state: props.vendor.state || '',
  postcode: props.vendor.postcode || '',
  country: props.vendor.country || 'MY',
})

const availableStates = computed<State[]>(() => {
  return getStatesByCountry(form.country)
})

// Reset state when country changes
function onCountryChange() {
  form.state = ''
}

function submit() {
  form.put(`/vendors/${props.vendor.id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Edit Vendor" />
  <AppLayout :breadcrumbs="[
    { title: 'Vendors', href: '/vendors' },
    { title: 'Edit', href: `/vendors/${vendor.id}/edit` }
  ]">
    <div class="p-4">
      <div class="mb-4">
        <h1 class="text-2xl font-semibold">Edit Vendor</h1>
        <p class="text-sm text-muted-foreground">Vendor ID: #{{ vendor.id }}</p>
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

            <!-- Address Section -->
            <div class="space-y-4 rounded-md border p-4">
              <h3 class="text-lg font-medium">Address Information</h3>
              
              <div>
                <label class="block text-sm font-medium">Address Line 1 (Street, Building/Factory Number) <span class="text-red-600">*</span></label>
                <input
                  v-model="form.address_line1"
                  type="text"
                  required
                  placeholder="e.g., 123 Jalan Merdeka, Taman Industri"
                  class="mt-1 block w-full rounded-md border p-2"
                  :class="{ 'border-red-500': form.errors.address_line1 }"
                />
                <p v-if="form.errors.address_line1" class="mt-1 text-sm text-red-600">{{ form.errors.address_line1 }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium">Address Line 2 (Unit, Suite, Floor)</label>
                <input
                  v-model="form.address_line2"
                  type="text"
                  placeholder="e.g., Unit 5A, Floor 2"
                  class="mt-1 block w-full rounded-md border p-2"
                  :class="{ 'border-red-500': form.errors.address_line2 }"
                />
                <p v-if="form.errors.address_line2" class="mt-1 text-sm text-red-600">{{ form.errors.address_line2 }}</p>
              </div>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium">Country <span class="text-red-600">*</span></label>
                  <select
                    v-model="form.country"
                    required
                    @change="onCountryChange"
                    class="mt-1 block w-full rounded-md border p-2"
                    :class="{ 'border-red-500': form.errors.country }"
                  >
                    <option value="">Select Country</option>
                    <option v-for="country in countries" :key="country.code" :value="country.code">
                      {{ country.name }}
                    </option>
                  </select>
                  <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium">State/Province <span class="text-red-600">*</span></label>
                  <select
                    v-model="form.state"
                    required
                    :disabled="!form.country || availableStates.length === 0"
                    class="mt-1 block w-full rounded-md border p-2 disabled:bg-gray-100"
                    :class="{ 'border-red-500': form.errors.state }"
                  >
                    <option value="">Select State</option>
                    <option v-for="state in availableStates" :key="state.code" :value="state.code">
                      {{ state.name }}
                    </option>
                  </select>
                  <p v-if="form.errors.state" class="mt-1 text-sm text-red-600">{{ form.errors.state }}</p>
                  <p v-if="!form.country" class="mt-1 text-xs text-gray-500">Please select a country first</p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium">City <span class="text-red-600">*</span></label>
                  <input
                    v-model="form.city"
                    type="text"
                    required
                    placeholder="e.g., Kuala Lumpur"
                    class="mt-1 block w-full rounded-md border p-2"
                    :class="{ 'border-red-500': form.errors.city }"
                  />
                  <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium">Postcode/ZIP <span class="text-red-600">*</span></label>
                  <input
                    v-model="form.postcode"
                    type="text"
                    required
                    :placeholder="form.country === 'MY' ? 'e.g., 50000' : 'e.g., Postcode'"
                    :pattern="form.country === 'MY' ? '[0-9]{5}' : undefined"
                    :maxlength="form.country === 'MY' ? 5 : 20"
                    class="mt-1 block w-full rounded-md border p-2"
                    :class="{ 'border-red-500': form.errors.postcode }"
                  />
                  <p v-if="form.errors.postcode" class="mt-1 text-sm text-red-600">{{ form.errors.postcode }}</p>
                  <p v-if="form.country === 'MY'" class="mt-1 text-xs text-gray-500">5 digits for Malaysia</p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 flex items-center gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-md bg-primary px-4 py-2 text-white disabled:opacity-50"
            >
              {{ form.processing ? 'Updating...' : 'Update Vendor' }}
            </button>
            <Link href="/vendors" class="rounded-md border px-4 py-2">Cancel</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
