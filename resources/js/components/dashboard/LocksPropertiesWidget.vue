<script setup>
import { ShoppingBag } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const props = defineProps({
  locksCount: {
    type: Number,
    default: 0
  },
  propertiesCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['buy-click'])

const handleBuyClick = () => {
  emit('buy-click')
}
</script>

<template>
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs text-slate-500">Замки | объекты</p>
      <p class="font-semibold text-slate-900 text-lg">
        {{ locksCount }} | {{ propertiesCount }}
      </p>
    </div>
    <Button 
      class="bg-indigo-600 hover:bg-indigo-700"
      @click="handleBuyClick"
    >
      <ShoppingBag class="w-4 h-4 mr-2" />
      Купить замки
    </Button>
  </div>
</template>