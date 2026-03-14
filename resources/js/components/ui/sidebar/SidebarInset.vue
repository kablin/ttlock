<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <main
    data-slot="sidebar-inset"
    :class="cn(
     'p-4 sm:p-6 lg:p-8',
      props.class,
    )"
  >
    <slot />
  </main>
</template>
