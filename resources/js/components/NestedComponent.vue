<template>
    <VueDraggable class="border-dashed border-2 my-5 border-gray-500 p-10 min-h-[50px]" tag="ul" v-model="list"
        group="g1">

        <slot />
        <li v-for="el in modelValue" :key="el.name" class="">
            <div class="flex">
                <div class=" bg-design inline-flex px-2 py-2 items-center justify-center gap-2 text-sm 
                        min-w-[200px] max-w-[200px] font-medium  cursor-grab text-design-foreground
                         shadow-xs hover:bg-design/90 rounded-[20px] lg:min-w-[230px] lg:max-w-[230px] 
                          break-words whitespace-normal h-auto ">
                    {{ el.name }}</div>



                <Button variant="outline" class="my-4 ">Добавить объект</Button>

            </div>

            <nested-component v-model="el.children" class="ms-5">
                <p v-if="el.children.length == 0"> Перетащите сюда ваши объекты, для доступа к которым надо открыть
                    замок
                    объекта {{ el.name }}</p>

            </nested-component>
        </li>

    </VueDraggable>
</template>
<script setup lang="ts">
import { VueDraggable } from 'vue-draggable-plus'
import { computed } from 'vue'
import { Button } from '@/components/ui/button';

interface IList {
    name: string
    children: IList[]
}

interface Props {
    modelValue: IList[]
}

const props = defineProps<Props>()

interface Emits {
    (e: 'update:modelValue', value: IList[]): void
}

const emits = defineEmits<Emits>()
const list = computed({
    get: () => props.modelValue,
    set: value => emits('update:modelValue', value)
})
</script>
