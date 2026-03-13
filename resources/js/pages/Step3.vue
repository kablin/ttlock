<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Lock } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Head } from '@inertiajs/vue3';
import SetupSteps from '@/components/setup/SetupSteps.vue'
import SetupNavigation from '@/components/setup/SetupNavigation.vue'
import AppLayout from '@/layouts/AppLayout.vue';
const lockSystems = [
  {
    id: 'ttlock',
    name: 'TTLock',
    available: true,
  },
  {
    id: 'ozlocks',
    name: 'OZLOCKS',
    available: false,
  },
  {
    id: 'tuya',
    name: 'tuya',
    available: false,
  },
]

const selectedSystem = ref(null)

const handleNext = () => {
  if (selectedSystem.value) {
    router.visit(`/setup/wizard/step4?lockSystem=${selectedSystem.value}`)
  }
}

const handleBack = () => {
  router.visit('/setup/wizard/step2')
}

const handleSkip = () => {
  router.visit('/setup/wizard/step4')
}
</script>

<template>
  <Head title="Мастер настройки" />
  <div class="min-h-screen bg-white flex flex-col">

    <AppLayout>



    <div class="flex-1 px-6 py-8 max-w-5xl mx-auto w-full">
      <h1 class="text-2xl font-semibold text-slate-900 mb-8">Мастер настройки</h1>

      <SetupSteps :current-step="3" />

      <div class="space-y-8">
        <!-- Info Banner -->
        <div class="relative overflow-hidden bg-gradient-to-r from-indigo-50 to-violet-50 rounded-xl border border-indigo-100 p-6">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
              <Lock class="w-6 h-6 text-white" />
            </div>
            <div class="space-y-1">
              <h3 class="font-semibold text-slate-900">Выберите замковую систему</h3>
              <p class="text-sm text-slate-600 leading-relaxed">
                Укажите, с какими умными замками вы работаете. После выбора вы сможете подключить замки и настроить их работу с объектами.
              </p>
            </div>
          </div>
          <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-indigo-100 rounded-full opacity-50" />
        </div>

        <!-- Lock Systems Grid -->
        <div class="flex flex-wrap justify-center gap-8 py-12">
          <button
            v-for="system in lockSystems"
            :key="system.id"
            @click="system.available && (selectedSystem = system.id)"
            :disabled="!system.available"
            :class="cn(
              'w-36 h-36 rounded-2xl border-2 flex flex-col items-center justify-center gap-3 transition-all',
              selectedSystem === system.id 
                ? 'border-indigo-600 shadow-lg bg-indigo-50/50' 
                : 'border-slate-200 hover:border-slate-300 bg-white',
              !system.available && 'opacity-50 cursor-not-allowed'
            )"
          >
            <div v-if="system.id === 'ttlock'" class="w-16 h-16 rounded-full bg-blue-50 border-2 border-blue-200 flex items-center justify-center">
              <svg viewBox="0 0 40 40" class="w-10 h-10">
                <circle cx="20" cy="20" r="18" fill="none" stroke="#3B82F6" stroke-width="2"/>
                <path d="M12 20 C12 14, 16 10, 20 10 C24 10, 28 14, 28 20 M16 20 L16 28 M20 20 L20 30 M24 20 L24 28" 
                      stroke="#3B82F6" stroke-width="2" stroke-linecap="round" fill="none"/>
              </svg>
            </div>
            <div v-if="system.id === 'ozlocks'" class="w-16 h-16 rounded-full bg-slate-100 border-2 border-slate-300 flex items-center justify-center">
              <span class="text-slate-600 font-bold text-lg">OZ</span>
            </div>
            <div v-if="system.id === 'tuya'" class="w-16 h-16 rounded-lg bg-orange-500 flex items-center justify-center">
              <span class="text-white font-bold text-lg">tuya</span>
            </div>
            <span :class="cn(
              'font-semibold text-sm',
              system.id === 'ttlock' && 'text-blue-600',
              system.id === 'ozlocks' && 'text-slate-700',
              system.id === 'tuya' && 'text-orange-500'
            )">
              {{ system.name }}
            </span>
            <span v-if="!system.available" class="text-xs text-slate-400">Скоро</span>
          </button>
        </div>
      </div>
    </div>
 </AppLayout>
    <SetupNavigation
      @back="handleBack"
      @next="handleNext"
      @skip="handleSkip"
      next-label="Выбрать"
      :is-next-disabled="!selectedSystem"
    />
  </div>
</template>