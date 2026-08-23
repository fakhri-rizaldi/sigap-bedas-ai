<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';
import AppFooter from '@/components/AppFooter.vue';
import CategoryBadge from '@/components/CategoryBadge.vue';
import UrgencyBadge from '@/components/UrgencyBadge.vue';
import { 
  CheckCircle2, 
  Copy, 
  Check, 
  Building2, 
  MapPin, 
  Calendar, 
  FileText, 
  PlusCircle, 
  ArrowLeft,
  Share2,
  Search
} from '@lucide/vue';

interface Dinas {
  id: number;
  nama_dinas: string;
  kode_dinas: string;
  kontak_telepon?: string;
}

interface Aduan {
  id: number;
  kode_tiket: string;
  teks_aduan: string;
  kategori: string;
  confidence_kategori?: number;
  urgensi: string;
  alasan_urgensi?: string;
  status: string;
  latitude?: number;
  longitude?: number;
  alamat?: string;
  foto_path?: string;
  nama_pelapor?: string;
  created_at: string;
  dinas?: Dinas;
}

const props = defineProps<{
  aduan: Aduan;
}>();

const copied = ref(false);

const copyTicket = async () => {
  try {
    await navigator.clipboard.writeText(props.aduan.kode_tiket);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2500);
  } catch (err) {
    console.error('Gagal menyalin:', err);
  }
};

const formatDate = (dateStr: string) => {
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }) + ' WIB';
  } catch {
    return dateStr;
  }
};
</script>

<template>
  <Head :title="`Tiket Laporan ${aduan.kode_tiket} - SIGAP Kab. Bandung`" />

  <div class="min-h-screen bg-[#F4F6F8] flex flex-col font-sans text-[#1B2733]">
    <AppHeader />

    <main class="max-w-[680px] w-full mx-auto px-4 sm:px-6 py-8 flex-1 space-y-6">

      <!-- Success Card Header -->
      <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 sm:p-8 text-center space-y-4">
        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto border border-emerald-200">
          <CheckCircle2 class="w-10 h-10" />
        </div>

        <div>
          <h1 class="text-2xl font-extrabold text-[#0A3D62] tracking-tight">
            Laporan Anda Berhasil Terkirim!
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 mt-1.5">
            Terima kasih atas partisipasi Anda. Laporan Anda telah tercatat dan otomatis diteruskan ke dinas teknis penanggung jawab.
          </p>
        </div>

        <!-- Ticket Box -->
        <div class="bg-slate-50 border-2 border-dashed border-[#0A3D62]/30 rounded-xl p-4 sm:p-5 my-3 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="text-center sm:text-left">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 block">
              Nomor Tiket Aduan Resmi
            </span>
            <span class="text-xl sm:text-2xl font-mono font-extrabold text-[#0A3D62] tracking-wide">
              {{ aduan.kode_tiket }}
            </span>
          </div>

          <button
            type="button"
            @click="copyTicket"
            class="px-4 py-2 rounded-lg bg-[#0A3D62] hover:bg-[#062A45] text-white text-xs font-bold flex items-center gap-1.5 shadow-xs transition cursor-pointer"
          >
            <Check v-if="copied" class="w-4 h-4 text-emerald-300" />
            <Copy v-else class="w-4 h-4 text-amber-300" />
            <span>{{ copied ? 'Tersalin!' : 'Salin Kode Tiket' }}</span>
          </button>
        </div>
      </div>

      <!-- Report Detail Summary Card -->
      <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 space-y-5">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
          <FileText class="w-4 h-4 text-[#0A3D62]" />
          <span>Ringkasan Disposisi Laporan</span>
        </h2>

        <!-- Dinas Penanggung Jawab Banner -->
        <div class="p-4 rounded-lg bg-blue-50/60 border border-blue-200 flex items-start gap-3">
          <div class="w-9 h-9 rounded-lg bg-[#0A3D62] text-white flex items-center justify-center shrink-0">
            <Building2 class="w-5 h-5 text-amber-300" />
          </div>
          <div>
            <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-900/70 block">
              Dinas Tujuan Auto-Routing
            </span>
            <p class="text-sm font-bold text-[#0A3D62]">
              {{ aduan.dinas?.nama_dinas || 'Dinas Teknis Terkait (Kabupaten Bandung)' }}
            </p>
            <p v-if="aduan.dinas?.kontak_telepon" class="text-xs text-slate-600 mt-0.5">
              Kontak Dinas: {{ aduan.dinas.kontak_telepon }}
            </p>
          </div>
        </div>

        <!-- Badges Kategori & Urgensi -->
        <div class="flex flex-wrap items-center gap-2">
          <CategoryBadge :kategori="aduan.kategori" :confidence="aduan.confidence_kategori" show-confidence />
          <UrgencyBadge :urgensi="aduan.urgensi" />
          <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
            Status: Baru Diterima
          </span>
        </div>

        <!-- Isi Keluhan -->
        <div class="space-y-1">
          <span class="text-xs font-semibold text-slate-500 block">Isi Aduan:</span>
          <p class="text-sm text-slate-800 bg-slate-50 p-3.5 rounded-lg border border-slate-200 leading-relaxed">
            {{ aduan.teks_aduan }}
          </p>
        </div>

        <!-- Lokasi Kejadian -->
        <div v-if="aduan.alamat" class="space-y-1">
          <span class="text-xs font-semibold text-slate-500 block">Lokasi:</span>
          <div class="flex items-start gap-2 text-xs text-slate-700">
            <MapPin class="w-4 h-4 text-[#0A3D62] shrink-0 mt-0.5" />
            <span>{{ aduan.alamat }}</span>
          </div>
        </div>

        <!-- Foto Bukti -->
        <div v-if="aduan.foto_path" class="space-y-1">
          <span class="text-xs font-semibold text-slate-500 block">Lampiran Foto:</span>
          <div class="rounded-lg overflow-hidden border border-slate-200 max-h-60 bg-slate-900 flex items-center justify-center">
            <img :src="aduan.foto_path" alt="Lampiran aduan" class="max-h-60 w-auto object-contain" />
          </div>
        </div>

        <!-- Timestamp -->
        <div class="flex items-center gap-2 text-xs text-slate-500 pt-2 border-t border-slate-100">
          <Calendar class="w-3.5 h-3.5" />
          <span>Waktu Laporan: {{ formatDate(aduan.created_at) }}</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
        <Link
          :href="`/lapor/status/${aduan.kode_tiket}`"
          class="w-full sm:w-1/2 py-3 px-4 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-sm flex items-center justify-center gap-2 shadow-md transition cursor-pointer"
        >
          <Search class="w-4 h-4 text-slate-950" />
          <span>Pantau Status Tiket Ini</span>
        </Link>
        <Link
          href="/lapor"
          class="w-full sm:w-1/2 py-3 px-4 rounded-xl bg-[#0A3D62] hover:bg-[#062A45] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition cursor-pointer"
        >
          <PlusCircle class="w-4 h-4 text-amber-300" />
          <span>Buat Laporan Baru</span>
        </Link>
      </div>

    </main>

    <AppFooter />
  </div>
</template>
