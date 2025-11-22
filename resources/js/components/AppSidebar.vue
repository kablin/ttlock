<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard, lockList,rents_objects, wizard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Информация',
        href: dashboard(),
        icon: LayoutGrid,
    },

    {
        title: 'Список замков',
        href: lockList(),
        icon: LayoutGrid,
    },


     {
        title: 'Список объектов',
        href: rents_objects(),
        icon: LayoutGrid,
    },

    {
        title: 'Настройки',
        href: editProfile(),
        icon: LayoutGrid,
    },

     {
        title: 'Мастер',
        href: wizard(),
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [
    /*  {
          title: 'Github Repo',
          href: 'https://github.com/laravel/vue-starter-kit',
          icon: Folder,
      },
      {
          title: 'Documentation',
          href: 'https://laravel.com/docs/starter-kits#vue',
          icon: BookOpen,
      },*/
];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
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
