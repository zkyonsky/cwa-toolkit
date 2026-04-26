<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, LayoutGrid, User, Building2, ClipboardList, PenTool, FileUp } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
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
import govs from '@/routes/govs';
import type { NavItem } from '@/types';
import { can } from '@/lib/can';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Assessments',
        href: "/assessments",
        icon: PenTool,
        permission: 'view-assessments',
    },
    {
        title: 'Manual',
        href: "/manual",
        icon: BookOpen,
    },
];

const footerNavItems: NavItem[] = [

    {
        title: 'Govs',
        href: govs.index(),
        icon: Building2,
        permission: 'view-govs',
    },
    {
        title: 'Assessees',
        href: "/assessees",
        icon: ClipboardList,
        permission: 'view-assessees',
    },
    {
        title: 'Users',
        href: '/users',
        icon: User,
        permission: 'view-users',
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: FolderGit2,
        permission: 'view-roles',
    },
    {
        title: 'Upload Data',
        href: '/upload-data',
        icon: FileUp,
        permission: 'view-users', // reusing view-users as proxy for admin
    },
];
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
