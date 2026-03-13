<script setup>
import { ChevronLeft } from 'lucide-vue-next'
import { Button } from '@/components/ui/button';


const props = defineProps({
  onBack: {
    type: Function,
    default: null
  },
  onNext: {
    type: Function,
    required: true
  },
  onSkip: {
    type: Function,
    default: null
  },
  nextLabel: {
    type: String,
    default: 'Продолжить'
  },
  skipLabel: {
    type: String,
    default: 'Пропустить'
  },
  showBack: {
    type: Boolean,
    default: true
  },
  showSkip: {
    type: Boolean,
    default: true
  },
  isNextDisabled: {
    type: Boolean,
    default: false
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  loadingLabel: {
    type: String,
    default: 'Загрузка...'
  }
})

const emit = defineEmits(['back', 'next', 'skip'])

const handleBack = () => {
  if (props.onBack) {
    props.onBack()
  }
  emit('back')
}

const handleNext = () => {
  //props.onNext()
  emit('next')

}

const handleSkip = () => {
  if (props.onSkip) {
    props.onSkip()
  }
  emit('skip')
}
</script>

<template>
  <div class="sticky bottom-0 border-t border-slate-200 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] mt-8">
    <div class="max-w-5xl mx-auto px-6 py-4">
      <div class="flex items-center justify-between gap-4">
        <!-- Back -->
        <div class="w-32">
          <Button
            v-if="showBack && onBack"
            variant="ghost"
            @click="handleBack"
            class="text-slate-600 hover:text-slate-900 px-2"
          >
            <ChevronLeft class="w-4 h-4 mr-1" />
            Назад
          </Button>
        </div>

        <!-- Primary CTA -->
        <Button
          @click="handleNext"
          :disabled="isNextDisabled || isLoading"
          class="flex-1 max-w-md bg-indigo-600 hover:bg-indigo-700 text-white h-11"
        >
          {{ isLoading ? loadingLabel : nextLabel }}
        </Button>

        <!-- Skip -->
        <div class="w-32 text-right">
          <Button
            v-if="showSkip && onSkip"
            variant="ghost"
            @click="handleSkip"
            class="text-slate-500 hover:text-slate-700"
          >
            {{ skipLabel }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>