<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { lockList, getLockList, getJobResult, lockList_refresh } from '@/routes';
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"

import { Centrifuge } from 'centrifuge'


const page = usePage();

const locks_data = ref()

onMounted(async () => {

    locks_data.value = props.locks


    const centrifuge = new Centrifuge(props.centrifugo_listener, {
        token: props.token
    })

    const sub = centrifuge.newSubscription('api:get_lock_list-' + page.props.auth.user.id)

    //получение сообщений по веб.сокет
    sub.on('publication', (ctx: any) => {
        loading.value = false
        axios.post(lockList_refresh().url).then((response: any) => {
            console.log('get from centrifugo')
            locks_data.value = response.data
        })
            .catch((error: any) => {
                console.log(error);

            })
            .finally(() => {

            });
    })

    /*//подключен к ws серверу
    centrifuge.on('connected', function (ctx) {
        console.log('connected CENTR', ctx);
    })

    //процесс подключения к ws серверу
    centrifuge.on('connecting', function (ctx) {
        console.log('connecting CENTR', ctx);
    })*/

    centrifuge.on('error', function (ctx) {
        console.log('ERROR: ', ctx);
    })

    centrifuge.connect()
    sub.subscribe()
})



const props = defineProps({
    locks: {
        type: Object
    },

    token: {
        type: String
    },
    centrifugo_listener:
    {
        type: String,
        required: true,
    }

});


const loading = ref(false)
const job_id = ref('')

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Список замков',
        href: lockList().url,
    },
];






const getLocks = async () => {

    loading.value = true
    try {
        const response = await axios.post(getLockList().url, {
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })

        //   pollForResult(response.data.job_id)

    } catch (error: any) {
        console.error('Error:', error)
    } finally {
        // loading.value = false
    }

}



</script>




<template>

    <Head title="Список замков" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">

                <div class="m-7">
                    <Button @click="getLocks" variant="design" :disabled="loading">
                        <Spinner v-if="loading" />
                        Синхронизировать с TTlock
                    </Button>
                </div>

                <Table>
                    <TableCaption>Список ваших замков</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[100px]">
                                Ид
                            </TableHead>
                            <TableHead>Название</TableHead>
                            <TableHead>Имя</TableHead>
                            <TableHead>Заряд</TableHead>
                            <TableHead class="text-right">
                                Действие
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="lock in locks_data" :key="lock.id">
                            <TableCell class="font-medium">
                                {{ lock.lock_id }}
                            </TableCell>
                            <TableCell>{{ lock.lock_name }}</TableCell>
                            <TableCell>{{ lock.lock_alias }}</TableCell>
                            <TableCell>{{ lock.electric_quantity }}</TableCell>
                            <TableCell class="text-right">

                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
