<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShieldAlert } from '@lucide/vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[#F4F6F8] p-6 md:p-10 font-sans text-[#1B2733]"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col items-center gap-3">
                    <Link
                        href="/lapor"
                        class="flex flex-col items-center gap-2 group"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0A3D62] text-amber-300 shadow-md transition group-hover:scale-105"
                        >
                            <ShieldAlert class="size-7" />
                        </div>
                        <div class="text-center">
                            <span class="font-extrabold text-base text-[#0A3D62] tracking-tight">SIGAP Kab. Bandung</span>
                            <p class="text-[11px] text-slate-500">Portal Layanan Pengaduan Resmi</p>
                        </div>
                    </Link>
                    <div class="space-y-1 text-center mt-2">
                        <h1 class="text-xl font-extrabold text-slate-900 font-heading">{{ title }}</h1>
                        <p class="text-center text-xs text-slate-500 max-w-xs">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
