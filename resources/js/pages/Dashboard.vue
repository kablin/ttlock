<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, openLock, pincodes_list, getCodesList, deleteKey, addCodeToLock, pincodes_page } from '@/routes';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue'
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { CheckIcon, ChevronsUpDownIcon, KeyRound, CircleX, LockKeyholeOpen } from 'lucide-vue-next'
import { cn } from "@/lib/utils"
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { Centrifuge } from 'centrifuge'
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { buttonVariants } from '@/components/ui/button'

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
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'


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

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,

} from '@/components/ui/pagination'




import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'


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
    centrifugo_listener:
    {
        type: String,
        required: true,
    },
    token: {
        type: String
    },

});

const open = ref(false)
const isOpenLockDialogOpen = ref(false)
const waitApiOpenLock = ref(false)
const waitApiDeleteKey = ref(false)
const waitApiSyncKeys = ref(false)
const waitApiAddKey = ref(false)
const isDeleteDialogOpen = ref(false)

const isAddKeyDialogOpen = ref(false)



const page = usePage();
const lockMessage = ref('')
const lockTitle = ref('')

const selRent = ref()

const keyList = ref()


const selectedLock = ref();
const selectedKey = ref();



onMounted(async () => {

    const centrifuge = new Centrifuge(props.centrifugo_listener, {
        token: props.token
    })

    const sub = centrifuge.newSubscription('api:open_lock-' + page.props.auth.user.id)
    const sub_codes = centrifuge.newSubscription('api:get_codes_list-' + page.props.auth.user.id)
    const sub_delkey = centrifuge.newSubscription('api:delete_code_from_lock-' + page.props.auth.user.id)

    const sub_addkey = centrifuge.newSubscription('api:add_code_to_lock-' + page.props.auth.user.id)


    //получение сообщений по веб.сокет
    sub.on('publication', (ctx) => {
        // console.log(ctx)
        waitApiOpenLock.value = false
        isOpenLockDialogOpen.value = true
        lockTitle.value = "Замок открыт"
        lockMessage.value = ctx?.data?.msg
    })



    //получение сообщений по веб.сокет
    sub_codes.on('publication', (ctx) => {
        refreshKeysList(selectedLock.value)
        waitApiSyncKeys.value = false
        isOpenLockDialogOpen.value = true
        lockTitle.value = "Ключи синхронизированы"
        lockMessage.value = ctx?.data?.msg
    })



    sub_delkey.on('publication', (ctx) => {
        console.log(ctx)
        refreshKeysList(selectedLock.value)
        waitApiDeleteKey.value = false
        isOpenLockDialogOpen.value = true
        lockTitle.value = "Ключ удален"
        lockMessage.value = ctx?.data?.msg

    })


    sub_addkey.on('publication', (ctx) => {

        refreshKeysList(selectedLock.value)
        waitApiAddKey.value = false
        isOpenLockDialogOpen.value = true
        lockTitle.value = "Ключ добавлен"
        lockMessage.value = ctx?.data?.msg

    })


    centrifuge.on('error', function (ctx) {
        console.log('ERROR: ', ctx);
        waitApiOpenLock.value = false
        waitApiSyncKeys.value = false
        waitApiDeleteKey.value = false
        waitApiAddKey.value = false
        isOpenLockDialogOpen.value = true
        lockTitle.value = "Ошибка"
        lockMessage.value = 'Ошибка'
    })

    centrifuge.connect()
    sub.subscribe()
    sub_codes.subscribe()
    sub_addkey.subscribe()
    sub_delkey.subscribe()
})



const selectedRent = computed(() =>
    props.rents.find(rent => rent.id === selRent.value),
)
function selectRent(selectedValue) {
    selRent.value = selectedValue === selRent.value ? '' : selectedValue
    if (selectedRent.value?.locks.length > 0) {
        selectedLock.value = selectedRent.value.locks[0]
        selectLock(selectedLock.value)
    }
    else selectedLock.value = null
    open.value = false
}





const selectLock = (lock) => {
    selectedLock.value = lock
    refreshKeysList(lock)
}



const refreshKeysList = (lock) => {
    axios.post(pincodes_list(lock.id).url).then((response) => {
      
        keyList.value = response.data.pincodes
    })
        .catch((error) => {
            console.log(error);

        })
        .finally(() => {

        });
}




const openDeleteDialog = (key) => {

    isDeleteDialogOpen.value = true
    selectedKey.value = key
};




const addKey = () => {
    isAddKeyDialogOpen.value = true

}


const openLockfn = async (lock) => {
    waitApiOpenLock.value = true
    try {
        const response = await axios.post(openLock().url, {
            'lock_id': lock.lock_id
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
    } catch (error) {
        console.error('Error:', error)
    } finally {
        // loading.value = false
    }
}



const deleteKeyfn = async () => {
    waitApiDeleteKey.value = true
    try {
        
        const response = await axios.post(deleteKey().url, {
            'lock_id': selectedLock.value.lock_id,
            'code_id': selectedKey.value.pin_code_id,
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
    } catch (error) {
        console.error('Error:', error)
    } finally {
        // loading.value = false
    }
}






const syncKeysfn = async (lock) => {
    waitApiSyncKeys.value = true
    try {
        const response = await axios.post(getCodesList().url, {
            'lock_id': lock.lock_id,
            'page_number': 1,
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
    } catch (error) {
        console.error('Error:', error)
    } finally {
        // loading.value = false
    }
}


const createKeyfn = async (lock) => {
    waitApiAddKey.value = true
    try {
        const response = await axios.post(addCodeToLock().url, {
            'lock_id': lock.lock_id,   //!!!!!!!
            'page_number': 1,         //!!!!!!!
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
    } catch (error) {
        console.error('Error:', error)
    } finally {
        // loading.value = false
    }
}





const goToKeyPage = async (page) => {

    axios.post(pincodes_page(selectedLock.value.id).url, { 'page': page, }).then((response) => {
        keyList.value = response.data.pincodes
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
                        <TableRow
                            :class="{ 'border-2  font-bold  bg-green-200 hover:bg-green-200': lock.id == selectedLock.id }"
                            v-for="lock in selectedRent.locks" @click="selectLock(lock)" :key="lock.id">
                            <TableCell class="font-medium">
                                {{ lock.lock_id }}
                            </TableCell>
                            <TableCell>{{ lock.lock_name }}</TableCell>
                            <TableCell>{{ lock.lock_alias }}</TableCell>
                            <TableCell>{{ lock.electric_quantity }}%</TableCell>
                            <TableCell class="text-right gap-4">



                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button class="mx-2" variant="design_outline" size="icon"
                                                @click="addKey(lock)">
                                                <KeyRound />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <span>Добавить ключ</span>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>

                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button :disabled="waitApiOpenLock" class="mx-2" variant="design_outline"
                                                size="icon" @click="openLockfn(lock)">
                                                <LockKeyholeOpen />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <span v-if="waitApiOpenLock">Выполняется запрос на открытие замка</span>
                                            <span v-else>Открыть замок</span>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>




                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-else class="text-center"> К данному объекту не привязан ни один замок</div>



            </div>


            <div v-if="selectedLock">


                <Tabs default-value="pins" class="h-full flex flex-col ">
                    <TabsList class="  w-full mt-3">
                        <TabsTrigger value="pins">
                            <div class=" font-bold  text-lg">Текущие ключи</div>
                        </TabsTrigger>
                        <TabsTrigger value="logs">
                            <div class=" font-bold  text-lg">Лог событий</div>

                        </TabsTrigger>
                    </TabsList>
                    <TabsContent value="pins" class="h-full justify-center flex-col  flex">

                        <template v-if="keyList && keyList.data?.length">

                            <Table class="mt-2">

                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[100px]">
                                            Ид
                                        </TableHead>
                                        <TableHead>Имя</TableHead>
                                        <TableHead>Код</TableHead>
                                        <TableHead>Действует с</TableHead>
                                        <TableHead>Действует до</TableHead>
                                        <TableHead>Загружен в замок</TableHead>
                                        <TableHead class="text-right">
                                            Действие
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="key in keyList.data" :key="key.id">
                                        <TableCell class="font-medium">
                                            {{ key.pin_code_id }}
                                        </TableCell>
                                        <TableCell>{{ key.code_name }}</TableCell>
                                        <TableCell>{{ key.pin_code }}</TableCell>
                                        <TableCell>{{ key.start }}</TableCell>
                                        <TableCell>{{ key.end }}</TableCell>
                                        <TableCell>
                                            <CheckIcon v-if="key.is_load" />
                                        </TableCell>
                                        <TableCell class="text-right gap-4">
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <Button variant="destructive2" size="icon" :disabled="waitApiDeleteKey" 
                                                            @click="openDeleteDialog(key)">
                                                            <CircleX />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <span>Удалить ключ</span>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>


                            <Pagination class="my-4 self-end" v-model:page="keyList.current_page"
                                :items-per-page="keyList.per_page" :total="keyList.total"
                                :default-page="keyList.current_page">
                                <PaginationContent>
                                    <template v-for="(item, index) in keyList.links" :key="index">
                                        <PaginationPrevious v-if="index == 0" @click.prevent="goToKeyPage(item.page)" />
                                        <PaginationNext v-else-if="index == (keyList.links.length - 1)"
                                            @click.prevent="goToKeyPage(item.page)" />
                                        <PaginationItem
                                            v-else-if="(keyList.current_page - 2 <= item.page) && (keyList.current_page + 2 >= item.page)"
                                            :value="item.page" :is-active="item.page === keyList.current_page"
                                            @click.prevent="goToKeyPage(item.page)">
                                            {{ item.page }}
                                        </PaginationItem>
                                    </template>
                                </PaginationContent>
                            </Pagination>

                        </template>

                        <div class="flex items-center mt-5 gap-6 justify-begin mt-8">

                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button variant="design" :disabled="waitApiSyncKeys"
                                            @click="syncKeysfn(selectedLock)">Синхронизировать
                                            с
                                            TTLock</Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <span v-if="waitApiSyncKeys">Выполняется синхронизация</span>
                                        <span v-else>Процесс синхронизации может занять неколько минут</span>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                    </TabsContent>


                    <TabsContent value="logs" class="h-full justify-center flex-col  flex">


                    </TabsContent>
                </Tabs>

            </div>
        </div>






        <AlertDialog v-model:open="isOpenLockDialogOpen">

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle> {{ lockTitle }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ lockMessage }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Закрыть</AlertDialogCancel>

                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>




        <AlertDialog v-model:open="isDeleteDialogOpen">

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Удалить пинкод {{ selectedKey?.pin_code }}?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Пинкод {{ selectedKey?.pin_code }} будет удален.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Отменить</AlertDialogCancel>
                    <AlertDialogAction :class="cn(buttonVariants({ variant: 'destructive2' }))" @click="deleteKeyfn()">
                        Удалить</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>





        <Dialog v-model:open="isAddKeyDialogOpen">

            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Добавить ключ в замок {{ selectLock.lock_alias }}</DialogTitle>

                </DialogHeader>
                <div class="grid gap-4">
                    <div class="grid gap-3">
                        <Label for="name-1">Название</Label>
                        <Input id="name-1" name="name" />
                    </div>
                    <div class="grid gap-3">
                        <Label for="username-1">Описание</Label>

                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="design_outline">
                            Закрыть
                        </Button>

                    </DialogClose>
                    <DialogClose as-child>
                        <Button @click="createKeyfn()" variant="design">
                            Сохранить
                        </Button>
                    </DialogClose>

                </DialogFooter>
            </DialogContent>

        </Dialog>





    </AppLayout>
</template>
