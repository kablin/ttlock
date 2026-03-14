<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3';
import SetupSteps from '@/components/setup/SetupSteps.vue'
import Mapping from '@/components/MappingLocks.vue'
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { RefreshCw } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { wizard_lock_list, wizard_sync_rents } from '@/routes';

const isSyncing = ref(false)
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

// Синхронизация (перезагрузка данных)
const handleSync = async () => {
  isSyncing.value = true
  let response = await axios.post(wizard_lock_list().url, {})
  if (response.data.status) {
    response = await axios.post(wizard_sync_rents().url, {})
  }
  if (response.data.status) {
    window.location.reload()
  }

  isSyncing.value = false
}



</script>

<template>

  <Head title="Мастер настройки" />
  <div class="min-h-screen bg-white flex flex-col">

    <AppLayout>
      <div class="flex-1 px-6 py-8 w-full">

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-slate-900 mb-2">Привязка замков к объектам</h1>
            <p class="text-slate-500 mb-8">Настройте соответствие замков и объектов недвижимости</p>
          </div>
          <div class="mt-8 flex justify-end mb-6">

            <Button @click="handleSync" :disabled="isSyncing" variant="outline" class="gap-2">
              <RefreshCw :class="cn('w-4 h-4', isSyncing && 'animate-spin')" />
              Синхронизация
            </Button>
          </div>


        </div>



        <Mapping :all_locks="all_locks" :rents="rents" />

      </div>
    </AppLayout>

  </div>
</template>