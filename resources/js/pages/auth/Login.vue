<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  ShieldAlert, 
  ShieldCheck, 
  Lock, 
  Mail, 
  ArrowLeft, 
  LogIn, 
  Loader2, 
  AlertCircle, 
  Building2
} from '@lucide/vue';
import { request } from '@/routes/password';

defineOptions({
  layout: null, // Standalone custom layout
});

const props = defineProps<{
  status?: string;
  canResetPassword?: boolean;
}>();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Masuk Portal Staf & OPD - SIGAP Kab. Bandung" />

  <div class="min-h-screen bg-[#F4F6F8] flex items-center justify-center p-4 sm:p-6 font-sans text-[#1B2733] relative selection:bg-[#0A3D62] selection:text-white">
    
    <!-- Background Subtle Blur Accents -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#0A3D62]/5 blur-3xl"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-amber-400/10 blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
      
      <!-- Status Alert -->
      <div
        v-if="status"
        class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2"
      >
        <ShieldCheck class="w-4 h-4 text-emerald-600 shrink-0" />
        <span>{{ status }}</span>
      </div>

      <!-- Main Login Card -->
      <div class="bg-white rounded-3xl shadow-xl border border-slate-200/90 overflow-hidden">
        
        <!-- Card Header with Embedded Back Button -->
        <div class="bg-gradient-to-br from-[#0A3D62] via-[#08304E] to-[#062A45] p-6 sm:p-8 text-white relative">
          
          <!-- Tombol Back / Kembali ke Beranda -->
          <div class="flex items-center justify-start mb-4">
            <Link
              href="/lapor"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition group cursor-pointer"
            >
              <ArrowLeft class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" />
              <span>Kembali ke Beranda</span>
            </Link>
          </div>

          <!-- Logo & Title -->
          <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl overflow-hidden border border-white/20 mx-auto shadow-inner">
              <img src="/logo-sigap.jpeg" alt="Logo SIGAP" class="w-full h-full object-cover" />
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold font-heading text-white tracking-tight">
              Portal Staf & OPD SIGAP
            </h1>
            <p class="text-xs text-blue-100/80 max-w-xs mx-auto leading-relaxed">
              Sistem Informasi & Manajemen Penanganan Aduan Publik
            </p>
          </div>

        </div>

        <!-- Form Body -->
        <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-5">
          
          <!-- Error Alert -->
          <div v-if="form.errors.email || form.errors.password" class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1 animate-shake">
            <div class="flex items-center gap-2 font-bold text-red-900">
              <AlertCircle class="w-4 h-4 text-red-600 shrink-0" />
              <span>Autentikasi Gagal</span>
            </div>
            <p v-if="form.errors.email" class="text-red-700 pl-6">{{ form.errors.email }}</p>
            <p v-if="form.errors.password" class="text-red-700 pl-6">{{ form.errors.password }}</p>
          </div>

          <!-- Email Field -->
          <div class="space-y-1.5">
            <label for="email" class="block text-xs font-bold text-slate-800">
              Alamat Email Akun Dinas <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                autofocus
                autocomplete="username"
                placeholder="nama.petugas@bandungkab.go.id"
                class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-3 focus:ring-[#0A3D62]/15 text-slate-800 transition"
              />
              <Mail class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
            </div>
          </div>

          <!-- Password Field -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label for="password" class="block text-xs font-bold text-slate-800">
                Kata Sandi <span class="text-red-500">*</span>
              </label>
              <Link
                v-if="canResetPassword"
                :href="request()"
                class="text-[11px] text-[#0A3D62] hover:text-[#062A45] font-semibold hover:underline"
              >
                Lupa sandi?
              </Link>
            </div>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-3 focus:ring-[#0A3D62]/15 text-slate-800 transition font-mono"
              />
              <Lock class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
            </div>
          </div>

          <!-- Remember Me Checkbox -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-slate-600 font-medium">
              <input
                type="checkbox"
                v-model="form.remember"
                class="w-4 h-4 rounded-md border-slate-300 text-[#0A3D62] focus:ring-[#0A3D62] cursor-pointer"
              />
              <span>Ingat sesi masuk di perangkat ini</span>
            </label>
          </div>

          <!-- Submit Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full min-h-[48px] px-5 py-3.5 rounded-xl bg-gradient-to-r from-[#0A3D62] to-[#062A45] hover:from-[#08304E] hover:to-[#052136] text-white font-extrabold text-sm flex items-center justify-center gap-2.5 shadow-md hover:shadow-lg transition-all duration-200 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer group"
            >
              <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin text-white" />
              <LogIn v-else class="w-4 h-4 text-amber-300 group-hover:translate-x-0.5 transition-transform" />
              <span>{{ form.processing ? 'Memverifikasi Kredensial...' : 'Masuk ke Portal Staf' }}</span>
            </button>
          </div>

          <!-- Security Disclaimer -->
          <div class="pt-2 border-t border-slate-100 flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
            <ShieldCheck class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
            <span>Akses khusus aparatur berwenang. Seluruh aktivitas audit sistem dicatat secara resmi.</span>
          </div>

        </form>

      </div>

        <p>© 2026 SIGAP</p>

    </div>

  </div>
</template>
