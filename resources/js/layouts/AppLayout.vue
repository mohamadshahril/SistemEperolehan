<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import type { BreadcrumbItemType } from '@/types'

// Props from wrapper (breadcrumbs)
interface Props {
  breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})

// Access page data (optional for user info / auth)
const page = usePage()
</script>

<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r">
      <div class="p-4 font-bold text-lg border-b">
        MyProcura Dashboard
      </div>

      <nav class="mt-4">
        <!-- Example existing links -->
        <Link
          :href="route('dashboard')"
          class="block py-2 px-4 hover:bg-gray-100"
          :class="{ 'bg-gray-200 font-semibold': route().current('dashboard') }"
        >
          Dashboard
        </Link>

        <Link
          :href="route('purchase-requests.index')"
          class="block py-2 px-4 hover:bg-gray-100"
          :class="{ 'bg-gray-200 font-semibold': route().current('purchase-requests.*') }"
        >
          Purchase Requests
        </Link>

        <!-- 🟩 Step 7: Add this Purchase Orders navigation link -->
        <Link
          :href="route('purchase-orders.index')"
          class="block py-2 px-4 hover:bg-gray-100"
          :class="{ 'bg-gray-200 font-semibold': route().current('purchase-orders.*') }"
        >
          Purchase Orders
        </Link>
        <!-- ✅ Highlight active state and works for /index, /create, /edit -->
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-6">
      <!-- Breadcrumbs (optional) -->
      <nav v-if="breadcrumbs.length" class="text-sm text-gray-500 mb-4">
        <ol class="flex space-x-2">
          <li v-for="(crumb, index) in breadcrumbs" :key="index">
            <span v-if="crumb.href">
              <Link :href="crumb.href" class="hover:underline text-blue-600">
                {{ crumb.label }}
              </Link>
            </span>
            <span v-else>{{ crumb.label }}</span>
            <span v-if="index < breadcrumbs.length - 1">›</span>
          </li>
        </ol>
      </nav>

      <!-- Slot for the page content -->
      <slot />
    </main>
  </div>
</template>

<style scoped>
aside {
  min-width: 16rem;
}
</style>
