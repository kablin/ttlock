<script setup>
import { X, GripVertical } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  onRemove: {
    type: Function,
    default: null
  },
  className: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['remove'])

const handleRemove = (event) => {
  event.stopPropagation()
  if (props.onRemove) {
    props.onRemove()
  }
  emit('remove')
}
</script>

<template>
  <div :class="`bg-white border border-slate-200 rounded-lg min-w-0  ${className}`">
    <div class="p-4 pb-3 border-b border-slate-100 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <GripVertical class="w-4 h-4 text-slate-300 cursor-move" />
        <h3 class="font-semibold text-slate-900 text-base">{{ title }}</h3>
      </div>
      <div class="flex items-center gap-2">
        <!-- Слот для кастомных действий в хедере -->
        <slot name="header-action" />
        
        <Button 
          v-if="onRemove"
          variant="ghost" 
          size="icon" 
          class="h-7 w-7 text-slate-400 hover:text-slate-600"
          @click="handleRemove"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>
    </div>
    <div class="p-4">
      <!-- Default slot для контента виджета -->
      <slot />
    </div>
  </div>
</template>