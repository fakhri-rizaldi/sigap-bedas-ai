<script setup lang="ts">
import { computed } from 'vue';
import { 
  MapPin, 
  Clock, 
  User, 
  AlertTriangle, 
  CheckCircle2, 
  Clock3, 
  XCircle,
  Sparkles,
  Building2,
  Image as ImageIcon
} from '@lucide/vue';

export interface AduanItem {
  id: number;
  kode_tiket: string;
  teks_aduan: string;
  kategori: string;
  confidence_kategori?: number;
  urgensi: 'Rendah' | 'Sedang' | 'Tinggi' | 'Darurat';
  alasan_urgensi?: string;
  dinas_id?: number | null;
  dinas?: {
    id: number;
    nama_dinas: string;
    kode_dinas: string;
  } | null;
  status: 'baru' | 'diproses' | 'selesai' | 'ditolak';
  catatan_petugas?: string | null;
  latitude: number | null;
  longitude: number | null;
  alamat: string | null;
  foto_path: string | null;
  nama_pelapor: string | null;
  kontak_pelapor?: string | null;
  sumber_klasifikasi?: string;
  perlu_review?: boolean;
  kategori_model_lokal?: string | null;
  confidence_model_lokal?: number | null;
  created_at: string;
  updated_at?: string;
  isNew?: boolean;
}

const props = defineProps<{
  aduan: AduanItem;
  isSelected?: boolean;
}>();

defineEmits<{
  (e: 'select', aduan: AduanItem): void;
}>();

// Status Badge Config
const statusConfig = computed(() => {
  switch (props.aduan.status) {
    case 'baru':
      return {
        label: 'Baru',
        bg: 'bg-slate-100 text-slate-700 border-slate-300',
        dot: 'bg-slate-400',
        icon: Clock3,
      };
    case 'diproses':
      return {
        label: 'Diproses',
        bg: 'bg-blue-50 text-blue-700 border-blue-200',
        dot: 'bg-blue-500',
        icon: Clock,
      };
    case 'selesai':
      return {
        label: 'Selesai',
        bg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        dot: 'bg-emerald-500',
        icon: CheckCircle2,
      };
    case 'ditolak':
      return {
        label: 'Ditolak',
        bg: 'bg-red-50 text-red-700 border-red-200',
        dot: 'bg-red-500',
        icon: XCircle,
      };
    default:
      return {
        label: props.aduan.status,
        bg: 'bg-slate-100 text-slate-700 border-slate-200',
        dot: 'bg-slate-400',
        icon: Clock3,
      };
  }
});

// Urgency Badge Config
const urgencyConfig = computed(() => {
  switch (props.aduan.urgensi) {
    case 'Darurat':
      return {
        label: 'Darurat',
        bg: 'bg-red-500 text-white border-red-600 shadow-xs',
        pulse: true,
      };
    case 'Tinggi':
      return {
        label: 'Tinggi',
        bg: 'bg-amber-500 text-white border-amber-600',
        pulse: false,
      };
    case 'Sedang':
      return {
        label: 'Sedang',
        bg: 'bg-sky-50 text-sky-700 border-sky-200',
        pulse: false,
      };
    default:
      return {
        label: 'Rendah',
        bg: 'bg-slate-50 text-slate-600 border-slate-200',
        pulse: false,
      };
  }
});

// Format Waktu Relatif
const formattedTime = computed(() => {
  if (!props.aduan.created_at) return '-';
  const date = new Date(props.aduan.created_at);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffMins < 1) return 'Baru saja';
  if (diffMins < 60) return `${diffMins} mnt lalu`;
  if (diffHours < 24) return `${diffHours} jam lalu`;
  if (diffDays === 1) return 'Kemarin';
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
});
</script>

<template>
  <div
    @click="$emit('select', aduan)"
    class="p-4 rounded-2xl border transition-all duration-200 cursor-pointer relative select-none group"
    :class="[
      isSelected 
        ? 'bg-blue-50/70 border-[#0A3D62] ring-2 ring-[#0A3D62]/15 shadow-md' 
        : 'bg-white hover:bg-slate-50/80 border-slate-200 hover:border-slate-300 shadow-xs hover:shadow-md',
      aduan.isNew ? 'ring-2 ring-amber-400 bg-amber-50/40 animate-in fade-in slide-in-from-top-2' : ''
    ]"
  >
    <!-- New Incoming Badge -->
    <span
      v-if="aduan.isNew"
      class="absolute -top-2 -right-2 px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-extrabold text-[10px] uppercase tracking-wider shadow-sm animate-bounce"
    >
      Live Baru
    </span>

    <!-- Top Metadata Bar -->
    <div class="flex items-center justify-between gap-2 mb-2">
      <!-- Kode Tiket -->
      <span class="font-mono text-xs font-bold text-slate-800 tracking-tight group-hover:text-[#0A3D62] transition">
        #{{ aduan.kode_tiket }}
      </span>

      <!-- Status & Urgency Pills -->
      <div class="flex items-center gap-1.5 shrink-0">
        <!-- Perlu Review Badge -->
        <span
          v-if="aduan.perlu_review"
          class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1 shrink-0"
          title="Hasil klasifikasi Gemini & Model Lokal berbeda / ambigu"
        >
          <AlertTriangle class="w-3 h-3 text-amber-600" />
          <span>Review</span>
        </span>

        <!-- Urgency Badge -->
        <span
          class="px-2 py-0.5 rounded-md text-[11px] font-bold border flex items-center gap-1"
          :class="urgencyConfig.bg"
        >
          <span v-if="urgencyConfig.pulse" class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
          {{ urgencyConfig.label }}
        </span>

        <!-- Status Badge -->
        <span
          class="px-2 py-0.5 rounded-md text-[11px] font-semibold border flex items-center gap-1"
          :class="statusConfig.bg"
        >
          <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig.dot"></span>
          {{ statusConfig.label }}
        </span>
      </div>
    </div>

    <!-- Teks Aduan Cuplikan -->
    <p class="text-xs sm:text-sm font-semibold text-slate-900 line-clamp-2 leading-relaxed mb-2.5">
      {{ aduan.teks_aduan }}
    </p>

    <!-- Bottom Info Bar -->
    <div class="flex flex-wrap items-center justify-between gap-y-1.5 text-[11px] text-slate-500 pt-2 border-t border-slate-100">
      
      <!-- Kategori & Dinas -->
      <div class="flex items-center gap-1.5">
        <span class="font-medium text-[#0A3D62] bg-[#0A3D62]/10 px-2 py-0.5 rounded-md">
          {{ aduan.kategori }}
        </span>
        <span v-if="aduan.dinas" class="text-slate-400 font-mono text-[10px]">
          • {{ aduan.dinas.kode_dinas }}
        </span>
      </div>

      <!-- Alamat & Waktu -->
      <div class="flex items-center gap-3">
        <div v-if="aduan.foto_path" class="flex items-center gap-1 text-slate-400" title="Memiliki Lampiran Foto">
          <ImageIcon class="w-3.5 h-3.5 text-blue-600" />
        </div>
        <div class="flex items-center gap-1 text-slate-400">
          <Clock class="w-3 h-3" />
          <span>{{ formattedTime }}</span>
        </div>
      </div>

    </div>

    <!-- Lokasi Preview jika ada -->
    <div v-if="aduan.alamat" class="mt-1.5 flex items-center gap-1 text-[11px] text-slate-400 truncate">
      <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
      <span class="truncate">{{ aduan.alamat }}</span>
    </div>

  </div>
</template>
