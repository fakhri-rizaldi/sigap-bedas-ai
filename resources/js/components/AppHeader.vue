<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { 
  FileText, 
  LayoutDashboard, 
  ChevronDown, 
  ExternalLink, 
  Home, 
  Search,
  Building2, 
  Compass, 
  Menu, 
  X,
  Truck,
  Leaf,
  HeartHandshake,
  Shield
} from '@lucide/vue';
import { onClickOutside } from '@vueuse/core';

// Dropdown State
const isInstansiDropdownOpen = ref(false);
const instansiDropdownRef = ref<HTMLDivElement | null>(null);

// Mobile Menu State
const isMobileMenuOpen = ref(false);

onClickOutside(instansiDropdownRef, () => {
  isInstansiDropdownOpen.value = false;
});

const instansiList = [
  {
    nama: 'DPUTR Kab. Bandung',
    singkatan: 'DPUTR',
    deskripsi: 'Dinas Pekerjaan Umum & Tata Ruang (Jalan & Jembatan)',
    url: 'https://dputr.bandungkab.go.id/beranda#',
    icon: Truck,
    badgeColor: 'bg-amber-500/20 text-amber-300 border-amber-400/30',
  },
  {
    nama: 'Dinas Lingkungan Hidup',
    singkatan: 'DLH',
    deskripsi: 'Penanganan Sampah, Drainase & Kebersihan',
    url: 'https://lingkunganhidup.bandungkab.go.id',
    icon: Leaf,
    badgeColor: 'bg-teal-500/20 text-teal-300 border-teal-400/30',
  },
  {
    nama: 'Dinas Sosial',
    singkatan: 'DINSOS',
    deskripsi: 'Bantuan Sosial & Kesejahteraan Warga',
    url: 'https://dinsos.bandungkab.go.id',
    icon: HeartHandshake,
    badgeColor: 'bg-purple-500/20 text-purple-300 border-purple-400/30',
  },
  {
    nama: 'Satpol PP Kab. Bandung',
    singkatan: 'Satpol PP',
    deskripsi: 'Ketertiban Umum, Trantibum & Linmas',
    url: 'https://satpolpp.bandungkab.go.id/',
    icon: Shield,
    badgeColor: 'bg-red-500/20 text-red-300 border-red-400/30',
  },
];
</script>

<template>
  <header class="bg-[#0A3D62] text-white shadow-md sticky top-0 z-50 border-b border-blue-900">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
      
      <!-- Brand / Logo -->
      <Link href="/lapor" class="flex items-center gap-3 group">
        <div class="w-10 h-10 rounded-xl overflow-hidden border border-white/20 shrink-0">
          <img src="/logo-sigap.jpeg" alt="Logo SIGAP" class="w-full h-full object-cover" />
        </div>
        <div>
          <div class="font-extrabold text-lg leading-tight tracking-tight">
            <span>SIGAP</span>
          </div>
          <p class="text-xs text-blue-100/90 font-normal">Sistem Informasi &amp; Gerak Aduan Publik</p>
        </div>
      </Link>

      <!-- Desktop Navigation -->
      <nav class="hidden md:flex items-center gap-2">
        <!-- 1. Beranda -->
        <Link
          href="/lapor"
          class="px-3 py-2 rounded-lg text-sm font-semibold hover:bg-white/10 transition flex items-center gap-1.5 text-blue-100 hover:text-white"
        >
          <Home class="w-4 h-4 text-blue-200" />
          <span>Beranda</span>
        </Link>

        <!-- 2. Lacak Status -->
        <Link
          href="/lapor/status"
          class="px-3 py-2 rounded-lg text-sm font-semibold hover:bg-white/10 transition flex items-center gap-1.5 text-blue-100 hover:text-white"
        >
          <Search class="w-4 h-4 text-amber-300" />
          <span>Lacak Status</span>
        </Link>

        <!-- 3. Layanan Instansi Dropdown -->
        <div ref="instansiDropdownRef" class="relative">
          <button
            type="button"
            @click="isInstansiDropdownOpen = !isInstansiDropdownOpen"
            class="px-3 py-2 rounded-lg text-sm font-semibold hover:bg-white/10 transition flex items-center gap-1.5 text-blue-100 hover:text-white cursor-pointer"
            :class="{ 'bg-white/15 text-white': isInstansiDropdownOpen }"
          >
            <Building2 class="w-4 h-4 text-amber-300" />
            <span>Layanan Instansi</span>
            <ChevronDown class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isInstansiDropdownOpen }" />
          </button>

          <!-- Dropdown Menu -->
          <div
            v-if="isInstansiDropdownOpen"
            class="absolute left-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50 text-slate-800 animate-in fade-in slide-in-from-top-2 duration-150"
          >
            <div class="px-3.5 py-1.5 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
              Portal Resmi OPD Pemkab Bandung
            </div>

            <div class="p-1 space-y-0.5">
              <a
                v-for="item in instansiList"
                :key="item.singkatan"
                :href="item.url"
                target="_blank"
                rel="noopener noreferrer"
                @click="isInstansiDropdownOpen = false"
                class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-blue-50 transition group"
              >
                <div class="w-8 h-8 rounded-lg bg-[#0A3D62]/10 text-[#0A3D62] flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-[#0A3D62] group-hover:text-white transition">
                  <component :is="item.icon" class="w-4 h-4" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between gap-1">
                    <p class="text-xs font-bold text-slate-900 group-hover:text-[#0A3D62] transition">
                      {{ item.nama }}
                    </p>
                    <ExternalLink class="w-3 h-3 text-slate-400 group-hover:text-[#0A3D62] transition shrink-0" />
                  </div>
                  <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                    {{ item.deskripsi }}
                  </p>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- 3. Visi & Misi -->
        <a
          href="https://bkad.bandungkab.go.id/page/statis/visimisi"
          target="_blank"
          rel="noopener noreferrer"
          class="px-3 py-2 rounded-lg text-sm font-semibold hover:bg-white/10 transition flex items-center gap-1.5 text-blue-100 hover:text-white"
        >
          <Compass class="w-4 h-4 text-emerald-300" />
          <span>Visi & Misi</span>
          <ExternalLink class="w-3 h-3 opacity-60 ml-0.5" />
        </a>

        <!-- Separator -->
        <div class="h-5 w-px bg-white/20 mx-1"></div>

        <!-- 4. Dashboard Staf (Auth/Admin) -->
        <Link
          href="/dashboard"
          class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 transition flex items-center gap-1.5 border border-white/20 text-amber-300 hover:text-white shadow-xs"
        >
          <LayoutDashboard class="w-4 h-4 text-amber-300" />
          <span>Dashboard Staf</span>
        </Link>
      </nav>

      <!-- Mobile Hamburger Button -->
      <div class="flex md:hidden items-center gap-2">
        <button
          type="button"
          @click="isMobileMenuOpen = !isMobileMenuOpen"
          class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition border border-white/20"
          :aria-expanded="isMobileMenuOpen"
          aria-label="Toggle Menu"
        >
          <X v-if="isMobileMenuOpen" class="w-6 h-6 text-amber-300" />
          <Menu v-else class="w-6 h-6" />
        </button>
      </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div
      v-if="isMobileMenuOpen"
      class="md:hidden bg-[#072B45] border-t border-blue-900 px-4 py-4 space-y-3 animate-in fade-in duration-200"
    >
      <Link
        href="/lapor"
        @click="isMobileMenuOpen = false"
        class="flex items-center gap-2.5 p-2.5 rounded-xl hover:bg-white/10 text-sm font-semibold text-white"
      >
        <Home class="w-4 h-4 text-blue-300" />
        <span>Beranda</span>
      </Link>

      <Link
        href="/lapor/status"
        @click="isMobileMenuOpen = false"
        class="flex items-center gap-2.5 p-2.5 rounded-xl hover:bg-white/10 text-sm font-semibold text-white"
      >
        <Search class="w-4 h-4 text-amber-300" />
        <span>Lacak Status Pengaduan</span>
      </Link>

      <!-- Mobile Instansi Accordion/List -->
      <div class="space-y-1 pt-1 border-t border-white/10">
        <p class="text-[11px] font-bold uppercase tracking-wider text-blue-200/80 px-2.5 pt-2">
          Layanan Instansi Terkait
        </p>
        <a
          v-for="item in instansiList"
          :key="item.singkatan"
          :href="item.url"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center justify-between p-2.5 rounded-xl hover:bg-white/10 text-xs font-medium text-slate-100"
        >
          <div class="flex items-center gap-2">
            <component :is="item.icon" class="w-4 h-4 text-amber-300" />
            <span>{{ item.nama }}</span>
          </div>
          <ExternalLink class="w-3.5 h-3.5 text-blue-300" />
        </a>
      </div>

      <!-- Visi Misi Mobile -->
      <a
        href="https://bkad.bandungkab.go.id/page/statis/visimisi"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center justify-between p-2.5 rounded-xl hover:bg-white/10 text-sm font-semibold text-white border-t border-white/10 pt-3"
      >
        <div class="flex items-center gap-2">
          <Compass class="w-4 h-4 text-emerald-300" />
          <span>Visi & Misi Kab. Bandung</span>
        </div>
        <ExternalLink class="w-3.5 h-3.5 text-blue-300" />
      </a>

      <!-- Dashboard Staf Mobile -->
      <div class="pt-2">
        <Link
          href="/dashboard"
          @click="isMobileMenuOpen = false"
          class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-md transition"
        >
          <LayoutDashboard class="w-4 h-4 text-slate-950" />
          <span>Masuk Dashboard Staf</span>
        </Link>
      </div>
    </div>
  </header>
</template>
