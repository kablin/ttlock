<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { lockList } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
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


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Список замков',
        href: lockList().url,
    },
];
</script>

<template>

    <Head title="Список замков" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
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
