<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps<{
  lat: number;
  lng: number;
  alamat?: string;
}>();

const mapContainer = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;

const createCustomIcon = () => {
  return L.divIcon({
    className: 'custom-map-pin',
    html: `
      <div style="position: relative; width: 28px; height: 36px; display: flex; align-items: center; justify-content: center;">
        <svg style="width: 28px; height: 36px; filter: drop-shadow(0 3px 5px rgba(0,0,0,0.35));" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14 0C6.268 0 0 6.268 0 14C0 23.625 12.6 34.825 13.14 35.305C13.385 35.522 13.692 35.639 14 35.639C14.308 35.639 14.615 35.522 14.86 35.305C15.4 34.825 28 23.625 28 14C28 6.268 21.732 0 14 0Z" fill="#0A3D62"/>
          <circle cx="14" cy="14" r="5.5" fill="#FCD34D"/>
        </svg>
      </div>
    `,
    iconSize: [28, 36],
    iconAnchor: [14, 36],
    popupAnchor: [0, -36],
  });
};

const updateMap = () => {
  if (!mapContainer.value) return;

  if (!map) {
    map = L.map(mapContainer.value, {
      center: [props.lat, props.lng],
      zoom: 15,
      zoomControl: false,
      attributionControl: false,
    });

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
    }).addTo(map);

    marker = L.marker([props.lat, props.lng], {
      icon: createCustomIcon(),
    }).addTo(map);

    if (props.alamat) {
      marker.bindPopup(`<div style="font-size: 11px; font-weight: bold; padding: 2px;">${props.alamat}</div>`);
    }
  } else {
    map.setView([props.lat, props.lng], 15);
    if (marker) {
      marker.setLatLng([props.lat, props.lng]);
      if (props.alamat) {
        marker.setPopupContent(`<div style="font-size: 11px; font-weight: bold; padding: 2px;">${props.alamat}</div>`);
      }
    }
  }
};

onMounted(() => {
  nextTick(() => {
    updateMap();
  });
});

watch(
  () => [props.lat, props.lng],
  () => {
    updateMap();
  }
);
</script>

<template>
  <div class="relative w-full h-36 rounded-xl overflow-hidden border border-slate-200 shadow-inner">
    <div ref="mapContainer" class="w-full h-full z-0"></div>
  </div>
</template>
