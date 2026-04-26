<script setup>
import AppContent from '@/components/AppContent.vue';
import { ref } from 'vue'
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import { Button } from '@/components/ui/button'
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Badge } from '@/components/ui/badge';

import { router } from '@inertiajs/vue3'
import { Menu, MessageCircle, Phone, Send, Bug, BookOpen, User, LogOut, Settings, ChevronDown } from 'lucide-vue-next'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { edit as editProfile } from '@/routes/profile';
import { cn } from '@/lib/utils'


import { usePage } from '@inertiajs/vue3';
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'


import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuSeparator,
    DropdownMenuShortcut,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'






const page = usePage();


const sidebarMobileOpen = ref(false)


withDefaults(defineProps(), {
    breadcrumbs: () => [],
});


const wizardPages = ['Step1', 'Step2', 'Step3', 'Step4', 'Step5']
const isWizardPage = wizardPages.includes(page.component)


// Поддержка: типы диалога
const supportDialogOpen = ref(false)
const supportDialogType = ref('support')

// Выход
const handleLogout = async () => {
    // Замените на ваш метод логаута
    // await base44.auth.logout()
    router.visit('/logout', { method: 'post' })
}

// Навигация
const navigate = (href) => {
    router.visit(href)
}

</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar v-if="!isWizardPage" v-model:mobile-open="sidebarMobileOpen" />

        <div  :class="!isWizardPage ? 'lg:ml-64' : ''">
            <header class="sticky top-0 z-30 h-16 bg-white/80 backdrop-blur-md border-b border-slate-200">
                <div class="h-full px-4 sm:px-6 flex items-center justify-between">

                    <!-- 🔘 Мобильная кнопка меню -->
                    <Button variant="ghost" size="icon" class="lg:hidden" @click="sidebarMobileOpen = true">
                        <Menu class="w-5 h-5" />
                        <span class="sr-only">Открыть меню</span>
                    </Button>


                    <div class="flex-1" />

                    <div class="flex items-center gap-3">

                        <!-- 💳 Тариф (только не в визарде) -->
                        <!--div v-if="!isWizardPage"
                            class="hidden md:flex items-center gap-3 px-3 py-2 border border-slate-200 rounded-lg">
                            <Badge class="bg-emerald-100 text-emerald-700 text-sm px-3 py-1">Free</Badge>
                            <Button size="sm" class="bg-indigo-600 hover:bg-indigo-700 h-8 text-xs"
                                @click="navigate('/billing')">
                                Изменить тариф
                            </Button>
                        </div-->

                        <!-- 💬 Поддержка -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="flex items-center gap-2">
                                    <MessageCircle class="w-4 h-4" />
                                    <span class="hidden sm:inline">Поддержка</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-64">
                                <div class="px-3 py-2 border-b">
                                    <p class="text-sm font-semibold text-slate-900">Служба поддержки</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Мы всегда на связи</p>
                                </div>

                                <DropdownMenuItem as-child class="py-2.5">
                                    <a href="tel:+74951234567" class="cursor-pointer flex items-center gap-3">
                                        <Phone class="w-4 h-4 text-slate-500" />
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium">+7 (495) 123-45-67</span>
                                            <span class="text-xs text-slate-500">Телефон поддержки</span>
                                        </div>
                                    </a>
                                </DropdownMenuItem>

                                <DropdownMenuSeparator />

                                <DropdownMenuItem as-child class="py-2.5">
                                    <a href="https://t.me/your_support_bot" target="_blank" rel="noopener noreferrer"
                                        class="cursor-pointer flex items-center gap-3">
                                        <Send class="w-4 h-4 text-slate-500" />
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium">Написать в поддержку</span>
                                            <span class="text-xs text-slate-500">Telegram бот</span>
                                        </div>
                                    </a>
                                </DropdownMenuItem>

                                <DropdownMenuItem @click="supportDialogType = 'bug'; supportDialogOpen = true"
                                    class="cursor-pointer py-2.5 flex items-center gap-3">
                                    <Bug class="w-4 h-4 text-slate-500" />
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">Сообщить об ошибке</span>
                                        <span class="text-xs text-slate-500">Форма обратной связи</span>
                                    </div>
                                </DropdownMenuItem>

                                <DropdownMenuSeparator />

                                <DropdownMenuItem as-child class="py-2.5">
                                    <a href="https://tilda.cc/ru/docs/" target="_blank" rel="noopener noreferrer"
                                        class="cursor-pointer flex items-center gap-3">
                                        <BookOpen class="w-4 h-4 text-slate-500" />
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium">База знаний</span>
                                            <span class="text-xs text-slate-500">Документация и гайды</span>
                                        </div>
                                    </a>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- 🔔 Уведомления (если используете Centrifugo) -->
                        <!-- <NotificationBell v-if="!isWizardPage" ... /> -->

                        <!-- 👤 Профиль пользователя -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="flex items-center gap-2">
                                    <Avatar class="w-8 h-8">
                                        <AvatarFallback class="bg-indigo-600 text-white text-sm">
                                            {{ $page.props.auth.user.name?.charAt(0) || 'U' }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span class="hidden sm:inline text-sm font-medium text-slate-700">
                                        {{ $page.props.auth.user.name || 'Пользователь' }}
                                    </span>
                                    <ChevronDown class="w-4 h-4 text-slate-400" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="center" class="w-64">
                                <DropdownMenuItem class="cursor-default hover:bg-transparent focus:bg-transparent">
                                    <User class="w-4 h-4 mr-2" />
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-900">{{ $page.props.auth.user.name ||
                                            'Пользователь' }}</p>
                                        <p class="text-xs text-slate-500">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child class="cursor-pointer">
                                    <div @click="navigate(editProfile().url)">
                                        <Settings class="w-4 h-4 mr-2" />
                                        Настройка аккаунта
                                    </div>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="handleLogout" class="text-red-600 cursor-pointer">
                                    <LogOut class="w-4 h-4 mr-2" />
                                    Выйти
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <div class="px-3 py-2.5 bg-slate-50">
                                    <div class="flex justify-center">
                                        <div class="border-2 border-indigo-600 rounded-lg px-3 py-1.5 bg-white">
                                            <p
                                                class="text-sm text-indigo-600 text-center font-mono font-bold tracking-wide">
                                                ID {{ $page.props.auth.user.id ?
                                                    String($page.props.auth.user.id).slice(-6).padStart(6, '0') : '000000'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </DropdownMenuContent>
                        </DropdownMenu>

                    </div>
                </div>
            </header>

            <AppContent variant="sidebar" class="">
                <slot />
            </AppContent>

        </div>
    </AppShell>
</template>
