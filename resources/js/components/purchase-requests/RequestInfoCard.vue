<script setup lang="ts">
import { useStatus } from '@/composables/useStatus';

const { statusClass } = useStatus();

const { request, currentUser } = defineProps<{
    request: {
        status?: string | null;
        submitted_at?: string | null;
    };
    currentUser?: {
        name?: string | null;
        location_iso_code?: string | null;
    };
}>();
</script>

<template>
    <div class="flex flex-col gap-2 rounded-md border bg-white p-4 text-sm text-muted-foreground shadow-sm">
        <!-- Applicant -->
        <div v-if="currentUser?.name">
            <p class="text-sm text-muted-foreground">
                Applicant Name:
                {{ currentUser.name }} ({{ currentUser.location_iso_code || '-' }})
            </p>
        </div>

        <!-- Status -->
        <div>
            Status:
            <span class="rounded px-2 py-0.5" :class="statusClass(request.status)">
        {{ request.status }}
      </span>
        </div>

        <!-- Submitted -->
        <div v-if="request.submitted_at">
            Submitted:
            {{ new Date(request.submitted_at).toLocaleString('en-MY') }}
        </div>
    </div>
</template>
