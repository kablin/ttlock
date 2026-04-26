<script setup>
import { Link as LinkIcon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
// 🔥 Inertia Link для навигации
import { Link } from '@inertiajs/vue3'

// 🔥 Ваш роут из @/routes (как в других компонентах)
 import { wizard_step5 } from '@/routes'
</script>

<template>
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs text-slate-500">Привязка замков</p>
      <p class="font-semibold text-slate-900">Настроить маппинг</p>
    </div>
    
    <!-- 🔥 Вариант 1: Inertia Link (рекомендуется) -->
    <Link :href="wizard_step5().url">
      <Button class="bg-indigo-600 hover:bg-indigo-700">
        <LinkIcon class="w-4 h-4 mr-2" />
        Подключить
      </Button>
    </Link>

  </div>
</template>