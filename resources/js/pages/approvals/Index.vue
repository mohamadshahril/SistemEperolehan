<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps<{
  requests: {
    data: Array<{ id:number; item_name:string; quantity:number; price:string; purpose:string|null; submitted_at:string; status:string; approval_comment: string | null; user: { id:number; name:string; email:string } }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    from_date?: string | null
    to_date?: string | null
    status?: string | null
    sort_by?: string | null
    sort_dir?: 'asc' | 'desc' | null
    per_page?: number | null
  }
  statuses?: string[]
}>()

const state = reactive({
  search: props.filters.search ?? '',
  from_date: props.filters.from_date ?? '',
  to_date: props.filters.to_date ?? '',
  status: props.filters.status ?? 'Pending',
  per_page: props.filters.per_page ?? 10,
  sort_by: props.filters.sort_by ?? 'submitted_at',
  sort_dir: (props.filters.sort_dir as 'asc' | 'desc' | null) ?? 'desc',
  // Store comments per request row so typing in one doesn't mirror to others
  comments: {} as Record<number, string>,
  actingId: null as number | null,
})

function applyFilters(extra: Record<string, unknown> = {}) {
  router.get('/approvals', {
    search: state.search || undefined,
    from_date: state.from_date || undefined,
    to_date: state.to_date || undefined,
    status: state.status || undefined,
    sort_by: state.sort_by || undefined,
    sort_dir: state.sort_dir || undefined,
    per_page: state.per_page || undefined,
    ...extra,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
  state.search = ''
  state.from_date = ''
  state.to_date = ''
  state.status = 'Pending'
  state.per_page = 10
  state.sort_by = 'submitted_at'
  state.sort_dir = 'desc'
  applyFilters({ page: 1 })
}

// Row-level helper: only Pending rows can be acted on
function isRowPending(id: number): boolean {
  const row = props.requests.data.find(r => r.id === id)
  return !!row && row.status === 'Pending'
}

function goTo(url: string | null) {
  if (!url) return
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

function submitAction(id: number, action: 'approve' | 'reject') {
  // Guard: only allow actions for Pending rows
  if (!isRowPending(id)) return
  if (state.actingId !== id) {
    state.actingId = id
  }
  const url = action === 'approve' ? `/approvals/${id}/approve` : `/approvals/${id}/reject`
  const payload = { comment: state.comments[id] || undefined }
  router.post(url, payload, {
    preserveScroll: true,
    onSuccess: () => {
      // Clear only the comment for this specific request
      state.comments[id] = ''
      state.actingId = null
    }
  })
}

function sortBy(column: 'id' | 'item_name' | 'quantity' | 'price' | 'submitted_at' | 'status') {
  if (state.sort_by === column) {
    state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    state.sort_by = column
    state.sort_dir = 'asc'
  }
  applyFilters()
}
</script>

<template>
  <Head title="Approval Purchase Requests" />
  <AppLayout :breadcrumbs="[{ title: 'Approval Purchase Requests', href: '/approvals' }]">
    <div class="p-4">
      <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Approval Purchase Requests</h1>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-6">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">Search</label>
          <input v-model="state.search" type="text" placeholder="Ref, employee, item, status or date (YYYY-MM-DD)" class="mt-1 block w-full rounded-md border p-2" @keyup.enter="applyFilters({ page: 1 })" />
        </div>
        <div>
          <label class="block text-sm font-medium">Status</label>
          <select v-model="state.status" class="mt-1 block w-full rounded-md border p-2">
            <option v-for="s in (props.statuses || ['All','Pending','Approved','Rejected'])" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">From date</label>
          <input v-model="state.from_date" type="date" class="mt-1 block w-full rounded-md border p-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">To date</label>
          <input v-model="state.to_date" type="date" class="mt-1 block w-full rounded-md border p-2" />
        </div>
        <div class="flex items-end gap-2">
          <button class="rounded-md border px-3 py-2" @click="applyFilters({ page: 1 })">Apply</button>
          <button class="rounded-md border px-3 py-2" @click="resetFilters">Reset</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y">
          <thead>
            <tr>
              <th class="px-3 py-2 text-left">View</th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('id')" class="hover:underline">Ref</button></th>
              <th class="px-3 py-2 text-left">Employee</th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('item_name')" class="hover:underline">Item</button></th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('quantity')" class="hover:underline">Qty</button></th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('price')" class="hover:underline">Price</button></th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('submitted_at')" class="hover:underline">Submitted</button></th>
              <th class="px-3 py-2 text-left"><button @click="sortBy('status')" class="hover:underline">Status</button></th>
              <th class="px-3 py-2 text-left">Comment</th>
              <th class="px-3 py-2 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in props.requests.data" :key="r.id" class="border-b align-top">
              <td class="px-3 py-2"><a class="rounded-md border px-2 py-1 text-sm hover:bg-muted" :href="`/approvals/${r.id}`">View</a></td>
              <td class="px-3 py-2 font-mono"><a class="text-primary hover:underline" :href="`/approvals/${r.id}`">#{{ r.id }}</a></td>
              <td class="px-3 py-2">{{ r.user.name }}</td>
              <td class="px-3 py-2">{{ r.item_name }}</td>
              <td class="px-3 py-2">{{ r.quantity }}</td>
              <td class="px-3 py-2">{{ r.price }}</td>
              <td class="px-3 py-2">{{ new Date(r.submitted_at).toLocaleString() }}</td>
              <td class="px-3 py-2">
                <span
                  class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                  :class="{
                    'bg-amber-100 text-amber-800': r.status === 'Pending',
                    'bg-green-100 text-green-800': r.status === 'Approved',
                    'bg-red-100 text-red-800': r.status === 'Rejected',
                  }"
                >
                  {{ r.status }}
                </span>
              </td>
              <td class="px-3 py-2">
                <template v-if="r.status === 'Pending'">
                  <textarea
                    v-model="state.comments[r.id]"
                    placeholder="add comment (optional)"
                    class="mt-1 block w-64 rounded-md border p-2"
                    rows="2"
                    @focus="state.actingId = r.id"
                  ></textarea>
                </template>
                <template v-else>
                  <div class="mt-1 w-64 whitespace-pre-wrap text-sm text-muted-foreground">
                    {{ r.approval_comment || '-' }}
                  </div>
                </template>
              </td>
              <td class="px-3 py-2 text-right">
                <div class="flex justify-end gap-2" v-if="r.status === 'Pending'">
                  <button
                    class="rounded-md bg-green-600 px-3 py-2 text-white"
                    @click="submitAction(r.id, 'approve')"
                  >Approve</button>
                  <button
                    class="rounded-md bg-red-600 px-3 py-2 text-white"
                    @click="submitAction(r.id, 'reject')"
                  >Reject</button>
                </div>
              </td>
            </tr>
            <tr v-if="props.requests.data.length === 0">
              <td colspan="10" class="px-3 py-6 text-center text-sm text-muted-foreground">No requests found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button
          v-for="l in props.requests.links"
          :key="l.label + (l.url || '')"
          :disabled="!l.url"
          @click="goTo(l.url)"
          class="rounded-md border px-3 py-1 disabled:opacity-50"
          :class="{ 'bg-primary text-white': l.active }"
          v-html="l.label"
        />
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
