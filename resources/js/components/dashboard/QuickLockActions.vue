<script setup>
import { ref, computed } from 'vue'
import {
    Search, X, Lock, Plus, LockOpen, Building2, RefreshCw,
    MapPin, Zap, BatteryMedium
} from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select'
import { cn } from '@/lib/utils'




const props = defineProps({
    locks: {
        type: Object,
        default: () => { }
    },
    properties: {
        type: Object,
        default: () => { }
    },

    openLockLoading: {
        type: Boolean,
        default: false
    },

    keysLoading: {
        type: Boolean,
        default: false
    },

    openLockResult: {
        type: Object,
        default: () => { }
    },
})

const emit = defineEmits(['selection-change', 'open-lock', 'refresh-pins','add-code'])



// Состояния
const search = ref('')
const showSuggestions = ref(false)
const selectedProperty = ref(null)
const selectedLock = ref(null)





// Открытие замка
const handleOpenLock = async () => {
    if (!selectedLock.value) return
    emit('open-lock', selectedLock.value)
}

const handleRefreshPins = async () => {
    if (!selectedLock.value) return
    emit('refresh-pins', selectedLock.value)
}


// Фильтруем только замки с привязкой к объектам
const mappedLocks = computed(() => props.locks.filter(l => l.rent_id > 0))

// Подсказки (вычисляемое свойство)
const suggestions = computed(() => {

    if (!search.value.trim()) return []

    const searchLower = search.value.toLowerCase()
    const results = []

    // Добавляем объекты
    props.properties.forEach(property => {
        const hasLocks = mappedLocks.value.some(l => l.rent_id === property.id)

        if (hasLocks && (
            property.name?.toLowerCase().includes(searchLower) ||
            property.location?.toLowerCase().includes(searchLower)
        )) {
            results.push({ type: 'property', property })
        }
    })

    // Добавляем замки
    mappedLocks.value.forEach(lock => {
        if (lock.lock_alias?.toLowerCase().includes(searchLower) || lock.lock_name?.toLowerCase().includes(searchLower)) {
            const property = props.properties.find(p => p.id === lock.rent_id)
            results.push({ type: 'lock', lock, property })
        }
    })

    return results.slice(0, 8)
})

// Обработка выбора объекта
const handleSelectProperty = (property) => {
    const propertyLocks = property.property.locks
    selectedProperty.value = property.property
    selectedLock.value = property.property.locks[0] || null
    search.value = ''
    showSuggestions.value = false

    emit('selection-change', property, selectedLock.value)
}

// Обработка выбора замка
const handleSelectLock = (lock, property) => {
    selectedProperty.value = property
    selectedLock.value = lock
    search.value = ''
    showSuggestions.value = false

    emit('selection-change', property, lock)
}

// Очистка выбора
const clearSelection = () => {
    search.value = ''
    selectedProperty.value = null
    selectedLock.value = null
    showSuggestions.value = false

    emit('selection-change', null, null)
}

// Статус замка
const getStatusInfo = (status) => {
    switch (status) {
        case 'online': return { label: 'Онлайн', color: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500' }
        case 'offline': return { label: 'Офлайн', color: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' }
        case 'low_battery': return { label: 'Низкий заряд', color: 'bg-amber-100 text-amber-700', dot: 'bg-amber-500' }
        case 'error': return { label: 'Ошибка', color: 'bg-red-100 text-red-700', dot: 'bg-red-500' }
        default: return { label: 'Неизвестно', color: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' }
    }
}

const statusInfo = computed(() => selectedLock.value ? getStatusInfo(selectedLock.value.status) : null)
const batteryLevel = computed(() => selectedLock.value?.electric_quantity || 0)
const propertyLocks = computed(() => {
    return selectedProperty.value
        ? mappedLocks.value.filter(l => l.rent_id === selectedProperty.value.id)
        : []
})

const handleLockChange = (lockId) => {
    const lock = propertyLocks.value.find(l => l.id === lockId)
    if (lock) {
        selectedLock.value = lock
        emit('selection-change', selectedProperty.value, lock)
    }
}

// Обработчики для input
const onSearchInput = (e) => {
    search.value = e.target.value
    showSuggestions.value = true
}

const onFocusSearch = () => {
    showSuggestions.value = true
}

const onBlurSearch = () => {
    // Небольшая задержка, чтобы успеть кликнуть по подсказке
    setTimeout(() => {
        showSuggestions.value = false
    }, 200)
}
</script>

<template>
    <div class="space-y-4">

        <!-- Поиск объекта -->
        <div class="space-y-1.5">
            <label class="text-sm font-medium text-slate-700">Объект</label>
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <Input v-model="search" placeholder="Название объекта или адрес..." class="pl-9 pr-9 h-10"
                    @input="onSearchInput" @focus="onFocusSearch" @blur="onBlurSearch" />
                <button v-if="search || selectedProperty" @click="clearSelection"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <X class="w-4 h-4" />
                </button>

                <!-- Выпадающие подсказки -->
                <div v-if="showSuggestions && suggestions.length > 0"
                    class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-50 max-h-72 overflow-y-auto">
                    <button v-for="item in suggestions"
                        :key="item.type === 'property' ? `property-${item.property.id}` : `lock-${item.property.id}`"
                        @click="item.type === 'property' ? handleSelectProperty(item) : handleSelectLock(item.lock, item.property)"
                        class="w-full px-4 py-3 text-left hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors">
                        <div class="flex items-center gap-3">
                            <Building2 v-if="item.type === 'property'" class="w-4 h-4 text-slate-400 flex-shrink-0" />
                            <Lock v-else class="w-4 h-4 text-slate-400 flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 truncate">
                                    {{ item.type === 'property' ? item.property.name : item.property.name }}
                                </p>
                                <p class="text-xs text-slate-500 truncate">
                                    {{ item.type === 'property' ? item.property.location : item.property?.name }}
                                </p>
                            </div>
                            <span v-if="item.type === 'property'" class="text-xs text-slate-400 flex-shrink-0">
                                {{mappedLocks.filter(l => l.rent_id === item.property.id).length}} замк.
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Карточка выбранного объекта + замок -->
        <div v-if="selectedProperty"
            class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-blue-50 overflow-hidden">

            <!-- Шапка объекта -->
            <div class="px-4 pt-4 pb-3 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <Building2 class="w-4 h-4 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 leading-tight">{{ selectedProperty.name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <MapPin class="w-3 h-3 text-indigo-500 flex-shrink-0" />
                        <p class="text-sm text-indigo-700 font-medium truncate">{{ selectedProperty.address || '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Разделитель -->
            <div class="mx-4 border-t border-indigo-200/60" />

            <!-- Выбор замка -->
            <div v-if="propertyLocks.length > 0" class="px-4 py-3">
                <div class="flex items-center gap-2 mb-2">
                    <Lock class="w-3.5 h-3.5 text-slate-500" />
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Замок</span>
                </div>

                <Select :model-value="selectedLock?.id" @update:model-value="handleLockChange">
                    <SelectTrigger class="h-9 bg-white/80 border-indigo-200 text-sm w-auto min-w-0 max-w-full">
                        <!-- 🔥 Заменяем SelectValue на кастомный рендер -->
                        <template v-if="selectedLock">
                            <div class="flex items-center gap-2">
                                <span
                                    :class="cn('w-2 h-2 rounded-full flex-shrink-0', getStatusInfo(selectedLock.status).dot)" />
                                <span class="truncate">{{ selectedLock.lock_alias }}</span>
                                <span v-if="selectedLock.electric_quantity !== undefined"
                                    class="text-xs text-slate-400 ml-1">
                                    {{ selectedLock.electric_quantity }}%
                                </span>
                            </div>
                        </template>
                        <!-- Placeholder если ничего не выбрано -->
                        <span v-else class="text-slate-500">Выберите замок</span>
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem v-for="lock in propertyLocks" :key="lock.id" :value="lock.id">
                            <div class="flex items-center gap-2">
                                <span
                                    :class="cn('w-2 h-2 rounded-full flex-shrink-0', getStatusInfo(lock.status).dot)" />
                                <span>{{ lock.lock_alias || lock.name }}</span>
                                <span v-if="lock.electric_quantity !== undefined" class="text-xs text-slate-400 ml-1">
                                    {{ lock.electric_quantity }}%
                                </span>
                            </div>
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Бейджи статуса -->
            <div v-if="selectedLock && statusInfo" class="px-4 pb-4 flex flex-wrap gap-1.5">
                <!--Badge :class="cn('text-xs px-2.5 py-0.5 gap-1', statusInfo.color)">
                    <span :class="cn('w-1.5 h-1.5 rounded-full', statusInfo.dot)" />
                    {{ statusInfo.label }}
                </Badge-->
                <Badge variant="outline"
                    class="text-xs px-2.5 py-0.5 bg-white/70 border-indigo-200 text-slate-600 gap-1">
                    <BatteryMedium class="w-3 h-3" />
                    {{ batteryLevel }}%
                </Badge>
                <Badge variant="outline" class="text-xs px-2.5 py-0.5 bg-white/70 border-indigo-200 text-slate-600">
                    {{ selectedLock.model || 'TTLock' }}
                </Badge>
                <Badge v-if="selectedLock.is_locked !== undefined" variant="outline" :class="cn(
                    'text-xs px-2.5 py-0.5 bg-white/70',
                    selectedLock.is_locked ? 'border-slate-300 text-slate-600' : 'border-green-300 text-green-700'
                )">
                    <Zap class="w-3 h-3 mr-0.5" />
                    {{ selectedLock.is_locked ? 'Закрыт' : 'Открыт' }}
                </Badge>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="flex flex-wrap gap-2">
            <Button size="sm" class="bg-green-600 hover:bg-green-700" :disabled="!selectedLock" @click="emit('add-code')">
                <Plus class="w-4 h-4 mr-1.5" />
                Добавить код
            </Button>
            <Button size="sm" class="bg-indigo-600 hover:bg-indigo-700" :disabled="!selectedLock || openLockLoading"
                @click="handleOpenLock">
                <span v-if="openLockLoading"
                    class="w-4 h-4 mr-1.5 border-2 border-white border-t-transparent rounded-full animate-spin inline-block" />
                <LockOpen v-else class="w-4 h-4 mr-1.5" />
                Открыть замок
            </Button>

            <Button size="sm" class="bg-indigo-600 hover:bg-indigo-700" :disabled="!selectedLock || keysLoading"
                @click="handleRefreshPins">
                <span v-if="keysLoading"
                    class="w-4 h-4 mr-1.5 border-2 border-white border-t-transparent rounded-full animate-spin inline-block" />
                <RefreshCw v-else :class="cn('w-3.5 h-3.5', keysLoading && 'animate-spin')" />

                Синхронизировать ключи
            </Button>
        </div>

        <!-- Результат открытия замка -->
        <div v-if="openLockResult?.msg"
            :class="cn('text-sm px-3 py-2 rounded-lg', openLockResult.success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700')">
            {{ openLockResult.msg }}
        </div>

    </div>
</template>