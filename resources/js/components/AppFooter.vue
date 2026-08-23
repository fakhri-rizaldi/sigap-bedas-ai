<script setup lang="ts">
import { PhoneCall, ShieldCheck, Info } from '@lucide/vue';
</script>

<template>
  <footer class="bg-[#062A45] text-slate-300 text-sm mt-16 border-t border-blue-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Kolom 1: Identitas Layanan -->
        <div>
          <div class="flex items-center gap-2 mb-3">
            <span class="font-extrabold text-white text-base">SIGAP</span>
          </div>
          <p class="text-xs leading-relaxed text-slate-300/80 mb-4">
            <strong>SIGAP Kab. Bandung</strong> — Platform terpadu penanganan aspirasi dan keluhan warga berbasis kecerdasan buatan untuk wilayah Kabupaten Bandung.
          </p>
          <div class="flex items-center gap-2 text-xs text-slate-400">
            <ShieldCheck class="w-4 h-4 text-emerald-400" />
            <span>Kerahasiaan data pelapor terlindungi</span>
          </div>
        </div>

        <!-- Kolom 2: Kontak Darurat -->
        <div>
          <h3 class="font-semibold text-white text-sm mb-3 flex items-center gap-2">
            <PhoneCall class="w-4 h-4 text-amber-400" />
            Kontak Darurat Pemkab Bandung
          </h3>
          <ul class="space-y-2 text-xs text-slate-300/80">
            <li><strong class="text-white">Call Center Darurat:</strong> 112 (Bebas Pulsa)</li>
            <li><strong class="text-white">Satpol PP Kab. Bandung:</strong> (022) 5891237</li>
            <li><strong class="text-white">DPUTR Kab. Bandung:</strong> (022) 5891234</li>
            <li><strong class="text-white">Dinas Lingkungan Hidup:</strong> (022) 5891235</li>
          </ul>
        </div>

        <!-- Kolom 3: Disclaimer & Alur Kerja -->
        <div>
          <h3 class="font-semibold text-white text-sm mb-3 flex items-center gap-2">
            <Info class="w-4 h-4 text-sky-400" />
            Informasi Pelayanan
          </h3>
          <p class="text-xs leading-relaxed text-slate-300/80 mb-3">
            Laporan yang masuk akan diklasifikasi secara otomatis oleh AI dan langsung diteruskan ke dinas teknis terkait untuk diverifikasi dan ditindaklanjuti.
          </p>
          <p class="text-[11px] text-slate-400">
            Jam operasional verifikasi manual staf dinas: Senin – Jumat (08.00 – 16.00 WIB). Layanan sistem AI aktif 24 jam.
          </p>
        </div>
      </div>

      <div class="mt-10 pt-6 border-t border-blue-900/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
        <p>© 2026 Pemerintah Kabupaten Bandung. Dikembangkan untuk Kerja Praktik / Sistem Layanan Publik.</p>
        <p class="font-medium text-slate-300">Bangkit, Edukatif, Dinamis, Agamis, Sejahtera (BEDAS)</p>
      </div>
    </div>
  </footer>
</template>
