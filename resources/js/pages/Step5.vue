<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3';
import SetupSteps from '@/components/setup/SetupSteps.vue'
import Mapping from '@/components/MappingLocks.vue'
import SetupNavigation from '@/components/setup/SetupNavigation.vue'
import AppLayout from '@/layouts/AppLayout.vue';




const props = defineProps({
  all_locks: {
    type: Object,
    default: true
  },
  rents: {
    type: Object,
    default: true
  },
})


const handleComplete = async () => {
    router.visit('/dashboard')
}


const handleBack = () => {
  router.visit('/setup/wizard/step4')
}


</script>

<template>

  <Head title="Мастер настройки" />
  <div class="min-h-screen bg-white flex flex-col">

    <AppLayout>
      <div class="flex-1 px-6 py-8 max-w-5xl mx-auto w-full">
        <h1 class="text-2xl font-semibold text-slate-900 mb-2">Привязка замков к объектам</h1>
        <p class="text-slate-500 mb-8">Настройте соответствие замков и объектов недвижимости</p>

        <SetupSteps :current-step="5" />

        <Mapping  :all_locks="all_locks" :rents="rents"/>


      </div>
    </AppLayout>
    <SetupNavigation @next="handleComplete" @back="handleBack" :show-back="true" :show-skip="false"
      next-label="Завершить настройку" :is-loading="isSaving" />
  </div>
</template>