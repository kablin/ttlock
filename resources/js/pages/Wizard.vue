<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { wizard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge';
import { Stepper, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'




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
        title: 'Подключите замки',
        description: '3 Выберите нужную модель умных замков',
    },
    {
        step: 4,
        title: 'Настройте маппинг',
        description: '4. Привяжите замки к объектам',
    },
]

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Мастер',
        href: wizard().url,
    },
];
</script>

<template>

    <Head title="Мастер" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-full">

            <Stepper orientation="vertical"  v-model="stepIndex" class="block w-full my-4" v-slot="{ modelValue }">

                <div class="flex w-full  flex-start gap-2">


                    <StepperItem v-for="(item, index) in steps" :key="item.step" :step="item.step" v-slot="{ state }"
                        class="relative flex w-full flex-col items-center ">

                        <StepperSeparator v-if="item.step !== steps[steps.length - 1]?.step"
                            class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-6 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary" />

                        <StepperTrigger>
                            <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
                                size="icon" class="z-10 rounded-full shrink-0"
                                :class="[state === 'active' && 'bg-gray-600 ring-2 ring-ring ring-offset-2 ring-offset-background']"
                                :disabled="state !== 'completed' && (index >= (modelValue || 0))">


                                <svg v-if="state == 'completed'" width="14" height="10" viewBox="0 0 14 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
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

                        <div class="flex flex-col items-center">
                            <StepperTitle>
                                {{ item.title }}
                            </StepperTitle>
                            <StepperDescription>
                                {{ item.description }}
                            </StepperDescription>
                        </div>
                    </StepperItem>
                </div>

                <div class="flex flex-col  gap-4  p-8  h-full">

                    <template v-if="stepIndex === 1">



                        <div class="">
                            <Badge variant="secondary" class="">
                                <p class="text-xs text-gray-800">Выберите из списка вашу систему управления
                                    недвижимостью</p>
                            </Badge>

                            <div class="flex items-center justify-center gap-3  mt-10">
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


                            <div class="max-w-md mx-auto mt-10"><Button variant="design" class=" w-full">

                                    Выбрать</Button>
                            </div>
                        </div>

                    </template>
                </div>

            </Stepper>

        </div>
    </AppLayout>
</template>
