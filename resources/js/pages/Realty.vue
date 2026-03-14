<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, onMounted } from 'vue'
import { settings, refreshToken } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Настройки',
        href: settings().url,
    },
];

const props = defineProps({

    user: {
        type: Object
    },
});


const loading = ref(false)
const realty_key = ref()



const handleSubmit = async () => {
    loading.value = true
    try {
        // Axios POST request
        const response = await axios.post(refreshToken().url, {
        }, {
            headers: {
                //      'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            }
        })
        realty_key.value = response.data.token
    } catch (error: any) {
        console.error('Error:', error)
    } finally {
        loading.value = false
    }
}







onMounted(() => {
    realty_key.value = props.user.realty_key
})



const copied = ref(false) // Состояние копирования



const copyToClipboard = async () => {
    if (!realty_key.value) return

    try {
        await navigator.clipboard.writeText(realty_key.value)
        copied.value = true

        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (error) {
        console.error('Ошибка копирования:', error)
        alert('Не удалось скопировать ключ')
    }
}


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Список замков" />

        <SettingsLayout>

            <div class="flex-1 px-6 py-8  w-full">
                <div
                    class="relative  p-4 flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border flex flex-col  gap-4">
                    <div>
                        <Label> Токен доступа к API</Label>
                    </div>

                    <!--div>
                        <Button variant="design" @click="handleSubmit" :disabled="loading">Обновить токен</Button>
                    </div-->
                    <div>
                        <p class="break-all">{{ realty_key }}</p>
                    </div>
                    <div>
                        <Button variant="design" @click="copyToClipboard" :disabled="!realty_key || copied"
                            class="whitespace-nowrap">
                            <template v-if="!copied">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Скопировать
                            </template>
                            <template v-else>
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Скопировано!
                            </template>
                        </Button>
                    </div>
                </div>
            </div>

        </SettingsLayout>
    </AppLayout>
</template>
