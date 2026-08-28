<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
  Plus, 
  Search, 
  Edit3, 
  Trash2, 
  Building2, 
  Layers, 
  ArrowLeft, 
  CheckCircle2, 
  AlertCircle, 
  X, 
  Loader2, 
  BarChart3, 
  Home, 
  LogOut,
  Sparkles,
  ShieldAlert
} from '@lucide/vue';

defineOptions({
  layout: null,
});

interface DinasItem {
  id: number;
  nama_dinas: string;
  kode_dinas: string;
}

interface MappingItem {
  id: number;
  kategori: string;
  dinas_id: number;
  deskripsi?: string | null;
  created_at?: string;
  dinas?: DinasItem | null;
}

const props = defineProps<{
  mappings: MappingItem[];
  dinasList: DinasItem[];
}>();

// Search state
const searchQuery = ref('');

const filteredMappings = computed(() => {
  if (!searchQuery.value.trim()) return props.mappings;
  const q = searchQuery.value.toLowerCase();
  return props.mappings.filter(m => 
    m.kategori.toLowerCase().includes(q) ||
    (m.dinas?.nama_dinas || '').toLowerCase().includes(q) ||
    (m.dinas?.kode_dinas || '').toLowerCase().includes(q) ||
    (m.deskripsi || '').toLowerCase().includes(q)
  );
});

// Modal State
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingMappingId = ref<number | null>(null);

// Form
const form = useForm({
  kategori: '',
  dinas_id: '' as number | string,
  deskripsi: '',
});

// Delete confirmation state
const isDeleteModalOpen = ref(false);
const deletingMapping = ref<MappingItem | null>(null);
const isDeleting = ref(false);

const openCreateModal = () => {
  isEditMode.value = false;
  editingMappingId.value = null;
  form.reset();
  form.clearErrors();
  if (props.dinasList.length > 0) {
    form.dinas_id = props.dinasList[0].id;
  }
  isModalOpen.value = true;
};

const openEditModal = (mapping: MappingItem) => {
  isEditMode.value = true;
  editingMappingId.value = mapping.id;
  form.clearErrors();
  form.kategori = mapping.kategori;
  form.dinas_id = mapping.dinas_id;
  form.deskripsi = mapping.deskripsi || '';
  isModalOpen.value = true;
};

const submitForm = () => {
  if (isEditMode.value && editingMappingId.value) {
    form.put(`/admin/kategori-mapping/${editingMappingId.value}`, {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post('/admin/kategori-mapping', {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const confirmDelete = (mapping: MappingItem) => {
  deletingMapping.value = mapping;
  isDeleteModalOpen.value = true;
};

const executeDelete = () => {
  if (!deletingMapping.value) return;
  isDeleting.value = true;
  router.delete(`/admin/kategori-mapping/${deletingMapping.value.id}`, {
    onFinish: () => {
      isDeleting.value = false;
      isDeleteModalOpen.value = false;
      deletingMapping.value = null;
    },
  });
};

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Manajemen Mapping Kategori & Dinas - SIGAP Kab. Bandung" />

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
              <span class="text-[9px] bg-amber-400 font-extrabold px-1.5 py-0.5 rounded text-slate-950 uppercase tracking-wider">Panel Admin</span>
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
          href="/admin/statistik"
          class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition"
        >
          <BarChart3 class="w-3.5 h-3.5 text-amber-300" />
          <span>Statistik</span>
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
      
      <!-- Top Title & Action Banner -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/90 shadow-xs">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-50 text-[#0A3D62] border border-blue-200">
            <Layers class="w-3.5 h-3.5 text-amber-500" />
            <span>Master Disposisi Layanan</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
            Mapping Kategori & Instansi Dinas
          </h1>
          <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
            Atur integrasi kategori aduan warga ke instansi Organisasi Perangkat Daerah (OPD) penanggung jawab secara dinamis tanpa mengubah kode program.
          </p>
        </div>

        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#0A3D62] hover:bg-[#062A45] text-white font-extrabold text-xs shadow-md hover:shadow-lg transition cursor-pointer shrink-0"
        >
          <Plus class="w-4 h-4 text-amber-300" />
          <span>Tambah Mapping Kategori</span>
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="flex items-center justify-between gap-4">
        <div class="relative w-full max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari kategori, nama dinas, singkatan..."
            class="w-full pl-9 pr-4 py-2 text-xs bg-white rounded-xl border border-slate-200 shadow-2xs focus:border-[#0A3D62] focus:ring-1 focus:ring-[#0A3D62] focus:outline-hidden transition"
          />
          <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" />
        </div>
        <span class="text-xs font-bold text-slate-500 whitespace-nowrap">
          Total: <strong class="text-slate-800">{{ filteredMappings.length }}</strong> Kategori
        </span>
      </div>

      <!-- Table of Mappings -->
      <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
              <tr>
                <th class="py-3.5 px-6">Nama Kategori</th>
                <th class="py-3.5 px-6">Dinas Penanggung Jawab</th>
                <th class="py-3.5 px-6">Deskripsi & Ruang Lingkup</th>
                <th class="py-3.5 px-6 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
              <tr 
                v-for="item in filteredMappings" 
                :key="item.id"
                class="hover:bg-slate-50/60 transition group"
              >
                <!-- Kategori -->
                <td class="py-4 px-6">
                  <div class="font-extrabold text-sm text-[#0A3D62] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    <span>{{ item.kategori }}</span>
                  </div>
                </td>

                <!-- Dinas OPD -->
                <td class="py-4 px-6">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0A3D62] shrink-0">
                      <Building2 class="w-3.5 h-3.5" />
                    </div>
                    <div>
                      <div class="font-extrabold text-xs text-slate-900">
                        {{ item.dinas?.nama_dinas || 'Dinas Belum Ditentukan' }}
                      </div>
                      <span class="text-[10px] font-mono font-bold text-amber-600 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200/60">
                        {{ item.dinas?.kode_dinas || '-' }}
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Deskripsi -->
                <td class="py-4 px-6 text-slate-500 max-w-xs truncate">
                  {{ item.deskripsi || '-' }}
                </td>

                <!-- Aksi -->
                <td class="py-4 px-6 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      type="button"
                      @click="openEditModal(item)"
                      class="p-2 rounded-lg bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-600 border border-slate-200 transition cursor-pointer"
                      title="Edit Mapping"
                    >
                      <Edit3 class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="confirmDelete(item)"
                      class="p-2 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-600 border border-slate-200 transition cursor-pointer"
                      title="Hapus Mapping"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredMappings.length === 0">
                <td colspan="4" class="py-12 text-center text-slate-400">
                  <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                    <Search class="w-6 h-6" />
                  </div>
                  <p class="font-bold text-sm text-slate-600">Tidak ada kategori yang cocok</p>
                  <p class="text-xs text-slate-400 mt-0.5">Coba sesuaikan kata kunci pencarian Anda.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>

    <!-- Modal Form (Tambah / Edit) -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4"
      @click.self="isModalOpen = false"
    >
      <div class="relative w-full max-w-md bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95">
        
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-[#0A3D62] to-[#08304E] text-white flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Layers class="w-4 h-4 text-amber-400" />
            <h3 class="font-extrabold text-sm">
              {{ isEditMode ? 'Edit Mapping Kategori' : 'Tambah Mapping Kategori Baru' }}
            </h3>
          </div>
          <button
            type="button"
            @click="isModalOpen = false"
            class="p-1 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="p-6 space-y-4 text-xs">
          
          <!-- Input Kategori -->
          <div class="space-y-1.5">
            <label for="kategori" class="block font-bold text-slate-700">
              Nama Kategori Pengaduan:
            </label>
            <input
              id="kategori"
              v-model="form.kategori"
              type="text"
              placeholder="Contoh: Penerangan Jalan Umum (PJU)"
              class="w-full p-2.5 rounded-xl bg-slate-50 border border-slate-300 font-semibold text-slate-900 focus:outline-hidden focus:border-[#0A3D62] focus:ring-1 focus:ring-[#0A3D62] transition"
              :class="{ 'border-red-500': form.errors.kategori }"
            />
            <span v-if="form.errors.kategori" class="text-[11px] text-red-600 font-semibold">
              {{ form.errors.kategori }}
            </span>
          </div>

          <!-- Select Dinas -->
          <div class="space-y-1.5">
            <label for="dinas_id" class="block font-bold text-slate-700">
              Instansi Dinas Penanggung Jawab (OPD):
            </label>
            <select
              id="dinas_id"
              v-model="form.dinas_id"
              class="w-full p-2.5 rounded-xl bg-slate-50 border border-slate-300 font-semibold text-slate-900 focus:outline-hidden focus:border-[#0A3D62] focus:ring-1 focus:ring-[#0A3D62] transition cursor-pointer"
              :class="{ 'border-red-500': form.errors.dinas_id }"
            >
              <option v-for="d in dinasList" :key="d.id" :value="d.id">
                {{ d.nama_dinas }} ({{ d.kode_dinas }})
              </option>
            </select>
            <span v-if="form.errors.dinas_id" class="text-[11px] text-red-600 font-semibold">
              {{ form.errors.dinas_id }}
            </span>
          </div>

          <!-- Textarea Deskripsi -->
          <div class="space-y-1.5">
            <label for="deskripsi" class="block font-bold text-slate-700">
              Deskripsi / Ruang Lingkup Layanan:
            </label>
            <textarea
              id="deskripsi"
              v-model="form.deskripsi"
              rows="3"
              placeholder="Jelaskan jenis-jenis aduan yang masuk ke dalam kategori ini..."
              class="w-full p-2.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0A3D62] focus:ring-1 focus:ring-[#0A3D62] transition"
            ></textarea>
          </div>

          <!-- Footer Buttons -->
          <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="isModalOpen = false"
              class="px-3.5 py-2 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 rounded-xl font-extrabold bg-[#0A3D62] hover:bg-[#08304E] text-white shadow-md transition disabled:opacity-50 flex items-center gap-1.5 cursor-pointer"
            >
              <Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ isEditMode ? 'Simpan Perubahan' : 'Tambah Kategori' }}</span>
            </button>
          </div>

        </form>

      </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div
      v-if="isDeleteModalOpen && deletingMapping"
      class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4"
      @click.self="isDeleteModalOpen = false"
    >
      <div class="relative w-full max-w-sm bg-white rounded-3xl p-6 overflow-hidden shadow-2xl border border-slate-200 text-center space-y-4 animate-in fade-in zoom-in-95">
        <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto">
          <Trash2 class="w-6 h-6" />
        </div>
        <div>
          <h3 class="font-extrabold text-sm text-slate-900">Hapus Mapping Kategori?</h3>
          <p class="text-xs text-slate-500 mt-1">
            Apakah Anda yakin ingin menghapus kategori <strong class="text-slate-800">'{{ deletingMapping.kategori }}'</strong>?
          </p>
        </div>
        <div class="flex items-center justify-center gap-2 pt-2">
          <button
            type="button"
            @click="isDeleteModalOpen = false"
            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="executeDelete"
            :disabled="isDeleting"
            class="px-4 py-2 rounded-xl text-xs font-extrabold bg-red-600 hover:bg-red-700 text-white shadow-md transition disabled:opacity-50 flex items-center gap-1.5 cursor-pointer"
          >
            <Loader2 v-if="isDeleting" class="w-3.5 h-3.5 animate-spin" />
            <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>
