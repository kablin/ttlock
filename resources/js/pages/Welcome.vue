<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
</script>

<template>

    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
            <!--nav class="flex items-center justify-end gap-4">
                Поддержка
            </nav-->
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">

                <div
                    class="relative -mb-px aspect-335/376 w-full shrink-0 overflow-hidden rounded-t-lg bg-[#fff2f2] lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:bg-[#1D0002]">



                </div>



                <div
                    class="flex-1  bg-white p-6 pb-12 text-[13px] leading-[20px]   lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] ">
                    <h1 class="mb-1 font-bold text-xl">Платформа управления умным доступом</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                        Подключи свои умные замки, настрой сценарии доступа и т. д
                    </p>

                    <div class="flex gap-4 mt-10">
                        <Button v-if="$page.props.auth.user" as-child variant="design">
                            <a :href="dashboard().url">Дашборд</a>
                        </Button>


                        <template v-else>

                            <Button as-child variant="design">
                                <a :href="login().url">Войти</a>
                            </Button>


                            <Button as-child variant="design_outline">
                                <a :href="register().url">Регистрация</a>
                            </Button>

                            <Button as-child variant="design_outline">
                                <a :href="register().url">Демо доступ</a>
                            </Button>
                        </template>

                    </div>

                </div>

            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
