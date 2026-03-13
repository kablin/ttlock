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


const wizardPages = ['/setup/wizard/step1', '/setup/wizard/step2', '/setup/wizard/step3', '/setup/wizard/step4', '/setup/wizard/step5']
const isWizardPage = wizardPages.includes(page.url)


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
        <AppContent variant="sidebar" class="" :class="!isWizardPage ? 'lg:ml-64' : ''">


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
                        <div v-if="!isWizardPage"
                            class="hidden md:flex items-center gap-3 px-3 py-2 border border-slate-200 rounded-lg">
                            <Badge class="bg-emerald-100 text-emerald-700 text-sm px-3 py-1">Free</Badge>
                            <Button size="sm" class="bg-indigo-600 hover:bg-indigo-700 h-8 text-xs"
                                @click="navigate('/billing')">
                                Изменить тариф
                            </Button>
                        </div>

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



            <!--header
                class="sticky top-0 z-30 bg-white/80 backdrop-blur-md  flex py-3 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
                <div class="flex items-center gap-2 w-full">


                    <Button variant="ghost" size="icon" class="lg:hidden" @click="sidebarMobileOpen = true">
                        <Menu class="w-5 h-5" />
                        <span class="sr-only">Открыть меню</span>
                    </Button>

                    <template v-if="breadcrumbs && breadcrumbs.length > 0">
                        <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    </template>


<div class="ml-auto grow flex items-center gap-4 justify-end">


    <Card class="rounded-none py-1 gap-0 shadow-xs">
        <CardHeader>

            <CardDescription>
                Кол-во ключей
            </CardDescription>
        </CardHeader>
        <CardContent class="flex items-center">
            <p class="text-md  lg:text-lg mr-3">10 | 500</p>
            <Badge variant="secondary" class="rounded-3xl hidden lg:block">
                <p class="text-xs  text-gray-800">Остаток | Общее количество</p>
            </Badge>
        </CardContent>
    </Card>



    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <div class="cursor-pointer flex items-center gap-4 border-gray-50 border-1 p-3">

                <svg width="30" height="30" viewBox="0 0 33 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M29.6667 13.6334C29.15 8.33336 26.0167 0 16.3334 0C6.65004 0 3.51668 8.33336 3.00004 13.6334C1.18754 14.3212 -0.0080065 16.0614 4.03712e-05 18V20.3334C4.03712e-05 22.9107 2.08942 25 4.66668 25C7.24402 25 9.3334 22.9106 9.3334 20.3334V18C9.32496 16.1032 8.1734 14.3988 6.41668 13.6834C6.75004 10.6166 8.3834 3.33336 16.3334 3.33336C24.2834 3.33336 25.9 10.6166 26.2334 13.6834C24.4803 14.4004 23.3345 16.1059 23.3334 18V20.3334C23.337 21.2115 23.5873 22.0709 24.0559 22.8135C24.5245 23.5561 25.1924 24.1521 25.9834 24.5334C25.2834 25.85 23.5 27.6334 19.1167 28.1666C18.2405 26.8362 16.5453 26.3116 15.0707 26.9148C13.5963 27.518 12.7547 29.0802 13.0622 30.6434C13.3697 32.2065 14.7403 33.3334 16.3334 33.3334C16.9507 33.3299 17.5549 33.1551 18.0787 32.8285C18.6025 32.5018 19.0253 32.0362 19.3 31.4834C26.45 30.6666 28.7334 26.9834 29.45 24.8166C31.3889 24.1886 32.6929 22.3713 32.6667 20.3334V18C32.6747 16.0614 31.4792 14.3212 29.6667 13.6334ZM6.00004 20.3334C6.00004 21.0697 5.40309 21.6666 4.66668 21.6666C3.93027 21.6666 3.3334 21.0698 3.3334 20.3334V18C3.33205 17.824 3.36555 17.6496 3.43195 17.4866C3.49835 17.3237 3.59635 17.1755 3.72029 17.0506C3.84423 16.9257 3.99167 16.8266 4.15411 16.7589C4.31654 16.6913 4.49076 16.6564 4.66672 16.6564C4.84268 16.6564 5.0169 16.6913 5.17933 16.7589C5.34177 16.8266 5.48921 16.9257 5.61315 17.0506C5.73709 17.1755 5.83509 17.3237 5.90149 17.4866C5.96789 17.6496 6.00139 17.824 6.00004 18V20.3334ZM26.6667 18C26.6667 17.2636 27.2636 16.6666 28 16.6666C28.7364 16.6666 29.3334 17.2636 29.3334 18V20.3334C29.3334 21.0697 28.7364 21.6666 28 21.6666C27.2636 21.6666 26.6667 21.0698 26.6667 20.3334V18Z"
                        fill="black" />
                </svg>

                <span class="hidden lg:block">Поддержка</span>
                <svg width="30" height="16" viewBox="0 0 30 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M14.5522 15.8022C14.2204 15.8037 13.902 15.6715 13.6688 15.4355L0.335487 2.10215C-0.123444 1.60964 -0.109902 0.842138 0.366118 0.366118C0.842138 -0.109902 1.60964 -0.123444 2.10215 0.335487L14.5522 12.7855L27.0022 0.335487C27.4947 -0.123444 28.2622 -0.109902 28.7382 0.366118C29.2142 0.842138 29.2277 1.60964 28.7688 2.10215L15.4355 15.4355C15.2023 15.6715 14.8839 15.8037 14.5522 15.8022Z"
                        fill="black" />
                </svg>

            </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56" align="start">
            <DropdownMenuLabel>Поддержка</DropdownMenuLabel>
            <DropdownMenuGroup>
                <DropdownMenuItem>
                    <a href="https://t.me/Renty_hosts_bot"
                        class="flex gap-4 rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.78754 14.0196C5.83131 14.0344 5.87549 14.0448 5.91963 14.0512C5.96777 14.1644 6.02996 14.3107 6.10252 14.4818C6.27959 14.8994 6.51818 15.4643 6.76446 16.0535C7.2667 17.2552 7.77332 18.4939 7.88521 18.8485C8.02372 19.2868 8.17013 19.5848 8.32996 19.7883C8.4126 19.8935 8.50819 19.9853 8.62003 20.0549C8.67633 20.0899 8.7358 20.1186 8.79788 20.14C8.80062 20.141 8.80335 20.1419 8.80608 20.1428C9.1261 20.2636 9.41786 20.2133 9.60053 20.1518C9.69827 20.1188 9.77735 20.0791 9.8334 20.0469C9.86198 20.0304 9.88612 20.0151 9.90538 20.0021L9.90992 19.9991L12.7361 18.2366L16.0007 20.7394C16.0488 20.7763 16.1014 20.8073 16.157 20.8316C16.5492 21.0027 16.929 21.0624 17.2862 21.0136C17.6429 20.9649 17.926 20.8151 18.1368 20.6464C18.3432 20.4813 18.4832 20.2963 18.5703 20.1589C18.6148 20.0887 18.6482 20.0266 18.6718 19.9791C18.6836 19.9552 18.6931 19.9346 18.7005 19.9181L18.7099 19.8963L18.7135 19.8877L18.715 19.8841L18.7156 19.8824L18.7163 19.8808C18.7334 19.8379 18.7466 19.7935 18.7556 19.7482L21.7358 4.72274C21.7453 4.67469 21.7501 4.62581 21.7501 4.57682C21.7501 4.13681 21.5843 3.71841 21.1945 3.46452C20.8613 3.24752 20.4901 3.23818 20.2556 3.25598C20.0025 3.27519 19.7688 3.33766 19.612 3.38757C19.5304 3.41355 19.4619 3.43861 19.4126 3.45773C19.3878 3.46734 19.3675 3.47559 19.3523 3.48188L19.341 3.48666L2.62725 10.0432L2.62509 10.044C2.61444 10.0479 2.60076 10.053 2.58451 10.0593C2.55215 10.0719 2.50878 10.0896 2.45813 10.1126C2.35935 10.1574 2.22077 10.2273 2.07856 10.3247C1.85137 10.4803 1.32888 10.9064 1.41686 11.6097C1.48705 12.1708 1.87143 12.5154 2.10562 12.6811C2.23421 12.7721 2.35638 12.8371 2.44535 12.8795C2.48662 12.8991 2.57232 12.9339 2.6095 12.9491L2.61889 12.9529L5.78754 14.0196ZM19.9259 4.86786L19.9236 4.86888C19.9152 4.8725 19.9069 4.87596 19.8984 4.87928L3.1644 11.4438C3.15566 11.4472 3.14686 11.4505 3.138 11.4536L3.12869 11.4571C3.11798 11.4613 3.09996 11.4686 3.07734 11.4788C3.06451 11.4846 3.05112 11.491 3.03747 11.4978C3.05622 11.5084 3.07417 11.5175 3.09012 11.5251C3.10543 11.5324 3.11711 11.5374 3.1235 11.54L6.26613 12.598C6.32365 12.6174 6.37727 12.643 6.42649 12.674L16.8033 6.59948L16.813 6.59374C16.8205 6.58927 16.8305 6.58353 16.8424 6.5768C16.866 6.56345 16.8984 6.54568 16.937 6.52603C17.009 6.48938 17.1243 6.43497 17.2541 6.39485C17.3444 6.36692 17.6109 6.28823 17.899 6.38064C18.0768 6.43767 18.2609 6.56028 18.3807 6.76798C18.4401 6.87117 18.4718 6.97483 18.4872 7.06972C18.528 7.2192 18.5215 7.36681 18.4896 7.49424C18.4208 7.76875 18.228 7.98287 18.0525 8.14665C17.9021 8.28706 15.9567 10.1629 14.0376 12.0147C13.0805 12.9381 12.1333 13.8525 11.4252 14.5359L10.9602 14.9849L16.8321 19.4867C16.9668 19.5349 17.0464 19.5325 17.0832 19.5274C17.1271 19.5214 17.163 19.5045 17.1997 19.4752C17.2407 19.4424 17.2766 19.398 17.3034 19.3557L17.3045 19.354L20.195 4.78102C20.1521 4.79133 20.1087 4.80361 20.0669 4.81691C20.0196 4.83198 19.9805 4.84634 19.9547 4.85637C19.9418 4.86134 19.9326 4.86511 19.9276 4.86719L19.9259 4.86786ZM11.4646 17.2618L10.2931 16.3636L10.0093 18.1693L11.4646 17.2618ZM9.21846 14.5814L10.3834 13.4567C11.0915 12.7732 12.0389 11.8588 12.9961 10.9352L13.9686 9.997L7.44853 13.8138L7.48351 13.8963C7.66121 14.3154 7.90087 14.8827 8.14845 15.4751C8.33358 15.918 8.52717 16.3844 8.70349 16.8162L8.98653 15.0158C9.01381 14.8422 9.09861 14.692 9.21846 14.5814Z"
                                fill="#000000" />
                        </svg>
                        <span class="hidden lg:block">Перейти в бот техподдержки</span>
                    </a>

                </DropdownMenuItem>
                <DropdownMenuItem>
                    <a href="tel:89052333333"
                        class="flex gap-4 rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <Phone />
                        <span class="hidden lg:block">89052333333</span>
                    </a>

                </DropdownMenuItem>


            </DropdownMenuGroup>


        </DropdownMenuContent>
    </DropdownMenu>



    <button type="button" class="relative p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">

        <svg width="28" height="28" viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M30.1776 22.4667C29.4372 21.8333 29.0109 20.9077 29.0109 19.9333V11.9C29.0109 5.31667 23.0443 0 15.6776 0C8.31095 0 2.34428 5.31667 2.34428 11.9V19.9333C2.34434 20.9077 1.91807 21.8333 1.17761 22.4667C-1.33905 24.7167 0.44428 28.55 4.01095 28.55H10.3443C11.1523 30.8007 13.2863 32.3018 15.6776 32.3018C18.0689 32.3018 20.2029 30.8007 21.0109 28.55H27.3443C30.9109 28.55 32.6943 24.7167 30.1776 22.4667ZM15.6776 29.8C14.6856 29.7963 13.7508 29.335 13.1443 28.55H18.1443C17.557 29.3226 16.6479 29.7833 15.6776 29.8ZM27.362 26.05C27.9679 26.1049 28.5448 25.78 28.812 25.2333C28.9417 24.9041 28.8236 24.5289 28.5286 24.3333C27.2729 23.2132 26.5469 21.616 26.5286 19.9333V11.9C26.5286 6.71667 21.662 2.5 15.6953 2.5C9.72863 2.5 4.86197 6.71667 4.86197 11.9V19.9333C4.84369 21.616 4.1177 23.2132 2.86197 24.3333C2.56083 24.524 2.43543 24.9002 2.56197 25.2333C2.82913 25.78 3.40603 26.1049 4.01197 26.05H27.362Z"
                fill="black" />
        </svg>


    </button>




</div>




</div>
</header-->







            <slot />
        </AppContent>
    </AppShell>
</template>
