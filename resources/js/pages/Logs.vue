<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { lockevents, lockevents_page } from '@/routes';
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

import { CheckIcon, } from 'lucide-vue-next'

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


import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,

} from '@/components/ui/pagination'



const page = usePage();
const logList = ref()

onMounted(async () => {

    goToLogPage(1)
})


const breadcrumbs = [
    {
        title: 'Лог',
        href: lockevents().url,
    },
];



const goToLogPage = async (page) => {
    axios.post(lockevents_page().url, { 'page': page, }).then((response) => {
        logList.value = response.data.log_events
    })
        .catch((error) => {
            console.log(error);

        })
        .finally(() => {

        });
    return true
}



</script>




<template>

    <Head title="Список замков" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 px-6 py-8 w-full">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">


                <template v-if="logList && logList.data?.length">

                    <Table class="mt-2">

                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    Ид
                                </TableHead>
                                <TableHead>Замок</TableHead>
                                <TableHead>Тип события</TableHead>
                                <TableHead>Тип</TableHead>
                                <TableHead>Успех с</TableHead>
                                <TableHead>Пользователь</TableHead>
                                <TableHead>Код</TableHead>
                                <TableHead>
                                    Дата
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in logList.data" :key="log.id">
                                <TableCell class="font-medium">
                                    {{ log.id }}
                                </TableCell>
                                <TableCell>{{ log.lock_id }}</TableCell>
                                <TableCell>{{ log.record_type_from_lock }}</TableCell>
                                <TableCell>{{ log.record_type }}</TableCell>
                                <TableCell>
                                    <template v-if="log.success">
                                        <svg width="19" height="14" viewBox="0 0 19 14" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M5.94631 13.786C5.59988 13.7858 5.26421 13.6607 4.99631 13.4319L0.516307 9.60895C-0.0962757 9.05461 -0.174597 8.09333 0.33954 7.43939C0.853678 6.78544 1.77384 6.67595 2.41631 7.19228L5.88631 10.1506L15.8863 0.556867C16.2622 0.091317 16.8633 -0.104683 17.4269 0.0545234C17.9905 0.213729 18.4142 0.699197 18.5128 1.29877C18.6114 1.89834 18.3669 2.50301 17.8863 2.84853L6.96631 13.3694C6.69 13.6391 6.32496 13.7882 5.94631 13.786Z"
                                                fill="#129F0D" />
                                        </svg>
                                    </template>
                                </TableCell>
                                <TableCell>{{ log.username }}</TableCell>
                                <TableCell>{{ log.keyboard_pwd }} </TableCell>
                                <TableCell>{{ log.created_at }} </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>


                    <Pagination class="my-4 self-end" v-model:page="logList.current_page"
                        :items-per-page="logList.per_page" :total="logList.total" :default-page="logList.current_page">
                        <PaginationContent>
                            <template v-for="(item, index) in logList.links" :key="index">
                                <PaginationPrevious v-if="index == 0" @click.prevent="goToLogPage(item.page)" />
                                <PaginationNext v-else-if="index == (logList.links.length - 1)"
                                    @click.prevent="goToLogPage(item.page)" />
                                <PaginationItem
                                    v-else-if="(logList.current_page - 2 <= item.page) && (logList.current_page + 2 >= item.page)"
                                    :value="item.page" :is-active="item.page === logList.current_page"
                                    @click.prevent="goToLogPage(item.page)">
                                    {{ item.page }}
                                </PaginationItem>
                            </template>
                        </PaginationContent>
                    </Pagination>

                </template>

            </div>
        </div>
    </AppLayout>
</template>
