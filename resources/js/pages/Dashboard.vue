<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    BookPlus,
    CheckSquare,
    Users,
    FileText,
    ShoppingCart,
    Clock,
    CheckCircle,
    TrendingUp,
    LayoutGrid,
    MapPinCheck,
    Truck,
    BarChart3,
    Settings,
    ShieldCheck,
    KeyRound,
    Gavel,
    ChevronRight,
    ArrowUpRight
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    summary?: Record<string, number>;
}>();

const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const allModules = [
    {
        title: 'Purchase Requests',
        description: 'Create and manage your procurement requests',
        href: '/purchase-requests',
        icon: BookPlus,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100 dark:bg-blue-900/30'
    },
    {
        title: 'Approvals',
        description: 'Review and approve pending requests',
        href: '/approvals',
        icon: CheckSquare,
        color: 'text-orange-600',
        bgColor: 'bg-orange-100 dark:bg-orange-900/30'
    },
    {
        title: 'Locations',
        description: 'Manage delivery and office locations',
        href: '/locations',
        icon: MapPinCheck,
        color: 'text-emerald-600',
        bgColor: 'bg-emerald-100 dark:bg-emerald-900/30'
    },
    {
        title: 'Vendors',
        description: 'Maintain vendor database and contact info',
        href: '/vendors',
        icon: Users,
        color: 'text-purple-600',
        bgColor: 'bg-purple-100 dark:bg-purple-900/30'
    },
    {
        title: 'Purchase Orders',
        description: 'Generate and track official purchase orders',
        href: '/purchase-orders',
        icon: ShoppingCart,
        color: 'text-indigo-600',
        bgColor: 'bg-indigo-100 dark:bg-indigo-900/30'
    },
    {
        title: 'Tenders',
        description: 'Manage procurement tenders and processes',
        href: '/tenders',
        icon: FileText,
        color: 'text-cyan-600',
        bgColor: 'bg-cyan-100 dark:bg-cyan-900/30'
    },
    {
        title: 'Tender Bids',
        description: 'Review and evaluate submitted tender bids',
        href: '/tender-bids',
        icon: Gavel,
        color: 'text-pink-600',
        bgColor: 'bg-pink-100 dark:bg-pink-900/30'
    },
    {
        title: 'Delivery Order',
        description: 'Track and confirm received deliveries',
        href: '/delivery-orders',
        icon: Truck,
        color: 'text-amber-600',
        bgColor: 'bg-amber-100 dark:bg-amber-900/30'
    },
    {
        title: 'Delivery Report',
        description: 'Generate analytical reports for deliveries',
        href: '/delivery-reports',
        icon: BarChart3,
        color: 'text-rose-600',
        bgColor: 'bg-rose-100 dark:bg-rose-900/30'
    },
    {
        title: 'Vots',
        description: 'Manage vote codes and budget allocations',
        href: '/vots',
        icon: Settings,
        color: 'text-slate-600',
        bgColor: 'bg-slate-100 dark:bg-slate-900/30'
    },
    {
        title: 'Type Procurements',
        description: 'Define different types of procurement',
        href: '/type-procurements',
        icon: Settings,
        color: 'text-gray-600',
        bgColor: 'bg-gray-100 dark:bg-gray-900/30'
    },
    {
        title: 'Item Units',
        description: 'Manage units of measurement for items',
        href: '/item-units',
        icon: Settings,
        color: 'text-zinc-600',
        bgColor: 'bg-zinc-100 dark:bg-zinc-900/30'
    },
    {
        title: 'Users',
        description: 'Manage system users and their accounts',
        href: '/users',
        icon: Users,
        color: 'text-blue-700',
        bgColor: 'bg-blue-100 dark:bg-blue-900/30'
    },
    {
        title: 'Roles',
        description: 'Configure user roles and access levels',
        href: '/roles',
        icon: ShieldCheck,
        color: 'text-violet-600',
        bgColor: 'bg-violet-100 dark:bg-violet-900/30'
    },
    {
        title: 'Permissions',
        description: 'Define granular system permissions',
        href: '/permissions',
        icon: KeyRound,
        color: 'text-fuchsia-600',
        bgColor: 'bg-fuchsia-100 dark:bg-fuchsia-900/30'
    },
];

const filteredModules = computed(() => allModules);

const getIcon = (key: string) => {
    const k = key.toLowerCase();
    if (k.includes('pending') || k.includes('awaiting')) return Clock;
    if (k.includes('approved')) return CheckCircle;
    if (k.includes('purchase_request')) return BookPlus;
    if (k.includes('vendor')) return Users;
    if (k.includes('tender')) return FileText;
    if (k.includes('order')) return ShoppingCart;
    if (k.includes('approval')) return CheckSquare;
    return TrendingUp;
};

const getCardColor = (key: string) => {
    const k = key.toLowerCase();
    if (k.includes('pending')) return 'text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30';
    if (k.includes('approved')) return 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30';
    if (k.includes('total') || k.includes('my_')) return 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30';
    return 'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30';
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(value, key) in (props.summary || {})" :key="key"
                    class="group relative overflow-hidden rounded-2xl border border-sidebar-border/70 bg-card p-6 shadow-sm transition-all hover:shadow-md dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">
                                {{ key.replace(/_/g, ' ') }}
                            </p>
                            <p class="text-3xl font-bold tracking-tight">
                                {{ value }}
                            </p>
                        </div>
                        <div :class="['rounded-xl p-3 transition-transform group-hover:scale-110', getCardColor(key)]">
                            <component :is="getIcon(key)" class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-muted-foreground">
                        <TrendingUp class="mr-1 h-3 w-3 text-green-500" />
                        <span class="text-green-500 font-medium">Updated just now</span>
                    </div>
                </div>
            </div>

            <div v-if="!props.summary || Object.keys(props.summary).length === 0" class="mt-8 flex h-64 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-sidebar-border/70 bg-muted/30">
                <LayoutGrid class="h-12 w-12 text-muted-foreground/50 mb-4" />
                <p class="text-lg font-medium text-muted-foreground">No summary data available.</p>
                <p class="text-sm text-muted-foreground/70">Check back later for updates.</p>
            </div>

            <!-- Application Modules Section -->
            <div class="mt-8">
                <div class="mb-6 flex items-center justify-between">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-bold tracking-tight">Application Modules</h2>
                        <p class="text-sm text-muted-foreground">Quick access to all system features based on your permissions.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Link
                        v-for="module in filteredModules"
                        :key="module.title"
                        :href="module.href"
                        class="group flex flex-col justify-between rounded-2xl border border-sidebar-border/70 bg-card p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md dark:border-sidebar-border"
                    >
                        <div>
                            <div :class="['mb-4 inline-flex rounded-xl p-3 transition-colors group-hover:bg-opacity-80', module.bgColor]">
                                <component :is="module.icon" :class="['h-6 w-6', module.color]" />
                            </div>
                            <h3 class="font-bold text-lg mb-1 flex items-center gap-1 group-hover:text-primary transition-colors">
                                {{ module.title }}
                                <ArrowUpRight class="h-4 w-4 opacity-0 -translate-y-1 translate-x-1 transition-all group-hover:opacity-100 group-hover:translate-y-0 group-hover:translate-x-0" />
                            </h3>
                            <p class="text-sm text-muted-foreground line-clamp-2">
                                {{ module.description }}
                            </p>
                        </div>
                        <div class="mt-6 flex items-center text-xs font-semibold text-primary/70 uppercase tracking-widest group-hover:text-primary">
                            Open Module
                            <ChevronRight class="ml-1 h-3 w-3 transition-transform group-hover:translate-x-1" />
                        </div>
                    </Link>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">System Status</h3>
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            Operational
                        </span>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground text-xs">Last Sync</span>
                            <span class="font-medium text-xs">Just now</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground text-xs">Version</span>
                            <span class="font-medium text-xs">v1.0.5</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">User Information</h3>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xl font-bold">
                            {{ (page.props.auth as any)?.user?.name ? (page.props.auth as any).user.name.charAt(0) : '?' }}
                        </div>
                        <div>
                            <div class="font-medium text-lg">{{ (page.props.auth as any)?.user?.name || 'User' }}</div>
                            <div class="text-sm text-muted-foreground">Logged in as {{ (page.props.auth as any)?.isAdmin ? 'Administrator' : (((page.props.auth as any)?.roles && (page.props.auth as any).roles[0]) || 'User') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
