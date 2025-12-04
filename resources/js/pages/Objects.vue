<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { objects, rent_create, rent_update, rent_delete, rent_attach_lock, rent_detach_lock } from '@/routes';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { ref, onMounted, computed } from 'vue'
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { VueDraggable } from 'vue-draggable-plus'
import { Textarea } from '@/components/ui/textarea'
import { CheckCircle2Icon, Pencil, CircleX, CheckIcon, ChevronsUpDown, ChevronsUpDownIcon } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

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
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'


const newObjectName = ref('')
const newObjectDescription = ref('')
const successCreateSchow = ref(false)
const successCreateText = ref('')

const isDialogOpen = ref(false)
const isChangeDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const isDetachDialogOpen = ref(false)
const currentRent = ref(null)
const currentLock = ref(null)


const breadcrumbs = [
    {
        title: 'Мои объекты',
        href: objects().url,
    },
];



const newRent = () => {

    if (newObjectName.value.trim()) {
        router.post(rent_create().url, { 'name': newObjectName.value, 'description': newObjectDescription.value },
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
    }
    return true





}


const saveRent = () => {

    router.post(rent_update().url, { 'rent_id': currentRent?.value.id, 'name': currentRent?.value.name.trim(), 'description': currentRent?.value.description.trim() },
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

    )
    return true
}


const attachLock = () => {

    if (selLock.value) {
        router.post(rent_attach_lock().url, { 'rent_id': currentRent?.value.id, 'lock_id': selLock.value },
            {
                preserveScroll: true,
                preserveState: true,
                history: false,
                onFinish: () => {
                    successCreateSchow.value = true
                    successCreateText.value = 'Замок ' + selectedFreeLock?.lock_alias + ' привязан к объекту  ' + currentRent?.value.name
                },
                onError: (errors) => {
                    console.log('Validation errors:', errors)
                }
            }


        );
    }

    return true
}



const detachLock = () => {
    if (currentLock.value) {
        router.post(rent_detach_lock().url, { 'lock_id': currentLock.value.id },
            {
                preserveScroll: true,
                preserveState: true,
                history: false,
                onFinish: () => {
                    successCreateSchow.value = true
                    successCreateText.value = 'Замок ' + currentLock.value.lock_alias + ' отвязан от объекта  ' + currentRent?.value.name
                },
                onError: (errors) => {
                    console.log('Validation errors:', errors)
                }
            }

        )
    }
    return true
}



const deleteRent = () => {

    if (currentRent?.value) {
        router.post(rent_delete().url, { 'rent_id': currentRent?.value.id },
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
    }
    return true
}







const open = ref(false)
const selLock = ref()

const selectedFreeLock = computed(() =>
    props.free_locks.find(freelock => freelock.id === selLock.value),
)

function selectLock(selectedValue) {
    selLock.value = selectedValue === selLock.value ? '' : selectedValue
    open.value = false
}








const props = defineProps({
    rents: {
        type: Object
    },
    free_locks: {
        type: Object
    },

});

const openAssignDialog = (rent) => {
    currentRent.value = rent
    isDialogOpen.value = true
};


const openChangeDialog = (rent) => {
    currentRent.value = Object.assign({}, rent)
    isChangeDialogOpen.value = true
};


const openDeleteDialog = (rent) => {
    currentRent.value = Object.assign({}, rent)
    isDeleteDialogOpen.value = true
};

const openDetachDialog = (rent, lock) => {
    currentRent.value = Object.assign({}, rent)
    currentLock.value = Object.assign({}, lock)
    isDetachDialogOpen.value = true
};

</script>

<template>





    <Head title="Мои объекты" />

    <AppLayout :breadcrumbs="breadcrumbs">


        <Badge variant="secondary" class="text-xs ms-10 text-gray-800 break-words whitespace-normal max-w-[650px] my-5">
            <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                    stroke="#545F71" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            Создайте объекты в которые установлены умные замки, и привяжите замки к ним
        </Badge>



        <div class="grid   items-start gap-4 mx-10">
            <Alert v-if="successCreateSchow" @click="successCreateSchow = false">
                <CheckCircle2Icon :size="16" />
                <AlertTitle class="text-green-500">{{ successCreateText }}</AlertTitle>

            </Alert>
        </div>
        <div class="flex  m-10 ">
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








        <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-4 mx-3">
            <Card v-for="rent in rents" :key="rent.id" class="relative">
                <span class=" m-2 top-0 text-xs absolute text-gray-500">id:{{ rent.id
                    }}</span>
                <CardHeader class="px-2">
                    <CardTitle class=""> {{ rent.name }}
                        <Button variant="design_outline" size="icon" @click="openChangeDialog(rent)">
                            <Pencil />
                        </Button>
                    </CardTitle>
                    <CardDescription>
                        {{ rent.description }}
                    </CardDescription>
                    <CardAction class="flex flex-col md:flex-row gap-3 ">


                        <Button class="order-last md:order-first" variant="design" @click="openAssignDialog(rent)">
                            Привязать замок
                        </Button>
                        <Button class="self-end" variant="destructive2" size="icon" @click="openDeleteDialog(rent)">
                            <CircleX />
                        </Button>
                    </CardAction>
                </CardHeader>
                <CardContent>




                    <Card class="w-full  relative gap-2 my-1 py-3" v-for="lock in rent.locks" :key="lock.id">
                        <span class=" m-1 top-0 text-xs absolute text-gray-500">id:{{ lock.id }}</span>

                        <Collapsible>

                            <CardHeader class=" flex items-center justify-between">

                                <CollapsibleTrigger class="flex   ">
                                    <ChevronsUpDown class="border-2 rounded-3xl shadow-xs hover:bg-accent me-2" />
                                    <CardTitle class="flex items-center">{{ lock.lock_alias }}</CardTitle>
                                </CollapsibleTrigger>
                                <!--CardDescription>
                                {{ lock.lock_name }}
                            </CardDescription-->
                                <CardAction>
                                    <Button @click="openDetachDialog(rent, lock)" variant="destructive2">
                                        Отвязать
                                    </Button>
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
                                                        <!--
                                                             :class="{
                                                                'bg-green-500': lock.electric_quantity >= 70,
                                                                'bg-yellow-500': lock.electric_quantity >= 30 && lock.electric_quantity < 70,
                                                                'bg-red-500': lock.electric_quantity < 30
                                                            }"
                                                             -->
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
                    <div v-if="rent.locks.length === 0" class="text-gray-500 text-sm flex justify-center">
                        <p>Нет привязанных замков</p>
                    </div>
                </CardContent>
            </Card>
        </div>






        <!---------------------------------------------------------------------------------------------------------->

        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Привязать замок к {{ currentRent?.name }}</DialogTitle>
                </DialogHeader>

                <div class="py-4">



                    <Popover v-model:open="open" class="">
                        <PopoverTrigger as-child>
                            <Button variant="outline" role="combobox" :aria-expanded="open"
                                class="w-full justify-between">
                                {{ selectedFreeLock?.lock_alias || "Выберите замок..." }}
                                <ChevronsUpDownIcon class="opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class=" p-0">
                            <Command>
                                <CommandInput class="h-9" placeholder="Выбор замка..." />
                                <CommandList>
                                    <CommandEmpty>Свободных замков не найдено.</CommandEmpty>
                                    <CommandGroup>
                                        <CommandItem v-for="freelock in free_locks" :key="freelock.id"
                                            :value="freelock.id" @select="(ev) => {
                                                selectLock(ev.detail.value)
                                            }">
                                            {{ freelock.lock_alias }}
                                            <CheckIcon :class="cn(
                                                'ml-auto',
                                                selLock === freelock.id ? 'opacity-100' : 'opacity-0',
                                            )" />
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="design_outline">
                            Отмена
                        </Button>
                    </DialogClose>
                    <DialogClose as-child>
                        <Button :disabled="!selLock" @click="attachLock()" variant="design">
                            Привязать
                        </Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>




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



        <AlertDialog v-model:open="isDetachDialogOpen">

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Отвязать замок {{ currentLock?.lock_alias }} от объекта {{ currentRent?.name }}?
                    </AlertDialogTitle>

                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Отменить</AlertDialogCancel>
                    <AlertDialogAction :class="cn(buttonVariants({ variant: 'destructive2' }))" @click="detachLock()">
                        Отвязать</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>











        <div class="flex flex-col gap-6 mt-5">
            <Pagination v-slot="{ page }" :items-per-page="10" :total="30" :default-page="2">
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


    </AppLayout>








</template>
