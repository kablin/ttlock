<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Eye, EyeOff, Check, X, Lock } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { cn } from '@/lib/utils'
import { Head } from '@inertiajs/vue3';
import SetupSteps from '@/components/setup/SetupSteps.vue'
import SetupNavigation from '@/components/setup/SetupNavigation.vue'
import AppLayout from '@/layouts/AppLayout.vue';
import {  saveCredential } from '@/routes';
import axios from 'axios';
// Состояния
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isConnecting = ref(false)
const notifications = ref([])
const msg = ref()
// Вычисляемое свойство
const hasSuccess = computed(() => notifications.value.some(n => n.type === 'success'))

// Методы
const handleConnect = async () => {
  if (!email.value.trim() || !password.value.trim()) return

  isConnecting.value = true


  const response = await axios.post(saveCredential().url, {
    "email": email.value,
    'password': password.value
  })


  msg.value = response.data.msg


  const isSuccess = response.data.status

  if (isSuccess) {
    notifications.value.push({
      id: Date.now(),
      type: 'success',
      message: 'Успешно. Синхронизируем замки. Это может занять несколько минут'
    })
  } else {
    notifications.value.push({
      id: Date.now(),
      type: 'error',
      message: msg.value
    })
  }

  isConnecting.value = false
}

const handleNext = () => {
  if (hasSuccess.value) {
    router.visit('/setup/wizard/step5')
  } else {

    handleConnect()
  }
}

const handleBack = () => {
  router.visit('/setup/wizard/step3')
}

const handleSkip = () => {
  router.visit('/setup/wizard/step5')
}

const dismissNotification = (id) => {
  notifications.value = notifications.value.filter(n => n.id !== id)
}
</script>

<template>

  <Head title="Мастер настройки" />
  <div class="min-h-screen bg-white flex flex-col">

    <AppLayout>
      <div class="flex-1 px-6 py-8 max-w-5xl mx-auto w-full">
        <h1 class="text-2xl font-semibold text-slate-900 mb-8">Мастер настройки</h1>

        <SetupSteps :current-step="4" />

        <div class="space-y-8">
          <!-- Info Banner -->
          <div
            class="relative overflow-hidden bg-gradient-to-r from-indigo-50 to-violet-50 rounded-xl border border-indigo-100 p-6">
            <div class="flex items-start gap-4">
              <div
                class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
                <Lock class="w-6 h-6 text-white" />
              </div>
              <div class="space-y-1">
                <h3 class="font-semibold text-slate-900">Подключите замки TTLock</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                  Введите данные своей учётной записи TTLock, чтобы система получила список замков.
                  <br />
                  <span class="text-slate-500">
                    Рекомендуется использовать основную учётную запись — она обеспечивает доступ ко всем замкам.
                  </span>
                </p>
              </div>
            </div>
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-indigo-100 rounded-full opacity-50" />
          </div>

          <!-- TTLock Login Form -->
          <div class="flex flex-col items-center py-6">
            <!-- TTLock Logo -->
            <div class="mb-6 text-center">
              <div
                class="w-20 h-20 rounded-full bg-blue-50 border-2 border-blue-200 flex items-center justify-center mb-2 mx-auto">
                <svg viewBox="0 0 40 40" class="w-12 h-12">
                  <circle cx="20" cy="20" r="18" fill="none" stroke="#3B82F6" stroke-width="2" />
                  <path d="M12 20 C12 14, 16 10, 20 10 C24 10, 28 14, 28 20 M16 20 L16 28 M20 20 L20 30 M24 20 L24 28"
                    stroke="#3B82F6" stroke-width="2" stroke-linecap="round" fill="none" />
                </svg>
              </div>
              <span class="text-blue-600 font-bold text-lg">TTLock</span>
            </div>

            <!-- Form -->
            <div class="w-full max-w-md space-y-4">
              <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Email</label>
                <Input v-model="email" type="email" placeholder="Введите email от TTLock" class="bg-slate-50"
                  :disabled="isConnecting" />
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Пароль</label>
                <div class="relative">
                  <Input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Введите пароль"
                    class="pr-10 bg-slate-50" :disabled="isConnecting" />
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <EyeOff v-if="showPassword" class="w-5 h-5" />
                    <Eye v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Notifications -->
          <div v-if="notifications.length > 0" class="space-y-2">
            <div v-for="notification in notifications" :key="notification.id" :class="cn(
              'flex items-center justify-between gap-3 px-4 py-3 rounded-lg mx-auto max-w-lg',
              notification.type === 'success'
                ? 'bg-green-50 border border-green-200'
                : 'bg-red-50 border border-red-200'
            )">
              <div class="flex items-center gap-3">
                <div :class="cn(
                  'w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0',
                  notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'
                )">
                  <Check v-if="notification.type === 'success'" class="w-4 h-4 text-white" />
                  <X v-else class="w-4 h-4 text-white" />
                </div>
                <span :class="cn(
                  'text-sm font-medium',
                  notification.type === 'success' ? 'text-green-800' : 'text-red-800'
                )">
                  {{ notification.message }}
                </span>
              </div>
              <button @click="dismissNotification(notification.id)" :class="cn(
                'p-1 rounded hover:bg-opacity-20 flex-shrink-0',
                notification.type === 'success' ? 'hover:bg-green-500' : 'hover:bg-red-500'
              )">
                <X :class="cn(
                  'w-4 h-4',
                  notification.type === 'success' ? 'text-green-600' : 'text-red-600'
                )" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
    <SetupNavigation @back="handleBack" @next="handleNext" @skip="handleSkip"
      :next-label="hasSuccess ? 'Продолжить' : 'Подключить замки TTLock'"
      :is-next-disabled="(!email.trim() || !password.trim()) && !hasSuccess" :is-loading="isConnecting"
      loading-label="Подключение..." />
  </div>
</template>