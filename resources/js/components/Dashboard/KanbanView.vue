<script setup lang="ts">
import { computed } from 'vue';
import TicketCard, { type AduanItem } from './TicketCard.vue';
import { Clock3, Clock, CheckCircle2, XCircle } from '@lucide/vue';

const props = defineProps<{
  aduans: AduanItem[];
  selectedAduanId?: number | null;
}>();

defineEmits<{
  (e: 'select', aduan: AduanItem): void;
}>();

const columns = computed(() => {
  return [
    {
      id: 'baru',
      title: 'Baru Masuk',
      icon: Clock3,
      badge: 'bg-slate-200 text-slate-800',
      headerBg: 'bg-slate-100 border-slate-300',
      items: props.aduans.filter((a) => a.status === 'baru'),
    },
    {
      id: 'diproses',
      title: 'Sedang Diproses',
      icon: Clock,
      badge: 'bg-blue-200 text-blue-900',
      headerBg: 'bg-blue-50 border-blue-200',
      items: props.aduans.filter((a) => a.status === 'diproses'),
    },
    {
      id: 'selesai',
      title: 'Selesai Ditangani',
      icon: CheckCircle2,
      badge: 'bg-emerald-200 text-emerald-900',
      headerBg: 'bg-emerald-50 border-emerald-200',
      items: props.aduans.filter((a) => a.status === 'selesai'),
    },
    {
      id: 'ditolak',
      title: 'Ditolak / Invalid',
      icon: XCircle,
      badge: 'bg-red-200 text-red-900',
      headerBg: 'bg-red-50 border-red-200',
      items: props.aduans.filter((a) => a.status === 'ditolak'),
    },
  ];
});
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 h-full overflow-x-auto pb-4">
    <div
      v-for="col in columns"
      :key="col.id"
      class="flex flex-col bg-slate-100/70 rounded-2xl border border-slate-200/90 overflow-hidden min-w-[280px]"
    >
      <!-- Column Header -->
      <div class="p-3.5 border-b border-slate-200 flex items-center justify-between" :class="col.headerBg">
        <div class="flex items-center gap-2">
          <component :is="col.icon" class="w-4 h-4 text-slate-700" />
          <h3 class="font-bold text-xs text-slate-800">{{ col.title }}</h3>
        </div>
        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold" :class="col.badge">
          {{ col.items.length }}
        </span>
      </div>

      <!-- Column Ticket List -->
      <div class="flex-1 overflow-y-auto p-2.5 space-y-2.5 max-h-[calc(100vh-280px)]">
        <TicketCard
          v-for="aduan in col.items"
          :key="aduan.id"
          :aduan="aduan"
          :is-selected="selectedAduanId === aduan.id"
          @select="$emit('select', $event)"
        />

        <div v-if="col.items.length === 0" class="py-8 text-center text-xs text-slate-400">
          Tidak ada tiket
        </div>
      </div>
    </div>
  </div>
</template>
