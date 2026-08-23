<script setup lang="ts">
import { ref } from 'vue';
import { Camera, Image as ImageIcon, X, AlertCircle, UploadCloud, CheckCircle2, Sparkles, Loader2 } from '@lucide/vue';

const emit = defineEmits<{
  (e: 'file-selected', file: string | File | null): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const fileName = ref<string | null>(null);
const fileSize = ref<string | null>(null);
const errorMessage = ref<string | null>(null);
const isDragging = ref(false);
const isCompressing = ref(false);

// Kompresi Gambar Otomatis ke Base64 Data URL (Max 1600px, Web-Optimized JPEG ~300KB)
const compressImage = (file: File): Promise<{ dataUrl: string; sizeMb: string; name: string }> => {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (event) => {
      const rawDataUrl = event.target?.result as string;
      const img = new Image();
      img.src = rawDataUrl;

      img.onload = () => {
        const canvas = document.createElement('canvas');
        const MAX_WIDTH = 1600;
        const MAX_HEIGHT = 1600;
        let width = img.width;
        let height = img.height;

        if (width > height) {
          if (width > MAX_WIDTH) {
            height = Math.round(height * (MAX_WIDTH / width));
            width = MAX_WIDTH;
          }
        } else {
          if (height > MAX_HEIGHT) {
            width = Math.round(width * (MAX_HEIGHT / height));
            height = MAX_HEIGHT;
          }
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
          resolve({
            dataUrl: rawDataUrl,
            sizeMb: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
            name: file.name,
          });
          return;
        }

        ctx.drawImage(img, 0, 0, width, height);

        // Export ke JPEG terkompresi
        const dataUrl = canvas.toDataURL('image/jpeg', 0.82);
        const approxBytes = Math.round((dataUrl.length * 3) / 4);
        const sizeMb = (approxBytes / (1024 * 1024)).toFixed(2) + ' MB';

        resolve({
          dataUrl,
          sizeMb,
          name: file.name.replace(/\.[^/.]+$/, '') + '.jpg',
        });
      };

      img.onerror = () => {
        resolve({
          dataUrl: rawDataUrl,
          sizeMb: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
          name: file.name,
        });
      };
    };

    reader.onerror = () => {
      errorMessage.value = 'Gagal membaca berkas gambar.';
    };
  });
};

const handleFile = async (file: File) => {
  errorMessage.value = null;

  // Validasi format file
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/heic'];
  if (!validTypes.includes(file.type) && !file.name.match(/\.(jpg|jpeg|png|webp|heic)$/i)) {
    errorMessage.value = 'Format file tidak didukung. Mohon gunakan format JPG, PNG, atau WebP.';
    return;
  }

  // Validasi ukuran mentah (Maksimal 15MB sebelum kompresi)
  if (file.size > 15 * 1024 * 1024) {
    errorMessage.value = 'Ukuran file asli terlalu besar (maksimal 15 MB).';
    return;
  }

  isCompressing.value = true;

  try {
    const result = await compressImage(file);

    fileName.value = result.name;
    fileSize.value = result.sizeMb;
    previewUrl.value = result.dataUrl;
    emit('file-selected', result.dataUrl);
  } catch (err) {
    console.warn('Gagal optimasi foto:', err);
    errorMessage.value = 'Gagal memproses gambar.';
  } finally {
    isCompressing.value = false;
  }
};

const onFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    handleFile(target.files[0]);
  }
};

const onDrop = (e: DragEvent) => {
  isDragging.value = false;
  if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
    handleFile(e.dataTransfer.files[0]);
  }
};

const removePhoto = () => {
  previewUrl.value = null;
  fileName.value = null;
  fileSize.value = null;
  errorMessage.value = null;
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
  emit('file-selected', null);
};

const triggerInput = () => {
  fileInputRef.value?.click();
};
</script>

<template>
  <div class="space-y-2">
    <!-- Error Alert -->
    <div v-if="errorMessage" class="flex items-center gap-2 p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
      <AlertCircle class="w-4 h-4 shrink-0 text-red-600" />
      <span>{{ errorMessage }}</span>
    </div>

    <!-- Upload Box or Preview -->
    <div
      v-if="!previewUrl"
      @click="triggerInput"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
      :class="[
        'border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-3 min-h-[140px] group',
        isDragging
          ? 'border-[#0A3D62] bg-blue-50/70 scale-[1.01]'
          : 'border-slate-300 hover:border-[#0A3D62] bg-slate-50/60 hover:bg-white hover:shadow-xs'
      ]"
    >
      <input
        ref="fileInputRef"
        type="file"
        accept="image/jpeg,image/png,image/jpg,image/webp"
        class="hidden"
        @change="onFileChange"
      />

      <div v-if="isCompressing" class="flex flex-col items-center gap-2">
        <Loader2 class="w-8 h-8 animate-spin text-[#0A3D62]" />
        <p class="text-xs font-bold text-slate-700">Mengoptimalkan & mengompresi foto...</p>
      </div>

      <template v-else>
        <div class="w-12 h-12 rounded-2xl bg-white shadow-xs border border-slate-200 flex items-center justify-center text-slate-600 group-hover:scale-110 group-hover:bg-[#0A3D62] group-hover:text-white transition-all duration-200">
          <UploadCloud class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-[#0A3D62] transition">
            Klik untuk unggah foto atau seret ke area ini
          </p>
          <p class="text-[11px] text-slate-500 mt-0.5 flex items-center justify-center gap-1">
            <Sparkles class="w-3 h-3 text-amber-500" />
            <span>Mendukung JPG, PNG, WebP (Otomatis dioptimalkan agar cepat terkirim)</span>
          </p>
        </div>
      </template>
    </div>

    <!-- Image Preview Box -->
    <div v-else class="relative rounded-2xl overflow-hidden border border-slate-300 bg-slate-900 group shadow-md">
      <img
        :src="previewUrl"
        alt="Preview foto aduan"
        class="w-full h-52 object-cover opacity-90 group-hover:opacity-100 transition-opacity"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex items-end justify-between p-4">
        <div class="flex items-center gap-2 text-white text-xs">
          <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
          <div class="min-w-0">
            <p class="font-bold truncate max-w-[200px] sm:max-w-xs">{{ fileName }}</p>
            <p class="text-[10px] text-slate-300">{{ fileSize }} • Siap dilampirkan (Teroptimasi)</p>
          </div>
        </div>
        <button
          type="button"
          @click.stop="removePhoto"
          class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold flex items-center gap-1 shadow-md transition cursor-pointer"
        >
          <X class="w-3.5 h-3.5" />
          <span>Ganti</span>
        </button>
      </div>
    </div>
  </div>
</template>
