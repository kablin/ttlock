<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';

import { wizard, wizard_attach_lock, wizard_detach_lock, wizard_dattach_lock, wizard_page, dashboard } from '@/routes';
import { router } from '@inertiajs/vue3'

import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { ref, onMounted } from 'vue'
import { Badge } from '@/components/ui/badge';
import { Stepper, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import debounce from 'debounce';
import { CheckCircle2Icon, Pencil, CircleX, CheckIcon, ChevronsUpDown, ChevronsUpDownIcon } from 'lucide-vue-next'



import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';




import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,

} from '@/components/ui/pagination'


import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert'


import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'

const stepIndex = ref(1)

const selectedSystem = ref(1)
const selectedLockSystem = ref(1)





const togglePassword = () => {
    showPassword.value = !showPassword.value
}

const showPassword = ref(false)



const steps = [
    {
        step: 1,
        title: 'Выберите систему',
        description: '1. Выберите вашу PMS | CRM систему',
    },
    {
        step: 2,
        title: 'Подключитесь',
        description: '2. Подключите PMS | CRM систему',
    },
    {
        step: 3,
        title: 'Cистема замков',
        description: '3. Выберите замковую систему',
    },
    {
        step: 4,
        title: 'Подключите замки',
        description: '4. Выберите нужную модель умных замков',
    },
    {
        step: 5,
        title: 'Настройте маппинг',
        description: '5. Привяжите замки к объектам',
    },
]

const breadcrumbs = [
    {
        title: 'Мастер',
        href: wizard().url,
    },
];


const getLocks = () => {

    return true
}



const currentRent = ref(null)

const rent_search = ref('')
const lock_search = ref('')
const successCreateSchow = ref(false)
const successCreateText = ref('')


onMounted(() => {
    rent_search.value = props.rent_search
    lock_search.value = props.lock_search


})




const props = defineProps({
    rents: {
        type: Object
    },
    free_locks: {
        type: Object
    },
    rent_search: {
        type: String
    },
    lock_search: {
        type: String
    },

});


const debouncedSearch = debounce(() => {
    goToPage(1, 1)
}, 800)





const goToPage = (page, lock_page) => {

    router.post(wizard_page().url, { 'rent_page': page, 'lock_page': lock_page, 'rent_search': rent_search.value, 'lock_search': lock_search.value },
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


const attachLock = (rent, lock) => {

    router.post(wizard_attach_lock().url, { 'rent_search': rent_search.value, 'lock_search': lock_search.value, 'lock_page': props.free_locks.current_page, 'rent_page': props.rents.current_page, 'rent_id': rent.id, 'lock_id': lock.id },
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





const dattachLock = (rent, lock, rent2) => {

    router.post(wizard_dattach_lock().url, { 'rent_search': rent_search.value, 'lock_search': lock_search.value, 'lock_page': props.free_locks.current_page, 'rent_page': props.rents.current_page, 'rent_id': rent.id, 'lock_id': lock.id, 'drent_id': rent2.id, },
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



const detachLock = (lock, rent) => {

    router.post(wizard_detach_lock().url, { 'rent_search': rent_search.value, 'lock_search': lock_search.value, 'lock_page': props.free_locks.current_page, 'rent_page': props.rents.current_page, 'rent_id': rent.id, 'lock_id': lock.id },
        {
            preserveScroll: true,
            preserveState: true,
            history: false,
            onFinish: () => {
                successCreateSchow.value = true
                successCreateText.value = 'Замок ' + lock.lock_alias + ' отвязан от объекта  ' + rent.name
            },
            onError: (errors) => {
                console.log('Validation errors:', errors)
            }
        }


    );


    return true
}




const dragItem = ref()
const dragRromRent = ref()
const isDrag = ref(false)

function onDragStart(item) {
    dragItem.value = item
    isDrag.value = true

}


function onDragStart2(item, rent) {
    dragItem.value = item
    dragRromRent.value = rent
    isDrag.value = true

}



function onFree() {
    isDrag.value = false
    detachLock(dragItem.value, dragRromRent.value)
    dragRromRent.value = null
}


function onAdd(rent) {
    isDrag.value = false
    if (dragRromRent.value)
        dattachLock(rent, dragItem.value, dragRromRent.value)

    else attachLock(rent, dragItem.value)

    dragRromRent.value = null

}


function onDragEnd() {
    isDrag.value = false
    dragRromRent.value = null

}




</script>

<template>

    <Head title="Мастер" />


    <AppShell variant="sidebar">

        <AppContent variant="sidebar" class="overflow-x-hidden   md:mx-3 lg:mx-8">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />



            <p class="text-2xl font-bold mx-1">Мастер настройки</p>

            <div class="h-full">



                <Stepper v-model="stepIndex" class="block w-full my-4" v-slot="{ modelValue, prevStep, nextStep }">

                    <div class="flex w-full flex-col lg:flex-row flex-start gap-2">


                        <StepperItem v-for="(item, index) in steps" :key="item.step" :step="item.step"
                            v-slot="{ state }" class="relative flex w-full flex-col items-center ">

                            <StepperSeparator v-if="item.step !== steps[steps.length - 1]?.step"
                                class=" hidden lg:block absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-6  h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary" />

                            <StepperTrigger>
                                <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
                                    size="icon" class="z-10 rounded-full shrink-0"
                                    :class="[state === 'active' && 'bg-gray-600 ring-2 ring-ring ring-offset-2 ring-offset-background']"
                                    :disabled="state !== 'completed' && (index >= (modelValue || 0))">


                                    <svg v-if="state == 'completed'" width="14" height="10" viewBox="0 0 14 10"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5.25479 9.0712C5.12352 9.07147 4.99349 9.04581 4.87217 8.9957C4.75084 8.94559 4.64061 8.87202 4.54779 8.7792L0.305787 4.5362C0.210213 4.44402 0.133955 4.33373 0.0814619 4.21177C0.0289684 4.0898 0.00129084 3.9586 4.4085e-05 3.82582C-0.00120267 3.69305 0.0240064 3.56135 0.0742003 3.43842C0.124394 3.31549 0.198568 3.20378 0.292393 3.10983C0.386219 3.01587 0.497817 2.94154 0.620676 2.89117C0.743536 2.8408 0.875196 2.81541 1.00797 2.81647C1.14075 2.81752 1.27199 2.84502 1.39403 2.89734C1.51607 2.94966 1.62647 3.02576 1.71879 3.1212L5.25379 6.6562L11.6188 0.293203C11.8063 0.105562 12.0607 9.38288e-05 12.3259 6.25751e-08C12.5912 -9.37036e-05 12.8456 0.105195 13.0333 0.292703C13.2209 0.480211 13.3264 0.734579 13.3265 0.999849C13.3266 1.26512 13.2213 1.51956 13.0338 1.7072L5.96179 8.7792C5.86897 8.87202 5.75873 8.94559 5.6374 8.9957C5.51608 9.04581 5.38605 9.07147 5.25479 9.0712Z"
                                            fill="#25A249" />
                                    </svg>
                                    <svg v-else-if="state == 'active'" width="20" height="20" viewBox="0 0 20 20"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M19.95 10.951C19.449 16.004 15.185 19.951 10 19.951C4.477 19.951 0 15.474 0 9.951C0 4.766 3.947 0.502 9 0.001V2.013C6.98271 2.26968 5.13885 3.28487 3.84319 4.85222C2.54752 6.41957 1.89728 8.42147 2.02462 10.451C2.15196 12.4806 3.04733 14.3855 4.52874 15.7786C6.01016 17.1717 7.96645 17.9485 10 17.951C11.9486 17.951 13.8302 17.2398 15.2917 15.951C16.7533 14.6622 17.6942 12.8843 17.938 10.951H19.951H19.95ZM19.95 8.951H17.938C17.7154 7.18861 16.9129 5.55034 15.6568 4.29424C14.4007 3.03814 12.7624 2.23559 11 2.013V0C13.2951 0.231235 15.4398 1.24861 17.0709 2.87982C18.7019 4.51104 19.719 6.65584 19.95 8.951Z"
                                            fill="red" />
                                    </svg>
                                    <svg v-else width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M19.95 10.951C19.449 16.004 15.185 19.951 10 19.951C4.477 19.951 0 15.474 0 9.951C0 4.766 3.947 0.502 9 0.001V2.013C6.98271 2.26968 5.13885 3.28487 3.84319 4.85222C2.54752 6.41957 1.89728 8.42147 2.02462 10.451C2.15196 12.4806 3.04733 14.3855 4.52874 15.7786C6.01016 17.1717 7.96645 17.9485 10 17.951C11.9486 17.951 13.8302 17.2398 15.2917 15.951C16.7533 14.6622 17.6942 12.8843 17.938 10.951H19.951H19.95ZM19.95 8.951H17.938C17.7154 7.18861 16.9129 5.55034 15.6568 4.29424C14.4007 3.03814 12.7624 2.23559 11 2.013V0C13.2951 0.231235 15.4398 1.24861 17.0709 2.87982C18.7019 4.51104 19.719 6.65584 19.95 8.951Z"
                                            fill="#bbbbbb" />
                                    </svg>

                                    <template v-if="item.icon">
                                        <div v-html="item.icon" class=""> </div>
                                    </template>

                                </Button>
                            </StepperTrigger>

                            <div class="flex flex-col items-center justify-center">
                                <StepperTitle>
                                    {{ item.title }}
                                </StepperTitle>
                                <StepperDescription>
                                    {{ item.description }}
                                </StepperDescription>
                            </div>
                        </StepperItem>
                    </div>


                    <Separator class="my-5  lg:hidden block" />
                    <div class="flex flex-col  gap-4  px-2 md:px-8 lg:mt-5 h-full">

                        <template v-if="stepIndex === 1">
                            <div class="">
                                <Badge variant="secondary"
                                    class="text-xs text-gray-800 break-words whitespace-normal mt-5">
                                    <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                                            stroke="#545F71" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    Выберите из списка вашу систему управления недвижимостью
                                </Badge>

                                <div class="flex items-center justify-center gap-3   flex-col lg:flex-row mt-10">
                                    <div @click="selectedSystem = 1"><img src="/images/Travel.png" class="max-w-[165px]"
                                            :class="[selectedSystem === 1 && 'ring-2  ring-red-600  ring-offset-red-600']" />
                                    </div>
                                    <div @click="selectedSystem = 2"><img src="/images/Realty.png" class="max-w-[165px]"
                                            width="142px"
                                            :class="[selectedSystem === 2 && 'ring-2  ring-red-600  ring-offset-red-600']" />
                                    </div>
                                    <div @click="selectedSystem = 3"><img src="/images/Bitrix.png" class="max-w-[165px]"
                                            :class="[selectedSystem === 3 && 'ring-2  ring-red-600  ring-offset-red-600']" />
                                    </div>
                                    <div @click="selectedSystem = 4"><img src="/images/YC.png" class="max-w-[165px]"
                                            :class="[selectedSystem === 4 && 'ring-2  ring-red-600  ring-offset-red-600']" />
                                    </div>
                                </div>
                                <div class="max-w-md mx-auto mt-10"><Button variant="design" @click="nextStep()"
                                        class=" w-full">

                                        Выбрать</Button>
                                </div>
                                <Button variant="design" class="mt-15 ms-auto flex  ">
                                    Перейти в демо режим
                                </Button>
                            </div>

                        </template>





                        <template v-if="stepIndex === 2">
                            <div class="">
                                <Badge variant="secondary"
                                    class="text-xs text-gray-800 break-words whitespace-normal mt-5 max-w-[650px]">
                                    <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                                            stroke="#545F71" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>

                                    Подключите систему управления недвижимостью.Перейдите в RealtyCalendar, скопируйте
                                    токен
                                    и вставьте его в поле ниже. Все ваши объекты подгрузятся в интерфейс, чтобы в
                                    дальнейшем
                                    связать их с замками.
                                </Badge>

                                <div class="flex items-center justify-center  gap-3    flex-col lg:flex-row mt-12">
                                    <div v-if="selectedSystem === 1"><img src="/images/Travel.png"
                                            class="max-w-[165px]" />
                                    </div>
                                    <div v-else-if="selectedSystem === 2"><img src="/images/Realty.png"
                                            class="max-w-[165px]" width="142px" />
                                    </div>
                                    <div v-else-if="selectedSystem === 3"><img src="/images/Bitrix.png"
                                            class="max-w-[165px]" />
                                    </div>
                                    <div v-else><img src="/images/YC.png" class="max-w-[165px]" />
                                    </div>


                                    <div class="flex flex-col-reverse  lg:flex-row   justify-center  ">

                                        <div class="self-start lg:self-end mb-4  ">
                                            <Button @click="prevStep()" variant="link" class="text-blue-500 ">
                                                Назад
                                            </Button>
                                        </div>
                                        <div
                                            class="relative  p-4    lg:min-w-[500px]  flex flex-col   justify-center gap-4">

                                            <div class=" mx-auto mb-3">
                                                <Button variant="design_outline" class="">
                                                    <a href="#"> Перейти в RealtyCalendar</a>
                                                </Button>
                                            </div>
                                            <Label for="token">Токен</Label>
                                            <Input id="token" name="token" class="mt-1 block w-full"
                                                autocomplete="current-password" placeholder="token" />

                                            <div class="  mt-5 flex justify-between">

                                                <Button variant="design" @click="nextStep()" class=" flex-1">
                                                    Подключить RealtyCalendar</Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <Button variant="design" class="mt-15 ms-auto flex  ">
                                    Перейти в демо режим
                                </Button>

                            </div>
                        </template>


                        <template v-if="stepIndex === 3">

                            <div class="">
                                <Badge variant="secondary"
                                    class="text-xs text-gray-800 break-words whitespace-normal mt-5">
                                    <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                                            stroke="#545F71" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    Выберите с какими замками вы работаете
                                </Badge>

                                <div class="flex items-center justify-center gap-3   flex-col lg:flex-row mt-10">
                                    <div @click="selectedLockSystem = 1"><img src="/images/locks/l1.png"
                                            class="max-w-[165px]"
                                            :class="[selectedLockSystem === 1 && 'ring-2  ring-red-600  ring-offset-red-600']" />
                                    </div>
                                    <div @click="selectedLockSystem = 2"><img src="/images/locks/l2.png"
                                            class="max-w-[165px]" width="142px"
                                            :class="[selectedLockSystem === 2 && 'ring-2  ring-red-600  ring-offset-red-600']" />
                                    </div>
                                    <div @click="selectedLockSystem = 3"><img src="/images/locks/l3.png"
                                            class="max-w-[165px]"
                                            :class="[selectedLockSystem === 3 && 'ring-2  ring-red-600  ring-offset-red-600']" />
                                    </div>

                                </div>

                                <div class="max-w-md mx-auto mt-10  justify-center flex">
                                    <Button @click="prevStep()" variant="link" class="text-blue-500 ">
                                        Назад
                                    </Button>

                                    <Button variant="design" @click="nextStep()" class=" w-full">

                                        Выбрать</Button>
                                </div>

                                <Button variant="design" class="mt-15 ms-auto flex  ">
                                    Перейти в демо режим
                                </Button>

                            </div>
                        </template>



                        <template v-if="stepIndex === 4">
                            <div class="">
                                <Badge variant="secondary"
                                    class="text-xs text-gray-800 break-words whitespace-normal max-w-[650px] mt-5">
                                    <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                                            stroke="#545F71" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>

                                    Введите данные своей учётной записи TTLock, чтобы система получила список
                                    замков.Рекомендуется использовать основную учётную запись — она обеспечивает доступ
                                    ко
                                    всем замкам.
                                </Badge>



                                <div class="ml-12" v-if="selectedLockSystem === 1"><img src="/images/locks/l1.png"
                                        class="mt-10 mx-auto" />
                                </div>
                                <div class="ml-12" v-else-if="selectedLockSystem === 2"><img src="/images/locks/l2.png"
                                        class="mt-10 mx-auto" width="142px" />
                                </div>

                                <div class="ml-12" v-else><img src="/images/locks/l3.png" class="mt-10 mx-auto" />
                                </div>
                            </div>

                            <div
                                class="flex flex-col-reverse  lg:flex-row  mx-auto justify-center  lg:w-7/12 xl:w-full ">

                                <div class="self-start lg:self-end mb-8  ">

                                    <Button @click="prevStep()" variant="link" class="text-blue-500 ">
                                        Назад
                                    </Button>
                                </div>

                                <div
                                    class="relative  p-4  md:min-h-min   xl:w-5/12 flex flex-col  items-center justify-center gap-4">
                                    <div class="grid gap-3 mx-3 w-full">
                                        <Label class="w-full min-w-[160px]">Учетная запись </Label>
                                        <Input class="w-full " type="email" placeholder="name@example.ru" />
                                    </div>
                                    <div class="grid w-full gap-3 mx-3 ">

                                        <Label class="w-full min-w-[140px]">Пароль</Label>
                                        <div class="flex">
                                            <Input :type="showPassword ? 'text' : 'password'" class=""
                                                placeholder="пароль" />
                                            <Button @click="togglePassword" variant="design" class="rounded-[5px]">
                                                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12" stroke="#ffffff"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path d="M1 12C1 12 5 20 12 20C19 20 23 12 23 12" stroke="#ffffff"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <circle cx="12" cy="12" r="3" stroke="#ffffff" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </Button>
                                        </div>
                                    </div>
                                    <div class="w-full">
                                        <Button variant="design" @click=" getLocks() ? nextStep() : undefined"
                                            class="my-4 w-full">Подключить замки TTLock</Button>
                                    </div>

                                </div>
                            </div>

                            <Button variant="design" class="mt-15 ms-auto flex  ">
                                Перейти в демо режим
                            </Button>

                        </template>








                        <template v-if="stepIndex === 5">

                            <Badge variant="secondary"
                                class="text-xs text-gray-800 break-words whitespace-normal max-w-[650px] my-5">
                                <svg class="mx-2" width="14" height="14" viewBox="0 0 14 14" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.41667 9.41667H6.75V6.75H6.08333M6.75 4.08333H6.75667M12.75 6.75C12.75 10.0637 10.0637 12.75 6.75 12.75C3.43629 12.75 0.75 10.0637 0.75 6.75C0.75 3.43629 3.43629 0.75 6.75 0.75C10.0637 0.75 12.75 3.43629 12.75 6.75Z"
                                        stroke="#545F71" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>

                                Настройте, какие замки относятся к каким объектам.Перетащите замок из списка к
                                соответствующему объекту.Это нужно, чтобы система понимала, какой замок открывает
                                конкретное
                                помещение.<br />
                                💡 Рекомендации:
                                Один объект может быть связан с несколькими замками (например, входная дверь + дверь в
                                объект).
                                Один замок может быть привязан только к одному объекту.
                            </Badge>


                            <div class="grid   items-start gap-4 md:mx-10">
                                <Alert v-if="successCreateSchow" @click="successCreateSchow = false" class="mb-5">
                                    <CheckCircle2Icon :size="16" />
                                    <AlertTitle class="text-green-500">{{ successCreateText }}</AlertTitle>

                                </Alert>
                            </div>



                            <div class="grid grid-cols-1 md:grid-cols-2  gap-8  mb-7">

                                <div class="mx-auto  md:mx-0  md:ms-auto  w-[360px]">

                                    <div
                                        class="flex-1 border-dashed border-2 px-4  w-full flex flex-col border-gray-500">
                                        <div class="my-3 mx-auto font-bold text-lg">Объекты</div>

                                        <Input v-model="rent_search" placeholder="поиск..." class="mb-8 border-2"
                                            @input="debouncedSearch" />

                                        <div v-for="rent in rents.data" :key="rent.id">

                                            <Card class="relative my-2 pt-6 pb-2 gap-2">
                                                <span class=" m-2 top-0 text-xs absolute text-gray-500">id:{{ rent.id
                                                }}</span>
                                                <CardHeader class="px-2">
                                                    <CardTitle class=""> {{ rent.name }}

                                                    </CardTitle>
                                                    <CardDescription>
                                                        {{ rent.description }}
                                                    </CardDescription>
                                                    <CardAction class="flex gap-3 ">

                                                    </CardAction>
                                                </CardHeader>
                                                <CardContent class=" min-h-[50px]" @dragover.prevent @drop="onAdd(rent)"
                                                    :class="{ 'bg-green-200': isDrag == true, }">



                                                    <Card
                                                        class="w-full cursor-pointer relative gap-2 my-1 pt-4 pb-0 border-green-300 border-1"
                                                        v-for="lock in rent.locks" :key="lock.id" draggable="true"
                                                        @dragstart="onDragStart2(lock, rent)" @dragend="onDragEnd()">
                                                        <span class=" m-1 top-0 text-xs absolute text-gray-500">id:{{
                                                            lock.id
                                                        }}</span>
                                                        <CardHeader class="  ">
                                                            <CardTitle class="">{{ lock.lock_alias }}</CardTitle>
                                                            <CardDescription>
                                                                {{ lock.lock_name }}
                                                            </CardDescription>

                                                            <CardAction>
                                                            </CardAction>
                                                        </CardHeader>

                                                        <CardContent>
                                                        </CardContent>

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


                                        <Pagination class="my-4 self-end" v-model:page="rents.current_page"
                                            :items-per-page="rents.per_page" :total="rents.total"
                                            :default-page="rents.current_page">
                                            <PaginationContent>
                                                <template v-for="(item, index) in rents.links" :key="index">
                                                    <PaginationPrevious v-if="index == 0"
                                                        @click.prevent="goToPage(item.page, free_locks.current_page)" />
                                                    <PaginationNext v-else-if="index == (rents.links.length - 1)"
                                                        @click.prevent="goToPage(item.page, free_locks.current_page)" />
                                                    <PaginationItem
                                                        v-else-if="(rents.current_page - 2 <= item.page) && (rents.current_page + 2 >= item.page)"
                                                        :value="item.page" :is-active="item.page === rents.current_page"
                                                        @click.prevent="goToPage(item.page, free_locks.current_page)">
                                                        {{ item.page }}
                                                    </PaginationItem>
                                                </template>
                                            </PaginationContent>
                                        </Pagination>

                                    </div>




                                </div>


                                <div
                                    class="  w-[360px] md:me-auto mx-auto md:mx-0 min-h-[50px] border-dashed border-2 border-gray-500 flex lg:min-w-[280px]  min-w-[240px] justify-center flex-col px-1 lg:px-4">
                                    <div class="my-3 font-bold mx-auto text-lg">Замки</div>
                                    <Input v-model="lock_search" placeholder="поиск..." class="mb-8 border-2"
                                        @input="debouncedSearch" />
                                    <div @dragover.prevent @drop="onFree()" :class="{ 'bg-green-200': isDrag == true, }"
                                        class=" flex-1  min-h-[50px] items-center border-dashed  flex lg:min-w-[280px]  min-w-[240px] justify-center flex-col ">

                                        <div v-if="free_locks && free_locks.data.length === 0"
                                            class="text-gray-500 text-sm flex justify-center">
                                            <p>Замки не найдены</p>
                                        </div>


                                        <div class="grid grid-cols-2 gap-2 w-full">

                                            <Card
                                                class="w-full cursor-pointer relative gap-2 my-1 pt-4 pb-0 border-green-300 border-1"
                                                v-for="lock in free_locks.data" :key="lock.id" draggable="true"
                                                @dragstart="onDragStart(lock)" @dragend="onDragEnd()">
                                                <span class=" m-1 top-0 text-xs absolute text-gray-500">id:{{ lock.id
                                                }}</span>

                                                <CardHeader class="  ">
                                                    <CardTitle class="">{{ lock.lock_alias }}</CardTitle>
                                                    <CardDescription>
                                                        {{ lock.lock_name }}
                                                    </CardDescription>

                                                    <CardAction>
                                                    </CardAction>
                                                </CardHeader>
                                                <CardContent>
                                                </CardContent>
                                            </Card>
                                        </div>
                                    </div>


                                    <Pagination class="my-4 self-end" v-model:page="free_locks.current_page"
                                        :items-per-page="free_locks.per_page" :total="free_locks.total"
                                        :default-page="free_locks.current_page">
                                        <PaginationContent>
                                            <template v-for="(item, index) in free_locks.links" :key="index">
                                                <PaginationPrevious v-if="index == 0"
                                                    @click.prevent="goToPage(rents.current_page, item.page)" />
                                                <PaginationNext v-else-if="index == (free_locks.links.length - 1)"
                                                    @click.prevent="goToPage(rents.current_page, item.page)" />
                                                <PaginationItem
                                                    v-else-if="(free_locks.current_page - 2 <= item.page) && (free_locks.current_page + 2 >= item.page)"
                                                    :value="item.page"
                                                    :is-active="item.page === free_locks.current_page"
                                                    @click.prevent="goToPage(rents.current_page, item.page)">
                                                    {{ item.page }}
                                                </PaginationItem>
                                            </template>
                                        </PaginationContent>
                                    </Pagination>
                                </div>
                            </div>




                            <div class="flex items-center flex-col md:flex-row justify-center gap-5">
                                <Button @click="prevStep()" variant="link" class="text-blue-500 ">
                                    Назад
                                </Button>
                                <Button variant="design" class="my-4 ">
                                    <a :href="dashboard().url">
                                        Завершить настройку и перейти в
                                        панель управления
                                    </a></Button>

                                <Button variant="design" class="my-4 ">
                                    <a :href="dashboard().url">
                                        Настроить позже
                                    </a></Button>
                            </div>

                        </template>




                    </div>

                </Stepper>

            </div>

        </AppContent>
    </AppShell>


</template>
