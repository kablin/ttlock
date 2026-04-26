<script setup>
import { computed } from 'vue'
import { RefreshCw } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const props = defineProps({
  locks: {
    type: Array,
    default: () => []
  },
  isRefreshing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['refresh'])

const handleRefresh = () => {
  emit('refresh')
}

// Вычисляемые статусы
const online = computed(() => props.locks.filter(l => l.status === 'online').length)
const offline = computed(() => props.locks.filter(l => l.status === 'offline').length)
const lowBattery = computed(() => props.locks.filter(l => l.status === 'low_battery' || (l.battery_level && l.battery_level < 20)).length)
const errors = computed(() => props.locks.filter(l => l.status === 'error').length)

const statuses = computed(() => [
  { label: 'Замки онлайн', count: online.value, color: 'bg-green-500' },
  { label: 'Замки оффлайн', count: offline.value, color: 'bg-red-500' },
  { label: 'Низкий заряд', count: lowBattery.value, color: 'bg-yellow-500' },
  { label: 'Ошибка загрузки ключа', count: errors.value, color: 'bg-amber-700' },
])
</script>

<template>
  <div class="flex items-center justify-between gap-4">
    <div class="flex items-center gap-6 flex-1">
      <div 
        v-for="(status, idx) in statuses" 
        :key="status.label" 
        :class="cn(
          'flex items-center gap-3 pr-6',
          idx < statuses.length - 1 && 'border-r border-slate-200'
        )"
      >
        <div :class="cn('w-3 h-3 rounded-full flex-shrink-0', status.color)" />
        <div class="min-w-0">
          <p class="text-xs text-slate-500 whitespace-nowrap">{{ status.label }}</p>
          <p class="font-bold text-xl text-slate-900">{{ status.count }}</p>
        </div>
      </div>
    </div>

    <Button 
      variant="outline" 
      size="sm" 
      @click="handleRefresh"
      :disabled="isRefreshing"
      class="gap-2 border-slate-300 h-8 flex-shrink-0"
    >
      <RefreshCw :class="cn('w-3.5 h-3.5', isRefreshing && 'animate-spin')" />
      Обновить
    </Button>
  </div>
</template>