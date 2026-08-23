<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  X, 
  ShieldAlert, 
  MapPin, 
  User, 
  Phone, 
  Mail, 
  Clock, 
  Sparkles, 
  CheckCircle2, 
  Clock3, 
  XCircle, 
  AlertTriangle,
  AlertCircle,
  Building2,
  ExternalLink,
  Send,
  Loader2,
  Image as ImageIcon
} from '@lucide/vue';
import TicketMiniMap from './TicketMiniMap.vue';
import type { AduanItem } from './TicketCard.vue';

const props = defineProps<{
  aduan: AduanItem | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'updated', updatedAduan: AduanItem): void;
}>();

// State Form Update Status
const selectedStatus = ref<'baru' | 'diproses' | 'selesai' | 'ditolak'>('baru');
const catatanPetugas = ref<string>('');
const isSubmitting = ref<boolean>(false);
const showImageModal = ref<boolean>(false);
const saveSuccessMessage = ref<string>('');
const saveErrorMessage = ref<string>('');

// Sinkronkan state saat aduan berganti
watch(
  () => props.aduan,
  (newVal) => {
    if (newVal) {
      selectedStatus.value = newVal.status;
      catatanPetugas.value = newVal.catatan_petugas || '';
      saveSuccessMessage.value = '';
      saveErrorMessage.value = '';
    }
  },
  { immediate: true }
);

const handleUpdateStatus = async () => {
  if (!props.aduan) return;

  isSubmitting.value = true;
  saveSuccessMessage.value = '';
  saveErrorMessage.value = '';

  try {
    const response = await axios.patch(`/dashboard/aduan/${props.aduan.id}/status`, {
      status: selectedStatus.value,
      catatan_petugas: catatanPetugas.value,
    });

    if (response.data?.status === 'success' && response.data?.data) {
      saveSuccessMessage.value = response.data.message || `Status tiket #${props.aduan.kode_tiket} berhasil diperbarui menjadi ${selectedStatus.value.toUpperCase()}!`;
      emit('updated', response.data.data);
      setTimeout(() => {
        saveSuccessMessage.value = '';
      }, 5000);
    }
  } catch (error: any) {
    console.error('Gagal memperbarui status:', error);
    saveErrorMessage.value = error.response?.data?.message || 'Gagal menyimpan perubahan status. Silakan coba kembali.';
    setTimeout(() => {
      saveErrorMessage.value = '';
    }, 6000);
  } finally {
    isSubmitting.value = false;
  }
};

// Urgency badge styling
const urgencyPill = computed(() => {
  if (!props.aduan) return { label: '', bg: '' };
  switch (props.aduan.urgensi) {
    case 'Darurat':
      return { label: 'Darurat', bg: 'bg-red-500 text-white' };
    case 'Tinggi':
      return { label: 'Tinggi', bg: 'bg-amber-500 text-white' };
    case 'Sedang':
      return { label: 'Sedang', bg: 'bg-sky-100 text-sky-800' };
    default:
      return { label: 'Rendah', bg: 'bg-slate-100 text-slate-700' };
  }
});
</script>

<template>
  <div v-if="aduan" class="h-full flex flex-col bg-white border-l border-slate-200">
    
    <!-- Top Action Header -->
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
      <div>
        <div class="flex items-center gap-2">
          <span class="font-mono text-sm font-extrabold text-slate-900">
            #{{ aduan.kode_tiket }}
          </span>
          <span
            class="px-2 py-0.5 rounded-full text-[11px] font-bold"
            :class="urgencyPill.bg"
          >
            {{ urgencyPill.label }}
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-0.5">
          Kategori: <strong class="text-slate-800">{{ aduan.kategori }}</strong>
        </p>
      </div>

      <button
        type="button"
        @click="$emit('close')"
        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition cursor-pointer"
        title="Tutup Panel Detail"
      >
        <X class="w-5 h-5" />
      </button>
    </div>

    <!-- Scrollable Workspace Body -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
      
      <!-- Alert Success Banner -->
      <div
        v-if="saveSuccessMessage"
        class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2 animate-in fade-in"
      >
        <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
        <span>{{ saveSuccessMessage }}</span>
      </div>

      <!-- 1. Teks Isi Keluhan Warga -->
      <div class="space-y-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
          Uraian Laporan Warga
        </h3>
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-line font-medium">
          {{ aduan.teks_aduan }}
        </div>
      </div>

      <!-- 2. Analisis AI Dua Lapis & Dinas Tujuan -->
      <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 text-xs font-bold text-[#0A3D62]">
            <Sparkles class="w-4 h-4 text-amber-500" />
            <span>Evaluasi Dua Lapis (Dual-AI)</span>
          </div>
          <span
            class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
            :class="aduan.perlu_review ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
          >
            {{ aduan.perlu_review ? '⚠️ Perlu Review' : '✅ Hasil Selaras' }}
          </span>
        </div>

        <!-- Komparasi 2 Model Side-by-Side -->
        <div class="grid grid-cols-2 gap-2 text-xs">
          <!-- Gemini AI -->
          <div class="p-2.5 rounded-xl bg-white border border-blue-100 space-y-1">
            <span class="text-[10px] text-slate-400 block font-semibold uppercase">☁️ Gemini Flash</span>
            <div class="font-extrabold text-slate-900 text-xs truncate">{{ aduan.kategori }}</div>
            <span class="text-[10px] text-blue-600 font-mono block">
              Akurasi: {{ Math.round((aduan.confidence_kategori || 0.9) * 100) }}%
            </span>
          </div>

          <!-- Local NLP Model -->
          <div class="p-2.5 rounded-xl bg-white border border-blue-100 space-y-1">
            <span class="text-[10px] text-slate-400 block font-semibold uppercase">🤖 Model NLP Mandiri</span>
            <div class="font-extrabold text-slate-900 text-xs truncate">
              {{ aduan.kategori_model_lokal || aduan.kategori }}
            </div>
            <span class="text-[10px] text-emerald-600 font-mono block">
              Akurasi: {{ Math.round((aduan.confidence_model_lokal || 0.85) * 100) }}%
            </span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-blue-100/60">
          <div>
            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Dinas Tujuan</span>
            <span class="font-bold text-slate-800">{{ aduan.dinas?.nama_dinas || 'Belum diarahkan' }}</span>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Prioritas Urgensi</span>
            <span class="font-bold text-slate-800">{{ aduan.urgensi }}</span>
          </div>
        </div>

        <p v-if="aduan.alasan_urgensi" class="text-[11px] text-slate-600 bg-white/80 p-2.5 rounded-xl border border-blue-100/60">
          <strong class="text-slate-800">Alasan Penilaian:</strong> {{ aduan.alasan_urgensi }}
        </p>
      </div>

      <!-- 3. Foto Bukti Lampiran -->
      <div v-if="aduan.foto_path" class="space-y-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
          <ImageIcon class="w-3.5 h-3.5" />
          <span>Foto Bukti Lapangan</span>
        </h3>
        <div 
          @click="showImageModal = true"
          class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-xs group cursor-pointer aspect-video bg-slate-900 flex items-center justify-center"
        >
          <img
            :src="aduan.foto_path"
            alt="Foto Bukti"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 opacity-90 group-hover:opacity-100"
          />
          <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5">
            <ExternalLink class="w-4 h-4" />
            <span>Klik untuk Memperbesar</span>
          </div>
        </div>
      </div>

      <!-- 4. Lokasi & Peta Mini -->
      <div class="space-y-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
          <MapPin class="w-3.5 h-3.5" />
          <span>Lokasi Kejadian</span>
        </h3>
        <p class="text-xs text-slate-700 font-medium">
          {{ aduan.alamat || 'Alamat tidak dicantumkan' }}
        </p>
        <TicketMiniMap
          v-if="aduan.latitude && aduan.longitude"
          :lat="aduan.latitude"
          :lng="aduan.longitude"
          :alamat="aduan.alamat || ''"
        />
      </div>

      <!-- 5. Identitas Pelapor -->
      <div class="space-y-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
          Data Pelapor Warga
        </h3>
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
          <div class="flex items-center gap-2 text-slate-700">
            <User class="w-4 h-4 text-slate-400 shrink-0" />
            <span class="font-bold">{{ aduan.nama_pelapor || 'Warga Anonim (Tidak mengisi nama)' }}</span>
          </div>
          <div v-if="aduan.kontak_pelapor" class="flex items-center gap-2 text-slate-700">
            <Phone class="w-4 h-4 text-slate-400 shrink-0" />
            <span>{{ aduan.kontak_pelapor }}</span>
          </div>
          <div v-if="aduan.email_pelapor" class="flex items-center gap-2 text-slate-700">
            <Mail class="w-4 h-4 text-slate-400 shrink-0" />
            <span>{{ aduan.email_pelapor }}</span>
          </div>
          <div class="flex items-center gap-2 text-slate-400 text-[11px] pt-1 border-t border-slate-200">
            <Clock class="w-3.5 h-3.5 shrink-0" />
            <span>Dilaporkan pada: {{ new Date(aduan.created_at).toLocaleString('id-ID') }}</span>
          </div>
        </div>
      </div>

      <!-- 6. Form Tindak Lanjut Staf (CRM Quick Action) -->
      <div class="p-5 rounded-2xl bg-slate-900 text-white space-y-4 shadow-lg">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
            <ShieldAlert class="w-4 h-4 text-amber-400" />
            <span>Tindak Lanjut Petugas</span>
          </h3>
          <span class="text-[10px] text-slate-400">Aksi Langsung</span>
        </div>

        <!-- Pilihan Status -->
        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold text-slate-300">
            Ubah Status Tiket:
          </label>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button
              type="button"
              @click="selectedStatus = 'baru'"
              class="py-2 px-3 rounded-xl font-bold border transition cursor-pointer flex items-center justify-center gap-1.5"
              :class="selectedStatus === 'baru' ? 'bg-slate-700 border-amber-400 text-amber-300' : 'bg-slate-800/80 border-slate-700 text-slate-400 hover:bg-slate-800'"
            >
              <Clock3 class="w-3.5 h-3.5" />
              <span>Baru</span>
            </button>

            <button
              type="button"
              @click="selectedStatus = 'diproses'"
              class="py-2 px-3 rounded-xl font-bold border transition cursor-pointer flex items-center justify-center gap-1.5"
              :class="selectedStatus === 'diproses' ? 'bg-blue-600 border-blue-400 text-white shadow-md' : 'bg-slate-800/80 border-slate-700 text-slate-400 hover:bg-slate-800'"
            >
              <Clock class="w-3.5 h-3.5" />
              <span>Diproses</span>
            </button>

            <button
              type="button"
              @click="selectedStatus = 'selesai'"
              class="py-2 px-3 rounded-xl font-bold border transition cursor-pointer flex items-center justify-center gap-1.5"
              :class="selectedStatus === 'selesai' ? 'bg-emerald-600 border-emerald-400 text-white shadow-md' : 'bg-slate-800/80 border-slate-700 text-slate-400 hover:bg-slate-800'"
            >
              <CheckCircle2 class="w-3.5 h-3.5" />
              <span>Selesai</span>
            </button>

            <button
              type="button"
              @click="selectedStatus = 'ditolak'"
              class="py-2 px-3 rounded-xl font-bold border transition cursor-pointer flex items-center justify-center gap-1.5"
              :class="selectedStatus === 'ditolak' ? 'bg-red-600 border-red-400 text-white shadow-md' : 'bg-slate-800/80 border-slate-700 text-slate-400 hover:bg-slate-800'"
            >
              <XCircle class="w-3.5 h-3.5" />
              <span>Ditolak</span>
            </button>
          </div>
        </div>

        <!-- Input Catatan Petugas -->
        <div class="space-y-1.5">
          <label for="catatan" class="block text-[11px] font-bold text-slate-300">
            Catatan Resmi Penanganan (Dapat dilihat warga):
          </label>
          <textarea
            id="catatan"
            v-model="catatanPetugas"
            rows="3"
            placeholder="Contoh: Regu penambalan jalan DPUTR telah diturunkan ke lokasi..."
            class="w-full p-3 text-xs bg-slate-800 rounded-xl border border-slate-700 text-slate-100 placeholder:text-slate-500 focus:outline-hidden focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition"
          ></textarea>
        </div>

        <!-- Status Alert Inside Action Card -->
        <div
          v-if="saveSuccessMessage"
          class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 text-xs font-bold flex items-center gap-2 animate-in fade-in"
        >
          <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{{ saveSuccessMessage }}</span>
        </div>

        <div
          v-if="saveErrorMessage"
          class="p-3 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 text-xs font-bold flex items-center gap-2 animate-in fade-in"
        >
          <AlertCircle class="w-4 h-4 text-red-400 shrink-0" />
          <span>{{ saveErrorMessage }}</span>
        </div>

        <!-- Tombol Simpan -->
        <button
          type="button"
          @click="handleUpdateStatus"
          :disabled="isSubmitting"
          class="w-full py-2.5 px-4 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs flex items-center justify-center gap-2 shadow-md transition disabled:opacity-50 cursor-pointer"
        >
          <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin text-slate-950" />
          <Send v-else class="w-3.5 h-3.5 text-slate-950" />
          <span>{{ isSubmitting ? 'Menyimpan & Menyiarkan...' : 'Simpan & Siarkan Real-Time' }}</span>
        </button>
      </div>

    </div>

    <!-- Image Lightbox Modal -->
    <div
      v-if="showImageModal && aduan.foto_path"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="showImageModal = false"
    >
      <div class="relative max-w-4xl max-h-[90vh] bg-slate-950 rounded-2xl overflow-hidden shadow-2xl border border-slate-800">
        <button
          type="button"
          @click="showImageModal = false"
          class="absolute top-3 right-3 p-2 rounded-full bg-black/60 text-white hover:bg-black/80 transition cursor-pointer z-10"
        >
          <X class="w-5 h-5" />
        </button>
        <img
          :src="aduan.foto_path"
          alt="Foto Aduan Full"
          class="max-w-full max-h-[85vh] object-contain mx-auto"
        />
        <div class="p-3 bg-slate-900 text-center text-xs text-slate-300">
          Foto bukti laporan #{{ aduan.kode_tiket }}
        </div>
      </div>
    </div>

  </div>
  
  <!-- Empty State -->
  <div v-else class="h-full flex flex-col items-center justify-center p-8 text-center text-slate-400 bg-slate-50/50">
    <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mb-3">
      <ShieldAlert class="w-8 h-8 text-slate-300" />
    </div>
    <h4 class="text-sm font-bold text-slate-700">Pilih Tiket untuk Melihat Detail</h4>
    <p class="text-xs text-slate-400 max-w-xs mt-1">
      Klik salah satu kartu tiket dari daftar di sebelah kiri untuk melihat rincian laporan, foto bukti, dan melakukan tindak lanjut.
    </p>
  </div>
</template>
