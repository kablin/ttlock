<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { lockList, getLockList,getJobResult } from '@/routes';
import { ref } from 'vue'
import { Button } from '@/components/ui/button';
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


const props = defineProps({
    locks: {
        type: Object
    },
});


const loading = ref(false)
const job_id = ref('')

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Список замков',
        href: lockList().url,
    },
];




const pollForResult = async (job_id) => {

    let result;
    while (!result || ! result.status ) {
        await new Promise(resolve => setTimeout(resolve, 5000)); // ждем 2 сек
        const response = await axios.post(getJobResult(job_id).url, {
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
        result = response.data;
        if (result.status ) {
           location.reload();
            break;
        }
    }
}



const getLocks = async () => {
    loading.value = true
    try {
        const response = await axios.post(getLockList().url, {
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
        pollForResult( response.data.job_id)

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
                    <Button @click="getLocks" :disabled="loading">Синхронизировать с TTlock</Button>
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
                        <TableRow v-for="lock in locks" :key="lock.id">
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
