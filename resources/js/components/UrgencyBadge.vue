<script setup lang="ts">
import { computed } from 'vue';
import { AlertOctagon, AlertTriangle, AlertCircle, Info } from '@lucide/vue';

const props = defineProps<{
  urgensi: string;
}>();

const config = computed(() => {
  switch (props.urgensi) {
    case 'Darurat':
      return {
        bg: 'bg-red-100 text-red-800 border-red-300 animate-pulse',
        icon: AlertOctagon,
        label: 'Darurat',
      };
    case 'Tinggi':
      return {
        bg: 'bg-red-50 text-[#D64545] border-red-200',
        icon: AlertTriangle,
        label: 'Tinggi',
      };
    case 'Sedang':
      return {
        bg: 'bg-amber-50 text-amber-800 border-amber-200',
        icon: AlertCircle,
        label: 'Sedang',
      };
    case 'Rendah':
      return {
        bg: 'bg-slate-100 text-slate-700 border-slate-200',
        icon: Info,
        label: 'Rendah',
      };
    default:
      return {
        bg: 'bg-slate-100 text-slate-700 border-slate-200',
        icon: Info,
        label: props.urgensi,
      };
  }
});
</script>

<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border shadow-xs transition',
      config.bg
    ]"
  >
    <component :is="config.icon" class="w-3.5 h-3.5 shrink-0" />
    <span>Urgensi: {{ config.label }}</span>
  </span>
</template>
