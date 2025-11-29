<script setup lang="ts">
import { FileText, Mail, Clock } from 'lucide-vue-next'

interface User {
  id: number
  name: string
  email: string
}

interface Props {
  id: number
  applicantName: string
  applicantEmail: string
  title: string
  purchaseRefNo?: string | null
  submittedAt: string | null
}

defineProps<Props>()
</script>

<template>
  <div class="space-y-4">
    <!-- Header with ID -->
    <div class="border-b pb-4">
      <div class="flex items-center gap-3">
        <FileText :size="24" class="text-blue-600" />
        <div>
          <h2 class="text-lg font-semibold">Purchase Request #{{ id }}</h2>
          <p class="text-sm text-muted-foreground">Submitted by {{ applicantName }}</p>
        </div>
      </div>
    </div>

    <!-- Basic Info Grid -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <!-- Title -->
      <div>
        <label class="block text-sm font-medium text-muted-foreground">Request Title</label>
        <p class="mt-1 text-base font-semibold">{{ title }}</p>
      </div>

      <!-- Applicant Info -->
      <div>
        <label class="block text-sm font-medium text-muted-foreground">Applicant</label>
        <div class="mt-1 flex items-center gap-2">
          <Mail :size="16" class="text-gray-400" />
          <div>
            <p class="font-semibold">{{ applicantName }}</p>
            <p class="text-sm text-muted-foreground">{{ applicantEmail }}</p>
          </div>
        </div>
      </div>

      <!-- Reference Number -->
      <div v-if="purchaseRefNo">
        <label class="block text-sm font-medium text-muted-foreground">Purchase Ref No</label>
        <p class="mt-1 font-mono text-base">{{ purchaseRefNo }}</p>
      </div>

      <!-- Submitted Date -->
      <div>
        <label class="block text-sm font-medium text-muted-foreground">Submitted At</label>
        <div class="mt-1 flex items-center gap-2">
          <Clock :size="16" class="text-gray-400" />
          <p class="text-base">
            {{ submittedAt ? new Date(submittedAt).toLocaleString('en-GB', { timeZone: 'UTC' }) : '-' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

