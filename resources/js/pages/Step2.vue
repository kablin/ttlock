<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Eye, EyeOff, ExternalLink, Check, X, Loader2, Link as LinkIcon, Copy } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { cn } from '@/lib/utils'
import { Head } from '@inertiajs/vue3';
import SetupSteps from '@/components/setup/SetupSteps.vue'
import SetupNavigation from '@/components/setup/SetupNavigation.vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { wizard_sync_rents } from '@/routes';
import axios from 'axios';



const page = usePage();
const copied = ref(false)
// Состояния
const token = ref(page.props.auth.user.realty_key)
const showToken = ref(false)
const isConnecting = ref(false)
const connectionStatus = ref(null) // 'success' | 'error' | null
const foundObjects = ref(0)

// Получаем параметр integration из URL
const urlParams = new URLSearchParams(window.location.search)
const integration = urlParams.get('integration') || 'realtycalendar'


// Копирование в буфер
const copyToClipboard = async () => {
  if (!token.value) return

  try {
    await navigator.clipboard.writeText(token.value)
    copied.value = true

    // Сброс через 2 секунды
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Не удалось скопировать:', err)
    // Fallback для старых браузеров
    const textarea = document.createElement('textarea')
    textarea.value = token.value
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand('copy')
    document.body.removeChild(textarea)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  }
}


// Методы
const handleConnect = async () => {
  if (!token.value.trim()) return

  isConnecting.value = true
  connectionStatus.value = null

  let response = await axios.post(wizard_sync_rents().url, {})

  let isSuccess = response.data.status
  if (isSuccess) {
    connectionStatus.value = 'success'
    foundObjects.value = response.data.count

  }


  isConnecting.value = false
}

const handleNext = () => {
  if (connectionStatus.value === 'success') {
    router.visit('/setup/wizard/step3')
  } else {
    handleConnect()
  }
}

const handleBack = () => {
  router.visit('/setup/wizard/step1')
}

const handleSkip = () => {
  router.visit('/setup/wizard/step3')
}

const dismissNotification = () => {
  connectionStatus.value = null
}
</script>

<template>

  <Head title="Мастер настройки" />
  <div class="min-h-screen bg-white flex flex-col">

    <AppLayout>




      <div class="flex-1 px-6 py-8 max-w-5xl mx-auto w-full">
        <h1 class="text-2xl font-semibold text-slate-900 mb-8">Мастер настройки</h1>

        <SetupSteps :current-step="2" />

        <div class="space-y-8">
          <!-- Info Banner -->
          <div
            class="relative overflow-hidden bg-gradient-to-r from-indigo-50 to-violet-50 rounded-xl border border-indigo-100 p-6">
            <div class="flex items-start gap-4">
              <div
                class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
                <LinkIcon class="w-6 h-6 text-white" />
              </div>
              <div class="space-y-1">
                <h3 class="font-semibold text-slate-900">Подключите систему управления недвижимостью</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                  Cкопируйте токен, перейдите в RealtyCalendar, и вставьте его.
                  Все ваши объекты подгрузятся в интерфейс, чтобы в дальнейшем связать их с замками.
                </p>
              </div>
            </div>
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-indigo-100 rounded-full opacity-50" />
          </div>

          <!-- Connection Form -->
          <div class="flex flex-col lg:flex-row items-center lg:items-start gap-8 py-6">
            <!-- Logo -->
            <div class="flex-shrink-0">
              <div
                class="w-32 h-32 bg-white rounded-2xl shadow-lg border border-slate-100 flex items-center justify-center p-4">
                <div class="text-center">
                  <div class="flex items-center justify-center gap-1 mb-1">
                    <div
                      class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-md">
                      R
                    </div>
                    <div
                      class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-lg flex items-center justify-center shadow-md">
                      <span class="text-slate-800 font-bold text-xs">C</span>
                    </div>
                  </div>
                  <span class="text-sm font-semibold text-slate-800 leading-tight block mt-2">
                    Realty<br />Calendar
                  </span>
                </div>
              </div>
            </div>

            <!-- Form -->
            <div class="flex-1 w-full max-w-md space-y-4">
              <Button variant="outline" class="w-full justify-center gap-2" as-child>
                <a href="https://realtycalendar.ru" target="_blank" rel="noopener noreferrer">
                  Перейти в RealtyCalendar
                  <ExternalLink class="w-4 h-4" />
                </a>
              </Button>

              <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Токен</label>
                <div class="relative">
                  <Input v-model="token" :type="showToken ? 'text' : 'password'"
                    placeholder="Вставьте токен из RealtyCalendar" class="pr-20 bg-slate-50" :disabled="isConnecting" />

                  <!-- Кнопки действий (справа внутри input) -->
                  <div class="absolute right-1 top-1/2 -translate-y-1/2 flex items-center gap-1">

                    <!-- 🔘 Копировать -->
                    <button type="button" @click="copyToClipboard" :disabled="!token"
                      class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-200 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                      title="Скопировать токен">
                      <Copy v-if="!copied" class="w-4 h-4" />
                      <Check v-else class="w-4 h-4 text-green-600" />
                    </button>

                    <!-- 👁️ Показать/скрыть -->
                    <button type="button" @click="showToken = !showToken"
                      class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors"
                      :title="showToken ? 'Скрыть токен' : 'Показать токен'">
                      <EyeOff v-if="showToken" class="w-4 h-4" />
                      <Eye v-else class="w-4 h-4" />
                    </button>

                  </div>
                </div>

                <!-- Подсказка после копирования -->
                <p v-if="copied" class="text-xs text-green-600 flex items-center gap-1">
                  <Check class="w-3 h-3" />
                  Токен скопирован в буфер обмена
                </p>
              </div>



            </div>
          </div>

          <!-- Success/Error Notification -->
          <div v-if="connectionStatus" class="flex justify-center">
            <div :class="cn(
              'inline-flex items-center gap-3 px-4 py-3 rounded-lg',
              connectionStatus === 'success'
                ? 'bg-green-50 border border-green-200'
                : 'bg-red-50 border border-red-200'
            )">
              <div :class="cn(
                'w-6 h-6 rounded-full flex items-center justify-center',
                connectionStatus === 'success' ? 'bg-green-500' : 'bg-red-500'
              )">
                <Check v-if="connectionStatus === 'success'" class="w-4 h-4 text-white" />
                <X v-else class="w-4 h-4 text-white" />
              </div>
              <span :class="cn(
                'text-sm font-medium',
                connectionStatus === 'success' ? 'text-green-800' : 'text-red-800'
              )">
                {{ connectionStatus === 'success'
                  ? `Успешно. Найдено ${foundObjects} объектов`
                  : 'Ошибка подключения. Проверьте токен'
                }}
              </span>
              <button @click="dismissNotification" :class="cn(
                'p-1 rounded hover:bg-opacity-20',
                connectionStatus === 'success' ? 'hover:bg-green-500' : 'hover:bg-red-500'
              )">
                <X :class="cn(
                  'w-4 h-4',
                  connectionStatus === 'success' ? 'text-green-600' : 'text-red-600'
                )" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
    <SetupNavigation @back="handleBack" @next="handleNext" @skip="handleSkip"
      :next-label="connectionStatus === 'success' ? 'Продолжить' : 'Подключить RealtyCalendar'"
      :is-next-disabled="!token.trim() && connectionStatus !== 'success'" :is-loading="isConnecting"
      loading-label="Подключение..." />
  </div>
</template>