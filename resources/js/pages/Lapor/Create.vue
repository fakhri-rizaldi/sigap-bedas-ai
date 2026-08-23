<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useDebounceFn } from '@vueuse/core';
import AppHeader from '@/components/AppHeader.vue';
import AppFooter from '@/components/AppFooter.vue';
import CategoryBadge from '@/components/CategoryBadge.vue';
import UrgencyBadge from '@/components/UrgencyBadge.vue';
import PhotoUploader from '@/components/PhotoUploader.vue';
import LocationPicker from '@/components/LocationPicker.vue';
import { 
  Sparkles, 
  Send, 
  Loader2, 
  CheckCircle2, 
  AlertCircle, 
  User, 
  Phone, 
  Mail,
  Lock, 
  FileText,
  Info,
  FileEdit,
  ChevronDown,
  ShieldCheck,
  Building2,
  Clock,
  Compass,
  ArrowRight,
  Layers,
  MapPin,
  Camera,
  CheckCircle,
  HelpCircle,
  Check
} from '@lucide/vue';

interface CategoryMapping {
  id: number;
  kategori: string;
  dinas?: {
    nama_dinas: string;
    kode_dinas: string;
  };
}

const props = defineProps<{
  categories?: CategoryMapping[];
}>();

// Form State Inertia
const form = useForm({
  teks_aduan: '',
  kategori: '',
  confidence_kategori: null as number | null,
  urgensi: 'Sedang',
  alasan_urgensi: '',
  latitude: -7.0252,
  longitude: 107.5197,
  alamat: '',
  foto: null as File | null,
  nama_pelapor: '',
  kontak_pelapor: '',
  email_pelapor: '',
});

// AI Classification State
const isClassifying = ref(false);
const aiClassified = ref(false);
const aiError = ref(false);

// Debounced classification (800ms)
const classifyText = useDebounceFn(async (text: string) => {
  const clean = text.trim();
  if (clean.length < 8) {
    form.kategori = '';
    form.confidence_kategori = null;
    form.urgensi = 'Sedang';
    form.alasan_urgensi = '';
    aiClassified.value = false;
    return;
  }

  isClassifying.value = true;
  aiError.value = false;

  try {
    const response = await axios.post('/api/aduan/classify', {
      teks_aduan: clean,
    });

    if (response.data?.status === 'success' && response.data?.data) {
      const data = response.data.data;
      form.kategori = data.kategori;
      form.confidence_kategori = data.confidence;
      form.urgensi = data.urgensi;
      form.alasan_urgensi = data.alasan;
      aiClassified.value = true;
    }
  } catch (err) {
    console.warn('Gagal klasifikasi AI:', err);
    aiError.value = true;
  } finally {
    isClassifying.value = false;
  }
}, 800);

watch(() => form.teks_aduan, (newVal) => {
  classifyText(newVal);
});

const isLocationValid = ref(true);

const onPhotoSelected = (file: string | File | null) => {
  form.foto = file as any;
};

const onLocationChanged = (payload: { lat: number; lng: number; address: string; isValid: boolean }) => {
  form.latitude = payload.lat;
  form.longitude = payload.lng;
  form.alamat = payload.address;
  isLocationValid.value = payload.isValid;
};

const scrollToForm = () => {
  const formEl = document.getElementById('section-form-aduan');
  if (formEl) {
    formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    const textarea = document.getElementById('teks_aduan');
    if (textarea) {
      setTimeout(() => textarea.focus(), 500);
    }
  }
};

const submitForm = () => {
  if (!isLocationValid.value) {
    alert('Titik lokasi aduan harus berada di dalam wilayah Kabupaten Bandung.');
    return;
  }

  form.post('/lapor', {
    preserveScroll: true,
    forceFormData: true,
    onError: (errors) => {
      console.warn('Form validation errors:', errors);
      const errEl = document.getElementById('form-error-banner');
      if (errEl) {
        errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    },
  });
};
</script>

<template>
  <Head title="SIGAP Kab. Bandung - Layanan Pengaduan Warga" />

  <div class="min-h-screen bg-[#F4F6F8] flex flex-col font-sans text-[#1B2733]">
    <AppHeader />

    <!-- Hero Section: Landscape Banner dengan Graphic Formal & Slogan BEDAS -->
    <section class="relative bg-gradient-to-br from-[#0A3D62] via-[#08304E] to-[#052136] text-white overflow-hidden py-12 lg:py-16 border-b border-blue-900 shadow-md">
      <!-- Background Graphic Pattern -->
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-blue-400 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-400 blur-3xl"></div>
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
              <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#grid-pattern)" />
        </svg>
      </div>

      <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          
          <!-- Kolom Kiri: Teks Judul, Slogan & Deskripsi -->
          <div class="lg:col-span-7 space-y-4 text-center lg:text-left">
            <!-- Judul Utama -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white font-heading">
              Layanan Pengaduan & Gerak Cepat Warga
            </h1>

            <!-- Slogan & Deskripsi -->
            <p class="text-sm sm:text-base text-blue-100/90 leading-relaxed max-w-xl">
              Wujudkan Kabupaten Bandung yang <strong>Bangkit, Edukatif, Dinamis, Agamis, dan Sejahtera (BEDAS)</strong>. Sampaikan keluhan jalan rusak, sampah, banjir, bantuan sosial, atau ketertiban umum untuk penanganan cepat dan tepat sasaran.
            </p>

            <!-- 4 Pilar Layanan Cepat -->
            <div class="pt-2 grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs text-blue-100">
              <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-center hover:bg-white/10 transition">
                <span class="block font-bold text-white">Jalan Rusak</span>
                <span class="text-[10px] text-amber-200">DPUTR</span>
              </div>
              <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-center hover:bg-white/10 transition">
                <span class="block font-bold text-white">Sampah / Banjir</span>
                <span class="text-[10px] text-teal-200">DLH</span>
              </div>
              <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-center hover:bg-white/10 transition">
                <span class="block font-bold text-white">Bansos</span>
                <span class="text-[10px] text-purple-200">DINSOS</span>
              </div>
              <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-center hover:bg-white/10 transition">
                <span class="block font-bold text-white">Ketertiban</span>
                <span class="text-[10px] text-red-200">Satpol PP</span>
              </div>
            </div>

            <!-- Tombol CTA Mulai Lapor -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
              <button
                type="button"
                @click="scrollToForm"
                class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold text-sm flex items-center justify-center gap-2 shadow-lg hover:shadow-amber-400/20 transition-all transform active:scale-95 cursor-pointer group"
              >
                <FileEdit class="w-4 h-4 text-slate-900 group-hover:rotate-12 transition-transform" />
                <span>Mulai Buat Laporan</span>
                <ChevronDown class="w-4 h-4 text-slate-900 animate-bounce ml-0.5" />
              </button>

              <div class="flex items-center gap-2 text-xs text-blue-200/80">
                <ShieldCheck class="w-4 h-4 text-emerald-400 shrink-0" />
                <span>Layanan Resmi Bebas Biaya</span>
              </div>
            </div>
          </div>

          <!-- Kolom Kanan: Graphic Badge Card Formal -->
          <div class="lg:col-span-5 hidden lg:block">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-2xl space-y-4">
              <div class="flex items-center gap-3 border-b border-white/15 pb-4">
                <div class="w-12 h-12 rounded-xl bg-amber-400/20 border border-amber-300/30 flex items-center justify-center text-amber-300 font-bold text-xl">
                  🏛️
                </div>
                <div>
                  <h3 class="font-bold text-base text-white">Alur Otomatisasi AI</h3>
                  <p class="text-xs text-blue-200">Klasifikasi instan tanpa birokrasi manual</p>
                </div>
              </div>

              <div class="space-y-3 text-xs text-blue-100/90">
                <div class="flex items-start gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                  <div class="w-6 h-6 rounded-full bg-blue-500/30 text-blue-300 flex items-center justify-center shrink-0 font-bold text-xs">1</div>
                  <p><strong>Tuliskan keluhan:</strong> AI membaca uraian Anda & menentukan kategori serta urgensi secara real-time.</p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                  <div class="w-6 h-6 rounded-full bg-amber-500/30 text-amber-300 flex items-center justify-center shrink-0 font-bold text-xs">2</div>
                  <p><strong>Auto-Routing Dinas:</strong> Tiket otomatis dialokasikan ke dinas teknis penanggung jawab tanpa antri.</p>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                  <div class="w-6 h-6 rounded-full bg-emerald-500/30 text-emerald-300 flex items-center justify-center shrink-0 font-bold text-xs">3</div>
                  <p><strong>Kode Tiket Terbit:</strong> Dapatkan nomor tiket resmi untuk memantau status tindak lanjut petugas.</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- Main Content Form (Landscape Wide Layout max-w-6xl) -->
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 py-10 flex-1">
      
      <!-- Anchor untuk Scroll Target -->
      <div id="section-form-aduan" class="scroll-mt-20 space-y-6">

        <!-- Header Card Form -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0A3D62] to-[#062A45] text-white flex items-center justify-center font-bold text-xl shadow-sm">
              <FileEdit class="w-5 h-5 text-amber-300" />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-extrabold text-[#0A3D62] font-heading">
                Formulir Aspirasi & Pengaduan Warga
              </h2>
              <p class="text-xs text-slate-500">
                Sistem SIGAP Kabupaten Bandung — Tanda bintang (<span class="text-red-500 font-bold">*</span>) wajib diisi.
              </p>
            </div>
          </div>

          <!-- Jaminan Privasi Badge -->
          <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200/80 text-xs font-semibold self-start md:self-auto">
            <ShieldCheck class="w-4 h-4 text-emerald-600 shrink-0" />
            <span>Kerahasiaan Identitas Dijamin Resmi</span>
          </div>
        </div>

        <form @submit.prevent="submitForm">

          <!-- Error Summary Alert -->
          <div v-if="form.hasErrors" class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1 animate-shake">
            <div class="flex items-center gap-2 font-bold text-red-900">
              <AlertCircle class="w-4 h-4 text-red-600 shrink-0" />
              <span>Mohon lengkapi bagian berikut sebelum mengirim:</span>
            </div>
            <ul class="list-disc list-inside pl-1 text-red-700 space-y-0.5 text-xs">
              <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
            </ul>
          </div>

          <!-- ================= MASTER 2-COLUMN DESKTOP GRID ================= -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

            <!-- ================= KOLOM KIRI (7 Kolom Desktop): ISI LAPORAN, BUKTI FOTO & IDENTITAS ================= -->
            <div class="lg:col-span-7 flex flex-col gap-6">

              <!-- 1. URAIAN ADUAN & LIVE AI CARD -->
              <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-4 hover:border-[#0A3D62]/40 transition-colors">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-[#0A3D62] text-white text-xs font-extrabold flex items-center justify-center shadow-xs">1</span>
                    <h3 class="text-sm font-extrabold text-slate-900">Uraian Aduan / Keluhan <span class="text-red-500">*</span></h3>
                  </div>
                  <span class="text-[11px] text-slate-400 font-mono font-medium">
                    {{ form.teks_aduan.length }} / 2000 karakter
                  </span>
                </div>

                <div class="space-y-3">
                  <textarea
                    id="teks_aduan"
                    v-model="form.teks_aduan"
                    rows="4"
                    maxlength="2000"
                    placeholder="Tuliskan keluhan Anda secara jelas...&#10;Contoh: Jalan aspal di dekat jembatan Citarum Dayeuhkolot berlubang cukup parah dan sering menimbulkan kecelakaan sepeda motor saat hujan."
                    class="w-full p-4 text-xs sm:text-sm bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-3 focus:ring-[#0A3D62]/15 text-slate-800 transition resize-y min-h-[120px] leading-relaxed shadow-inner"
                    required
                  ></textarea>

                  <!-- Live AI Classification Card -->
                  <div class="transition-all duration-300">
                    <!-- Loading Shimmer State -->
                    <div v-if="isClassifying" class="p-3.5 bg-blue-50/60 border border-blue-200/80 rounded-xl flex items-center gap-3 animate-pulse">
                      <Loader2 class="w-4 h-4 text-[#0A3D62] animate-spin shrink-0" />
                      <span class="text-xs text-[#0A3D62] font-semibold">AI sedang menganalisis kategori & urgensi laporan...</span>
                    </div>

                    <!-- AI Result Card -->
                    <div
                      v-else-if="aiClassified && form.kategori"
                      class="p-4 bg-gradient-to-r from-blue-50/90 via-slate-50 to-amber-50/50 border border-blue-200 rounded-xl space-y-2.5 shadow-xs animate-in fade-in duration-300"
                    >
                      <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-[#0A3D62]">
                          <Sparkles class="w-4 h-4 text-amber-500" />
                          <span>Hasil Analisis Otomatis AI:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                          <CategoryBadge :kategori="form.kategori" :confidence="form.confidence_kategori ?? undefined" show-confidence />
                          <UrgencyBadge :urgensi="form.urgensi" />
                        </div>
                      </div>
                      <p v-if="form.alasan_urgensi" class="text-xs text-slate-600 italic bg-white/90 p-2.5 rounded-lg border border-slate-200/60 leading-relaxed">
                        "{{ form.alasan_urgensi }}"
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 2. BUKTI FOTO LAPANGAN -->
              <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-3 hover:border-[#0A3D62]/40 transition-colors">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-slate-700 text-white text-xs font-extrabold flex items-center justify-center shadow-xs">2</span>
                    <h3 class="text-sm font-extrabold text-slate-900">Lampiran Foto Bukti</h3>
                  </div>
                  <span class="text-[11px] text-slate-400 font-medium">Opsional</span>
                </div>

                <PhotoUploader @file-selected="onPhotoSelected" />
              </div>

              <!-- 3. IDENTITAS PELAPOR -->
              <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-4 hover:border-[#0A3D62]/40 transition-colors mt-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-slate-700 text-white text-xs font-extrabold flex items-center justify-center shadow-xs">3</span>
                    <h3 class="text-sm font-extrabold text-slate-900">Identitas Pelapor</h3>
                  </div>
                  <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold">Opsional (Bisa Anonim)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- Nama -->
                  <div>
                    <label for="nama_pelapor" class="block text-xs font-bold text-slate-700 mb-1">
                      Nama Lengkap
                    </label>
                    <div class="relative">
                      <input
                        id="nama_pelapor"
                        v-model="form.nama_pelapor"
                        type="text"
                        placeholder="Nama Anda"
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-2 focus:ring-[#0A3D62]/15 text-slate-800 transition"
                      />
                      <User class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    </div>
                  </div>

                  <!-- WhatsApp -->
                  <div>
                    <label for="kontak_pelapor" class="block text-xs font-bold text-slate-700 mb-1">
                      Nomor WhatsApp / HP
                    </label>
                    <div class="relative">
                      <input
                        id="kontak_pelapor"
                        v-model="form.kontak_pelapor"
                        type="tel"
                        placeholder="0812xxxxxxxx"
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-2 focus:ring-[#0A3D62]/15 text-slate-800 transition"
                      />
                      <Phone class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    </div>
                  </div>
                </div>

                <!-- Email -->
                <div>
                  <label for="email_pelapor" class="block text-xs font-bold text-slate-700 mb-1">
                    Alamat Email <span class="text-slate-400 font-normal">(Opsional)</span>
                  </label>
                  <div class="relative">
                    <input
                      id="email_pelapor"
                      v-model="form.email_pelapor"
                      type="email"
                      placeholder="email@contoh.com"
                      class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-2 focus:ring-[#0A3D62]/15 text-slate-800 transition"
                    />
                    <Mail class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  </div>
                </div>
              </div>

            </div>

            <!-- ================= KOLOM KANAN (5 Kolom Desktop): TITIK LOKASI & ACTION SUBMIT ================= -->
            <div class="lg:col-span-5 flex flex-col gap-6">

              <!-- 4. TITIK LOKASI & WILAYAH (MASTER MAP CARD) -->
              <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-4 hover:border-[#0A3D62]/40 transition-colors flex-1 flex flex-col justify-between">
                <div>
                  <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3 mb-4">
                    <span class="w-6 h-6 rounded-lg bg-[#0A3D62] text-white text-xs font-extrabold flex items-center justify-center shadow-xs">4</span>
                    <h3 class="text-sm font-extrabold text-slate-900">Titik Lokasi & Wilayah <span class="text-red-500">*</span></h3>
                  </div>

                  <LocationPicker
                    :initial-lat="form.latitude"
                    :initial-lng="form.longitude"
                    :initial-address="form.alamat"
                    @location-changed="onLocationChanged"
                  />
                </div>
              </div>

              <!-- 5. ACTION CARD (SUBMIT BUTTON) -->
              <div class="bg-gradient-to-br from-slate-900 via-[#0A3D62] to-[#062A45] rounded-2xl p-6 shadow-lg text-white space-y-4">
                <div class="space-y-2">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-amber-300 flex items-center gap-1.5">
                    <Check class="w-4 h-4 text-amber-400" />
                    <span>Layanan Terpadu Kabupaten Bandung</span>
                  </h4>
                  <ul class="text-[11px] text-blue-100/90 space-y-1.5">
                    <li class="flex items-center gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                      <span>Klasifikasi otomatis & langsung dialokasikan ke dinas teknis.</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                      <span>Nomor registrasi tiket diterbitkan untuk pemantauan tindak lanjut.</span>
                    </li>
                  </ul>
                </div>

                <button
                  type="submit"
                  :disabled="form.processing || form.teks_aduan.trim().length < 10 || !isLocationValid"
                  class="w-full min-h-[54px] px-6 py-4 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-sm sm:text-base flex items-center justify-center gap-3 shadow-lg hover:shadow-amber-400/30 transition-all duration-200 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer group"
                >
                  <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin text-slate-950" />
                  <Send v-else class="w-5 h-5 text-slate-950 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                  <span>{{ form.processing ? 'Mengirimkan Laporan Anda...' : (!isLocationValid ? 'Lokasi Harus di Kab. Bandung' : 'Kirim Laporan Pengaduan Resmi') }}</span>
                </button>
              </div>

            </div>

          </div>

        </form>

      </div>

    </main>

    <AppFooter />
  </div>
</template>
