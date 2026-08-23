<script setup lang="ts">
import { computed } from 'vue';
import { Construction, Trash2, HeartHandshake, ShieldAlert, Tag } from '@lucide/vue';

const props = defineProps<{
  kategori: string;
  confidence?: number;
  showConfidence?: boolean;
}>();

const config = computed(() => {
  switch (props.kategori) {
    case 'Jalan Rusak':
      return {
        bg: 'bg-[#8E5B3D]/10',
        text: 'text-[#8E5B3D]',
        border: 'border-[#8E5B3D]/30',
        dot: 'bg-[#8E5B3D]',
        icon: Construction,
      };
    case 'Lingkungan & Drainase':
      return {
        bg: 'bg-[#2E8B8B]/10',
        text: 'text-[#2E8B8B]',
        border: 'border-[#2E8B8B]/30',
        dot: 'bg-[#2E8B8B]',
        icon: Trash2,
      };
    case 'Bantuan Sosial':
      return {
        bg: 'bg-[#7A4FA3]/10',
        text: 'text-[#7A4FA3]',
        border: 'border-[#7A4FA3]/30',
        dot: 'bg-[#7A4FA3]',
        icon: HeartHandshake,
      };
    case 'Keamanan & Ketertiban':
      return {
        bg: 'bg-[#B03A2E]/10',
        text: 'text-[#B03A2E]',
        border: 'border-[#B03A2E]/30',
        dot: 'bg-[#B03A2E]',
        icon: ShieldAlert,
      };
    default:
      return {
        bg: 'bg-slate-100',
        text: 'text-slate-700',
        border: 'border-slate-300',
        dot: 'bg-slate-500',
        icon: Tag,
      };
  }
});

const confidencePercent = computed(() => {
  if (props.confidence === undefined || props.confidence === null) return null;
  return Math.round(props.confidence * 100) + '%';
});
</script>

<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border transition-all duration-200 shadow-sm',
      config.bg,
      config.text,
      config.border
    ]"
  >
    <component :is="config.icon" class="w-3.5 h-3.5 shrink-0" />
    <span>{{ kategori }}</span>
    <span v-if="showConfidence && confidencePercent" class="ml-1 px-1.5 py-0.2 rounded-md bg-white/70 text-[11px] font-mono font-bold shadow-xs">
      {{ confidencePercent }}
    </span>
  </span>
</template>
