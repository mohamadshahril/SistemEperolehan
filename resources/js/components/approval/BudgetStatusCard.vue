<script setup lang="ts">
import { DollarSign, Calendar, User, CheckCircle, XCircle, Clock } from 'lucide-vue-next'
import StatusBadge from './StatusBadge.vue'

interface Props {
  budget: number | string
  status: string
  submittedAt: string | null
  approvedAt?: string | null
  approvedBy?: { id: number; name: string } | null
}

defineProps<Props>()

const formatCurrency = (amount: number | string): string => {
  return Number(amount).toLocaleString('en-MY', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const formatDate = (date: string | null): string => {
  if (!date) return '-'
  return new Date(date).toLocaleString('en-GB', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}
</script>

<template>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
    <!-- Budget Card -->
    <div class="rounded-lg border bg-gradient-to-br from-blue-50 to-blue-100 p-4 shadow-sm">
      <div class="flex items-center gap-2 text-blue-700">
        <DollarSign :size="20" />
        <span class="text-sm font-medium">Budget Allocated</span>
      </div>
      <p class="mt-2 text-2xl font-bold text-blue-900">
        RM {{ formatCurrency(budget) }}
      </p>
    </div>

    <!-- Status Card -->
    <div class="rounded-lg border bg-white p-4 shadow-sm">
      <div class="flex items-center gap-2 text-gray-700">
        <component
          :is="status === 'Approved' ? CheckCircle : status === 'Rejected' ? XCircle : Clock"
          :size="20"
        />
        <span class="text-sm font-medium">Current Status</span>
      </div>
      <div class="mt-2">
        <StatusBadge :status="status" size="lg" />
      </div>
    </div>

    <!-- Submitted Date Card -->
    <div class="rounded-lg border bg-white p-4 shadow-sm">
      <div class="flex items-center gap-2 text-gray-700">
        <Calendar :size="20" />
        <span class="text-sm font-medium">Submitted On</span>
      </div>
      <p class="mt-2 text-base font-semibold text-gray-900">
        {{ formatDate(submittedAt) }}
      </p>
    </div>

    <!-- Approval Info Card -->
    <div
      v-if="approvedAt && approvedBy"
      class="rounded-lg border p-4 shadow-sm"
      :class="{
        'bg-gradient-to-br from-green-50 to-green-100': status === 'Approved',
        'bg-gradient-to-br from-red-50 to-red-100': status === 'Rejected',
      }"
    >
      <div
        class="flex items-center gap-2"
        :class="{
          'text-green-700': status === 'Approved',
          'text-red-700': status === 'Rejected',
        }"
      >
        <User :size="20" />
        <span class="text-sm font-medium">
          {{ status === 'Approved' ? 'Approved' : 'Rejected' }} By
        </span>
      </div>
      <p
        class="mt-2 text-base font-semibold"
        :class="{
          'text-green-900': status === 'Approved',
          'text-red-900': status === 'Rejected',
        }"
      >
        {{ approvedBy.name }}
      </p>
      <p class="mt-1 text-xs text-gray-600">
        {{ formatDate(approvedAt) }}
      </p>
    </div>

    <!-- Pending Card -->
    <div
      v-else
      class="rounded-lg border bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 shadow-sm"
    >
      <div class="flex items-center gap-2 text-yellow-700">
        <Clock :size="20" />
        <span class="text-sm font-medium">Awaiting Approval</span>
      </div>
      <p class="mt-2 text-base font-semibold text-yellow-900">
        Pending Review
      </p>
    </div>
  </div>
</template>
