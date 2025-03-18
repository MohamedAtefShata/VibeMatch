<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Folder, LayoutGrid, Music, User, Users, Settings } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

// Props to determine if the user is an admin
interface Props {
    isAdmin?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isAdmin: false
});

// User navigation items
const userNavItems = computed<NavItem[]>(() => [
    {
        title: 'Song Recommendation',
        href: '/dashboard',
        icon: Music,
    },
    {
        title: 'Personalized',
        href: '/personalized',
        icon: User,
    },
]);

// Admin navigation items
const adminNavItems = computed<NavItem[]>(() => [
    {
        title: 'Manage Songs',
        href: '/admin/songs',
        icon: Music,
    },
    {
        title: 'Manage Users',
        href: '/admin/users',
        icon: Users,
    },
]);

// Use the appropriate navigation items based on user role
const mainNavItems = computed<NavItem[]>(() =>
    props.isAdmin ? adminNavItems.value : userNavItems.value
);

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/MohamedAtefShata/VibeMatch',
        icon: Folder,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <!-- Display appropriate navigation based on user role -->
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
