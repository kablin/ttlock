<script setup>
import { Check } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

defineProps({
  currentStep: {
    type: Number,
    required: true
  }
})

const steps = [
  { 
    id: 1, 
    title: '1. Выберите систему управления',
    subtitle: null
  },
  { 
    id: 2, 
    title: '2. Подключите систему управления',
    subtitle: 'Незавершённый шаг'
  },
  { 
    id: 3, 
    title: '3. Выберите замковую систему',
    subtitle: null
  },
  { 
    id: 4, 
    title: '4. Подключите замки',
    subtitle: 'Выберите модель умных замков'
  },
  { 
    id: 5, 
    title: '5. Настройте маппинг замков',
    subtitle: 'Привяжите замки к объектам'
  },
]
</script>

<template>
  <div class="flex items-start gap-0 mb-8 overflow-x-auto pb-2">
    <div 
      v-for="step in steps" 
      :key="step.id" 
      :class="cn(
        'flex-1 min-w-[140px] px-3 py-3 border-t-2 transition-colors',
        step.id < currentStep && 'border-green-500 bg-green-50/50',
        step.id === currentStep && 'border-indigo-600 bg-indigo-50/50',
        step.id > currentStep && 'border-slate-200'
      )"
    >
      <div class="flex items-start gap-2">
        <div :class="cn(
          'w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5',
          step.id < currentStep && 'bg-green-500 text-white',
          step.id === currentStep && 'border-2 border-red-500',
          step.id > currentStep && 'border-2 border-slate-300'
        )">
          <Check v-if="step.id < currentStep" class="w-3 h-3" />
        </div>
        <div>
          <p :class="cn(
            'text-xs font-medium leading-tight',
            step.id < currentStep && 'text-green-700',
            step.id === currentStep && 'text-slate-900',
            step.id > currentStep && 'text-slate-500'
          )">
            {{ step.title }}
          </p>
          <p v-if="step.subtitle && step.id === currentStep" class="text-xs mt-0.5 text-red-500">
            {{ step.subtitle }}
          </p>
          <p v-if="step.subtitle && step.id !== currentStep && step.id > currentStep" class="text-xs mt-0.5 text-slate-400">
            {{ step.subtitle }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>