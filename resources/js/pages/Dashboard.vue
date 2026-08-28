<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { 
  Search, 
  ListFilter, 
  Columns3, 
  Map as MapIcon,
  Clock3, 
  Clock, 
  CheckCircle2, 
  Flame,
  LogOut, 
  Sparkles,
  ArrowUpDown,
  Building2,
  MapPin,
  RotateCcw, 
  Home,
  X,
  AlertTriangle,
  BarChart3,
  Settings2
} from '@lucide/vue';
import TicketCard, { type AduanItem } from '@/components/Dashboard/TicketCard.vue';
import TicketInspector from '@/components/Dashboard/TicketInspector.vue';
import KanbanView from '@/components/Dashboard/KanbanView.vue';
import HeatmapMap from '@/components/Dashboard/HeatmapMap.vue';
import { echo } from '@/echo';

interface Props {
  aduans: {
    data: AduanItem[];
    current_page: number;
    last_page: number;
    total: number;
  };
  stats: {
    total: number;
    baru: number;
    diproses: number;
    selesai: number;
    ditolak: number;
    darurat: number;
    perlu_review?: number;
  };
  dinasList: Array<{
    id: number;
    nama_dinas: string;
    kode_dinas: string;
  }>;
  categoriesList?: Array<{
    id: number;
    kategori: string;
    dinas_id: number;
    dinas?: {
      id: number;
      nama_dinas: string;
      kode_dinas: string;
    } | null;
  }>;
  kecamatanList: string[];
  filters: {
    status?: string;
    urgensi?: string;
    kategori?: string;
    dinas_id?: string;
    kecamatan?: string;
    search?: string;
    sort?: string;
    perlu_review?: string;
  };
  authDinas?: {
    id: number;
    nama_dinas: string;
    kode_dinas: string;
  } | null;
}

const props = defineProps<Props>();

defineOptions({
  layout: null, // Clean standalone layout
});

// State List Tiket & Real-Time
const ticketList = ref<AduanItem[]>([...props.aduans.data]);
const selectedTicket = ref<AduanItem | null>(ticketList.value.length > 0 ? ticketList.value[0] : null);
const statsState = ref({ ...props.stats });
const isLiveConnected = ref<boolean>(false);
const viewMode = ref<'list' | 'kanban' | 'map'>('list');
const isMobileInspectorOpen = ref<boolean>(false);
const newTicketAlert = ref<string | null>(null);

// Filters State
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedUrgensi = ref(props.filters.urgensi || 'all');
const selectedDinas = ref(props.filters.dinas_id || 'all');
const selectedKecamatan = ref(props.filters.kecamatan || 'all');
const selectedSort = ref(props.filters.sort || 'latest');
const selectedPerluReview = ref(props.filters.perlu_review === 'true');

// Cek apakah ada filter yang aktif
const isAnyFilterActive = computed(() => {
  return searchQuery.value !== '' || 
    selectedStatus.value !== 'all' || 
    selectedUrgensi.value !== 'all' || 
    selectedDinas.value !== 'all' || 
    selectedKecamatan.value !== 'all' || 
    selectedSort.value !== 'latest' ||
    selectedPerluReview.value;
});

// Sinkronkan ticketList saat props berubah dari server
watch(
  () => props.aduans.data,
  (newVal) => {
    ticketList.value = [...newVal];
    if (selectedTicket.value) {
      const found = ticketList.value.find(t => t.id === selectedTicket.value?.id);
      if (found) selectedTicket.value = found;
    }
  }
);

// Terapkan Filter ke Server via Inertia
const applyFilters = useDebounceFn(() => {
  router.get(
    '/dashboard',
    {
      search: searchQuery.value || undefined,
      status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
      urgensi: selectedUrgensi.value !== 'all' ? selectedUrgensi.value : undefined,
      dinas_id: selectedDinas.value !== 'all' ? selectedDinas.value : undefined,
      kecamatan: selectedKecamatan.value !== 'all' ? selectedKecamatan.value : undefined,
      sort: selectedSort.value !== 'latest' ? selectedSort.value : undefined,
      perlu_review: selectedPerluReview.value ? 'true' : undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
}, 300);

watch([selectedStatus, selectedUrgensi, selectedDinas, selectedKecamatan, selectedSort, selectedPerluReview], () => {
  applyFilters();
});

const handleSearchInput = () => {
  applyFilters();
};

const togglePerluReview = () => {
  selectedPerluReview.value = !selectedPerluReview.value;
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedStatus.value = 'all';
  selectedUrgensi.value = 'all';
  selectedDinas.value = 'all';
  selectedKecamatan.value = 'all';
  selectedSort.value = 'latest';
  selectedPerluReview.value = false;
  applyFilters();
};

// Pemilihan Tiket
const selectTicket = (ticket: AduanItem) => {
  selectedTicket.value = ticket;
  ticket.isNew = false;
  isMobileInspectorOpen.value = true;
};

const globalToastMessage = ref<string | null>(null);

// Update Status Tiket dari Inspector
const handleTicketUpdated = (updated: AduanItem) => {
  const index = ticketList.value.findIndex(t => t.id === updated.id);
  if (index !== -1) {
    ticketList.value[index] = { ...ticketList.value[index], ...updated };
  }
  selectedTicket.value = updated;

  // Tampilkan Alert Toast Sukses
  globalToastMessage.value = `Status tiket #${updated.kode_tiket} berhasil diperbarui menjadi ${updated.status.toUpperCase()} & disiarkan real-time!`;
  setTimeout(() => {
    globalToastMessage.value = null;
  }, 4500);
};

// WebSocket Reverb Echo Listener
onMounted(() => {
  try {
    const channel = echo.channel('aduans');
    isLiveConnected.value = true;

    // Listen: Tiket Baru Masuk dari Warga
    channel.listen('.aduan.created', (e: any) => {
      const incoming: AduanItem = {
        id: e.id,
        kode_tiket: e.kode_tiket,
        teks_aduan: e.teks_aduan,
        kategori: e.kategori,
        confidence_kategori: e.confidence_kategori,
        urgensi: e.urgensi,
        alasan_urgensi: e.alasan_urgensi,
        dinas_id: e.dinas_id,
        dinas: e.dinas_nama ? { id: e.dinas_id, nama_dinas: e.dinas_nama, kode_dinas: '' } : null,
        status: e.status || 'baru',
        latitude: e.latitude,
        longitude: e.longitude,
        alamat: e.alamat,
        foto_path: e.foto_path,
        nama_pelapor: e.nama_pelapor,
        created_at: e.created_at || new Date().toISOString(),
        isNew: true,
      };

      ticketList.value.unshift(incoming);
      statsState.value.total += 1;
      statsState.value.baru += 1;
      if (incoming.urgensi === 'Darurat' || incoming.urgensi === 'Tinggi') {
        statsState.value.darurat += 1;
      }

      newTicketAlert.value = `Laporan baru #${incoming.kode_tiket} (${incoming.kategori}) baru saja diterima.`;
      setTimeout(() => {
        newTicketAlert.value = null;
      }, 6000);
    });

    // Listen: Status Tiket Diperbarui
    channel.listen('.aduan.status_updated', (e: any) => {
      const idx = ticketList.value.findIndex(t => t.id === e.id || t.kode_tiket === e.kode_tiket);
      if (idx !== -1) {
        ticketList.value[idx].status = e.status;
        if (e.catatan_petugas) ticketList.value[idx].catatan_petugas = e.catatan_petugas;
        
        if (selectedTicket.value?.id === e.id) {
          selectedTicket.value.status = e.status;
          if (e.catatan_petugas) selectedTicket.value.catatan_petugas = e.catatan_petugas;
        }
      }
    });
  } catch (err) {
    console.warn('Reverb WebSocket tidak aktif:', err);
    isLiveConnected.value = false;
  }
});

onUnmounted(() => {
  try {
    echo.leaveChannel('aduans');
  } catch (err) {
    // Ignore cleanup error
  }
});

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Staff Dashboard - SIGAP Kab. Bandung" />

  <div class="h-screen w-screen bg-[#F4F6F8] flex flex-col font-sans text-[#1B2733] overflow-hidden selection:bg-[#0A3D62] selection:text-white relative">
    
    <!-- Floating Success Toast Notification -->
    <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-[-20px] opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="globalToastMessage"
        class="fixed top-16 right-6 z-50 p-4 rounded-2xl bg-slate-900 text-white shadow-2xl border border-emerald-500/60 flex items-center gap-3 max-w-md pointer-events-auto"
      >
        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-emerald-300">Tindak Lanjut Berhasil Disimpan</p>
          <p class="text-[11px] text-slate-300 mt-0.5 leading-snug truncate">{{ globalToastMessage }}</p>
        </div>
        <button
          type="button"
          @click="globalToastMessage = null"
          class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer shrink-0"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </transition>

    <!-- 1. Header Bar Minimalis -->
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
              <span class="text-[9px] bg-amber-400 font-extrabold px-1.5 py-0.5 rounded text-slate-950 uppercase tracking-wider">Dashboard Staf</span>
            </div>
          </div>
        </Link>
      </div>

      <!-- Quick Search Bar -->
      <div class="flex-1 max-w-sm mx-4">
        <div class="relative w-full">
          <input
            v-model="searchQuery"
            @input="handleSearchInput"
            type="text"
            placeholder="Cari tiket, nama, lokasi..."
            class="w-full pl-8 pr-3 py-1.5 text-xs bg-white/10 hover:bg-white/15 focus:bg-white text-white focus:text-slate-900 placeholder:text-blue-200/70 focus:placeholder:text-slate-400 rounded-lg border border-white/20 focus:border-amber-400 focus:outline-hidden transition"
          />
          <Search class="w-3.5 h-3.5 text-blue-200 absolute left-2.5 top-2 pointer-events-none" />
        </div>
      </div>

      <!-- Right Action Bar -->
      <div class="flex items-center gap-2">
        <Link
          href="/admin/statistik"
          class="hidden md:inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition"
          title="Dashboard Statistik Agregat"
        >
          <BarChart3 class="w-3.5 h-3.5 text-amber-300" />
          <span>Statistik</span>
        </Link>

        <Link
          href="/admin/kategori-mapping"
          class="hidden md:inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition"
          title="Master Mapping Kategori ke Dinas"
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

    <!-- 2. Live Notification Banner -->
    <div
      v-if="newTicketAlert"
      class="bg-amber-400 text-slate-950 px-4 py-1.5 text-xs font-bold flex items-center justify-between shrink-0 shadow-xs"
    >
      <div class="flex items-center gap-2">
        <Sparkles class="w-3.5 h-3.5 animate-spin text-slate-950" />
        <span>{{ newTicketAlert }}</span>
      </div>
      <button @click="newTicketAlert = null" class="text-[11px] hover:underline cursor-pointer font-extrabold">
        Tutup
      </button>
    </div>

    <!-- 3. SIMPLIFIED FILTER BAR (Single-Row Clean Controls) -->
    <div class="bg-white border-b border-slate-200 px-4 sm:px-6 py-2.5 shrink-0 space-y-2">
      
      <div class="flex flex-wrap items-center justify-between gap-3">
        
        <!-- Left: Status Tabs (Segmented Control) -->
        <div class="flex items-center bg-slate-100 p-1 rounded-xl gap-1 text-xs overflow-x-auto">
          <!-- Semua -->
          <button
            type="button"
            @click="selectedStatus = 'all'; selectedUrgensi = 'all'"
            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer select-none flex items-center gap-1.5"
            :class="selectedStatus === 'all' && selectedUrgensi === 'all' ? 'bg-white text-[#0A3D62] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            <span>Semua</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-md" :class="selectedStatus === 'all' && selectedUrgensi === 'all' ? 'bg-[#0A3D62]/10 text-[#0A3D62]' : 'bg-slate-200 text-slate-600'">
              {{ statsState.total }}
            </span>
          </button>

          <!-- Baru -->
          <button
            type="button"
            @click="selectedStatus = 'baru'; selectedUrgensi = 'all'"
            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer select-none flex items-center gap-1.5"
            :class="selectedStatus === 'baru' ? 'bg-amber-400 text-slate-950 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            <Clock3 class="w-3.5 h-3.5 text-amber-600" />
            <span>Baru</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-md bg-white/70 text-slate-900">
              {{ statsState.baru }}
            </span>
          </button>

          <!-- Diproses -->
          <button
            type="button"
            @click="selectedStatus = 'diproses'; selectedUrgensi = 'all'"
            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer select-none flex items-center gap-1.5"
            :class="selectedStatus === 'diproses' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            <Clock class="w-3.5 h-3.5" :class="selectedStatus === 'diproses' ? 'text-white' : 'text-blue-600'" />
            <span>Diproses</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-md" :class="selectedStatus === 'diproses' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'">
              {{ statsState.diproses }}
            </span>
          </button>

          <!-- Selesai -->
          <button
            type="button"
            @click="selectedStatus = 'selesai'; selectedUrgensi = 'all'"
            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer select-none flex items-center gap-1.5"
            :class="selectedStatus === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            <CheckCircle2 class="w-3.5 h-3.5" :class="selectedStatus === 'selesai' ? 'text-white' : 'text-emerald-600'" />
            <span>Selesai</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-md" :class="selectedStatus === 'selesai' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'">
              {{ statsState.selesai }}
            </span>
          </button>

          <!-- Darurat -->
          <button
            type="button"
            @click="selectedUrgensi = selectedUrgensi === 'Darurat' ? 'all' : 'Darurat'"
            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer select-none flex items-center gap-1.5"
            :class="selectedUrgensi === 'Darurat' ? 'bg-red-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            <Flame class="w-3.5 h-3.5 text-red-500" />
            <span>Darurat</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-md" :class="selectedUrgensi === 'Darurat' ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700'">
              {{ statsState.darurat }}
            </span>
          </button>

          <!-- Perlu Review (Dual-Layer AI Flag) -->
          <button
            type="button"
            @click="togglePerluReview"
            class="px-3 py-1.5 rounded-lg font-bold transition cursor-pointer select-none flex items-center gap-1.5"
            :class="selectedPerluReview ? 'bg-amber-500 text-slate-950 shadow-xs ring-2 ring-amber-400' : 'text-slate-600 hover:text-slate-900'"
          >
            <AlertTriangle class="w-3.5 h-3.5 text-amber-600" />
            <span>Perlu Review</span>
            <span
              v-if="(statsState.perlu_review ?? 0) > 0"
              class="text-[10px] px-1.5 py-0.2 rounded-md font-extrabold"
              :class="selectedPerluReview ? 'bg-slate-950 text-white' : 'bg-amber-200 text-amber-900'"
            >
              {{ statsState.perlu_review }}
            </span>
          </button>
        </div>

        <!-- Right: Compact Dropdowns & View Toggle -->
        <div class="flex items-center gap-2">
          
          <!-- Dropdown OPD -->
          <div class="relative">
            <select
              v-model="selectedDinas"
              class="text-xs bg-slate-50 hover:bg-slate-100 py-1.5 px-3 rounded-lg border border-slate-300 text-slate-800 font-medium focus:outline-hidden focus:border-[#0A3D62] cursor-pointer"
            >
              <option value="all">Semua Instansi Dinas</option>
              <option v-for="d in dinasList" :key="d.id" :value="String(d.id)">
                {{ d.kode_dinas }}
              </option>
            </select>
          </div>

          <!-- Dropdown Kecamatan -->
          <div class="relative">
            <select
              v-model="selectedKecamatan"
              class="text-xs bg-slate-50 hover:bg-slate-100 py-1.5 px-3 rounded-lg border border-slate-300 text-slate-800 font-medium focus:outline-hidden focus:border-[#0A3D62] cursor-pointer"
            >
              <option value="all">Semua Wilayah</option>
              <option v-for="kec in kecamatanList" :key="kec" :value="kec">
                Kec. {{ kec }}
              </option>
            </select>
          </div>

          <!-- Sort Dropdown -->
          <select
            v-model="selectedSort"
            class="text-xs bg-slate-50 hover:bg-slate-100 py-1.5 px-3 rounded-lg border border-slate-300 text-slate-800 font-medium focus:outline-hidden focus:border-[#0A3D62] cursor-pointer"
          >
            <option value="latest">Terbaru</option>
            <option value="urgensi">Urgensi Tertinggi</option>
          </select>

          <!-- Reset Filter Button (Only when active) -->
          <button
            v-if="isAnyFilterActive"
            type="button"
            @click="resetFilters"
            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
            title="Reset Filter"
          >
            <RotateCcw class="w-3.5 h-3.5" />
          </button>

          <!-- Separator -->
          <div class="h-4 w-px bg-slate-200 mx-0.5"></div>

          <!-- View Switcher (Feed vs Kanban vs Map) -->
          <div class="flex items-center bg-slate-100 p-0.5 rounded-lg text-xs">
            <button
              type="button"
              @click="viewMode = 'list'"
              class="p-1.5 rounded-md font-bold transition cursor-pointer flex items-center gap-1"
              :class="viewMode === 'list' ? 'bg-white text-[#0A3D62] shadow-xs' : 'text-slate-500 hover:text-slate-800'"
              title="Tampilan List Feed"
            >
              <ListFilter class="w-3.5 h-3.5" />
              <span class="hidden md:inline text-[11px]">List</span>
            </button>
            <button
              type="button"
              @click="viewMode = 'kanban'"
              class="p-1.5 rounded-md font-bold transition cursor-pointer flex items-center gap-1"
              :class="viewMode === 'kanban' ? 'bg-white text-[#0A3D62] shadow-xs' : 'text-slate-500 hover:text-slate-800'"
              title="Tampilan Kanban Board"
            >
              <Columns3 class="w-3.5 h-3.5" />
              <span class="hidden md:inline text-[11px]">Kanban</span>
            </button>
            <button
              type="button"
              @click="viewMode = 'map'"
              class="p-1.5 rounded-md font-bold transition cursor-pointer flex items-center gap-1"
              :class="viewMode === 'map' ? 'bg-white text-[#0A3D62] shadow-xs' : 'text-slate-500 hover:text-slate-800'"
              title="Tampilan Peta & Heatmap"
            >
              <MapIcon class="w-3.5 h-3.5" />
              <span class="hidden md:inline text-[11px]">Peta & Heatmap</span>
            </button>
          </div>

        </div>

      </div>

    </div>

    <!-- 4. CLEAN 2-COLUMN SPLIT WORKSPACE -->
    <div class="flex-1 flex overflow-hidden">
      
      <!-- Kolom 1: Ticket Feed / Kanban / Map Workspace -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-[#F4F6F8]">
        
        <!-- Mode 1: Peta & Heatmap Interaktif -->
        <div v-if="viewMode === 'map'" class="h-full min-h-[500px]">
          <HeatmapMap
            :filters="{
              status: selectedStatus,
              urgensi: selectedUrgensi,
              dinas_id: selectedDinas,
              kecamatan: selectedKecamatan,
            }"
            :selected-aduan-id="selectedTicket?.id"
            @select="selectTicket"
          />
        </div>

        <!-- Mode 2: Kanban Pipeline -->
        <div v-else-if="viewMode === 'kanban'" class="h-full">
          <KanbanView
            :aduans="ticketList"
            :selected-aduan-id="selectedTicket?.id"
            @select="selectTicket"
          />
        </div>

        <!-- Mode 3: List Feed -->
        <div v-else class="space-y-3 max-w-4xl mx-auto">
          <TicketCard
            v-for="aduan in ticketList"
            :key="aduan.id"
            :aduan="aduan"
            :is-selected="selectedTicket?.id === aduan.id"
            @select="selectTicket"
          />

          <!-- Empty State -->
          <div
            v-if="ticketList.length === 0"
            class="p-12 text-center bg-white rounded-2xl border border-slate-200 space-y-3"
          >
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
              <Search class="w-6 h-6" />
            </div>
            <h3 class="font-bold text-sm text-slate-800">Tidak ada aduan yang sesuai filter</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
              Coba ganti tab status atau atur ulang filter pencarian.
            </p>
            <button
              type="button"
              @click="resetFilters"
              class="px-4 py-2 rounded-xl bg-[#0A3D62] text-white text-xs font-bold hover:bg-[#062A45] transition cursor-pointer"
            >
              Reset Semua Filter
            </button>
          </div>
        </div>

      </main>

      <!-- Kolom 2: Right Inspector Pane (Inspector Slide-in on Mobile) -->
      <aside
        class="w-full lg:w-[460px] xl:w-[500px] h-full shrink-0 bg-white z-40 transition-transform duration-300"
        :class="[
          isMobileInspectorOpen ? 'fixed inset-0 lg:static' : 'hidden lg:block'
        ]"
      >
        <TicketInspector
          :aduan="selectedTicket"
          :categories-list="categoriesList"
          @close="isMobileInspectorOpen = false"
          @updated="handleTicketUpdated"
        />
      </aside>

    </div>

  </div>
</template>
