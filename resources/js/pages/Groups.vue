<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { groups, group_create, group_delete, group_update, group_attach_lock, group_detach_lock, group_dattach_lock, groups_page } from '@/routes';
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
import debounce from 'debounce';


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

const currentGroup = ref(null)


const breadcrumbs = [
    {
        title: 'Мои группы',
        href: groups().url,
    },
];


const rent_search = ref('')
const lock_search = ref('')
const group_search = ref('')


const props = defineProps({
    rents: {
        type: Object
    },
    free_locks: {
        type: Object
    },
    groups_list: {
        type: Object
    },
    rent_search: {
        type: String
    },
    lock_search: {
        type: String
    },
    group_search: {
        type: String
    },


});



onMounted(() => {
    rent_search.value = props.rent_search
    lock_search.value = props.lock_search
    group_search.value = props.group_search

    console.log(props.group_search)

})


const debouncedSearch = debounce(() => {
    goToPage(1, 1, 1)
}, 500)



const goToPage = (group_page, lock_page, rent_page) => {

    router.post(groups_page().url, {
        'group_page': group_page, 'rent_page': rent_page, 'lock_page': lock_page,
        'rent_search': rent_search.value, 'lock_search': lock_search.value, 'group_search': group_search.value
    },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }
    );
    return true
}




const newGroup = () => {
    if (newObjectName.value.trim()) {

        router.post(group_create().url, { 'name': newObjectName.value, 'description': newObjectDescription.value },
            {
                preserveScroll: true,
                preserveState: true,
                history: false,
                onFinish: () => {
                    successCreateSchow.value = true
                    successCreateText.value = 'Группа ' + newObjectName.value + ' создана'
                    newObjectName.value = ''
                    newObjectDescription.value = ''

                    rent_search.value = ''
                    lock_search.value = ''
                    group_search.value = ''
                },
                onError: (errors) => {
                    console.log('Validation errors:', errors)
                }
            }

        );
    }
    return true
}




const saveGroup = () => {

    router.post(group_update().url, { 'group_id': currentGroup?.value.id, 'name': currentGroup?.value.name.trim(), 'description': currentGroup?.value.description.trim() },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Группа ' + currentGroup?.value.name + ' изменена'


                rent_search.value = ''
                lock_search.value = ''
                group_search.value = ''
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }

    );
    return true
}


const attachLock = (group, lock) => {

    router.post(group_attach_lock().url, {
        'rent_search': rent_search.value, 'lock_search': lock_search.value, 'group_search': group_search.value,
        'group_page': props.groups_list.current_page, 'lock_page': props.free_locks.current_page, 'rent_page': props.rents.current_page,
        'group_id': group.id, 'lock_id': lock.id
    },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Замок ' + lock.lock_alias + ' привязан к группе ' + group.name
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }


    );

    return true
}


const dattachLock = (group, lock, group2) => {

    router.post(group_dattach_lock().url, {
        'rent_search': rent_search.value, 'lock_search': lock_search.value, 'group_search': group_search.value,
        'group_page': props.groups_list.current_page, 'lock_page': props.free_locks.current_page, 'rent_page': props.rents.current_page,
        'group_id': group.id, 'lock_id': lock.id, 'dgroup_id': group2.id,
    },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Замок ' + lock.lock_alias + ' привязан к группе ' + group.name
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }


    );

    return true
}



const detachLock = (lock, group) => {

    router.post(group_detach_lock().url, {
        'rent_search': rent_search.value, 'lock_search': lock_search.value, 'group_search': group_search.value,
        'group_page': props.groups_list.current_page, 'lock_page': props.free_locks.current_page, 'rent_page': props.rents.current_page,
        'group_id': group.id, 'lock_id': lock.id
    },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Замок ' + lock.lock_alias + ' отвязан от группы  ' + group.name
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }


    );


    return true
}






const deleteGroup = () => {

    router.post(group_delete().url, { 'group_id': currentGroup?.value.id },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Группа ' + currentGroup?.value.name + ' удалена'


                rent_search.value = ''
                lock_search.value = ''
                group_search.value = ''
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }

    );
    return true
}




const openChangeDialog = (group) => {
    currentGroup.value = Object.assign({}, group)
    isChangeDialogOpen.value = true
};


const openDeleteDialog = (group) => {
    currentGroup.value = Object.assign({}, group)
    isDeleteDialogOpen.value = true
};








const dragItem = ref()
const dragRromGroup = ref()
const isDrag = ref(false)

function onDragStart(item) {
    dragItem.value = item
    isDrag.value = true

}


function onDragStart2(item, group) {
    dragItem.value = item
    dragRromGroup.value = group
    isDrag.value = true

}



function onFree() {
    isDrag.value = false
    detachLock(dragItem.value, dragRromGroup.value)
    dragRromGroup.value = null
}


function onAdd(group) {
    isDrag.value = false
    if (dragRromGroup.value)
        dattachLock(group, dragItem.value, dragRromGroup.value)

    else attachLock(group, dragItem.value)

    dragRromGroup.value = null

}


function onDragEnd() {
    isDrag.value = false
    dragRromGroup.value = null

}


</script>

<template>



    <Head title="Мои группы" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <Badge variant="secondary" class="text-xs mx-10 text-gray-800 break-words whitespace-normal max-w-[650px] my-5">
            <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                    stroke="#545F71" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            Привязка замков и объектов к группам
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
                        Новая группа
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Новая группа</DialogTitle>
                        <DialogDescription>
                            Добавьте новую группу, с которой можно связать замки и объекты
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
                            <Button @click="newGroup()" variant="design">
                                Создать
                            </Button>
                        </DialogClose>

                    </DialogFooter>
                </DialogContent>

            </Dialog>
        </div>






        <div class="grid grid-cols-1 md:grid-cols-6  gap-3 mx-3 mb-7">

            <div
                class="  md:col-span-2 min-h-[50px] border-dashed border-2 border-gray-500 flex lg:min-w-[280px]  min-w-[240px] justify-center flex-col px-1 lg:px-4">
                <div class="my-3 font-bold mx-auto text-lg">Замки</div>

                <Input v-model="lock_search" placeholder="поиск..." class="mb-8 border-2" @input="debouncedSearch" />

                <div @dragover.prevent @drop="onFree()" :class="{ 'bg-green-200': isDrag == true, }"
                    class=" flex-1  min-h-[50px] items-center border-dashed  flex lg:min-w-[280px]  min-w-[240px] justify-center flex-col ">

                    <div v-if="free_locks && free_locks.data.length === 0"
                        class="text-gray-500 text-sm flex justify-center">
                        <p>Нет свободных замков</p>
                    </div>

                    <Card class="w-full cursor-pointer relative gap-2 my-1 py-3  border-green-300 border-1"
                        v-for="lock in free_locks.data" :key="lock.id" draggable="true" @dragstart="onDragStart(lock)"
                        @dragend="onDragEnd()">
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




                <Pagination class="my-4 self-end" v-model:page="free_locks.current_page"
                    :items-per-page="free_locks.per_page" :total="free_locks.total"
                    :default-page="free_locks.current_page">
                    <PaginationContent>
                        <template v-for="(item, index) in free_locks.links" :key="index">
                            <PaginationPrevious v-if="index == 0"
                                @click.prevent="goToPage(groups_list.current_page, item.page, rents.current_page)" />
                            <PaginationNext v-else-if="index == (free_locks.links.length - 1)"
                                @click.prevent="goToPage(groups_list.current_page, item.page, rents.current_page)" />
                            <PaginationItem
                                v-else-if="(free_locks.current_page - 2 <= item.page) && (free_locks.current_page + 2 >= item.page)"
                                :value="item.page" :is-active="item.page === free_locks.current_page"
                                @click.prevent="goToPage(groups_list.current_page, item.page, rents.current_page)">
                                {{ item.page }}
                            </PaginationItem>
                        </template>
                    </PaginationContent>
                </Pagination>

            </div>

            <div class="md:col-span-4 0">

                <div class="flex-1 border-dashed border-2 px-4  w-full flex flex-col border-gray-500">
                    <div class="my-3 mx-auto font-bold text-lg">Группы</div>
                    <Input v-model="group_search" placeholder="поиск..." class="mb-8 border-2"
                        @input="debouncedSearch" />

                    <div v-for="group in groups_list.data" :key="group.id">





                        <Card class="relative my-2 pt-6 pb-2">
                            <span class=" m-2 top-0 text-xs absolute text-gray-500">id:{{ group.id
                            }}</span>
                            <CardHeader class="px-2">
                                <CardTitle class=""> {{ group.name }}

                                </CardTitle>
                                <CardDescription>
                                    {{ group.description }}
                                </CardDescription>
                                <CardAction class="flex gap-3 ">
                                    <Button variant="design_outline" size="icon" @click="openChangeDialog(group)">
                                        <Pencil />
                                    </Button>
                                    <Button variant="destructive2" size="icon" @click="openDeleteDialog(group)">
                                        <CircleX />
                                    </Button>
                                </CardAction>
                            </CardHeader>
                            <CardContent class=" min-h-[80px]" @dragover.prevent @drop="onAdd(group)"
                                :class="{ 'bg-green-200': isDrag == true, }">



                                <Card class="w-full cursor-pointer relative gap-2 my-1 py-3 border-green-300 border-1"
                                    v-for="lock in group.locks" :key="lock.id" draggable="true"
                                    @dragstart="onDragStart2(lock, group)" @dragend="onDragEnd()">
                                    <span class=" m-1 top-0 text-xs absolute text-gray-500">id:{{ lock.id
                                    }}</span>

                                    <Collapsible>

                                        <CardHeader class=" flex cursor-pointer items-center justify-between">

                                            <CollapsibleTrigger class="flex   ">
                                                <ChevronsUpDown
                                                    class="border-2 rounded-3xl shadow-xs hover:bg-accent me-2" />
                                                <CardTitle class="flex cursor-pointer items-center">{{ lock.lock_alias
                                                }}
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



                                <div v-if="group.locks.length === 0"
                                    class="text-gray-500 text-sm flex my-auto items-center justify-center ">
                                    <div>
                                        <p>Нет связей</p>
                                    </div>


                                </div>
                            </CardContent>
                        </Card>

                    </div>


                    <Pagination class="my-4 self-end" v-model:page="groups_list.current_page"
                        :items-per-page="groups_list.per_page" :total="groups_list.total"
                        :default-page="groups_list.current_page">
                        <PaginationContent>
                            <template v-for="(item, index) in groups_list.links" :key="index">
                                <PaginationPrevious v-if="index == 0"
                                    @click.prevent="goToPage(item.page, free_locks.current_page, rents.current_page)" />
                                <PaginationNext v-else-if="index == (groups_list.links.length - 1)"
                                    @click.prevent="goToPage(item.page, free_locks.current_page, rents.current_page)" />
                                <PaginationItem
                                    v-else-if="(groups_list.current_page - 2 <= item.page) && (groups_list.current_page + 2 >= item.page)"
                                    :value="item.page" :is-active="item.page === groups_list.current_page"
                                    @click.prevent="goToPage(item.page, free_locks.current_page, rents.current_page)">
                                    {{ item.page }}
                                </PaginationItem>
                            </template>
                        </PaginationContent>
                    </Pagination>

                </div>




            </div>




        </div>









        <!---------------------------------------------------------------------------------------------------------->




        <Dialog v-model:open="isChangeDialogOpen">

            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Изменить группу {{ currentGroup?.name }}</DialogTitle>

                </DialogHeader>
                <div class="grid gap-4">
                    <div class="grid gap-3">
                        <Label for="name-1">Название</Label>
                        <Input id="name-1" name="name" v-model="currentGroup.name" />
                    </div>
                    <div class="grid gap-3">
                        <Label for="username-1">Описание</Label>
                        <Textarea placeholder="" v-model="currentGroup.description" />
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="design_outline">
                            Закрыть
                        </Button>

                    </DialogClose>
                    <DialogClose as-child>
                        <Button @click="saveGroup()" variant="design">
                            Сохранить
                        </Button>
                    </DialogClose>

                </DialogFooter>
            </DialogContent>

        </Dialog>



        <AlertDialog v-model:open="isDeleteDialogOpen">

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Удалить группу {{ currentGroup?.name }}?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Группа {{ currentGroup?.name }} будет удалена. Все связанные с ней замки и объекты можно будет
                        привязать
                        к
                        другим группам.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Отменить</AlertDialogCancel>
                    <AlertDialogAction :class="cn(buttonVariants({ variant: 'destructive2' }))" @click="deleteGroup()">
                        Удалить</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>







    </AppLayout>








</template>
