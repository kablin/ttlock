<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { objects, rent_create2, rent_update2, rent_delete2, rent_attach_lock2, rent_detach_lock2 } from '@/routes';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { ref, onMounted, computed, watchEffect, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea'
import { CheckCircle2Icon, Pencil, CircleX, CheckIcon, ChevronsUpDown, ChevronsUpDownIcon } from 'lucide-vue-next'
import axios from 'axios';


import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'

import { cn } from "@/lib/utils"
import { buttonVariants } from '@/components/ui/button'

import { Progress } from '@/components/ui/progress'
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert'

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
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'



import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'


const newObjectName = ref('')
const newObjectDescription = ref('')
const successCreateSchow = ref(false)
const successCreateText = ref('')


const isChangeDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)

const currentRent = ref(null)


const breadcrumbs = [
    {
        title: 'Мои объекты',
        href: objects().url,
    },
];




const local_rents = ref([])
const local_free_locks = ref([])




const props = defineProps({
    rents: {
        type: Object
    },
    free_locks: {
        type: Object
    },

});




watchEffect(() => {

    console.log(props.free_locks);
    local_rents.value = JSON.parse(JSON.stringify(props.rents))
    local_free_locks.value = JSON.parse(JSON.stringify(props.free_locks))
})







const newRent = (rent, lock) => {

    router.post(rent_create2().url, { 'name': newObjectName.value, 'description': newObjectDescription.value },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Объект ' + newObjectName.value + ' создан'
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }

    );
    return true
}




const saveRent = (rent, lock) => {

    router.post(rent_update2().url, { 'rent_id': currentRent?.value.id, 'name': currentRent?.value.name.trim(), 'description': currentRent?.value.description.trim() },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Объект ' + currentRent?.value.name + ' изменен'
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }

    );
    return true
}


const attachLock = (rent, lock) => {

    router.post(rent_attach_lock2().url, { 'rent_id': rent.id, 'lock_id': lock.id },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Замок ' + lock.lock_alias + ' привязан к объекту ' + rent.name
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }


    );

    return true
}



const detachLock = (lock) => {

    router.post(rent_detach_lock2().url, { 'lock_id': lock.id },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Замок ' + lock.lock_alias + ' отвязан от объекта  '
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }


    );


    return true
}






const deleteRent = (rent, lock) => {

    router.post(rent_delete2().url, { 'rent_id': currentRent?.value.id },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Объект ' + currentRent?.value.name + ' удален'
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }

    );
    return true
}




const openChangeDialog = (rent) => {
    currentRent.value = Object.assign({}, rent)
    isChangeDialogOpen.value = true
};


const openDeleteDialog = (rent) => {
    currentRent.value = Object.assign({}, rent)
    isDeleteDialogOpen.value = true
};








const dragItem = ref()
const isDrag = ref(false)

function onDragStart(item) {
    dragItem.value = item
    isDrag.value = true

}


function onFree() {
    isDrag.value = false
    detachLock(dragItem.value)
}


function onAdd(rent) {
    isDrag.value = false
    attachLock(rent, dragItem.value)

}


function onDragEnd() {
    isDrag.value = false

}


</script>

<template>



    <Head title="Мои объекты" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <Badge variant="secondary" class="text-xs mx-10 text-gray-800 break-words whitespace-normal max-w-[650px] my-5">
            <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                    stroke="#545F71" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            Создайте объекты в которые установлены умные замки, и привяжите замки к ним, перетаскивая их мышкой.
        </Badge>



        <div class="grid   items-start gap-4 mx-10">
            <Alert v-if="successCreateSchow" @click="successCreateSchow = false" class="mb-5">
                <CheckCircle2Icon :size="16" />
                <AlertTitle class="text-green-500">{{ successCreateText }}</AlertTitle>

            </Alert>
        </div>
        <div class="flex  mx-10  mb-6">
            <Dialog>

                <DialogTrigger as-child>
                    <Button variant="design">
                        Новый объект
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Новый объект</DialogTitle>
                        <DialogDescription>
                            Добавьте новый объект в котором установлены умные замки TTLock
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-4">
                        <div class="grid gap-3">
                            <Label for="name-1">Название</Label>
                            <Input id="name-1" name="name" v-model="newObjectName" />
                        </div>
                        <div class="grid gap-3">
                            <Label for="username-1">Описание</Label>
                            <Textarea placeholder="" v-model="newObjectDescription" />
                        </div>
                    </div>
                    <DialogFooter>
                        <DialogClose as-child>
                            <Button variant="design_outline">
                                Закрыть
                            </Button>

                        </DialogClose>
                        <DialogClose as-child>
                            <Button @click="newRent()" variant="design">
                                Добавить
                            </Button>
                        </DialogClose>

                    </DialogFooter>
                </DialogContent>

            </Dialog>
        </div>






        <div class="grid grid-cols-1 md:grid-cols-6  gap-3 mx-3 mb-7">

            <div
                class="  md:col-span-2 min-h-[50px] border-dashed border-2 border-gray-500 flex lg:min-w-[280px]  min-w-[240px] justify-center flex-col px-1 lg:px-4">
                <div class="my-3 font-bold mx-auto text-lg">Свободные замки</div>

                <div @dragover.prevent @drop="onFree()"  :class="{'bg-green-200': isDrag == true, }"
                    class=" flex-1  min-h-[50px] items-center border-dashed  flex lg:min-w-[280px]  min-w-[240px] justify-center flex-col ">

                    <div v-if="local_free_locks && local_free_locks.length === 0"
                        class="text-gray-500 text-sm flex justify-center">
                        <p>Нет свободных замков</p>
                    </div>

                    <Card class="w-full cursor-pointer relative gap-2 my-1 py-3  border-green-300 border-1" v-for="lock in local_free_locks" :key="lock.id"
                        draggable="true" @dragstart="onDragStart(lock)"  @dragend="onDragEnd()" >
                        <span class=" m-1 top-0 text-xs absolute text-gray-500">id:{{ lock.id }}</span>

                        <Collapsible>

                            <CardHeader class=" flex items-center justify-between">

                                <CollapsibleTrigger class="flex   ">
                                    <ChevronsUpDown class="border-2 rounded-3xl shadow-xs hover:bg-accent me-2" />
                                    <CardTitle class="flex items-center">{{ lock.lock_alias }}</CardTitle>
                                </CollapsibleTrigger>
                                <CardAction>
                                </CardAction>
                            </CardHeader>
                            <CollapsibleContent>
                                <CardContent>
                                    <ul class="mt-3">
                                        <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            Имя<span>{{ lock.lock_name }}</span>
                                        </li>
                                        <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            Мастер ключ<span>{{ lock.no_key_pwd }}</span>
                                        </li>
                                        <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            TTlock Id<span>{{ lock.lock_id }}</span>
                                        </li>

                                        <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            Заряд батареи

                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <Progress :model-value="lock.electric_quantity"
                                                            class="w-[30%]" />

                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <span>{{ lock.electric_quantity }}%</span>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
                                        </li>
                                    </ul>
                                </CardContent>
                            </CollapsibleContent>
                        </Collapsible>
                    </Card>
                </div>




                <Pagination class="self-end my-4" v-slot="{ page }" :items-per-page="10" :total="30" :default-page="2">
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />

                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                :is-active="item.value === page">
                                {{ item.value }}
                            </PaginationItem>
                        </template>

                        <PaginationEllipsis :index="4" />

                        <PaginationNext />
                    </PaginationContent>
                </Pagination>

            </div>

            <div class="md:col-span-4 0">

                <div class="flex-1 border-dashed border-2 px-4  w-full flex flex-col border-gray-500">
                    <div class="my-3 mx-auto font-bold text-lg">Объекты</div>

                    <div v-for="rent in local_rents" :key="rent.id">





                        <Card class="relative my-2 pt-6 pb-0">
                            <span class=" m-2 top-0 text-xs absolute text-gray-500">id:{{ rent.id
                            }}</span>
                            <CardHeader class="px-2">
                                <CardTitle class=""> {{ rent.name }}

                                </CardTitle>
                                <CardDescription>
                                    {{ rent.description }}
                                </CardDescription>
                                <CardAction class="flex gap-3 ">
                                    <Button variant="design_outline" size="icon" @click="openChangeDialog(rent)">
                                        <Pencil />
                                    </Button>
                                    <Button variant="destructive2" size="icon" @click="openDeleteDialog(rent)">
                                        <CircleX />
                                    </Button>
                                </CardAction>
                            </CardHeader>
                            <CardContent class="tg-zone min-h-[80px]" @dragover.prevent @drop="onAdd(rent)"
                            
                            :class="{'bg-green-200': isDrag == true, }"  >



                                <Card class="w-full cursor-pointer relative gap-2 my-1 py-3 border-green-300 border-1" v-for="lock in rent.locks" :key="lock.id"
                                    draggable="true" @dragstart="onDragStart(lock)"  @dragend="onDragEnd()" >
                                    <span class=" m-1 top-0 text-xs absolute text-gray-500">id:{{ lock.id
                                    }}</span>

                                    <Collapsible>

                                        <CardHeader class=" flex cursor-pointer items-center justify-between">

                                            <CollapsibleTrigger class="flex   ">
                                                <ChevronsUpDown
                                                    class="border-2 rounded-3xl shadow-xs hover:bg-accent me-2" />
                                                <CardTitle class="flex cursor-pointer items-center">{{ lock.lock_alias }}
                                                </CardTitle>
                                            </CollapsibleTrigger>
                                            <CardAction>

                                            </CardAction>
                                        </CardHeader>
                                        <CollapsibleContent>
                                            <CardContent>


                                                <ul class="mt-3">
                                                    <li
                                                        class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                        Имя<span>{{ lock.lock_name }}</span>
                                                    </li>
                                                    <li
                                                        class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                        Мастер ключ<span>{{ lock.no_key_pwd }}</span>
                                                    </li>
                                                    <li
                                                        class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                        TTlock Id<span>{{ lock.lock_id }}</span>
                                                    </li>

                                                    <li
                                                        class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                        Заряд батареи

                                                        <TooltipProvider>
                                                            <Tooltip>
                                                                <TooltipTrigger as-child>
                                                                    <Progress :model-value="lock.electric_quantity"
                                                                        class="w-[30%]" />

                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <span>{{ lock.electric_quantity }}%</span>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        </TooltipProvider>
                                                    </li>
                                                </ul>
                                            </CardContent>
                                        </CollapsibleContent>
                                    </Collapsible>
                                </Card>



                                <div v-if="rent.locks.length === 0"
                                    class="text-gray-500 text-sm flex my-auto items-center justify-center ">
                                    <div>
                                        <p>Нет привязанных замков</p>
                                    </div>


                                </div>
                            </CardContent>
                        </Card>

                    </div>


                    <Pagination class="my-4 self-end" v-slot="{ page }" :items-per-page="10" :total="30"
                        :default-page="2">
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious />

                            <template v-for="(item, index) in items" :key="index">
                                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                    :is-active="item.value === page">
                                    {{ item.value }}
                                </PaginationItem>
                            </template>

                            <PaginationEllipsis :index="4" />

                            <PaginationNext />
                        </PaginationContent>
                    </Pagination>

                </div>




            </div>




        </div>









        <!---------------------------------------------------------------------------------------------------------->




        <Dialog v-model:open="isChangeDialogOpen">

            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Изменить объект {{ currentRent?.name }}</DialogTitle>

                </DialogHeader>
                <div class="grid gap-4">
                    <div class="grid gap-3">
                        <Label for="name-1">Название</Label>
                        <Input id="name-1" name="name" v-model="currentRent.name" />
                    </div>
                    <div class="grid gap-3">
                        <Label for="username-1">Описание</Label>
                        <Textarea placeholder="" v-model="currentRent.description" />
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="design_outline">
                            Закрыть
                        </Button>

                    </DialogClose>
                    <DialogClose as-child>
                        <Button @click="saveRent()" variant="design">
                            Сохранить
                        </Button>
                    </DialogClose>

                </DialogFooter>
            </DialogContent>

        </Dialog>



        <AlertDialog v-model:open="isDeleteDialogOpen">

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Удалить объект {{ currentRent?.name }}?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Объект {{ currentRent?.name }} будет удален. Все связанные с ним замки можно будет привязать к
                        другим объектам.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Отменить</AlertDialogCancel>
                    <AlertDialogAction :class="cn(buttonVariants({ variant: 'destructive2' }))" @click="deleteRent()">
                        Удалить</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>







    </AppLayout>








</template>
