<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { rents_objects } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { ref, onMounted } from 'vue'
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { VueDraggable } from 'vue-draggable-plus'
import NestedDraggable from '@/components/NestedComponent.vue'




import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'





const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Мои объекты',
        href: rents_objects().url,
    },
];



onMounted(() => {


})



const addObject = () => {

    let tmp = {
        name: 'Замок ' + Math.floor(Math.random() * 1000),
        id: Math.floor(Math.random() * 1000),
        children: []
    }
    locks.value.unshift(tmp)
    return true
}




const locks = ref([
    {
        name: 'Замок 1',
        id: 1,
        children: []
    },
    {
        name: 'Замок с очень длинным именем, вот прям совсем длинным',
        id: 2,
        children: []
    },
    {
        name: 'Замок 3',
        id: 3,
        children: []
    },
    {
        name: 'Замок 4',
        id: 4,
        children: []
    },
    {
        name: 'Замок 5',
        id: 5,
        children: []
    },
    {
        name: 'Замок 6',
        id: 6,
        children: []
    },
    {
        name: 'Замок 7',
        id: 7,
        children: []
    },
    {
        name: 'Замок 8',
        id: 8,
        children: []
    }, {
        name: 'Замок 9',
        id: 9,
        children: []
    },
    {
        name: 'Замок 10',
        id: 10,
        children: []
    }

])





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

            Тут какой-то текст который поясняет, как это все работает и зачем вообще эти объекты нужны
        </Badge>


        {{ locks }}


        <div class="flex justify-between  flex-col lg:p-10">
            <Button variant="" class=" " @click="addObject()">Добавить объект</Button>
            <nested-draggable v-model="locks" >


            </nested-draggable>

        </div>




    </AppLayout>
</template>
