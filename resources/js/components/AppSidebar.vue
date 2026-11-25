<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, LayoutGrid, Settings2, Users } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { index as calendarIndex, edit as calendarEdit } from '@/routes/calendar';
import { index as playersIndex } from '@/routes/players';
import type { NavItem } from '@/types';

const page = usePage();

const fullNavItems: NavItem[] = [
    {
        title: 'Calendrier',
        href: calendarIndex(),
        icon: CalendarDays,
    },
    {
        title: 'Tableau de bord',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Éditer les cases',
        href: calendarEdit(),
        icon: Settings2,
    },
    {
        title: 'Joueurs',
        href: playersIndex(),
        icon: Users,
    },
];

const playerNavItems: NavItem[] = [
    {
        title: 'Calendrier',
        href: calendarIndex(),
        icon: CalendarDays,
    },
];

// Les joueurs ne voient que le calendrier ; l'administration reste admin.
const mainNavItems = computed<NavItem[]>(() =>
    page.props.auth.user.role === 'admin' ? fullNavItems : playerNavItems,
);
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
