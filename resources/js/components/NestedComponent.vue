<template>
    <VueDraggable class="border-dashed border-2 my-5 border-gray-500 p-1 lg:p-10 min-h-[50px]" tag="ul" v-model="list"
        group="g1">

        <slot />
        <li v-for="el in modelValue" :key="el.name" class="">
            <div class="flex items-center">
                <div class=" bg-design inline-flex px-2 py-2 items-center justify-center gap-2 text-sm 
                        min-w-[200px] max-w-[200px] font-medium  cursor-grab text-design-foreground
                         shadow-xs hover:bg-design/90 rounded-[20px] lg:min-w-[230px] lg:max-w-[230px] 
                          break-words whitespace-normal h-auto ">
                    {{ el.name }}</div>





            </div>

            <nested-component v-model="el.children" class="lg:ms-5 ms-1"    v-if="currentDepth < maxDepth"     
              :depth="currentDepth + 1" >
                <p v-if="el.children.length == 0"> Перетащите сюда ваши объекты, для доступа к которым надо открыть
                    замок
                    объекта {{ el.name }}</p>

                <Button variant="" @click="addObject(el)" class=" ">Добавить объект</Button>

            </nested-component>

            <div v-else class="ms-5 text-sm text-red-500 italic">
                Достигнута максимальная глубина вложений.
            </div>
        </li>

    </VueDraggable>
</template>
<script setup lang="ts">
import { VueDraggable } from 'vue-draggable-plus'
import { computed } from 'vue'
import { Button } from '@/components/ui/button';




const addObject = (parentElement: IList) => {
    const tmp: IList = {
        name: 'Замок ' + Math.floor(Math.random() * 1000),
        id: Math.floor(Math.random() * 1000),
        children: []
    };

    // Добавляем новый объект в дочерний список указанного элемента
    parentElement.children.unshift(tmp);

    // Опционально: обновляем модель, если реактивность требует этого явно
    // В большинстве случаев, особенно с Vue 3 и ссылками, это не обязательно,
    // так как изменения в объекте/массиве отслеживаются автоматически.
    // Но если возникают проблемы с реактивностью, можно раскомментировать:
    // emits('update:modelValue', [...props.modelValue]);
}


const maxDepth = 5;
interface IList {
    name: string
    id: number;
    children: IList[]
}

interface Props {
    modelValue: IList[]
    depth?: number; 
}

//const props = defineProps<Props>()


const props = withDefaults(defineProps<Props>(), {
    depth: 0, // По умолчанию - корневой уровень (глубина 0)
});  

const currentDepth = computed(() => props.depth);



interface Emits {
    (e: 'update:modelValue', value: IList[]): void
}

const emits = defineEmits<Emits>()
const list = computed({
    get: () => props.modelValue,
    set: value => emits('update:modelValue', value)
})
</script>
