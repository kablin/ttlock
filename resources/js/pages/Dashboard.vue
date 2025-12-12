<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue'
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-vue-next'
import { cn } from "@/lib/utils"

import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'


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
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'


const breadcrumbs = [
    {
        title: 'Информация',
        href: dashboard().url,
    },
];


const props = defineProps({
    rents: {
        type: Object
    },


});

const open = ref(false)


const selRent = ref()

const selectedRent = computed(() =>
    props.rents.find(rent => rent.id === selRent.value),
)
function selectRent(selectedValue) {
    selRent.value = selectedValue === selRent.value ? '' : selectedValue
    open.value = false
}


const selectedLock = ref();

const selectLock = (lock) => {
   selectedLock.value = lock
}



</script>

<template>

    <Head title="Информация" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">

                <Card class="rounded-none py-3 gap-0 shadow-xs">
                    <CardHeader>
                        <CardDescription>
                            Интеграция с PMS | CRM
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center">
                        <p class="text-lg mr-3 font-bold">RealtyCalendar</p>
                    </CardContent>
                </Card>



                <Card class="rounded-none py-3 gap-0 shadow-xs">
                    <CardHeader>
                        <CardDescription>
                            Замки | объекты
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center justify-between">
                        <p class="text-lg mr-3 font-bold">10 | 15</p>
                        <CardAction>
                            <Button variant="design">Купить замки</Button>

                        </CardAction>
                    </CardContent>
                </Card>


                <Card class="rounded-none py-3 gap-0 shadow-xs">
                    <CardHeader>
                        <CardDescription>
                            Мой тариф
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center justify-between">
                        <p class="text-lg mr-3 font-bold">Free</p>
                        <CardAction>
                            <Button variant="design">Изменить тариф</Button>

                        </CardAction>
                    </CardContent>
                </Card>
            </div>

            <p class="text-2xl font-bold">Быстрые действия с замком</p>




            <Popover v-model:open="open" class="">
                <PopoverTrigger as-child>
                    <Button variant="outline" role="combobox" :aria-expanded="open" class=" justify-between">
                        {{ selectedRent?.name || "Выберите объект..." }}
                        <ChevronsUpDownIcon class="opacity-50" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-[var(--reka-popover-trigger-width)] p-0" :side-offset="5" align="start">

                    <Command class="w-full">
                        <CommandInput class="h-9" placeholder="Выбор объекта..." />
                        <CommandList>
                            <CommandEmpty>Объектов не найдено.</CommandEmpty>
                            <CommandGroup>
                                <CommandItem v-for="rent in rents" :key="rent.id" :value="rent.id" @select="(ev) => {
                                    selectRent(ev.detail.value)
                                }">
                                    {{ rent.name }}
                                    <CheckIcon :class="cn(
                                        'ml-auto',
                                        selRent === rent.id ? 'opacity-100' : 'opacity-0',
                                    )" />
                                </CommandItem>
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>


            <div v-if="selectedRent">

                <div class="grid   gap-3 grid-cols-[max-content_1fr]">

                    <p class="font-bold me-5">Объект:</p>
                    <p class="  text-gray-500">{{ selectedRent?.name }}</p>
                    <p class="font-bold me-6"> Описание:</p>
                    <p class="  text-gray-500">{{ selectedRent?.description }}</p>

                </div>

                <div v-if="selectedRent.locks.length" class="mt-5 font-bold text-center"> Замки</div>
{{ selectedLock }}
                <Table v-if="selectedRent.locks.length" class="mt-2">
                    
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
                        <TableRow   :class="{ 'border-2   bg-green-500 hover:bg-green-500': lock.id == selectedLock.id }"  v-for="lock in selectedRent.locks" @click="selectLock(lock)" :key="lock.id">
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

                <div v-else class="text-center"> К данному объекту не привязан ни один замок</div>



            </div>








            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <Card class="rounded-none  py-3 gap-0 shadow-xs">
                    <CardHeader class="">
                        <CardDescription>
                            Выдать ключ
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center justify-end">
                        <Button variant="design_outline">Выдать</Button>
                    </CardContent>
                </Card>

                <Card class="rounded-none py-3 gap-0 shadow-xs">
                    <CardHeader>
                        <CardDescription>
                            Открыть замок
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center justify-end">
                        <Button variant="design_outline">Открыть</Button>
                    </CardContent>
                </Card>


                <Card class="rounded-none py-3 gap-0 shadow-xs">
                    <CardHeader>
                        <CardDescription>
                            Добавить замок | объект
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center justify-end">
                        <Button variant="design_outline">Добавить</Button>
                    </CardContent>
                </Card>


            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
