<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard, lockList, groups, wizard_step1, lockevents, objects2, tarifs } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Дашборд',
        href: dashboard(),
        svg: `<img width="25px" src="/images/icons/Document.png">`,
    },


    {
        title: 'Тариф и оплата',
        href: tarifs(),
         svg: `<img width="25px" src="/images/icons/hugeicons_payment-02.png">`,
    },


    {
        title: 'Управление Пространствами',
        href: groups(),
        svg: `<img width="25px" src="/images/icons/Keyboard-Close.png">`,
    },


    {
        title: 'Управление замками',
        href: objects2(),
        svg: `<img width="25px" src="/images/icons/Click.png">`,
    },


    {
        title: 'Список замков',
        href: lockList(),
        svg: `<img width="25px" src="/images/icons/Unlock.png">`,
    },


    {
        title: 'Логи',
        href: lockevents(),
        icon: LayoutGrid,
    },

    {
        title: 'Настройки',
        href: editProfile(),
        icon: LayoutGrid,
    },

    {
        title: 'Мастер',
        href: wizard_step1(),
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
