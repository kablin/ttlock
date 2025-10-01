<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { ref } from 'vue'
import { refreshToken, getToken } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Обновить токен',
        href: refreshToken().url,
    },
];

const loading = ref(false)

const token = ref()

//const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

const handleSubmit = async () => {
    loading.value = true


    try {
        // Axios POST request
        const response = await axios.post(getToken().url, {

        }, {
            headers: {
          //      'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',

            }
        })
        console.log('Response:', response.data)
        token.value = response.data.token

    } catch (error: any) {
        console.error('Error:', error)
    } finally {
        loading.value = false
    }
}


</script>

<template>

    <Head title="Список замков" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border flex flex-col items-center justify-center gap-4">
                <div>
                    <Label>Внимание! Токен будет показан только один раз</Label>
                </div>
                <div>
                    <Button @click="handleSubmit" :disabled="loading">Обновить токен</Button>
                </div>
                <div>
                    <p>{{ token }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
