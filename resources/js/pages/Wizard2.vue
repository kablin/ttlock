<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue';
// import { route } from 'ziggy-js' // Раскомментируйте, если у вас настроен Ziggy
import { cn } from '@/lib/utils'
import { Head } from '@inertiajs/vue3';
import SetupSteps from '@/components/setup/SetupSteps.vue'
import SetupNavigation from '@/components/setup/SetupNavigation.vue'

const integrations = [
  {
    id: 'travelline',
    name: 'TravelLine',
    available: false,
  },
  {
    id: 'realtycalendar',
    name: 'Realty Calendar',
    available: true,
  },
  {
    id: 'bitrix24',
    name: 'Bitrix24',
    available: false,
  },
  {
    id: 'yclients',
    name: 'YClients',
    available: false,
  },
]

const selectedIntegration = ref(null)

const handleNext = () => {
  if (selectedIntegration.value) {
    // Вариант 1: Если используете Ziggy (рекомендуется в Laravel)
    // router.visit(route('setup.wizard.step2', { integration: selectedIntegration.value }))

    // Вариант 2: Прямой путь (если Ziggy нет)
    router.visit(`/setup/wizard/step2?integration=${selectedIntegration.value}`)
  }
}

const handleSkip = () => {
  // router.visit(route('setup.wizard.step2'))
  router.visit('/setup/wizard/step2')
}
</script>

<template>


  <Head title="Мастер настройки" />
  <div class="min-h-screen bg-white flex flex-col">

    <AppLayout>



      <!-- Main Content -->
      <div class="flex-1 px-6 py-8 max-w-5xl mx-auto w-full">
        <h1 class="text-2xl font-semibold text-slate-900 mb-8">Мастер настройки</h1>

        <SetupSteps :current-step="1" />

        <!-- Step Content -->
        <div class="space-y-8">
          <!-- Info Banner -->
          <div
            class="relative overflow-hidden bg-gradient-to-r from-indigo-50 to-violet-50 rounded-xl border border-indigo-100 p-6">
            <div class="flex items-start gap-4">
              <div
                class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
              </div>
              <div class="space-y-1">
                <h3 class="font-semibold text-slate-900">Подключите вашу систему управления</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                  Выберите PMS или CRM, которую вы используете для управления арендой.
                  После подключения ваши бронирования будут автоматически синхронизироваться с замками.
                </p>
              </div>
            </div>
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-indigo-100 rounded-full opacity-50" />
          </div>

          <!-- Integrations Grid -->
          <div class="flex flex-wrap justify-center gap-6 py-8">
            <button v-for="integration in integrations" :key="integration.id"
              @click="integration.available && (selectedIntegration = integration.id)"
              :disabled="!integration.available" :class="cn(
                'w-28 h-28 rounded-xl border-2 flex flex-col items-center justify-center gap-2 transition-all',
                selectedIntegration === integration.id
                  ? 'border-indigo-600 shadow-lg'
                  : 'border-slate-200 hover:border-slate-300',
                !integration.available && 'opacity-50 cursor-not-allowed'
              )">
              <div v-if="integration.id === 'travelline'"
                class="w-16 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-xs leading-tight text-center">TRAVEL<br />LINE</span>
              </div>
              <div v-if="integration.id === 'realtycalendar'"
                class="w-16 h-12 border border-slate-200 rounded-lg flex items-center justify-center gap-1">
                <div class="w-6 h-6 bg-red-500 rounded text-white text-xs flex items-center justify-center font-bold">R
                </div>
                <span class="text-xs font-medium text-slate-700 leading-tight">Realty<br />Calendar</span>
              </div>
              <div v-if="integration.id === 'bitrix24'" class="w-16 h-12 flex items-center justify-center">
                <span class="text-blue-600 font-bold text-lg">Bitrix<span class="text-blue-400">24</span></span>
              </div>
              <div v-if="integration.id === 'yclients'"
                class="w-16 h-12 bg-yellow-400 rounded-lg flex items-center justify-center">
                <span class="text-slate-900 font-bold text-sm">yclients</span>
              </div>
              <span v-if="!integration.available" class="text-xs text-slate-400">Скоро</span>
            </button>
          </div>
        </div>
      </div>
    </AppLayout>
    <SetupNavigation @next="handleNext" @skip="handleSkip" :show-back="false" next-label="Выбрать"
      :is-next-disabled="!selectedIntegration" class="" />
  </div>


</template>