<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { tarifs, webhook_pay } from '@/routes';
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { Label } from '@/components/ui/label';
import { CheckIcon, CheckCircle2Icon } from 'lucide-vue-next'
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { router } from '@inertiajs/vue3'

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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'


import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert'


const breadcrumbs = [
    {
        title: 'Тарифы',
        href: tarifs().url,
    },
];


const successSchow = ref(false)
const successText = ref('')

const selectedTarif = ref(1)


const props = defineProps({
    code_packet: {
        type: Object
    },
    tarifes: {
        type: Object
    },
});


const tarifPay = () => {
    axios.post(webhook_pay().url, { tarif_id: selectedTarif.value }).then((response) => {
        let data = response.data
        if (data.status) {
            window.location.href = data.url 
        }
    })
        .catch((error) => {
            console.log(error);

        })
        .finally(() => {

        });

    /* successSchow.value = true
     successText.value = 'Успешно. Тариф подключен'*/
    return true
}



</script>




<template>

    <Head title="Список замков" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="mx-10 p-4">
            <Alert v-if="successSchow" @click="successSchow = false" class="flex items-center gap-4">
                <CheckCircle2Icon :size="16" />
                <AlertTitle class="text-green-500">{{ successText }}</AlertTitle>

            </Alert>
        </div>


        <div class="flex  gap-4 justify-center p-4">

            <Card class="rounded-none py-3 gap-0 shadow-xs min-w-6/12 md:mt-13 ">
                <CardHeader>

                    <CardTitle class="">
                        Осталось ключей: {{ code_packet[0].count }}
                    </CardTitle>

                    <CardDescription>
                        до {{ code_packet['end'] }} {{ new Date(code_packet[0].end).toLocaleString("ru-RU") }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex gap-5  mt-10 flex-col items-center justify-between">

                    <Label for="name-1" class="self-start">Выберите тариф:</Label>

                    <Select v-model="selectedTarif">
                        <SelectTrigger>
                            <SelectValue placeholder="Выберите тариф" />
                        </SelectTrigger>
                        <SelectContent class="w-[var(--reka-select-trigger-width)]">
                            <SelectItem v-for="tarif in tarifes" :key="tarif.id" :value="tarif.id">
                                {{ tarif.name }} - {{ tarif.keys }} ключей на 1 год / {{ tarif.price }}₽
                            </SelectItem>

                        </SelectContent>
                    </Select>




                    <CardAction class="flex mx-auto w-full">
                        <Button @click="tarifPay" variant="design" class="w-full">Оплатить</Button>

                    </CardAction>
                </CardContent>
            </Card>



        </div>
    </AppLayout>
</template>
