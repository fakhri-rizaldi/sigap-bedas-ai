<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { MapPin, Navigation, Loader2, Search, Check, Building2, Landmark, X, ChevronDown, AlertTriangle } from '@lucide/vue';
import { useDebounceFn, onClickOutside } from '@vueuse/core';
import axios from 'axios';
import L from 'leaflet';

interface SearchResult {
  display_name: string;
  lat: number;
  lng: number;
  jalan?: string | null;
  desa_kelurahan?: string | null;
  kecamatan?: string | null;
}

interface KecamatanItem {
  nama: string;
  lat: number;
  lng: number;
  total_desa: number;
}

const props = defineProps<{
  initialLat?: number | null;
  initialLng?: number | null;
  initialAddress?: string | null;
}>();

const emit = defineEmits<{
  (e: 'location-changed', payload: { lat: number; lng: number; address: string; isValid: boolean }): void;
}>();

const mapContainer = ref<HTMLDivElement | null>(null);
const searchDropdownRef = ref<HTMLDivElement | null>(null);

const currentLat = ref<number>(props.initialLat ?? -7.0252); // Soreang default
const currentLng = ref<number>(props.initialLng ?? 107.5197);
const addressText = ref<string>(props.initialAddress ?? '');
const detailJalan = ref<string>('');
const reverseGeocodeValid = ref<boolean>(true);

// Batasan Wilayah Koordinat Khusus Kabupaten Bandung (Tolak Kota Bandung & Cimahi)
const isLocationInBandung = computed(() => {
  if (currentLat.value === null || currentLng.value === null) return false;
  
  // 1. Batas luar Kabupaten Bandung
  const inOuterBounds = (
    currentLat.value >= -7.3500 &&
    currentLat.value <= -6.7800 &&
    currentLng.value >= 107.2500 &&
    currentLng.value <= 107.9500
  );

  if (!inOuterBounds) return false;

  // 2. Cek validitas dari server OpenStreetMap reverse geocode
  if (!reverseGeocodeValid.value) return false;

  // 3. Enklave Kota Bandung (Alun-alun, Gedung Sate, Dago, Sukajadi, dll)
  const isInsideKotaBandung = (
    currentLat.value >= -6.9600 && 
    currentLat.value <= -6.8650 && 
    currentLng.value >= 107.5650 && 
    currentLng.value <= 107.6950
  );

  // 4. Enklave Kota Cimahi
  const isInsideKotaCimahi = (
    currentLat.value >= -6.9150 && 
    currentLat.value <= -6.8650 && 
    currentLng.value >= 107.5150 && 
    currentLng.value <= 107.5600
  );

  if ((isInsideKotaBandung || isInsideKotaCimahi) && !selectedKecamatan.value) {
    return false;
  }

  return true;
});

// Dropdown Wilayah State
const listKecamatan = ref<KecamatanItem[]>([]);
const listDesa = ref<string[]>([]);
const selectedKecamatan = ref<string>('');
const selectedDesa = ref<string>('');
const isLoadingWilayah = ref<boolean>(false);

// Geocoding State
const isGeocoding = ref<boolean>(false);
const isSearching = ref<boolean>(false);
const isLocating = ref<boolean>(false);
const gpsAccuracy = ref<number | null>(null);

const searchResults = ref<SearchResult[]>([]);
const isDropdownOpen = ref<boolean>(false);

let map: L.Map | null = null;
let marker: L.Marker | null = null;

// Tutup dropdown jika klik di luar
onClickOutside(searchDropdownRef, () => {
  isDropdownOpen.value = false;
});

// Custom Marker Icon Leaflet dengan Pin Point Presisi (Bottom Needle Anchor)
const createCustomIcon = () => {
  return L.divIcon({
    className: 'custom-map-pin',
    html: `
      <div style="position: relative; width: 32px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: grab;">
        <svg style="width: 32px; height: 42px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4));" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14 0C6.268 0 0 6.268 0 14C0 23.625 12.6 34.825 13.14 35.305C13.385 35.522 13.692 35.639 14 35.639C14.308 35.639 14.615 35.522 14.86 35.305C15.4 34.825 28 23.625 28 14C28 6.268 21.732 0 14 0Z" fill="#0A3D62"/>
          <circle cx="14" cy="14" r="5.5" fill="#FCD34D"/>
        </svg>
      </div>
    `,
    iconSize: [32, 42],
    iconAnchor: [16, 42], // Tepat di ujung jarum paling bawah
    popupAnchor: [0, -42],
  });
};

// Ambil data 31 Kecamatan Kab. Bandung
const loadKecamatan = async () => {
  try {
    const res = await axios.get('/api/wilayah/kecamatan');
    if (res.data?.status === 'success') {
      listKecamatan.value = res.data.data;
    }
  } catch (err) {
    console.warn('Gagal memuat data kecamatan:', err);
  }
};

// Handler saat Kecamatan dipilih
const onKecamatanChange = async () => {
  selectedDesa.value = '';
  listDesa.value = [];

  if (!selectedKecamatan.value) return;

  const kec = listKecamatan.value.find(k => k.nama === selectedKecamatan.value);
  if (kec) {
    updatePosition(kec.lat, kec.lng, false);
    if (map) {
      map.flyTo([kec.lat, kec.lng], 14, { duration: 1.0 });
    }
  }

  isLoadingWilayah.value = true;
  try {
    const res = await axios.get('/api/wilayah/desa', {
      params: { kecamatan: selectedKecamatan.value }
    });
    if (res.data?.status === 'success') {
      listDesa.value = res.data.data;
    }
  } catch (err) {
    console.warn('Gagal memuat desa:', err);
  } finally {
    isLoadingWilayah.value = false;
  }

  buildFormattedAddress();
};

// Handler saat Desa dipilih
const onDesaChange = () => {
  buildFormattedAddress();
};

// Gabungkan teks alamat terstruktur
const buildFormattedAddress = () => {
  const parts = [];
  if (detailJalan.value.trim()) {
    parts.push(detailJalan.value.trim());
  }
  if (selectedDesa.value) {
    parts.push(`Desa/Kel. ${selectedDesa.value}`);
  }
  if (selectedKecamatan.value) {
    parts.push(`Kec. ${selectedKecamatan.value}`);
  }
  parts.push('Kabupaten Bandung');

  addressText.value = parts.join(', ');

  emit('location-changed', {
    lat: currentLat.value,
    lng: currentLng.value,
    address: addressText.value,
    isValid: isLocationInBandung.value,
  });
};

// Reverse Geocoding (Koordinat -> Alamat Teks)
const fetchAddress = async (lat: number, lng: number) => {
  isGeocoding.value = true;
  try {
    const response = await axios.get('/api/geocode', {
      params: { lat, lng },
    });
    if (response.data?.status === 'success' && response.data?.data) {
      const data = response.data.data;
      const addr = data.alamat_lengkap || `Koordinat: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
      addressText.value = addr;
      
      // Validasi dari server apakah titik berada di Kabupaten Bandung
      reverseGeocodeValid.value = data.is_kabupaten_bandung !== false;

      // Sinkronkan dropdown jika terdeteksi kecamatan resmi Kab. Bandung
      if (data.is_kabupaten_bandung && data.kecamatan) {
        const cleanKec = data.kecamatan.replace(/^Kecamatan\s+/i, '').trim();
        const found = listKecamatan.value.find(k => k.nama.toLowerCase() === cleanKec.toLowerCase());
        if (found && selectedKecamatan.value !== found.nama) {
          selectedKecamatan.value = found.nama;
          await onKecamatanChange();
        }
      }

      emit('location-changed', { 
        lat, 
        lng, 
        address: addr, 
        isValid: isLocationInBandung.value 
      });
    }
  } catch (error) {
    console.error('Gagal reverse geocoding:', error);
    const fallbackAddr = `Koordinat: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    addressText.value = fallbackAddr;
    emit('location-changed', { 
      lat, 
      lng, 
      address: fallbackAddr, 
      isValid: isLocationInBandung.value 
    });
  } finally {
    isGeocoding.value = false;
  }
};

// Forward Geocoding Search (Ketik Alamat -> Dropdown Rekomendasi)
const searchAddresses = useDebounceFn(async (query: string) => {
  const clean = query.trim();
  if (clean.length < 2) {
    searchResults.value = [];
    isDropdownOpen.value = false;
    return;
  }

  isSearching.value = true;
  try {
    const response = await axios.get('/api/geocode/search', {
      params: { q: clean },
    });
    if (response.data?.status === 'success' && Array.isArray(response.data?.data)) {
      searchResults.value = response.data.data;
      isDropdownOpen.value = searchResults.value.length > 0;
    }
  } catch (error) {
    console.warn('Gagal mencari rekomendasi alamat:', error);
  } finally {
    isSearching.value = false;
  }
}, 300);

const onAddressInput = () => {
  emit('location-changed', {
    lat: currentLat.value,
    lng: currentLng.value,
    address: addressText.value,
    isValid: isLocationInBandung.value,
  });
  searchAddresses(addressText.value);
};

const selectSearchResult = (item: SearchResult) => {
  addressText.value = item.display_name;
  currentLat.value = item.lat;
  currentLng.value = item.lng;
  isDropdownOpen.value = false;

  if (marker) {
    marker.setLatLng([item.lat, item.lng]);
  }

  if (map) {
    map.flyTo([item.lat, item.lng], 16, { duration: 1.2 });
  }

  emit('location-changed', {
    lat: item.lat,
    lng: item.lng,
    address: item.display_name,
    isValid: isLocationInBandung.value,
  });
};

const updatePosition = (lat: number, lng: number, shouldGeocode = true) => {
  currentLat.value = lat;
  currentLng.value = lng;

  if (marker) {
    marker.setLatLng([lat, lng]);
  }

  if (map) {
    map.panTo([lat, lng]);
  }

  if (shouldGeocode) {
    fetchAddress(lat, lng);
  } else {
    emit('location-changed', {
      lat,
      lng,
      address: addressText.value,
      isValid: isLocationInBandung.value,
    });
  }
};

const locateUser = () => {
  if (!navigator.geolocation) {
    alert('Browser Anda tidak mendukung deteksi lokasi (Geolocation).');
    return;
  }

  isLocating.value = true;
  gpsAccuracy.value = null;

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const accuracy = Math.round(position.coords.accuracy);
      gpsAccuracy.value = accuracy;

      updatePosition(lat, lng, true);
      if (map) {
        map.setView([lat, lng], accuracy > 1000 ? 14 : 16);
      }
      isLocating.value = false;
    },
    (err) => {
      console.warn('Geolocation error:', err.message);
      alert('Gagal mendapatkan lokasi GPS. Silakan gunakan pilihan Kecamatan atau geser pin langsung di peta.');
      isLocating.value = false;
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
};

onMounted(async () => {
  await loadKecamatan();

  if (!mapContainer.value) return;

  // Inisialisasi Map
  map = L.map(mapContainer.value, {
    center: [currentLat.value, currentLng.value],
    zoom: 13,
    scrollWheelZoom: false,
    maxBounds: [[-7.60, 107.00], [-6.60, 108.20]],
    maxBoundsViscosity: 0.8,
  });

  // Tile layer OSM
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19,
  }).addTo(map);

  // Marker Pin Draggable
  marker = L.marker([currentLat.value, currentLng.value], {
    draggable: true,
    icon: createCustomIcon(),
  }).addTo(map);

  marker.on('dragend', () => {
    if (!marker) return;
    const position = marker.getLatLng();
    updatePosition(position.lat, position.lng, true);
  });

  map.on('click', (e: L.LeafletMouseEvent) => {
    updatePosition(e.latlng.lat, e.latlng.lng, true);
  });

  // Initial geocode jika belum ada address
  if (!addressText.value) {
    fetchAddress(currentLat.value, currentLng.value);
  }
});

onUnmounted(() => {
  if (map) {
    map.remove();
    map = null;
  }
});
</script>

<template>
  <div class="space-y-4">
    <!-- Header Label & Tombol GPS -->
    <div class="space-y-1.5">
      <div class="flex items-center justify-between">
        <div>
          <label class="block text-sm font-bold text-slate-900">
            Titik Lokasi Aduan (Kabupaten Bandung) <span class="text-red-500">*</span>
          </label>
          <p class="text-[11px] text-slate-500">Pilih wilayah resmi atau gunakan GPS / geser pin peta</p>
        </div>

        <button
          type="button"
          @click="locateUser"
          :disabled="isLocating"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-[#0A3D62] bg-blue-50 hover:bg-blue-100 border border-blue-200 shadow-xs transition disabled:opacity-50 cursor-pointer"
        >
          <Loader2 v-if="isLocating" class="w-3.5 h-3.5 animate-spin" />
          <Navigation v-else class="w-3.5 h-3.5 text-[#0A3D62]" />
          <span>{{ isLocating ? 'Mencari GPS...' : 'Deteksi GPS' }}</span>
        </button>
      </div>

      <!-- Info Akurasi GPS -->
      <div v-if="gpsAccuracy !== null" class="text-[11px] p-2 rounded-lg border transition-all" :class="gpsAccuracy > 1000 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200'">
        <span v-if="gpsAccuracy > 1000">
          ⚠️ <strong>Akurasi Desktop/WiFi (~{{ (gpsAccuracy / 1000).toFixed(1) }} km):</strong> Lokasi terdeteksi dari jaringan internet browser. Silakan pilih Kecamatan/Desa atau geser pin di peta untuk titik presisi.
        </span>
        <span v-else>
          ✅ <strong>Lokasi Terdeteksi Presisi</strong> (Akurasi satelit GPS: ±{{ gpsAccuracy }} meter).
        </span>
      </div>

      <!-- Warning jika Titik di Luar Wilayah Kabupaten Bandung -->
      <div v-if="!isLocationInBandung" class="text-xs p-3 rounded-xl bg-red-50 text-red-800 border border-red-200 flex items-start gap-2 animate-shake">
        <AlertTriangle class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
        <div>
          <p class="font-bold text-red-900">Lokasi di Luar Wilayah Kabupaten Bandung</p>
          <p class="text-[11px] text-red-700">Layanan SIGAP hanya melayani aduan dalam wilayah administratif Kabupaten Bandung. Silakan pilih Kecamatan / Desa di bawah atau pindahkan pin ke dalam peta Kab. Bandung.</p>
        </div>
      </div>
    </div>

    <!-- 1. FILTER WILAYAH RESMI (Dropdown Kecamatan & Desa/Kelurahan) -->
    <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-200 space-y-3">
      <div class="flex items-center gap-1.5 text-xs font-bold text-[#0A3D62]">
        <Landmark class="w-3.5 h-3.5 text-[#0A3D62]" />
        <span>Pilih Wilayah Administrasi Kabupaten Bandung:</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- Dropdown Kecamatan -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-700 mb-1">
            Kecamatan <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <select
              v-model="selectedKecamatan"
              @change="onKecamatanChange"
              class="w-full pl-3 pr-8 py-2 text-xs sm:text-sm bg-white rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-2 focus:ring-[#0A3D62]/15 text-slate-800 font-medium cursor-pointer"
            >
              <option value="">-- Pilih Kecamatan (31 Wilayah) --</option>
              <option v-for="kec in listKecamatan" :key="kec.nama" :value="kec.nama">
                Kecamatan {{ kec.nama }} ({{ kec.total_desa }} Desa)
              </option>
            </select>
          </div>
        </div>

        <!-- Dropdown Desa / Kelurahan -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-700 mb-1">
            Desa / Kelurahan
          </label>
          <div class="relative">
            <select
              v-model="selectedDesa"
              @change="onDesaChange"
              :disabled="!selectedKecamatan || isLoadingWilayah"
              class="w-full pl-3 pr-8 py-2 text-xs sm:text-sm bg-white rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-2 focus:ring-[#0A3D62]/15 text-slate-800 font-medium disabled:bg-slate-100 disabled:text-slate-400 cursor-pointer"
            >
              <option value="">
                {{ !selectedKecamatan ? '-- Pilih Kecamatan Terlebih Dahulu --' : (isLoadingWilayah ? 'Memuat desa...' : '-- Pilih Desa / Kelurahan --') }}
              </option>
              <option v-for="desa in listDesa" :key="desa" :value="desa">
                Desa / Kel. {{ desa }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Peta Leaflet Container -->
    <div class="relative rounded-xl overflow-hidden border border-slate-300 shadow-inner" :class="{ 'ring-2 ring-red-500 border-red-500': !isLocationInBandung }">
      <div ref="mapContainer" class="w-full h-56 z-10"></div>
      <div class="absolute bottom-2 left-2 z-20 bg-white/95 backdrop-blur-xs px-2.5 py-1 rounded-md text-[11px] text-slate-700 border border-slate-200 shadow-xs flex items-center gap-1.5 font-medium">
        <MapPin class="w-3.5 h-3.5 text-[#0A3D62]" />
        <span>Geser pin atau klik di peta untuk titik presisi</span>
      </div>
    </div>

    <!-- 3. Input Alamat / Patokan Lengkap dengan Autocomplete Dropdown -->
    <div ref="searchDropdownRef" class="relative space-y-1">
      <label class="block text-xs font-semibold text-slate-700">
        Rincian Alamat / Nama Jalan / Patokan Lokasi <span class="text-slate-400 font-normal">(Bisa diedit manual)</span>
      </label>

      <div class="relative">
        <input
          v-model="addressText"
          type="text"
          placeholder="Contoh: Jl. Raya Soreang No. 45 RT 02 RW 03, dekat jembatan..."
          class="w-full pl-9 pr-9 py-2.5 text-xs sm:text-sm bg-white rounded-lg border border-slate-300 focus:outline-hidden focus:border-[#0A3D62] focus:ring-3 focus:ring-[#0A3D62]/15 text-slate-800 transition"
          @input="onAddressInput"
          @focus="isDropdownOpen = searchResults.length > 0"
        />
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
        
        <!-- Loading Spinner -->
        <Loader2 v-if="isGeocoding || isSearching" class="w-4 h-4 text-[#0A3D62] animate-spin absolute right-3 top-3" />
        
        <!-- Clear Button -->
        <button
          v-else-if="addressText"
          type="button"
          @click="addressText = ''; isDropdownOpen = false;"
          class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Autocomplete Dropdown Menu -->
      <div
        v-if="isDropdownOpen && searchResults.length > 0"
        class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 max-h-56 overflow-y-auto animate-in fade-in slide-in-from-top-2 duration-150"
      >
        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 flex items-center justify-between">
          <span>Rekomendasi Lokasi Terdeteksi</span>
          <span>Kab. Bandung</span>
        </div>

        <button
          v-for="(result, idx) in searchResults"
          :key="idx"
          type="button"
          @click="selectSearchResult(result)"
          class="w-full px-3.5 py-2.5 text-left text-xs hover:bg-blue-50/80 border-b border-slate-100 last:border-0 flex items-start gap-2.5 transition group cursor-pointer"
        >
          <div class="w-6 h-6 rounded-md bg-[#0A3D62]/10 text-[#0A3D62] flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-[#0A3D62] group-hover:text-white transition">
            <MapPin class="w-3.5 h-3.5" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
              <p class="font-bold text-slate-800 truncate">
                {{ result.jalan || result.display_name.split(',')[0] }}
              </p>
              <span v-if="result.desa_kelurahan" class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-medium shrink-0">
                {{ result.desa_kelurahan }}
              </span>
            </div>
            <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
              {{ result.display_name }}
            </p>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<style>
/* Leaflet map container fix */
.leaflet-container {
  font-family: inherit !important;
}
</style>
