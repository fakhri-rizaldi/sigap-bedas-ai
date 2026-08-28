<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.heat';
import { 
  Layers, 
  Flame, 
  MapPin, 
  RefreshCw, 
  AlertCircle, 
  ChevronRight,
  Sparkles,
  Eye
} from '@lucide/vue';
import type { AduanItem } from './TicketCard.vue';

interface HeatmapPoint {
  id: number;
  kode_tiket: string;
  lat: number;
  lng: number;
  weight: number;
  kategori: string;
  urgensi: string;
  status: string;
  alamat: string;
  teks_aduan: string;
  foto_path: string | null;
  nama_pelapor: string | null;
  dinas_nama: string | null;
  dinas_kode: string | null;
  created_at: string;
}

interface Props {
  filters?: {
    status?: string;
    urgensi?: string;
    kategori?: string;
    dinas_id?: string;
    kecamatan?: string;
  };
  selectedAduanId?: number | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'select', aduan: AduanItem): void;
}>();

const mapContainer = ref<HTMLDivElement | null>(null);
const map = ref<L.Map | null>(null);
const heatLayer = ref<any>(null);
const markerGroup = ref<L.LayerGroup | null>(null);

const points = ref<HeatmapPoint[]>([]);
const isLoading = ref<boolean>(true);
const showHeatmap = ref<boolean>(true);
const showMarkers = ref<boolean>(true);

// Soreang, Ibu Kota Kabupaten Bandung
const DEFAULT_CENTER: [number, number] = [-7.0252, 107.5197];
const DEFAULT_ZOOM = 11;

// Warna Token Urgensi Sesuai Styleguide
const getUrgencyColor = (urgensi: string): string => {
  switch (urgensi) {
    case 'Darurat': return '#D64545';
    case 'Tinggi': return '#E9A400';
    case 'Sedang': return '#3373B0';
    default: return '#6C757D';
  }
};

// Buat SVG Custom Pin Marker Leaflet dengan Pin Point Presisi (Bottom Tip Anchor)
const createPinIcon = (urgensi: string) => {
  const color = getUrgencyColor(urgensi);
  const isDarurat = urgensi === 'Darurat';

  const html = `
    <div style="position: relative; width: 28px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
      ${isDarurat ? `<div style="position: absolute; width: 36px; height: 36px; border-radius: 50%; background-color: rgba(214, 69, 69, 0.4); animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; top: 0; left: -4px;"></div>` : ''}
      <svg style="width: 28px; height: 36px; filter: drop-shadow(0 3px 4px rgba(0,0,0,0.35));" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 0C6.268 0 0 6.268 0 14C0 23.625 12.6 34.825 13.14 35.305C13.385 35.522 13.692 35.639 14 35.639C14.308 35.639 14.615 35.522 14.86 35.305C15.4 34.825 28 23.625 28 14C28 6.268 21.732 0 14 0Z" fill="${color}"/>
        <circle cx="14" cy="14" r="5.5" fill="#FFFFFF"/>
      </svg>
    </div>
  `;

  return L.divIcon({
    html,
    className: 'custom-aduan-pin',
    iconSize: [28, 36],
    iconAnchor: [14, 36], // Titik jarum paling bawah tepat di (14, 36)
    popupAnchor: [0, -36],
  });
};

// Ambil Data Titik & Heatmap dari API
const fetchData = async () => {
  isLoading.value = true;
  try {
    const params: Record<string, string> = {};
    if (props.filters?.status && props.filters.status !== 'all') params.status = props.filters.status;
    if (props.filters?.urgensi && props.filters.urgensi !== 'all') params.urgensi = props.filters.urgensi;
    if (props.filters?.kategori && props.filters.kategori !== 'all') params.kategori = props.filters.kategori;
    if (props.filters?.dinas_id && props.filters.dinas_id !== 'all') params.dinas_id = props.filters.dinas_id;
    if (props.filters?.kecamatan && props.filters.kecamatan !== 'all') params.kecamatan = props.filters.kecamatan;

    const res = await axios.get('/api/aduan/heatmap-data', { params });
    if (res.data.status === 'success') {
      points.value = res.data.data;
      renderMapLayers();
    }
  } catch (err) {
    console.error('Gagal memuat data heatmap aduan:', err);
  } finally {
    isLoading.value = false;
  }
};

// Render Layer Heatmap & Marker ke Peta
const renderMapLayers = () => {
  if (!map.value) return;

  // 1. Bersihkan Layer Sebelumnya
  if (heatLayer.value) {
    map.value.removeLayer(heatLayer.value);
    heatLayer.value = null;
  }
  if (markerGroup.value) {
    markerGroup.value.clearLayers();
  } else {
    markerGroup.value = L.layerGroup().addTo(map.value);
  }

  // 2. Render Heatmap Density Layer
  if (showHeatmap.value && points.value.length > 0) {
    const heatData = points.value.map(p => [p.lat, p.lng, p.weight]);
    
    // @ts-ignore (L.heatLayer extension)
    heatLayer.value = (L as any).heatLayer(heatData, {
      radius: 28,
      blur: 18,
      maxZoom: 16,
      max: 1.0,
      gradient: {
        0.2: '#3373B0', // Biru (Rendah)
        0.4: '#20BF6B', // Hijau (Sedang)
        0.7: '#E9A400', // Amber (Tinggi)
        1.0: '#D64545', // Merah (Darurat)
      }
    }).addTo(map.value);
  }

  // 3. Render Interactive Marker Pins
  if (showMarkers.value && markerGroup.value) {
    points.value.forEach(p => {
      const marker = L.marker([p.lat, p.lng], {
        icon: createPinIcon(p.urgensi)
      });

      // Konten Popup Interaktif
      const popupDiv = document.createElement('div');
      popupDiv.className = 'p-1 text-slate-800 space-y-2 min-w-[220px] font-sans';
      popupDiv.innerHTML = `
        <div class="flex items-center justify-between border-b border-slate-200 pb-1.5 gap-2">
          <span class="font-mono font-bold text-xs text-[#0A3D62]">#${p.kode_tiket}</span>
          <span class="text-[10px] px-1.5 py-0.5 rounded font-extrabold uppercase ${
            p.status === 'baru' ? 'bg-amber-100 text-amber-800' :
            p.status === 'diproses' ? 'bg-blue-100 text-blue-800' :
            p.status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'
          }">${p.status}</span>
        </div>
        <div>
          <div class="font-bold text-xs text-slate-900">${p.kategori}</div>
          <div class="text-[11px] text-slate-600 line-clamp-2 mt-0.5">${p.teks_aduan}</div>
        </div>
        <div class="text-[10px] text-slate-500 flex items-center gap-1">
          <span>📍 ${p.alamat}</span>
        </div>
        <button id="btn-inspect-${p.id}" class="w-full mt-1.5 py-1 px-2.5 rounded-lg bg-[#0A3D62] hover:bg-[#062A45] text-white text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer">
          <span>Buka di Inspector</span>
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      `;

      // Event listener tombol Inspector dalam popup
      marker.bindPopup(popupDiv);
      marker.on('popupopen', () => {
        const btn = document.getElementById(`btn-inspect-${p.id}`);
        if (btn) {
          btn.onclick = () => {
            emit('select', {
              id: p.id,
              kode_tiket: p.kode_tiket,
              teks_aduan: p.teks_aduan,
              kategori: p.kategori,
              urgensi: p.urgensi,
              status: p.status,
              alamat: p.alamat,
              latitude: p.lat,
              longitude: p.lng,
              foto_path: p.foto_path,
              nama_pelapor: p.nama_pelapor,
              dinas: p.dinas_nama ? { id: 0, nama_dinas: p.dinas_nama, kode_dinas: p.dinas_kode || '' } : null,
              created_at: p.created_at,
            });
            map.value?.closePopup();
          };
        }
      });

      markerGroup.value?.addLayer(marker);
    });
  }
};

// Inisialisasi Peta
onMounted(() => {
  nextTick(() => {
    if (!mapContainer.value) return;

    map.value = L.map(mapContainer.value, {
      zoomControl: false,
      zoomAnimation: true,
    }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);

    // Basemap OpenStreetMap Resmi (Clean, Cepat & Bebas Watermark)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19,
    }).addTo(map.value);

    // Pindahkan Zoom Control ke Pojok Kanan Bawah
    L.control.zoom({ position: 'bottomright' }).addTo(map.value);

    // Event saat zoom / pan selesai untuk menyegarkan ukuran render
    map.value.on('zoomend', () => {
      map.value?.invalidateSize();
    });

    // Pastikan ukuran canvas peta terkalibrasi setelah mount
    setTimeout(() => {
      map.value?.invalidateSize();
    }, 250);

    window.addEventListener('resize', handleResize);

    fetchData();
  });
});

const handleResize = () => {
  map.value?.invalidateSize();
};

// Watcher Filters
watch(
  () => props.filters,
  () => {
    fetchData();
  },
  { deep: true }
);

// Toggle Layer Watcher
watch([showHeatmap, showMarkers], () => {
  renderMapLayers();
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
  if (map.value) {
    map.value.remove();
    map.value = null;
  }
});
</script>

<template>
  <div class="relative w-full h-full bg-slate-100 flex flex-col rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
    
    <!-- Top Control Bar (Peta & Layer Toggle) -->
    <div class="absolute top-4 left-4 z-[1000] flex flex-wrap items-center gap-2">
      
      <!-- Layer Toggle Controls -->
      <div class="bg-white/95 backdrop-blur-md px-3 py-2 rounded-xl shadow-md border border-slate-200 flex items-center gap-2 text-xs">
        <span class="font-bold text-slate-700 flex items-center gap-1.5">
          <Layers class="w-3.5 h-3.5 text-[#0A3D62]" />
          <span>Layer Peta:</span>
        </span>

        <!-- Heatmap Toggle -->
        <button
          type="button"
          @click="showHeatmap = !showHeatmap"
          class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1.5 cursor-pointer"
          :class="showHeatmap ? 'bg-orange-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          <Flame class="w-3.5 h-3.5" />
          <span>Heatmap Kepadatan</span>
        </button>

        <!-- Markers Pin Toggle -->
        <button
          type="button"
          @click="showMarkers = !showMarkers"
          class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1.5 cursor-pointer"
          :class="showMarkers ? 'bg-[#0A3D62] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          <MapPin class="w-3.5 h-3.5" />
          <span>Pin Lokasi ({{ points.length }})</span>
        </button>

        <!-- Refresh Button -->
        <button
          type="button"
          @click="fetchData"
          class="p-1.5 rounded-lg text-slate-500 hover:text-[#0A3D62] hover:bg-slate-100 transition cursor-pointer"
          title="Muat Ulang Titik"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
        </button>
      </div>

    </div>

    <!-- Bottom Left Legend Card -->
    <div class="absolute bottom-6 left-4 z-[1000] bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-md border border-slate-200 text-xs space-y-2 max-w-xs pointer-events-auto">
      <div class="font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center justify-between">
        <span>Legenda Tingkat Urgensi</span>
        <span class="text-[10px] text-slate-500 font-normal">{{ points.length }} Titik Aktif</span>
      </div>

      <!-- Urgency Colors -->
      <div class="grid grid-cols-2 gap-1.5 text-[11px]">
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-[#D64545]"></span>
          <span class="text-slate-700 font-medium">Darurat (Tinggi)</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-[#E9A400]"></span>
          <span class="text-slate-700 font-medium">Tinggi (Perhatian)</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-[#3373B0]"></span>
          <span class="text-slate-700 font-medium">Sedang (Rutin)</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-[#6C757D]"></span>
          <span class="text-slate-700 font-medium">Rendah</span>
        </div>
      </div>

      <!-- Heatmap Density Bar -->
      <div v-if="showHeatmap" class="pt-1.5 border-t border-slate-200 space-y-1">
        <div class="flex justify-between text-[10px] text-slate-500 font-medium">
          <span>Kepadatan Rendah</span>
          <span>Sangat Padat (Hotspot)</span>
        </div>
        <div class="h-2 rounded-full w-full bg-gradient-to-r from-blue-500 via-emerald-400 via-amber-400 to-red-600"></div>
      </div>
    </div>

    <!-- Map Container -->
    <div ref="mapContainer" class="w-full h-full z-0"></div>

    <!-- Loading Spinner Overlay -->
    <div
      v-if="isLoading"
      class="absolute inset-0 bg-white/40 backdrop-blur-xs flex items-center justify-center z-[1001]"
    >
      <div class="bg-white px-4 py-2.5 rounded-xl shadow-lg border border-slate-200 flex items-center gap-2.5 text-xs font-bold text-slate-800">
        <RefreshCw class="w-4 h-4 animate-spin text-[#0A3D62]" />
        <span>Memuat data sebaran wilayah...</span>
      </div>
    </div>

  </div>
</template>

<style>
/* Styling Custom Pin Marker */
.custom-aduan-pin {
  background: transparent;
  border: none;
}
.leaflet-popup-content-wrapper {
  border-radius: 1rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  padding: 0.25rem;
}
.leaflet-popup-content {
  margin: 0.5rem;
  line-height: 1.4;
}
</style>
