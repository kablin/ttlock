<script setup>
import { ref, computed } from 'vue'
import { format } from 'date-fns'
import { ru } from 'date-fns/locale'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { cn } from '@/lib/utils'
//import AddCodeDialog from '@/components/access/AddCodeDialog.vue'

// 🔥 Импортируем вынесенные таблицы
import CodesTable from '@/components/dashboard/CodesTable.vue'
import LogsTable from '@/components/dashboard/LogsTable.vue'

const props = defineProps({

  selectedProperty: { type: Object, default: null },
  keys: { type: Object, default: null },
  logs: { type: Object, default: null },
  
  selectedLock: { type: Object, default: null },
})

const emit = defineEmits(['refresh','key-page','log-page'])

const ITEMS_PER_PAGE = 10

const activeTab = ref('codes')


const editingGrant = ref(null)
const showEditDialog = ref(false)
const selectedCodes = ref([])
const selectAll = ref(false)
const isDeleting = ref(false)

const base44 = { entities: { AccessGrant: { delete: async () => new Promise(r => setTimeout(r, 300)) } } }






const getTypeBadgeColor = (type) => {
  return { 'Клиент': 'bg-slate-100 text-slate-700',  'Персонал': 'bg-amber-100 text-amber-700' }[type] || 'bg-slate-100 text-slate-700'
}

const getCodeStatusColor = (grant) => {
  if (!grant.is_load ) return { bg: 'bg-red-100', text: 'text-red-700', label: 'Не загружен' }
  if (grant.used ) return { bg: 'bg-indigo-100', text: 'text-indigo-700', label: 'Гость зашел' }
  return { bg: 'bg-green-100', text: 'text-green-700', label: 'Загружен в замок' }
}

const formatPasscode = (passcode) => {
  if (!passcode) return '0000#'
  const digits = passcode.replace(/\D/g, '').slice(0, 4)
  return digits.padStart(4, '0') + '#'
}

const handleDelete = async (grant) => {
  if (!confirm(`Удалить код доступа для ${grant.guest_name}?`)) return
  isDeleting.value = true
  try {
    await base44.entities.AccessGrant.delete(grant.id)
    emit('refresh')
    selectedCodes.value = selectedCodes.value.filter(id => id !== grant.id)
    if (selectedCodes.value.length === 0) selectAll.value = false
  } finally { isDeleting.value = false }
}

const handleBulkDelete = async () => {
  if (selectedCodes.value.length === 0 || !confirm(`Удалить выбранные коды (${selectedCodes.value.length} шт.)?`)) return
  isDeleting.value = true
  try {
    for (const id of selectedCodes.value) await base44.entities.AccessGrant.delete(id)
    emit('refresh')
    selectedCodes.value = []
    selectAll.value = false
  } finally { isDeleting.value = false }
}

const toggleCodeSelection = (id) => {
  const idx = selectedCodes.value.indexOf(id)
  idx > -1 ? selectedCodes.value.splice(idx, 1) : selectedCodes.value.push(id)
}

const toggleSelectAll = (grants) => {
  selectAll.value = !selectAll.value
  console.log(grants)
  selectedCodes.value = selectAll.value ? grants.map(g => g.id) : []

    console.log(selectedCodes.value)
}

const handleEdit = (grant) => {
  editingGrant.value = grant
  showEditDialog.value = true
}




const tabs = computed(() => [
  { id: 'codes', label: 'Текущие коды' },
  { id: 'lockLogs', label: 'Журнал событий' }
])
</script>

<template>
  <div>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
      <div class="flex border-b border-slate-200 bg-white overflow-x-auto">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
          :class="cn('px-4 sm:px-6 py-3 font-medium transition-all relative whitespace-nowrap shrink-0', activeTab === tab.id ? 'text-slate-900' : 'text-slate-500 hover:text-slate-700')">
          {{ tab.label }}
          <span v-if="activeTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600" />
        </button>
      </div>
 
      <div class="p-4">
        <template v-if="activeTab === 'codes'">
          <div v-if="selectedCodes.length > 0"
            class="mb-3 flex items-center justify-between p-2 bg-indigo-50 rounded-lg">
            <span class="text-sm text-indigo-700 font-medium">Выбрано кодов: {{ selectedCodes.length }}</span>
            <Button variant="destructive" size="sm" @click="handleBulkDelete" :disabled="isDeleting">Удалить
              выбранное</Button>
          </div>
          <CodesTable :get-type-badge-color="getTypeBadgeColor" :keys="keys" 
            :get-code-status-color="getCodeStatusColor" :format-passcode="formatPasscode" @edit="handleEdit"
            @delete="handleDelete" :selected-codes="selectedCodes" @toggle-code-selection="toggleCodeSelection"
            :select-all="selectAll" @toggle-select-all="toggleSelectAll" @key-page="(page) => emit('key-page', page)"/>
        </template>

        <template v-if="activeTab === 'lockLogs'">
          <LogsTable :logs="logs"   @log-page="(page) => emit('log-page', page)" />
        </template>


      </div>
    </div>


  </div>
</template>


