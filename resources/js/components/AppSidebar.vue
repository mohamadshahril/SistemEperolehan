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
import { Link, usePage } from '@inertiajs/vue3';
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
    ShieldCheck,
    KeyRound,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const footerNavItems: NavItem[] = [];

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
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

// Permission-gated sidebar
const page = usePage();
const filteredItems = computed(() => {
    const auth: any = page.props?.auth || {};
    const can: Record<string, boolean> = auth?.can || {};
    const isAdmin: boolean = !!auth?.isAdmin;

    // Helper to check permission or admin
    const allow = (perm?: string) => isAdmin || (!!perm && !!can[perm]);

    return mainNavItems.filter((item) => {
        switch (item.title) {
            case 'Dashboard':
                return true;
            case 'Purchase Requests':
                // Visible to Admin or users who can create PR (e.g., Staff). Managers typically shouldn't see this.
                return allow('create purchase requests');
            case 'Approvals':
                return allow('view approvals');
            case 'Locations':
                return allow('view locations');
            case 'Vendors':
                return allow('view vendors');
            case 'Purchase Orders':
                return allow('view purchase orders');
            case 'Tenders':
                return allow('view tenders');
            case 'Delivery Order':
                return allow('view delivery orders');
            case 'Delivery Report':
                return allow('view delivery reports');
            case 'Vots':
                return allow('view vots');
            case 'Type Procurements':
                return allow('view type procurements');
            case 'Item Units':
                return allow('view item units');
            case 'Users':
                // Only Admin or manage users should see settings menus
                return allow('manage users');
            case 'Roles':
                return allow('manage roles');
            case 'Permissions':
                return allow('manage permissions');
            default:
                return false;
        }
    });
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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
