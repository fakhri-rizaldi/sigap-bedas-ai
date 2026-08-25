<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';
import AppFooter from '@/components/AppFooter.vue';
import CategoryBadge from '@/components/CategoryBadge.vue';
import UrgencyBadge from '@/components/UrgencyBadge.vue';
import TicketMiniMap from '@/components/Dashboard/TicketMiniMap.vue';
import { 
  Search, 
  CheckCircle2, 
  Clock, 
  Clock3, 
  Building2, 
  MapPin, 
  Copy, 
  Check, 
  AlertCircle, 
  ArrowRight,
  ShieldCheck,
  FileText,
  HelpCircle,
  ExternalLink,
  MessageSquare,
  Flame,
  XCircle,
  RotateCw,
  Loader2
} from '@lucide/vue';

interface Dinas {
  id: number;
  nama_dinas: string;
  kode_dinas: string;
  deskripsi?: string;
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
  catatan_petugas?: string;
  latitude?: number;
  longitude?: number;
  alamat?: string;
  foto_path?: string;
  nama_pelapor?: string;
  created_at: string;
  updated_at?: string;
  dinas?: Dinas;
}

interface Props {
  aduan: Aduan | null;
  searchKode: string;
  searched: boolean;
}

const props = defineProps<Props>();

// Local Reactive State
const inputKode = ref<string>(props.searchKode || '');
const copied = ref<boolean>(false);
const isRefreshing = ref<boolean>(false);
const activeLightbox = ref<boolean>(false);

const handleSearch = () => {
  const clean = inputKode.value.trim().toUpperCase();
  if (!clean) return;

  router.get('/lapor/status/' + encodeURIComponent(clean), {}, {
    preserveState: false,
    preserveScroll: true,
  });
};

const handleRefresh = () => {
  if (!props.aduan?.kode_tiket) return;
  isRefreshing.value = true;
  router.get('/lapor/status/' + encodeURIComponent(props.aduan.kode_tiket), {}, {
    preserveState: false,
    preserveScroll: true,
    onFinish: () => {
      isRefreshing.value = false;
    }
  });
};

const copyTicket = async () => {
  if (!props.aduan?.kode_tiket) return;
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

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-';
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
  <Head :title="aduan ? `Status Tiket #${aduan.kode_tiket} - SIGAP Kab. Bandung` : 'Lacak Status Pengaduan - SIGAP Kab. Bandung'" />

  <div class="min-h-screen bg-[#F4F6F8] flex flex-col font-sans text-[#1B2733] selection:bg-[#0A3D62] selection:text-white">
    <AppHeader />

    <!-- 1. HERO SEARCH BANNER -->
    <section class="bg-gradient-to-br from-[#0A3D62] via-[#08304E] to-[#052136] text-white py-10 sm:py-14 border-b border-blue-900 shadow-md">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-4">
        

        <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white">
          Lacak Progres & Tindak Lanjut Aduan
        </h1>
        <p class="text-xs sm:text-sm text-blue-100/90 max-w-xl mx-auto leading-relaxed">
          Pantau status penanganan keluhan Anda secara transparan langsung dari instansi dinas teknis terkait.
        </p>

        <!-- Search Bar Box -->
        <form @submit.prevent="handleSearch" class="max-w-xl mx-auto pt-2">
          <div class="relative flex items-center shadow-2xl rounded-2xl overflow-hidden bg-white border-2 border-white/30 focus-within:border-amber-400 transition">
            <div class="pl-4 text-slate-400">
              <Search class="w-5 h-5" />
            </div>
            <input
              v-model="inputKode"
              type="text"
              placeholder="Masukkan Nomor Tiket (Contoh: BDS-20260823-XXXX)..."
              class="w-full py-3.5 px-3 text-xs sm:text-sm font-mono text-slate-900 placeholder:text-slate-400 focus:outline-hidden"
              required
            />
            <button
              type="submit"
              class="px-5 sm:px-7 py-3.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs sm:text-sm transition flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
              <span>Lacak</span>
              <ArrowRight class="w-4 h-4" />
            </button>
          </div>
        </form>

      </div>
    </section>

    <!-- 2. MAIN CONTENT CONTAINER -->
    <main class="max-w-4xl w-full mx-auto px-4 sm:px-6 py-8 flex-1 space-y-6">

      <!-- ================= KONDISI 1: TIKET DITEMUKAN ================= -->
      <div v-if="aduan" class="space-y-6 animate-in fade-in duration-300">
        
        <!-- Header Card: Nomor Tiket & Status Badge -->
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs border border-slate-200 space-y-4">
          
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Registrasi Aduan</span>
              <div class="flex items-center gap-2 mt-0.5">
                <span class="font-mono text-xl sm:text-2xl font-extrabold text-[#0A3D62]">
                  #{{ aduan.kode_tiket }}
                </span>
                <button
                  type="button"
                  @click="copyTicket"
                  class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-[#0A3D62] transition cursor-pointer"
                  title="Salin Nomor Tiket"
                >
                  <Check v-if="copied" class="w-4 h-4 text-emerald-600" />
                  <Copy v-else class="w-4 h-4" />
                </button>
                <span v-if="copied" class="text-[11px] text-emerald-600 font-bold animate-in fade-in">Tersalin!</span>

                <button
                  type="button"
                  @click="handleRefresh"
                  :disabled="isRefreshing"
                  class="ml-2 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 hover:text-[#0A3D62] hover:bg-slate-100 transition border border-slate-200 cursor-pointer disabled:opacity-50"
                  title="Perbarui Status Terkini"
                >
                  <Loader2 v-if="isRefreshing" class="w-3.5 h-3.5 animate-spin text-[#0A3D62]" />
                  <RotateCw v-else class="w-3.5 h-3.5 text-slate-500" />
                  <span>{{ isRefreshing ? 'Memuat...' : 'Perbarui' }}</span>
                </button>
              </div>
            </div>

            <!-- Status Badge -->
            <div class="flex flex-col sm:items-end gap-1.5">
              <span
                class="px-3.5 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider"
                :class="[
                  aduan.status === 'baru' ? 'bg-amber-100 text-amber-800 border border-amber-300' :
                  aduan.status === 'diproses' ? 'bg-blue-100 text-blue-800 border border-blue-300' :
                  aduan.status === 'selesai' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' :
                  'bg-slate-100 text-slate-700 border border-slate-300'
                ]"
              >
                Status: {{ aduan.status }}
              </span>

              <span class="text-[11px] text-slate-500">
                Diajukan pada: {{ formatDate(aduan.created_at) }}
              </span>
            </div>
          </div>

          <!-- ================= VISUAL PROGRESS STEPPER ================= -->
          <div class="pt-2">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4">
              Tahapan Progres Tindak Lanjut
            </h3>

            <!-- 3-Step Flow Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              
              <!-- Step 1: Laporan Masuk -->
              <div
                class="p-4 rounded-xl border transition-all"
                :class="[
                  aduan.status === 'baru'
                    ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-400/20'
                    : 'bg-emerald-50/50 border-emerald-200'
                ]"
              >
                <div class="flex items-center gap-2 mb-1.5">
                  <div
                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-extrabold text-white"
                    :class="aduan.status === 'baru' ? 'bg-amber-500' : 'bg-emerald-600'"
                  >
                    <Check v-if="aduan.status !== 'baru'" class="w-3.5 h-3.5" />
                    <span v-else>1</span>
                  </div>
                  <span class="font-extrabold text-xs text-slate-900">1. Laporan Diterima</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                  Laporan telah tercatat di basis data SIGAP dan diklasifikasi oleh AI.
                </p>
                <span class="text-[10px] text-slate-400 block mt-2 font-medium">
                  {{ formatDate(aduan.created_at) }}
                </span>
              </div>

              <!-- Step 2: Sedang Diproses -->
              <div
                class="p-4 rounded-xl border transition-all"
                :class="[
                  aduan.status === 'diproses'
                    ? 'bg-blue-50/80 border-blue-400 ring-2 ring-blue-500/20'
                    : aduan.status === 'selesai'
                    ? 'bg-emerald-50/50 border-emerald-200'
                    : 'bg-slate-50 border-slate-200 opacity-60'
                ]"
              >
                <div class="flex items-center gap-2 mb-1.5">
                  <div
                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-extrabold text-white"
                    :class="[
                      aduan.status === 'diproses' ? 'bg-blue-600' :
                      aduan.status === 'selesai' ? 'bg-emerald-600' : 'bg-slate-400'
                    ]"
                  >
                    <Check v-if="aduan.status === 'selesai'" class="w-3.5 h-3.5" />
                    <span v-else>2</span>
                  </div>
                  <span class="font-extrabold text-xs text-slate-900">2. Tindak Lanjut Dinas</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                  {{ aduan.dinas ? `Sedang ditangani oleh ${aduan.dinas.nama_dinas}.` : 'Disposisi ke dinas penanggung jawab.' }}
                </p>
                <span v-if="aduan.status === 'diproses' || aduan.status === 'selesai'" class="text-[10px] text-slate-400 block mt-2 font-medium">
                  {{ formatDate(aduan.updated_at || aduan.created_at) }}
                </span>
              </div>

              <!-- Step 3: Selesai Ditangani -->
              <div
                class="p-4 rounded-xl border transition-all"
                :class="[
                  aduan.status === 'selesai'
                    ? 'bg-emerald-50/90 border-emerald-400 ring-2 ring-emerald-500/20'
                    : aduan.status === 'ditolak'
                    ? 'bg-red-50 border-red-200'
                    : 'bg-slate-50 border-slate-200 opacity-60'
                ]"
              >
                <div class="flex items-center gap-2 mb-1.5">
                  <div
                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-extrabold text-white"
                    :class="[
                      aduan.status === 'selesai' ? 'bg-emerald-600' :
                      aduan.status === 'ditolak' ? 'bg-red-600' : 'bg-slate-400'
                    ]"
                  >
                    <Check v-if="aduan.status === 'selesai'" class="w-3.5 h-3.5" />
                    <XCircle v-else-if="aduan.status === 'ditolak'" class="w-3.5 h-3.5" />
                    <span v-else>3</span>
                  </div>
                  <span class="font-extrabold text-xs text-slate-900">
                    {{ aduan.status === 'ditolak' ? 'Laporan Ditutup' : '3. Penanganan Selesai' }}
                  </span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                  {{ aduan.status === 'selesai' ? 'Tindakan lapangan telah selesai dikerjakan.' : 'Tahap akhir penyelesaian keluhan.' }}
                </p>
                <span v-if="aduan.status === 'selesai'" class="text-[10px] text-emerald-700 block mt-2 font-bold">
                  Selesai pada: {{ formatDate(aduan.updated_at) }}
                </span>
              </div>

            </div>
          </div>

          <!-- Catatan Resmi Petugas Dinas (Jika Ada) -->
          <div
            v-if="aduan.catatan_petugas"
            class="p-4 rounded-xl bg-blue-50/70 border border-blue-200 space-y-1.5"
          >
            <div class="flex items-center gap-2 text-xs font-extrabold text-[#0A3D62]">
              <MessageSquare class="w-4 h-4 text-blue-600" />
              <span>Catatan Resmi Petugas Lapangan:</span>
            </div>
            <p class="text-xs text-slate-800 leading-relaxed pl-6">
              "{{ aduan.catatan_petugas }}"
            </p>
          </div>

        </div>

        <!-- Detail Rincian Aduan & Lokasi -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
          
          <!-- Kolom Kiri: Uraian & Foto -->
          <div class="lg:col-span-7 bg-white rounded-2xl p-6 shadow-xs border border-slate-200 space-y-4">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-2.5">
              Rincian Isi Laporan
            </h3>

            <!-- Kategori & Urgensi Badges -->
            <div class="flex flex-wrap items-center gap-2">
              <CategoryBadge :kategori="aduan.kategori" :confidence="aduan.confidence_kategori" show-confidence />
              <UrgencyBadge :urgensi="aduan.urgensi" />
            </div>

            <!-- Teks Aduan Warga -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">
              {{ aduan.teks_aduan }}
            </div>

            <!-- Alasan Analisis AI -->
            <div v-if="aduan.alasan_urgensi" class="text-xs text-slate-500 italic bg-amber-50/50 p-3 rounded-lg border border-amber-200/50">
              💡 <strong>Alasan Urgensi AI:</strong> {{ aduan.alasan_urgensi }}
            </div>

            <!-- Foto Bukti -->
            <div v-if="aduan.foto_path" class="space-y-2 pt-2">
              <span class="text-xs font-bold text-slate-700 block">Lampiran Foto Bukti:</span>
              <div
                @click="activeLightbox = true"
                class="relative rounded-xl overflow-hidden border border-slate-200 bg-slate-900 group cursor-pointer max-h-60"
              >
                <img
                  :src="aduan.foto_path"
                  alt="Foto bukti aduan"
                  class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-300 opacity-90 group-hover:opacity-100"
                />
                <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                  <span class="px-3 py-1.5 rounded-lg bg-white/90 text-slate-900 text-xs font-bold shadow-md">
                    Klik untuk Memperbesar
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Kolom Kanan: Dinas Penanggung Jawab & Peta Lokasi -->
          <div class="lg:col-span-5 space-y-6">
            
            <!-- Card Dinas Terkait -->
            <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 space-y-3">
              <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-2.5 flex items-center gap-1.5">
                <Building2 class="w-4 h-4 text-[#0A3D62]" />
                <span>Instansi Penanggung Jawab</span>
              </h3>

              <div v-if="aduan.dinas" class="space-y-2 text-xs">
                <div class="font-extrabold text-sm text-[#0A3D62]">
                  {{ aduan.dinas.nama_dinas }} ({{ aduan.dinas.kode_dinas }})
                </div>
                <p class="text-slate-600 leading-relaxed">
                  {{ aduan.dinas.deskripsi || 'Organisasi Perangkat Daerah Pemerintah Kabupaten Bandung.' }}
                </p>
              </div>
              <div v-else class="text-xs text-slate-500 italic">
                Sedang dalam proses verifikasi alokasi dinas.
              </div>
            </div>

            <!-- Card Peta Lokasi -->
            <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 space-y-3">
              <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-2.5 flex items-center gap-1.5">
                <MapPin class="w-4 h-4 text-[#0A3D62]" />
                <span>Titik Lokasi Kejadian</span>
              </h3>

              <p class="text-xs text-slate-700 font-medium">
                📍 {{ aduan.alamat || 'Kabupaten Bandung' }}
              </p>

              <div v-if="aduan.latitude && aduan.longitude" class="h-44 rounded-xl overflow-hidden border border-slate-200">
                <TicketMiniMap
                  :lat="aduan.latitude"
                  :lng="aduan.longitude"
                  :alamat="aduan.alamat"
                />
              </div>
            </div>

          </div>

        </div>

      </div>

      <!-- ================= KONDISI 2: TIKET TIDAK DITEMUKAN ================= -->
      <div
        v-else-if="searched"
        class="bg-white rounded-2xl p-10 text-center shadow-xs border border-slate-200 space-y-4 max-w-lg mx-auto animate-in fade-in"
      >
        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto border border-amber-200">
          <AlertCircle class="w-7 h-7" />
        </div>

        <div class="space-y-1.5">
          <h2 class="text-lg font-extrabold text-slate-900">
            Nomor Tiket Tidak Ditemukan
          </h2>
          <p class="text-xs text-slate-600 leading-relaxed">
            Tidak ada laporan dengan nomor tiket <strong class="font-mono text-[#0A3D62] font-bold">#{{ searchKode }}</strong>.
          </p>
        </div>

        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-500 text-left space-y-1">
          <p class="font-bold text-slate-700">💡 Tips Pencarian:</p>
          <ul class="list-disc list-inside space-y-0.5">
            <li>Pastikan format nomor tiket sesuai: <code class="bg-white px-1 rounded border">BDS-YYYYMMDD-XXXX</code></li>
            <li>Periksa kembali huruf besar/kecil atau tanda strip penghubung.</li>
          </ul>
        </div>

        <Link
          href="/lapor"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0A3D62] hover:bg-[#062A45] text-white font-bold text-xs transition shadow-xs cursor-pointer"
        >
          <span>Buat Laporan Baru</span>
          <ArrowRight class="w-4 h-4" />
        </Link>
      </div>

      <!-- ================= KONDISI 3: BELUM MENCARI (DEFAULT STATE) ================= -->
      <div
        v-else
        class="bg-white rounded-2xl p-10 text-center shadow-xs border border-slate-200 space-y-4 max-w-xl mx-auto"
      >
        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0A3D62] flex items-center justify-center mx-auto border border-blue-200">
          <Search class="w-7 h-7" />
        </div>

        <div class="space-y-1.5">
          <h2 class="text-base font-extrabold text-slate-900">
            Masukkan Nomor Tiket Pengaduan Anda
          </h2>
          <p class="text-xs text-slate-600 leading-relaxed">
            Gunakan nomor registrasi resmi yang Anda dapatkan saat mengirimkan laporan di SIGAP untuk melihat alur penanganan petugas.
          </p>
        </div>
      </div>

    </main>

    <!-- 3. LIGHTBOX MODAL (FULL IMAGE PREVIEW) -->
    <div
      v-if="activeLightbox && aduan?.foto_path"
      @click="activeLightbox = false"
      class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 cursor-zoom-out animate-in fade-in"
    >
      <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl">
        <img
          :src="aduan.foto_path"
          alt="Foto bukti resolusi penuh"
          class="w-full h-full object-contain"
        />
        <p class="text-center text-white text-xs mt-2">Klik di mana saja untuk menutup</p>
      </div>
    </div>

    <AppFooter />
  </div>
</template>
