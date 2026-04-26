<script setup>
import { Pencil, Trash2, Copy } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import { cn } from '@/lib/utils'
import { parse, format, isValid } from 'date-fns'
import { ru } from 'date-fns/locale'
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,

} from '@/components/ui/pagination'


const props = defineProps({
  keys: { type: Object, default: null },
  selectedCodes: { type: Array, default: () => [] },
  selectAll: { type: Boolean, default: false },
  fullView: { type: Boolean, default: false },

  getTypeBadgeColor: Function,
  getCodeStatusColor: Function,
  formatPasscode: Function
})

const emit = defineEmits(['edit', 'delete', 'toggle-code-selection', 'toggle-select-all' ,'key-page'])


const formatCustomDate = (dateStr, time = false) => {
  if (!dateStr) return ''
  // Парсим строку "26.04.2026 00:00:00" в валидную JS Date
  const parsed = parse(dateStr, 'dd.MM.yyyy HH:mm:ss', new Date())
  if (!time)
    return isValid(parsed) ? format(parsed, 'dd.MM.yyyy', { locale: ru }) : ''
  else
    return isValid(parsed) ? format(parsed, 'HH:mm', { locale: ru }) : ''
}


const copyToClipboard = async (text) => {
  try { await navigator.clipboard.writeText(text) } catch { }
}
</script>

<template>
  <div v-if="keys" class="overflow-x-auto">
    <table  class="w-full text-xs">
      <thead>
        <tr class="border-b border-slate-100">
          <th class="w-10 py-2">
            <div class="flex items-center justify-center">
              <Checkbox class="h-4 w-4" :checked="selectAll" @click="emit('toggle-select-all', keys.data)" />
            </div>
          </th>
          <th class="text-center py-2 font-medium text-slate-500 text-xs">ID</th>
          <th class="text-center py-2 font-medium text-slate-500">Код</th>
          <th class="text-center py-2 font-medium text-slate-500">Тип</th>
          <th class="text-center py-2 font-medium text-slate-500">Начало</th>
          <th class="text-center py-2 font-medium text-slate-500">Окончание</th>
          <th class="w-16 text-center py-2 font-medium text-slate-500"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!keys.data.length">
          <td colspan="8" class="py-8 text-center text-slate-500">Нет кодов доступа</td>
        </tr>
        <tr v-for="g in keys.data" :key="g.id" class="border-b border-slate-50 hover:bg-slate-50/50">
          <td class="py-3">
            <div class="flex items-center justify-center">

              <Checkbox class="h-4 w-4" :checked="selectedCodes.includes(g.id)"
                @update:checked="emit('toggle-code-selection', g.id)" />
            </div>
          </td>
          <td class="py-3 text-slate-500 font-mono text-[9px] text-center">{{ g.pin_code_id }}</td>

          <td class="py-3 text-center">
            <div class="flex flex-col gap-1 items-center">
              <div class="flex items-center gap-1">
                <Badge
                  :class="cn('text-sm font-mono font-bold px-3 py-1', getCodeStatusColor(g).bg, getCodeStatusColor(g).text)">
                  {{ formatPasscode(g.pin_code) }}
                </Badge>
                <Button variant="ghost" size="icon" class="h-6 w-6 text-slate-400"
                  @click="copyToClipboard(formatPasscode(g.pin_code))">
                  <Copy class="w-3 h-3" />
                </Button>
              </div>
              <span class="text-[10px] text-slate-400">{{ getCodeStatusColor(g).label }}</span>
            </div>
          </td>
          <td class="py-3 text-center">
            <div class="flex justify-center">
              <Badge :class="cn('text-[10px] px-1.5 py-0.5', getTypeBadgeColor(g.code_name))">{{ g.code_name }}</Badge>
            </div>
          </td>
          <td class="py-3 text-slate-600 text-xs text-center">
            <template v-if="g.start_local">
              <div class="flex flex-col items-center">
                <span class="font-medium">{{ formatCustomDate(g.start_local) }}</span>
                <span class="text-[10px] text-slate-400">{{ formatCustomDate(g.start_local, true)
                  }}</span>
              </div>
            </template><span v-else>—</span>
          </td>
          <td class="py-3 text-slate-600 text-xs text-center">
            <template v-if="g.end_local">
              <div class="flex flex-col items-center">
                <span class="font-medium">{{ formatCustomDate(g.end_local) }}</span>
                <span class="text-[10px] text-slate-400">{{ formatCustomDate(g.end_local, true)
                  }}</span>
              </div>
            </template><span v-else>—</span>
          </td>
          <td class="py-3">
            <div class="flex items-center justify-center gap-0.5">
              <Button variant="ghost" size="icon" class="h-6 w-6 text-slate-400 hover:text-indigo-600"
                @click="emit('edit', g)">
                <Pencil class="w-3 h-3" />
              </Button>
              <Button variant="ghost" size="icon" class="h-6 w-6 text-slate-400 hover:text-red-600"
                @click="emit('delete', g)">
                <Trash2 class="w-3 h-3" />
              </Button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>



    <Pagination class="my-4 self-end" v-model:page="keys.current_page" :items-per-page="keys.per_page"
      :total="keys.total" :default-page="keys.current_page">
      <PaginationContent>
        <template v-for="(item, index) in keys.links" :key="index">
          <PaginationPrevious v-if="index == 0" @click.prevent="emit('key-page', item.page)" />
          <PaginationNext v-else-if="index == (keys.links.length - 1)" @click.prevent="emit('key-page', item.page)" />
          <PaginationItem v-else-if="(keys.current_page - 2 <= item.page) && (keys.current_page + 2 >= item.page)"
            :value="item.page" :is-active="item.page === keys.current_page" @click.prevent="emit('key-page', item.page)">
            {{ item.page }}
          </PaginationItem>
        </template>
      </PaginationContent>
    </Pagination>

  </div>
</template>