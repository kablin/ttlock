<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import draggable from 'vuedraggable'
import { 
  Home, Building, KeyRound, Lock, ShoppingCart, 
  CreditCard, Link2, BookOpen, Settings, GripVertical, X 
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

import { dashboard, lockList, groups, wizard_step1, lockevents, mapping, tarifs } from '@/routes';

const props = defineProps({

  mobileOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:mobileOpen'])

// Иконки маппинг
const icons = {
  Home, Building, KeyRound, Lock, ShoppingCart,
  CreditCard, Link2, BookOpen, Settings
}

//  { id: 'eventlog', name: 'Журнал событий', href: 'EventLog', icon: 'BookOpen' },
//    { id: 'shop', name: 'Магазин оборудования', href: 'Properties', icon: 'ShoppingCart' },
//  { id: 'properties', name: 'Объекты и доступы', href: 'PropertiesAndAccess', icon: 'Building' },
//
// Дефолтная навигация
const defaultNavigation = [
  { id: 'dashboard', name: 'Обзор', href: dashboard().url, icon: 'Home' },
  { id: 'locks', name: 'Управление замками', href: lockList().url, icon: 'Lock' },
  /*{ id: 'billing', name: 'Тариф и оплата', href: tarifs().url, icon: 'CreditCard' },*/
  { id: 'mapping', name: 'Привязка замков', href: mapping().url, icon: 'Link2' },
  { id: 'settings', name: 'Мастер настройки', href: wizard_step1().url, icon: 'Settings' },
]

// Состояние
const navigation = ref([...defaultNavigation])

// Загрузка порядка из localStorage
onMounted(() => {
  const saved = localStorage.getItem('menuOrder')
  if (saved) {
    try {
      const savedOrder = JSON.parse(saved)
      if (Array.isArray(savedOrder) && savedOrder.length > 0) {
        const restored = savedOrder
          .map(id => defaultNavigation.find(item => item.id === id))
          .filter(Boolean)
        const missing = defaultNavigation.filter(item => !savedOrder.includes(item.id))
        if (restored.length > 0) {
          navigation.value = [...restored, ...missing]
        }
      }
    } catch (e) {
      console.error('Failed to restore menu order', e)
    }
  }
})

// Drag & Drop: сохранение порядка
const onDragEnd = () => {
  localStorage.setItem('menuOrder', JSON.stringify(navigation.value.map(item => item.id)))
}

// Навигация
const navigate = (href) => {
 router.visit(href)
  emit('update:mobileOpen', false)
}

// Закрыть сайдбар на мобильном
const closeMobile = () => {
  emit('update:mobileOpen', false)
}
</script>

<template>
  <aside 
    :class="cn(
      'fixed top-0 left-0 z-50 h-full w-64 bg-white border-r border-slate-200 transform transition-transform duration-200 ease-in-out lg:translate-x-0',
      mobileOpen ? 'translate-x-0' : '-translate-x-full'
    )"
  >
    <div class="flex flex-col h-full">
 
      <!-- Logo + Close button (mobile) -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-slate-100">
        <Link :href="dashboard()" class="flex items-center">
          <img 
            src="https://qtrypzzcjebvfcihiynt.supabase.co/storage/v1/object/public/base44-prod/public/69628f4d0d107902144d2a82/353a167e4__1.png" 
            alt="RENTYSOFT" 
            class="h-9"
          />
        </Link>
        <Button
          variant="ghost"
          size="icon"
          class="lg:hidden"
          @click="closeMobile"
        >
          <X class="w-5 h-5" />
        </Button>
      </div>

      <!-- Navigation with Drag & Drop -->
      <draggable
        v-model="navigation"
        item-key="id"
        handle=".drag-handle"
        class="flex-1 py-4 px-3 overflow-y-auto"
        @end="onDragEnd"
      >
        <template #item="{ element: item, index }">
          <div class="mb-1">
            <div
              @click="navigate(item.href)"
              :class="cn(
                'flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer',
                1 === item.href 
                  ? 'bg-indigo-600 text-white' 
                  : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
              )"
            >
              <!-- Drag handle -->
              <div
                class="drag-handle cursor-grab active:cursor-grabbing"
                :class="1 === item.href ? 'text-white/70' : 'text-slate-400'"
                @click.stop
              >
                <GripVertical class="w-4 h-4" />
              </div>
              
              <!-- Icon -->
              <component :is="icons[item.icon]" class="w-5 h-5" />
              
              <!-- Label -->
              <span class="flex-1">{{ item.name }}</span>
            </div>
          </div>
        </template>
      </draggable>

    </div>
  </aside>
</template>