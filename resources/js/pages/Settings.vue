<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, onMounted } from 'vue'
import { settings,  saveCredential } from '@/routes';
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
    credential: {
        type: Object
    },
    user: {
        type: Object
    },
});




const showPassword = ref(false)

const loadingTtlock = ref(false)

const credential_login = ref(props.credential?.login ?? "")
const credential_password = ref(props.credential?.password ?? "")


const msg = ref()
const error = ref(false)


const togglePassword = () => {
    showPassword.value = !showPassword.value
}

//const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')



const saveTtlockCredential = async () => {
    loadingTtlock.value = true

    try {
        // Axios POST request
        const response = await axios.post(saveCredential().url, {
            "email": credential_login.value,
            'password': credential_password.value
        })

        msg.value = response.data.msg
        error.value = !response.data.status



    } catch (error: any) {
        console.error('Error:', error)
    } finally {
        loadingTtlock.value = false
    }
}





</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Список замков" />

        <SettingsLayout>
            <div class="flex-1 px-6 py-8 w-full">
                <div
                    class="relative  p-4 flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border flex flex-col  items-center justify-center gap-4">
                    <div class="grid gap-3 mx-3 w-full">
                        <Label class="w-full min-w-[160px]">Учетная запись ttlock</Label>
                        <Input v-model="credential_login" class="w-full " type="email" placeholder="name@example.ru" />
                    </div>
                    <div class="grid w-full gap-3 mx-3 ">

                        <Label class="w-full min-w-[140px]">Пароль</Label>
                        <div class="flex">
                            <Input :type="showPassword ? 'text' : 'password'" class="" placeholder="пароль"
                                v-model="credential_password" />
                            <Button @click="togglePassword" variant="design">
                                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12" stroke="#ffffff" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M1 12C1 12 5 20 12 20C19 20 23 12 23 12" stroke="#ffffff" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" stroke="#ffffff" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </Button>
                        </div>
                    </div>
                    <div>
                        <Button variant="design" class="my-4" @click="saveTtlockCredential"
                            :disabled="loadingTtlock">Сохранить</Button>
                    </div>
                    <div>
                        <p class="break-all " :class="error ? 'text-red-500' : 'text-green-700'">{{ msg }}</p>
                    </div>
                </div>

            </div>




        </SettingsLayout>
    </AppLayout>
</template>
