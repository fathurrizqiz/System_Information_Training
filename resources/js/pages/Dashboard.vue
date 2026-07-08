<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

// === CHART.JS IMPORTS ===
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    PointElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    Filler,
} from 'chart.js';

import { Line, Bar } from 'vue-chartjs';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    PointElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    Filler
);

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

// === TYPES & BREADCRUMBS ===
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

interface JamDiklat { tahun: number; totalJamDiklat: number; }
interface TargetKategori {
    kategori: string; totalKaryawan: number; targetPerOrang: number;
    totalTargetJam: number; aktualJam: number; persentase: number;
}
interface TrenBulanan {
    bulan: number; namaBulan: string; aktualBulan: number;
    targetKumulatif: number; aktualKumulatif: number;
}
interface TrenPerBagian { namaBagian: string; data: number[]; }

const props = defineProps<{
    totalKaryawans: number;
    totalPerKategori: TargetKategori[];
    totalJamDiklat: JamDiklat;
    targetAll: number;
    persentaseOverall: number;
    bulanSekarang: number;
    namaBulan: string;
    expectedProgress: number;
    paceStatus: 'on_track' | 'behind';
    bagianTercapai: number;
    bagianBelumTercapai: number;
    rataRataPencapaian: number;
    topPerformer: TargetKategori | null;
    lowestPerformer: TargetKategori | null;
    trenBulanan: TrenBulanan[];
    trenPerTopBagian: TrenPerBagian[];
    filters: { search?: string; };
}>();

const search = ref(props.filters?.search || '');

function debounce(func: (...args: any[]) => void, wait: number) {
    let timeout: NodeJS.Timeout;
    return function executedFunction(...args: any[]) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

watch(search, debounce((newSearch: string) => {
    router.get(route('dashboard'), { search: newSearch || undefined }, { preserveState: true, replace: true });
}, 300));

const page = usePage();
const isImpersonating = computed(() => page.props.auth?.is_impersonating || false);

// === HELPERS ===
function formatNumber(num: number): string {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

interface StatusInfo {
    label: string; textColor: string; bgColor: string;
    ringColor: string; barColor: string; dotColor: string;
}

function getStatusInfo(persentase: number): StatusInfo {
    if (persentase >= 100) return { label: 'Tercapai', textColor: 'text-emerald-700 dark:text-emerald-300', bgColor: 'bg-emerald-50 dark:bg-emerald-900/30', ringColor: 'stroke-emerald-500', barColor: 'bg-emerald-500', dotColor: 'bg-emerald-500' };
    if (persentase >= 75) return { label: 'Hampir Tercapai', textColor: 'text-blue-700 dark:text-blue-300', bgColor: 'bg-blue-50 dark:bg-blue-900/30', ringColor: 'stroke-blue-500', barColor: 'bg-blue-500', dotColor: 'bg-blue-500' };
    if (persentase >= 50) return { label: 'Perlu Perhatian', textColor: 'text-amber-700 dark:text-amber-300', bgColor: 'bg-amber-50 dark:bg-amber-900/30', ringColor: 'stroke-amber-500', barColor: 'bg-amber-500', dotColor: 'bg-amber-500' };
    return { label: 'Kritis', textColor: 'text-red-700 dark:text-red-300', bgColor: 'bg-red-50 dark:bg-red-900/30', ringColor: 'stroke-red-500', barColor: 'bg-red-500', dotColor: 'bg-red-500' };
}

// Ring Chart Math
const RING_RADIUS = 52;
const RING_CIRCUMFERENCE = 2 * Math.PI * RING_RADIUS;
const overallStatus = computed(() => getStatusInfo(props.persentaseOverall));
const isOnTrack = computed(() => props.paceStatus === 'on_track');
const ringOffset = computed(() => RING_CIRCUMFERENCE * (1 - Math.min(props.persentaseOverall, 100) / 100));

// === CHART CONFIGURATIONS ===
const chartColors = [
    { border: 'rgb(99, 102, 241)', bg: 'rgba(99, 102, 241, 0.1)' },   // Indigo
    { border: 'rgb(16, 185, 129)', bg: 'rgba(16, 185, 129, 0.1)' },   // Emerald
    { border: 'rgb(245, 158, 11)', bg: 'rgba(245, 158, 11, 0.1)' },   // Amber
    { border: 'rgb(239, 68, 68)', bg: 'rgba(239, 68, 68, 0.1)' },     // Red
    { border: 'rgb(139, 92, 246)', bg: 'rgba(139, 92, 246, 0.1)' },   // Violet
];

// 1. Chart Kumulatif (Target vs Aktual RS)
const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index' as const, intersect: false },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(148, 163, 184, 0.1)' },
            ticks: { color: '#94a3b8' }
        },
        x: {
            grid: { display: false },
            ticks: { color: '#94a3b8' }
        }
    },
    plugins: {
        legend: { position: 'top' as const, labels: { color: '#64748b', usePointStyle: true, padding: 20 } },
        tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f8fafc',
            bodyColor: '#cbd5e1',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: function (context: any) {
                    let label = context.dataset.label || '';
                    if (label) { label += ': '; }
                    if (context.parsed.y !== null) { label += formatNumber(context.parsed.y) + ' Jam'; }
                    return label;
                }
            }
        }
    }
};

const lineChartData = computed(() => ({
    labels: props.trenBulanan.map(t => t.namaBulan),
    datasets: [
        {
            label: 'Target Kumulatif',
            data: props.trenBulanan.map(t => t.targetKumulatif),
            borderColor: 'rgb(100, 116, 139)',
            backgroundColor: 'rgba(100, 116, 139, 0.1)',
            borderWidth: 2,
            borderDash: [5, 5], // Garis putus-putus
            pointRadius: 3,
            fill: false,
            tension: 0.3
        },
        {
            label: 'Realisasi Kumulatif',
            data: props.trenBulanan.map(t => t.aktualKumulatif),
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.2)',
            borderWidth: 3,
            pointRadius: 4,
            pointBackgroundColor: 'rgb(99, 102, 241)',
            fill: true,
            tension: 0.3
        }
    ]
}));

// 2. Chart Top 5 Bagian (Per Bulan)
const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(148, 163, 184, 0.1)' },
            ticks: { color: '#94a3b8' }
        },
        x: {
            stacked: true,
            grid: { display: false },
            ticks: { color: '#94a3b8' }
        }
    },
    plugins: {
        legend: { position: 'top' as const, labels: { color: '#64748b', usePointStyle: true, padding: 20 } },
        tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f8fafc',
            bodyColor: '#cbd5e1',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: function (context: any) {
                    let label = context.dataset.label || '';
                    if (label) { label += ': '; }
                    if (context.parsed.y !== null) { label += formatNumber(context.parsed.y) + ' Jam'; }
                    return label;
                }
            }
        }
    }
};

const barChartData = computed(() => ({
    labels: props.trenBulanan.map(t => t.namaBulan),
    datasets: props.trenPerTopBagian.map((bagian, index) => ({
        label: bagian.namaBagian,
        data: bagian.data,
        backgroundColor: chartColors[index % chartColors.length].bg.replace('0.1', '0.7'),
        borderColor: chartColors[index % chartColors.length].border,
        borderWidth: 1,
        borderRadius: 4,
        borderSkipped: false as const,
    }))
}));
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 md:gap-6 md:p-6">
            
            <!-- Impersonation Alert -->
            <div v-if="isImpersonating" class="w-full">
                <!-- (Sama seperti sebelumnya, tidak diubah untuk hemat ruang) -->
                <div class="flex flex-col items-start justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm sm:flex-row sm:items-center dark:border-amber-900/50 dark:bg-amber-900/20">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-amber-900 dark:text-amber-200">Mode Penyamaran Aktif</p>
                            <p class="text-xs text-amber-700 dark:text-amber-400">Anda sedang melihat dashboard sebagai {{ $page.props.auth.user.name }}</p>
                        </div>
                    </div>
                    <Link :href="route('impersonation.leave')" method="post" as="button" class="flex shrink-0 items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-md transition-all hover:bg-amber-700 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" /></svg>
                        Kembali ke Admin
                    </Link>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                <input type="text" v-model="search" placeholder="Cari bagian ..." class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:ring-blue-800" />
            </div>

            <!-- SUMMARY CARDS -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
                <!-- Card 1: Total Karyawan -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">Total Karyawan</p>
                        <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" /></svg>
                        </div>
                    </div>
                    <p class="mt-3 text-2xl font-extrabold text-slate-900 md:text-3xl dark:text-white">{{ formatNumber(totalKaryawans) }}</p>
                    <p class="mt-1 text-xs text-slate-400">orang terdaftar</p>
                </div>
                <!-- Card 2: Total Jam Target -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">Jam Target</p>
                        <div class="rounded-lg bg-violet-50 p-2 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
                    <p class="mt-3 text-2xl font-extrabold text-slate-900 md:text-3xl dark:text-white">{{ formatNumber(Math.round(targetAll)) }}</p>
                    <p class="mt-1 text-xs text-slate-400">jam / tahun</p>
                </div>
                <!-- Card 3: Jam Terlaksana -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">Terlaksana</p>
                        <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
                    <p class="mt-3 text-2xl font-extrabold text-slate-900 md:text-3xl dark:text-white">{{ formatNumber(Math.round(totalJamDiklat.totalJamDiklat)) }}</p>
                    <p class="mt-1 text-xs text-slate-400">jam {{ totalJamDiklat.tahun }}</p>
                </div>
                <!-- Card 4: Pencapaian (Ring Chart) -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">Pencapaian</p>
                        <div class="rounded-lg p-2" :class="overallStatus.bgColor">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="overallStatus.textColor" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="relative shrink-0">
                            <svg viewBox="0 0 120 120" class="h-16 w-16 md:h-20 md:w-20">
                                <circle cx="60" cy="60" :r="RING_RADIUS" fill="none" stroke-width="10" class="stroke-slate-100 dark:stroke-slate-800" />
                                <circle cx="60" cy="60" :r="RING_RADIUS" fill="none" stroke-width="10" stroke-linecap="round" :class="[overallStatus.ringColor, 'transition-all duration-1000 ease-out']" :stroke-dasharray="RING_CIRCUMFERENCE" :stroke-dashoffset="ringOffset" transform="rotate(-90 60 60)" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold md:text-sm" :class="overallStatus.textColor">{{ persentaseOverall }}%</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-lg font-extrabold text-slate-900 md:text-xl dark:text-white">{{ overallStatus.label }}</p>
                            <p class="text-xs text-slate-400">{{ formatNumber(Math.round(totalJamDiklat.totalJamDiklat)) }} / {{ formatNumber(Math.round(targetAll)) }} jam</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PACE & INSIGHTS -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div class="col-span-2 rounded-xl border p-4 shadow-sm sm:col-span-3 lg:col-span-2" :class="isOnTrack ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-900/20' : 'border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-900/20'">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full" :class="isOnTrack ? 'bg-emerald-500' : 'bg-red-500'">
                            <svg v-if="isOnTrack" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold" :class="isOnTrack ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300'">{{ isOnTrack ? 'On Track' : 'Behind Schedule' }}</p>
                            <p class="text-[11px]" :class="isOnTrack ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">Aktual {{ persentaseOverall }}% vs Target {{ namaBulan }} {{ expectedProgress }}%</p>
                        </div>
                    </div>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full" :class="isOnTrack ? 'bg-emerald-200 dark:bg-emerald-800' : 'bg-red-200 dark:bg-red-800'">
                        <div class="h-full rounded-full transition-all duration-700" :class="isOnTrack ? 'bg-emerald-500' : 'bg-red-500'" :style="{ width: Math.min(expectedProgress, 100) + '%' }"></div>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2"><div class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></div><p class="text-xs text-slate-500 dark:text-slate-400">Tercapai</p></div>
                    <p class="mt-2 text-xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ bagianTercapai }}</p><p class="text-[11px] text-slate-400">bagian</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2"><div class="h-2 w-2 shrink-0 rounded-full bg-red-500"></div><p class="text-xs text-slate-500 dark:text-slate-400">Belum Tercapai</p></div>
                    <p class="mt-2 text-xl font-extrabold text-red-600 dark:text-red-400">{{ bagianBelumTercapai }}</p><p class="text-[11px] text-slate-400">bagian</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2"><div class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></div><p class="text-xs text-slate-500 dark:text-slate-400">Rata-rata</p></div>
                    <p class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">{{ rataRataPencapaian }}%</p><p class="text-[11px] text-slate-400">pencapaian</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2"><div class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></div><p class="text-xs text-slate-500 dark:text-slate-400">Terbaik</p></div>
                    <p v-if="topPerformer" class="mt-1 text-sm font-bold text-slate-900 truncate dark:text-white">{{ topPerformer.kategori }}</p>
                    <p v-else class="mt-1 text-sm text-slate-400">—</p>
                    <p v-if="topPerformer" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">{{ topPerformer.persentase }}%</p>
                </div>
            </div>

            <!-- ==================== GRAFIK STATISTIK (BARU) ==================== -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Grafik 1: Tren Kumulatif Rumah Sakit -->
                <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-slate-900 md:text-base dark:text-white">Tren Kumulatif {{ totalJamDiklat.tahun }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Perbandingan Target vs Realisasi jam diklat kumulatif</p>
                    </div>
                    <div class="relative h-64 md:h-72">
                        <Line :data="lineChartData" :options="lineChartOptions" />
                    </div>
                </div>

                <!-- Grafik 2: Konsistensi Top 5 Bagian -->
                <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-slate-900 md:text-base dark:text-white">Konsistensi Top 5 Bagian</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Distribusi jam diklat per bulan pada 5 bagian teratas</p>
                    </div>
                    <div class="relative h-64 md:h-72">
                        <Bar v-if="trenPerTopBagian.length > 0" :data="barChartData" :options="barChartOptions" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
                            Tidak ada data bagian untuk dibandingkan.
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== TABLE SECTION ==================== -->
            <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 md:text-lg dark:text-white">Detail per Bagian</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Tahun {{ totalJamDiklat.tahun }} &middot; Diurutkan dari pencapaian tertinggi</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-400">
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span> ≥100%</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2 rounded-full bg-blue-500"></span> 75-99%</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span> 50-74%</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2 rounded-full bg-red-500"></span> &lt;50%</span>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-medium uppercase tracking-wider text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3.5">#</th>
                                <th class="px-6 py-3.5">Nama Bagian</th>
                                <th class="px-6 py-3.5 text-center">Karyawan</th>
                                <th class="px-6 py-3.5 text-right">Target Jam</th>
                                <th class="px-6 py-3.5 text-right">Aktual Jam</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5">Pencapaian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="(item, index) in totalPerKategori" :key="item.kategori" class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/25">
                                <td class="px-6 py-4 text-slate-400 font-mono text-xs">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-200">{{ item.kategori }}</td>
                                <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-300">{{ item.totalKaryawan }} <span class="text-slate-400">org</span></td>
                                <td class="px-6 py-4 text-right tabular-nums text-slate-600 dark:text-slate-300">{{ formatNumber(Math.round(item.totalTargetJam)) }}</td>
                                <td class="px-6 py-4 text-right font-semibold tabular-nums text-slate-900 dark:text-white">{{ formatNumber(Math.round(item.aktualJam)) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="[getStatusInfo(item.persentase).bgColor, getStatusInfo(item.persentase).textColor]">
                                        {{ getStatusInfo(item.persentase).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative h-2 w-28 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="absolute h-full rounded-full transition-all duration-500" :class="getStatusInfo(item.persentase).barColor" :style="{ width: Math.min(item.persentase, 100) + '%' }"></div>
                                        </div>
                                        <span class="w-14 text-right text-sm font-bold tabular-nums" :class="getStatusInfo(item.persentase).textColor">{{ item.persentase }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="divide-y divide-slate-100 lg:hidden dark:divide-slate-800">
                    <div v-for="(item, index) in totalPerKategori" :key="item.kategori" class="p-5">
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ index + 1 }}</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ item.kategori }}</span>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="[getStatusInfo(item.persentase).bgColor, getStatusInfo(item.persentase).textColor]">
                                {{ getStatusInfo(item.persentase).label }}
                            </span>
                        </div>
                        <div class="mb-3 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-lg bg-slate-50 py-2 dark:bg-slate-800/50">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Karyawan</p>
                                <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white">{{ item.totalKaryawan }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 py-2 dark:bg-slate-800/50">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Target</p>
                                <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white">{{ formatNumber(Math.round(item.totalTargetJam)) }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 py-2 dark:bg-slate-800/50">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Aktual</p>
                                <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white">{{ formatNumber(Math.round(item.aktualJam)) }}</p>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <p class="text-xs text-slate-400">Pencapaian</p>
                                <p class="text-sm font-bold tabular-nums" :class="getStatusInfo(item.persentase).textColor">{{ item.persentase }}%</p>
                            </div>
                            <div class="relative h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="absolute h-full rounded-full transition-all duration-500" :class="getStatusInfo(item.persentase).barColor" :style="{ width: Math.min(item.persentase, 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!totalPerKategori || totalPerKategori.length === 0" class="flex flex-col items-center justify-center gap-3 py-16 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    <p class="text-sm font-medium">Belum ada data pencapaian.</p>
                    <p v-if="search" class="text-xs">Coba ubah kata kunci pencarian Anda.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>