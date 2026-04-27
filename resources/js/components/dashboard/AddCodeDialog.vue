<script setup>
import { ref, computed, watch } from 'vue'
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date'
import { Calendar as CalendarIcon, Copy, Shuffle, KeyRound, Clock } from 'lucide-vue-next'
import axios from 'axios';
import { parse, format, isValid } from 'date-fns'
import { addCodeToLock, changeCode} from '@/routes';
import { ru } from 'date-fns/locale'

import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTrigger,
    DialogTitle,
    DialogClose,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { Calendar } from '@/components/ui/calendar'
import { Switch } from '@/components/ui/switch'
import { cn } from '@/lib/utils'

const props = defineProps({
    open: { type: Boolean, default: false },
    onOpenChange: { type: Function, required: true },
    code: { type: Object, default: null },
    selectedLock: { type: Object, default: null }
})

const emit = defineEmits(['success'])

// Состояния формы
const keyType = ref('Клиент')
const guestName = ref('')
const isPermanent = ref(false)

// Даты и время (используем @internationalized/date как в вашем коде)
const dateFrom = ref(today(getLocalTimeZone()))
const dateTo = ref(today(getLocalTimeZone()).add({ days: 2 }));
const timeFrom = ref('00:00')
const timeTo = ref('23:59')

// Календари
const openCalendFrom = ref(false)
const openCalendTo = ref(false)

// Генерация кода
const useRandomCode = ref(true)
const previewCode = ref('')
const isGenerating = ref(false)

// Типы ключей
const keyTypes = [
    { value: 'client', label: 'Клиент' },
    { value: 'staff', label: 'Персонал' }
]

// Генерация случайного 4-значного кода
const generateRandomCode = () => {
    return Math.floor(1000 + Math.random() * 9000).toString() + '#'
}

// Обновление превью кода
const refreshPreviewCode = () => {
    if (useRandomCode.value) {
        previewCode.value = generateRandomCode()
    }
}


const formatCustomDate = (dateStr, time = false) => {
    if (!dateStr) return ''
    // Парсим строку "26.04.2026 00:00:00" в валидную JS Date
    const parsed = parse(dateStr, 'dd.MM.yyyy HH:mm:ss', new Date())
    if (!time)
        return isValid(parsed) ? format(parsed, 'dd.MM.yyyy', { locale: ru }) : ''
    else
        return isValid(parsed) ? format(parsed, 'HH:mm', { locale: ru }) : ''
}


// Инициализация при открытии
const initForm = () => {
    guestName.value = ''
    keyType.value = props.code?.code_name ?? 'Клиент'
    isPermanent.value = false


    const parsedDate = props.code
        ? parse(props.code.start_local, 'dd.MM.yyyy HH:mm:ss', new Date())
        : new Date();
    const safeDate = isNaN(parsedDate.getTime()) ? new Date() : parsedDate;

    dateFrom.value = new CalendarDate(
        safeDate.getFullYear(),
        safeDate.getMonth() + 1, // CalendarDate ожидает месяц 1–12
        safeDate.getDate()
    );

    timeFrom.value = format(safeDate, 'HH:mm', { locale: ru })


    const parsedDateto = props.code
        ? parse(props.code.end_local, 'dd.MM.yyyy HH:mm:ss', new Date())
        : new Date(today(getLocalTimeZone()).add({ days: 2 }));
    const safeDateto = isNaN(parsedDateto.getTime()) ? new Date() : parsedDateto;

    dateTo.value = new CalendarDate(
        safeDateto.getFullYear(),
        safeDateto.getMonth() + 1, // CalendarDate ожидает месяц 1–12
        safeDateto.getDate()
    );
    timeTo.value = format(safeDateto, 'HH:mm', { locale: ru })
    useRandomCode.value = true

    refreshPreviewCode()
}

// Обработчики
const handleToggleRandom = (val) => {
    useRandomCode.value = val
    if (val) refreshPreviewCode()
}

const handleRegenerate = () => {
    isGenerating.value = true
    // Небольшая анимация
    setTimeout(() => {
        refreshPreviewCode()
        isGenerating.value = false
    }, 200)
}

const copyCode = async () => {
    if (previewCode.value) {
        await navigator.clipboard.writeText(previewCode.value)
    }
}

// Валидация
const isFormValid = computed(() => {
    return true //guestName.value.trim().length > 0 && previewCode.value
})






// Отправка формы
const handleSubmit = async () => {
    if (!isFormValid.value) return

    try {
        if (isPermanent.value) {
            dateTo.value = today(getLocalTimeZone()).add({ years: 5 });
        }

        if (props.code) {
            const response = await axios.post(changeCode().url, {
                'lock_id': props.selectedLock.lock_id,
                'code_id': props.code.pin_code_id,
                'utc': '-3',
                'begin': (dateFrom.value && timeFrom.value) ? dateFrom.value + ' ' + timeFrom.value : null,
                'end': (dateTo.value && timeTo.value) ? dateTo.value + ' ' + timeTo.value : null,
            }, {
                headers: {
                    'Content-Type': 'application/json',
                }
            })

        }
        else {
            const response = await axios.post(addCodeToLock().url, {
                'lock_id': props.selectedLock.lock_id,
                'code': null,
                'utc': '-3',
                'code_name': keyType.value,
                'begin': (dateFrom.value && timeFrom.value) ? dateFrom.value + ' ' + timeFrom.value : null,
                'end': (dateTo.value && timeTo.value) ? dateTo.value + ' ' + timeTo.value : null,
            }, {
                headers: {
                    'Content-Type': 'application/json',
                }
            })

        }


        emit('success', { code: previewCode.value, name: guestName.value })
        props.onOpenChange(false)
    } catch (error) {
        console.error('Ошибка создания ключа:', error)
        // 🔥 Добавьте обработку ошибки (toast, alert и т.д.)
    }
}

// Вспомогательная: объединение даты и времени
const combineDateTime = (date, time) => {
    if (!date) return new Date().toISOString()
    const [hours, minutes] = time.split(':')
    const d = date.toDate(getLocalTimeZone())
    d.setHours(parseInt(hours), parseInt(minutes), 0, 0)
    return d.toISOString()
}

// Инициализация при открытии диалога
watch(() => props.open, (newVal) => {
    if (newVal) initForm()
    // @update:open="onOpenChange"
})
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <DialogContent class="sm:max-w-[600px]">
            <form @submit.prevent="handleSubmit">
                <DialogHeader>
                    <DialogTitle>
                      {{ code ? 'Изменить ключ':'Добавить ключ' }}  
                        <span v-if="selectedLock?.lock_alias" class="text-slate-500 font-normal">
                            для {{ selectedLock.lock_alias }}
                        </span>
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-5 py-4">

                    <!-- Тип ключа: визуальные кнопки -->
                    <div v-if="!code">
                        <Label class="text-sm font-medium">Тип ключа</Label>
                        <div class="flex gap-2 mt-2">
                            <button v-for="type in keyTypes" :key="type.value" type="button"
                                @click="keyType = type.label" :class="cn(
                                    'flex-1 px-3 py-2 rounded-lg border-2 text-sm font-medium transition-all',
                                    keyType === type.label
                                        ? 'border-indigo-600 bg-indigo-50 text-indigo-700'
                                        : 'border-slate-200 hover:border-slate-300 text-slate-700'
                                )">
                                {{ type.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Имя гостя / сотрудника -->
                    <!--div>
            <Label for="guestName">Имя *</Label>
            <Input
              id="guestName"
              v-model="guestName"
              :placeholder="keyType === 'staff' ? 'Анна Смирнова' : 'Иван Иванов'"
              required
              class="mt-1.5"
            />
          </div-->

                    <!-- Блок генерации кода -->
                    <!--div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <KeyRound class="w-4 h-4 text-slate-500" />
                <span class="text-sm font-medium text-slate-700">Код доступа</span>
              </div>
              <div class="flex items-center gap-2">
                <span :class="cn('text-xs', !useRandomCode ? 'text-indigo-600 font-medium' : 'text-slate-400')">
                  Вручную
                </span>
                <Switch :checked="useRandomCode" @update:checked="handleToggleRandom" />
                <span :class="cn('text-xs', useRandomCode ? 'text-indigo-600 font-medium' : 'text-slate-400')">
                  Случайный
                </span>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div :class="cn(
                'flex-1 flex items-center justify-center gap-3 rounded-lg px-4 py-3 border-2',
                previewCode ? 'border-indigo-200 bg-indigo-50' : 'border-dashed border-slate-300 bg-white'
              )">
                <span v-if="previewCode" class="text-2xl font-bold font-mono text-indigo-700 tracking-[0.2em]">
                  {{ previewCode }}
                </span>
                <span v-else class="text-sm text-slate-400">
                  Код будет сгенерирован
                </span>
                <Button
                  v-if="previewCode"
                  type="button"
                  size="icon"
                  variant="ghost"
                  class="h-7 w-7 text-indigo-400 hover:text-indigo-600"
                  @click="copyCode"
                >
                  <Copy class="w-3.5 h-3.5" />
                </Button>
              </div>
              <Button
                v-if="useRandomCode"
                type="button"
                variant="outline"
                size="sm"
                @click="handleRegenerate"
                class="gap-1.5 shrink-0"
                :disabled="isGenerating"
              >
                <Shuffle :class="cn('w-3.5 h-3.5', isGenerating && 'animate-spin')" />
                Новый
              </Button>
            </div>
          </div-->
                    <!-- Переключатель бессрочного доступа -->
                    <div class="flex items-center justify-between p-3 border rounded-lg bg-slate-50">
                        <div>
                            <Label for="isPermanent" class="text-sm font-medium">Бессрочный доступ</Label>
                            <p class="text-xs text-slate-500 mt-0.5">Без ограничения по дате окончания</p>
                        </div>

                        <Switch id="isPermanent" v-model="isPermanent" />
                    </div>

                    <!-- Период действия -->
                    <template v-if="!isPermanent">
                        <div class="grid grid-cols-2 gap-3 max-lg:grid-cols-1">
                            <!-- Начало -->
                            <div class="space-y-2">
                                <Label class="text-xs text-slate-600">Начало</Label>
                                <div class="grid grid-cols-2 gap-2">
                                    <Popover v-model:open="openCalendFrom">
                                        <PopoverTrigger as-child>
                                            <Button variant="outline"
                                                class="justify-start text-left font-normal h-8 text-xs px-2">
                                                <CalendarIcon class="mr-1 h-3 w-3" />
                                                {{ dateFrom ?
                                                    dateFrom.toDate(getLocalTimeZone()).toLocaleDateString('ru-RU') : 'Дата'
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0" align="start">
                                            <Calendar locale="ru-RU" :model-value="dateFrom" @update:model-value="(value) => {
                                                if (value) {
                                                    dateFrom = value
                                                    openCalendFrom = false
                                                }
                                            }" />
                                        </PopoverContent>
                                    </Popover>
                                    <div class="relative">
                                        <Clock
                                            class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                                        <Input v-model="timeFrom" type="time" class="pl-8 h-8 text-xs" />
                                    </div>
                                </div>
                            </div>

                            <!-- Окончание -->
                            <div class="space-y-2">
                                <Label class="text-xs text-slate-600">Окончание</Label>
                                <div class="grid grid-cols-2 gap-2">
                                    <Popover v-model:open="openCalendTo">
                                        <PopoverTrigger as-child>
                                            <Button variant="outline"
                                                class="justify-start text-left font-normal h-8 text-xs px-2">
                                                <CalendarIcon class="mr-1 h-3 w-3" />
                                                {{ dateTo ?
                                                    dateTo.toDate(getLocalTimeZone()).toLocaleDateString('ru-RU') : 'Дата'
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0" align="start">
                                            <Calendar locale="ru-RU" :model-value="dateTo" @update:model-value="(value) => {
                                                if (value) {
                                                    dateTo = value
                                                    openCalendTo = false
                                                }
                                            }" />
                                        </PopoverContent>
                                    </Popover>
                                    <div class="relative">
                                        <Clock
                                            class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                                        <Input v-model="timeTo" type="time" class="pl-8 h-8 text-xs" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

                <DialogFooter class="gap-2 sm:gap-2">
                    <DialogClose as-child>

                        <Button type="button" variant="design_outline" @click="onOpenChange(false)">Отмена</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="!isFormValid" class="bg-indigo-600 hover:bg-indigo-700">
                      {{ code ? 'Изменить ключ':'Создать ключ' }}  
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>