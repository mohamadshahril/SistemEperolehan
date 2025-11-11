<script setup lang="ts">
// Static logo import via Vite. Note: file present as aimlogos.png (single .png).
import logoUrl from '@/assets/aimlogo.png';
</script>

<template>
  <div class="flex items-center gap-2">
    <img :src="logoUrl" alt="App Logo" class="h-12 w-12 object-contain" />
    <div class="ml-1 grid flex-1 text-left text-sm">
      <span class="mb-0.5 truncate leading-tight font-semibold">Sistem E-perolehan</span>
    </div>
  </div>
</template>
