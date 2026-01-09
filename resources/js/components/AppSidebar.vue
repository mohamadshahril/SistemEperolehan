<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookPlus,
    LayoutGrid,
    MapPinCheck,
    Users,
    ShoppingCart,
    CheckSquare,
    Settings,
    Truck,
    BarChart3,
    FileText,
    Gavel,
    ShieldCheck,
    KeyRound,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const footerNavItems: NavItem[] = [];

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Purchase Requests',
        href: '/purchase-requests',
        icon: BookPlus,
    },
    {
        title: 'Approvals',
        href: '/approvals',
        icon: CheckSquare,
    },
    {
        title: 'Locations',
        href: '/locations',
        icon: MapPinCheck,
    },
    {
        title: 'Vendors',
        href: '/vendors',
        icon: Users,
    },
    {
        title: 'Purchase Orders',
        href: '/purchase-orders',
        icon: ShoppingCart,
    },
    {
        title: 'Tenders',
        href: '/tenders',
        icon: FileText,
    },
    {
        title: 'Tender Bids',
        href: '/tender-bids',
        icon: Gavel,
    },
    {
        title: 'Delivery Order',
        href: '/delivery-orders',
        icon: Truck,
    },
    {
        title: 'Delivery Report',
        href: '/delivery-reports',
        icon: BarChart3,
    },
    {
        title: 'Vots',
        href: '/vots',
        icon: Settings,
    },
    {
        title: 'Type Procurements',
        href: '/type-procurements',
        icon: Settings,
    },
    {
        title: 'Item Units',
        href: '/item-units',
        icon: Settings,
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: ShieldCheck,
    },
    {
        title: 'Permissions',
        href: '/permissions',
        icon: KeyRound,
    },
];

// Sidebar items
const filteredItems = computed(() => mainNavItems);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="filteredItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
