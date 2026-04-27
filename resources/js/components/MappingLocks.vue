<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, X, Lock, Building2, Check, GripVertical, RefreshCw } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { cn } from '@/lib/utils'
import { Head } from '@inertiajs/vue3';
import SetupSteps from '@/components/setup/SetupSteps.vue'
import SetupNavigation from '@/components/setup/SetupNavigation.vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { wizard_map, wizard_unmap } from '@/routes';
import axios from 'axios';
import { Centrifuge } from 'centrifuge'


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


// Состояния
const properties = ref([])
const locks = ref([])
const isSaving = ref(false)
const propertySearch = ref('')
const lockSearch = ref('')
const showUnassignedOnly = ref(false)
const mappings = ref({})
const selectedProperty = ref(null)
const draggedLock = ref(null)

// Загрузка данных
const loadData = async () => {
  try {
    properties.value = props.rents
    locks.value = props.all_locks

    locks.value.forEach(lock => {
      if (lock.rent_id) assignLock(lock.id, lock.rent_id)
    })

  } catch (error) {
    console.error('Ошибка загрузки данных:', error)
  }
}





// Вычисляемые свойства
const filteredProperties = computed(() => {
  return properties.value.filter(p =>
    p.name?.toLowerCase().includes(propertySearch.value.toLowerCase()) ||
    p.location?.toLowerCase().includes(propertySearch.value.toLowerCase())
  )
})

const assignedLockIds = computed(() => {
  return Object.values(mappings.value).flat()
})

const filteredLocks = computed(() => {
  return locks.value.filter(l => {
    const matchesSearch = l.lock_name?.toLowerCase().includes(lockSearch.value.toLowerCase()) ||
      l.lock_alias?.toLowerCase().includes(lockSearch.value.toLowerCase())
    const matchesFilter = !showUnassignedOnly.value || !assignedLockIds.value.includes(l.id)
    return matchesSearch && matchesFilter
  })
})

const totalMappings = computed(() => {
  return Object.values(mappings.value).flat().length
})

// Методы для работы с маппингом
const getLocksForProperty = (propertyId) => {
  return mappings.value[propertyId] || []
}

const assignLock = (lockId, propertyId) => {
  const newMappings = { ...mappings.value }
  Object.keys(newMappings).forEach(pId => {
    newMappings[pId] = newMappings[pId].filter(id => id !== lockId)
  })

  if (!newMappings[propertyId]) {
    newMappings[propertyId] = []
  }
  newMappings[propertyId].push(lockId)

  mappings.value = newMappings
}

const unassignLock = async (lockId, propertyId) => {
  let response = await axios.post(wizard_unmap().url, {
    "lock_id": lockId,
    'rent_id': propertyId
  })
  if (response.data.status) {
    const newMappings = { ...mappings.value }
    newMappings[propertyId] = newMappings[propertyId].filter(id => id !== lockId)
    mappings.value = newMappings
  }
}

// Drag & Drop
const handleDragStart = (e, lock) => {
  draggedLock.value = lock
  e.dataTransfer.effectAllowed = 'move'
}

const handleDragOver = (e) => {
  e.preventDefault()
  e.dataTransfer.dropEffect = 'move'
}

const handleDrop = async (e, propertyId) => {
  e.preventDefault()
  if (draggedLock.value) {
    let id = draggedLock.value.id;
    let response = await axios.post(wizard_map().url, {
      "lock_id": id,
      'rent_id': propertyId
    })
    let isSuccess = response.data.status
    if (isSuccess) {
      assignLock(id, propertyId)
    }
    draggedLock.value = null
  }
}

const handleDragEnd = () => {
  draggedLock.value = null
}

// Загрузка данных при монтировании
onMounted(() => {
  loadData()
})


watch(() => props.all_locks, (newVal) => {
    if (newVal) loadData()

})

watch(() => props.rents, (newVal) => {
    if (newVal) loadData()

})
</script>

<template>



  <!-- Info Banner -->
  <div
    class="relative overflow-hidden bg-gradient-to-r from-indigo-50 to-violet-50 rounded-xl border border-indigo-100 p-6">
    <div class="flex items-start gap-4">
      <div
        class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
        <Lock class="w-6 h-6 text-white" />
      </div>
      <div class="space-y-2">
        <h3 class="font-semibold text-slate-900">Как работает маппинг</h3>
        <p class="text-sm text-slate-600 leading-relaxed">
          Перетащите замки из правого списка на нужный объект, чтобы связать их.
          Один объект может иметь несколько замков (например, входная дверь + парадная).
        </p>
        <div class="flex items-center gap-4 text-xs text-slate-500 pt-1">
          <span class="flex items-center gap-1">
            <div class="w-2 h-2 rounded-full bg-green-500" />
            Привязано замков: {{ totalMappings }}
          </span>
          <span class="flex items-center gap-1">
            <div class="w-2 h-2 rounded-full bg-slate-300" />
            Не привязано: {{ locks.length - totalMappings }}
          </span>
        </div>
      </div>
    </div>
    <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-indigo-100 rounded-full opacity-50" />
  </div>

  <!-- Main Content - Two Columns -->
  <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-6">
    <!-- Left Column - Properties (wider) -->
    <div class="lg:col-span-3 bg-white border border-slate-200 rounded-xl overflow-hidden">
      <div class="p-4 border-b border-slate-100 bg-slate-50">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-slate-900 flex items-center gap-2">
            <Building2 class="w-4 h-4 text-slate-500" />
            Объекты
          </h3>
          <span class="text-xs text-slate-500">{{ properties.length }} всего</span>
        </div>
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <Input v-model="propertySearch" placeholder="Поиск по названию или адресу..." class="pl-9 bg-white" />
        </div>
      </div>

      <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
        <div v-for="property in filteredProperties" :key="property.id" :class="cn(
          'p-5 rounded-xl border-2 transition-all cursor-pointer min-h-[100px]',
          selectedProperty === property.id
            ? 'border-indigo-500 bg-indigo-50/50'
            : 'border-slate-200 hover:border-slate-300 bg-white',
          draggedLock && 'border-dashed border-indigo-300 bg-indigo-50/30'
        )" @click="selectedProperty = selectedProperty === property.id ? null : property.id" @dragover="handleDragOver"
          @drop="(e) => handleDrop(e, property.id)">
          <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-slate-900 text-base">{{ property.name }}</h4>
              <p class="text-sm text-slate-500">{{ property.location }}</p>
            </div>
            <Badge v-if="getLocksForProperty(property.id).length > 0" variant="secondary"
              class="bg-green-100 text-green-700 flex-shrink-0">
              замков:{{ getLocksForProperty(property.id).length }}
            </Badge>
          </div>

          <!-- Assigned Locks -->
          <div v-if="getLocksForProperty(property.id).length > 0" class="mt-3 flex flex-wrap gap-2">
            <div v-for="lockId in getLocksForProperty(property.id)" :key="lockId"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-sm">
              <Lock class="w-3 h-3 text-indigo-500" />
              <span class="text-slate-700">{{locks.find(l => l.id === lockId)?.lock_alias}}</span>
              <button @click.stop="unassignLock(lockId, property.id)" class="ml-1 p-0.5 hover:bg-slate-100 rounded">
                <X class="w-3 h-3 text-slate-400 hover:text-slate-600" />
              </button>
            </div>
          </div>

          <!-- Drop zone hint -->
          <div v-if="draggedLock && getLocksForProperty(property.id).length === 0"
            class="mt-3 py-2 border border-dashed border-indigo-300 rounded-lg bg-indigo-50/50 text-center">
            <span class="text-xs text-indigo-600">Отпустите здесь</span>
          </div>
        </div>

        <div v-if="filteredProperties.length === 0" class="py-8 text-center text-slate-500 text-sm">
          Объекты не найдены
        </div>
      </div>
    </div>

    <!-- Right Column - Locks (narrower) -->
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl overflow-hidden">
      <div class="p-3 border-b border-slate-100 bg-slate-50">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium text-slate-900 flex items-center gap-2 text-sm">
            <Lock class="w-4 h-4 text-slate-500" />
            Замки
          </h3>
          <span class="text-xs text-slate-500">{{ locks.length }} всего</span>
        </div>
        <div class="relative mb-2">
          <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
          <Input v-model="lockSearch" placeholder="Поиск..." class="pl-8 bg-white h-8 text-sm" />
        </div>
        <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer">
          <Checkbox v-model="showUnassignedOnly" class="h-3.5 w-3.5" />
          Только непривязанные
        </label>
      </div>

      <div class="p-2 space-y-1.5 max-h-[500px] overflow-y-auto">
        <div v-for="lock in filteredLocks" :key="lock.id" draggable="true" @dragstart="(e) => handleDragStart(e, lock)"
          @dragend="handleDragEnd" :class="cn(
            'flex items-center gap-2 px-2.5 py-2 rounded-lg border transition-all cursor-grab active:cursor-grabbing',
            assignedLockIds.includes(lock.id)
              ? 'border-green-200 bg-green-50/50'
              : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm',
            draggedLock?.id === lock.id && 'opacity-50'
          )">
          <GripVertical class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" />

          <Lock :class="cn(
            'w-3.5 h-3.5 flex-shrink-0',
            assignedLockIds.includes(lock.id) ? 'text-green-500' : 'text-slate-400'
          )" />

          <span :class="cn(
            'text-sm truncate flex-1',
            assignedLockIds.includes(lock.id) ? 'text-green-700' : 'text-slate-700'
          )">
            {{ lock.lock_alias }}
          </span>

          <Check v-if="assignedLockIds.includes(lock.id)" class="w-3.5 h-3.5 text-green-500 flex-shrink-0" />
          <div v-else class="w-1.5 h-1.5 rounded-full bg-indigo-500 flex-shrink-0" />
        </div>

        <div v-if="filteredLocks.length === 0" class="py-8 text-center text-slate-500 text-sm">
          {{ showUnassignedOnly
            ? 'Все замки привязаны к объектам'
            : 'Замки не найдены'
          }}
        </div>
      </div>
    </div>
  </div>

</template>