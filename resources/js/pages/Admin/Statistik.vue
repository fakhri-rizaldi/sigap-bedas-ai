<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
  BarChart3, 
  Download, 
  Layers, 
  Building2, 
  CheckCircle2, 
  Clock3, 
  AlertTriangle, 
  Flame, 
  Sparkles, 
  ArrowLeft, 
  Settings2, 
  Home, 
  LogOut,
  History,
  ArrowRight,
  TrendingUp,
  BrainCircuit,
  FileSpreadsheet
} from '@lucide/vue';

defineOptions({
  layout: null,
});

interface KategoriStat {
  kategori: string;
  total: number;
  percentage: number;
}

interface DinasStat {
  id: number;
  nama_dinas: string;
  kode_dinas: string;
  total_masuk: number;
  total_selesai: number;
  total_diproses: number;
  resolution_rate: number;
}

interface AiPerformance {
  total_aduan: number;
  total_koreksi: number;
  total_perlu_review: number;
  agreement_count: number;
  accuracy_rate: number;
}

interface RecentCorrection {
  id: number;
  kategori_lama: string;
  kategori_baru: string;
  alasan_koreksi?: string | null;
  created_at: string;
  aduan?: {
    id: number;
    kode_tiket: string;
    teks_aduan: string;
  } | null;
  user?: {
    id: number;
    name: string;
  } | null;
  dinas_lama?: {
    nama_dinas: string;
  } | null;
  dinas_baru?: {
    nama_dinas: string;
  } | null;
}

const props = defineProps<{
  totalAduan: number;
  kategoriDistribution: KategoriStat[];
  statusCounts: {
    baru: number;
    diproses: number;
    selesai: number;
    ditolak: number;
  };
  urgensiCounts: {
    Darurat: number;
    Tinggi: number;
    Sedang: number;
    Rendah: number;
  };
  dinasStats: DinasStat[];
  aiPerformance: AiPerformance;
  recentCorrections: RecentCorrection[];
}>();

const handleLogout = () => {
  router.post('/logout');
};

const getCategoryColor = (kategori: string) => {
  switch (kategori) {
    case 'Jalan Rusak':
      return { bg: 'bg-amber-500', text: 'text-amber-700', light: 'bg-amber-50', border: 'border-amber-200' };
    case 'Sampah/Banjir':
      return { bg: 'bg-teal-500', text: 'text-teal-700', light: 'bg-teal-50', border: 'border-teal-200' };
    case 'Bansos':
      return { bg: 'bg-purple-500', text: 'text-purple-700', light: 'bg-purple-50', border: 'border-purple-200' };
    case 'Keamanan/Ketertiban':
      return { bg: 'bg-rose-500', text: 'text-rose-700', light: 'bg-rose-50', border: 'border-rose-200' };
    default:
      return { bg: 'bg-blue-500', text: 'text-blue-700', light: 'bg-blue-50', border: 'border-blue-200' };
  }
};
</script>

<template>
  <Head title="Statistik Agregat & Analitik Eksekutif - SIGAP Kab. Bandung" />

  <div class="min-h-screen bg-[#F4F6F8] font-sans text-[#1B2733] flex flex-col">
    
    <!-- 1. Header Bar -->
    <header class="bg-[#0A3D62] text-white border-b border-blue-900 px-4 sm:px-6 h-14 shrink-0 flex items-center justify-between z-30 shadow-xs">
      
      <!-- Brand Logo -->
      <div class="flex items-center gap-3">
        <Link href="/dashboard" class="flex items-center gap-2.5 group">
          <div class="w-8 h-8 rounded-lg overflow-hidden border border-white/20 shrink-0 group-hover:ring-2 group-hover:ring-amber-400/50 transition">
            <img src="/logo-sigap.jpeg" alt="Logo SIGAP" class="w-full h-full object-cover" />
          </div>
          <div>
            <div class="font-extrabold text-sm leading-tight flex items-center gap-1.5">
              <span>SIGAP</span>
              <span class="text-[9px] bg-amber-400 font-extrabold px-1.5 py-0.5 rounded text-slate-950 uppercase tracking-wider">Statistik &amp; Analitik</span>
            </div>
          </div>
        </Link>
      </div>

      <!-- Navigation links -->
      <div class="flex items-center gap-2">
        <Link
          href="/dashboard"
          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition"
        >
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>Tiket CRM</span>
        </Link>

        <Link
          href="/admin/kategori-mapping"
          class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition"
        >
          <Settings2 class="w-3.5 h-3.5 text-blue-200" />
          <span>Mapping Dinas</span>
        </Link>

        <Link
          href="/lapor"
          class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition"
        >
          <Home class="w-3 h-3" />
          <span>Form Warga</span>
        </Link>

        <button
          type="button"
          @click="handleLogout"
          class="p-1.5 rounded-lg bg-white/10 hover:bg-red-500/20 hover:text-red-300 text-white border border-white/20 transition cursor-pointer"
          title="Keluar"
        >
          <LogOut class="w-3.5 h-3.5" />
        </button>
      </div>

    </header>

    <!-- 2. Main Content Area -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 md:p-8 space-y-6">
      
      <!-- Top Title & Export Action Banner -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/90 shadow-xs">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-50 text-[#0A3D62] border border-blue-200">
            <BarChart3 class="w-3.5 h-3.5 text-amber-500" />
            <span>Eksekutif Dashboard</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
            Statistik &amp; Performa Pelayanan Publik
          </h1>
          <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
            Ringkasan analitik pengaduan warga, performa respons dinas OPD, dan akurasi klasifikasi AI cerdas Kabupaten Bandung.
          </p>
        </div>

        <a
          href="/admin/statistik/export-csv"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#0A3D62] hover:bg-[#062A45] text-white font-extrabold text-xs shadow-md hover:shadow-lg transition cursor-pointer shrink-0"
        >
          <FileSpreadsheet class="w-4 h-4 text-emerald-400" />
          <span>Unduh Dataset CSV (Retraining)</span>
        </a>
      </div>

      <!-- 3. KPI Key Metrics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Aduan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Aduan</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0A3D62] flex items-center justify-center">
              <Layers class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-heading">
            {{ totalAduan }}
          </div>
          <p class="text-[11px] text-slate-500 font-medium">Laporan warga masuk ke sistem</p>
        </div>

        <!-- Selesai Diproses -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tuntas Ditangani</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <CheckCircle2 class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 font-heading">
            {{ statusCounts.selesai }}
          </div>
          <p class="text-[11px] text-emerald-600 font-medium">
            {{ totalAduan > 0 ? Math.round((statusCounts.selesai / totalAduan) * 100) : 0 }}% tingkat penyelesaian
          </p>
        </div>

        <!-- Sedang Diproses -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sedang Diproses</span>
            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
              <Clock3 class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-sky-700 font-heading">
            {{ statusCounts.diproses }}
          </div>
          <p class="text-[11px] text-slate-500 font-medium">Dalam tindak lanjut petugas</p>
        </div>

        <!-- Akurasi AI -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Akurasi AI Model</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <BrainCircuit class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-[#0A3D62] font-heading">
            {{ aiPerformance.accuracy_rate }}%
          </div>
          <p class="text-[11px] text-slate-500 font-medium">
            {{ aiPerformance.total_koreksi }} tiket dikoreksi staf
          </p>
        </div>

      </div>

      <!-- 4. Komposisi Kategori & Urgensi (2 Kolom) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Distribusi Kategori -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <Layers class="w-4 h-4 text-[#0A3D62]" />
              <span>Komposisi Kategori Aduan</span>
            </h3>
            <span class="text-xs text-slate-400 font-semibold">{{ kategoriDistribution.length }} Kategori</span>
          </div>

          <div class="space-y-3">
            <div 
              v-for="item in kategoriDistribution" 
              :key="item.kategori"
              class="space-y-1 text-xs"
            >
              <div class="flex items-center justify-between font-bold">
                <span class="text-slate-800">{{ item.kategori }}</span>
                <span class="text-slate-500 font-mono">{{ item.total }} aduan ({{ item.percentage }}%)</span>
              </div>
              <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                <div 
                  class="h-full rounded-full transition-all duration-500"
                  :class="getCategoryColor(item.kategori).bg"
                  :style="{ width: `${item.percentage}%` }"
                ></div>
              </div>
            </div>

            <div v-if="kategoriDistribution.length === 0" class="text-center py-6 text-slate-400">
              Belum ada data aduan
            </div>
          </div>
        </div>

        <!-- Sebaran Tingkat Urgensi -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <Flame class="w-4 h-4 text-red-500" />
              <span>Sebaran Tingkat Urgensi</span>
            </h3>
            <span class="text-xs text-slate-400 font-semibold">Prioritas AI</span>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <!-- Darurat -->
            <div class="p-3.5 rounded-2xl bg-red-50/80 border border-red-200 space-y-1">
              <span class="text-[11px] font-extrabold text-red-700 uppercase tracking-wider flex items-center gap-1">
                <Flame class="w-3.5 h-3.5" /> Darurat
              </span>
              <div class="text-xl font-extrabold text-red-900 font-heading">{{ urgensiCounts.Darurat }}</div>
              <p class="text-[10px] text-red-600">Butuh intervensi &lt; 24 jam</p>
            </div>

            <!-- Tinggi -->
            <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200 space-y-1">
              <span class="text-[11px] font-extrabold text-amber-700 uppercase tracking-wider flex items-center gap-1">
                <AlertTriangle class="w-3.5 h-3.5" /> Tinggi
              </span>
              <div class="text-xl font-extrabold text-amber-900 font-heading">{{ urgensiCounts.Tinggi }}</div>
              <p class="text-[10px] text-amber-600">Prioritas penanganan utama</p>
            </div>

            <!-- Sedang -->
            <div class="p-3.5 rounded-2xl bg-sky-50/80 border border-sky-200 space-y-1">
              <span class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider">
                Sedang
              </span>
              <div class="text-xl font-extrabold text-sky-900 font-heading">{{ urgensiCounts.Sedang }}</div>
              <p class="text-[10px] text-sky-600">Penanganan reguler</p>
            </div>

            <!-- Rendah -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
              <span class="text-[11px] font-extrabold text-slate-600 uppercase tracking-wider">
                Rendah
              </span>
              <div class="text-xl font-extrabold text-slate-800 font-heading">{{ urgensiCounts.Rendah }}</div>
              <p class="text-[10px] text-slate-500">Aspirasi &amp; informasi</p>
            </div>
          </div>
        </div>

      </div>

      <!-- 5. Performa & Tingkat Penyelesaian per Dinas (OPD) -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div class="space-y-0.5">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <Building2 class="w-4 h-4 text-[#0A3D62]" />
              <span>Kinerja Penanganan per Dinas Instansi (OPD)</span>
            </h3>
            <p class="text-xs text-slate-400">Tingkat respons dan resolusi laporan warga di tiap dinas</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
              <tr>
                <th class="py-3 px-4">Nama Instansi Dinas</th>
                <th class="py-3 px-4 text-center">Total Masuk</th>
                <th class="py-3 px-4 text-center">Sedang Proses</th>
                <th class="py-3 px-4 text-center">Tuntas Selesai</th>
                <th class="py-3 px-4">Tingkat Resolusi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
              <tr v-for="d in dinasStats" :key="d.id" class="hover:bg-slate-50/60 transition">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ d.nama_dinas }}</div>
                  <span class="text-[10px] font-mono text-amber-600 font-bold bg-amber-50 px-1 rounded">{{ d.kode_dinas }}</span>
                </td>
                <td class="py-3.5 px-4 text-center font-bold text-slate-800 font-mono">{{ d.total_masuk }}</td>
                <td class="py-3.5 px-4 text-center text-sky-700 font-bold font-mono">{{ d.total_diproses }}</td>
                <td class="py-3.5 px-4 text-center text-emerald-700 font-bold font-mono">{{ d.total_selesai }}</td>
                <td class="py-3.5 px-4 min-w-[160px]">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                      <div 
                        class="h-full bg-emerald-500 rounded-full transition-all"
                        :style="{ width: `${d.resolution_rate}%` }"
                      ></div>
                    </div>
                    <span class="text-[11px] font-bold font-mono text-emerald-700">{{ d.resolution_rate }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 6. Riwayat Koreksi Staf (Active Learning & Audit Trail) -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div class="space-y-0.5">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <History class="w-4 h-4 text-amber-600" />
              <span>Log Koreksi Kategori Terakhir (Active Learning Feed)</span>
            </h3>
            <p class="text-xs text-slate-400">Data koreksi manual oleh staf ini digunakan sebagai sampel retraining model NLP mandiri.</p>
          </div>
          <span class="text-xs font-bold text-slate-500 font-mono">
            {{ recentCorrections.length }} Log Terakhir
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
              <tr>
                <th class="py-3 px-4">Kode Tiket</th>
                <th class="py-3 px-4">Koreksi Kategori</th>
                <th class="py-3 px-4">Alasan Staf</th>
                <th class="py-3 px-4">Petugas</th>
                <th class="py-3 px-4 text-right">Waktu</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
              <tr v-for="log in recentCorrections" :key="log.id" class="hover:bg-slate-50/60 transition">
                <td class="py-3.5 px-4 font-mono font-bold text-[#0A3D62]">
                  #{{ log.aduan?.kode_tiket || '-' }}
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1.5 text-xs font-bold">
                    <span class="line-through text-slate-400">{{ log.kategori_lama }}</span>
                    <ArrowRight class="w-3 h-3 text-amber-600" />
                    <span class="text-[#0A3D62]">{{ log.kategori_baru }}</span>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate">
                  {{ log.alasan_koreksi || '-' }}
                </td>
                <td class="py-3.5 px-4 text-slate-700">
                  {{ log.user?.name || 'Staf Dinas' }}
                </td>
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-[11px]">
                  {{ new Date(log.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                </td>
              </tr>

              <tr v-if="recentCorrections.length === 0">
                <td colspan="5" class="py-8 text-center text-slate-400">
                  Belum ada log koreksi manual yang tercatat.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>

  </div>
</template>
