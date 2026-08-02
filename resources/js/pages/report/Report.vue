<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Laporan Diklat', href: 'laporan.diklat' },
];

// --- INTERFACES (Tetap sama) ---
interface Filters { months: number[]; year: number; bagian: string; }
interface TargetKategori { kategori: string; totalKaryawan: number; targetPerOrang: number; totalTargetJam: number; aktualJam: number; persentase: number; unitKerjas?: UnitKerja[]; karyawans?: KaryawanDetail[]; }
interface DetailRiwayat { id: number; tanggal: string; nama_diklat: string; jam: number; }
interface KaryawanDetail { nrp: string; nama: string; aktual: number; target: number; persentase: number; detail_diklat?: DetailRiwayat[]; }
interface UnitKerja { unitKerja: string; karyawans: KaryawanDetail[]; }

const props = defineProps<{ filters: Filters; totalPerKategori: TargetKategori[]; totalJamDiklat: number; targetAll: number; }>();

const laporanPerBagian = computed(() => props.totalPerKategori.map((bagian) => {
    const unitKerjas = Array.isArray(bagian.unitKerjas)
        ? bagian.unitKerjas.filter((unit): unit is UnitKerja => Array.isArray(unit?.karyawans))
        : [];

    return {
        ...bagian,
        // Kompatibilitas data lama: Bagian -> Karyawan.
        unitKerjas: unitKerjas.length > 0
            ? unitKerjas
            : Array.isArray(bagian.karyawans)
                ? [{ unitKerja: 'Tanpa Unit Kerja', karyawans: bagian.karyawans }]
                : [],
    };
}));

// --- STATE & LOGIC ---
const listBulan = [
    { id: 1, nama: 'Jan' }, { id: 2, nama: 'Feb' }, { id: 3, nama: 'Mar' },
    { id: 4, nama: 'Apr' }, { id: 5, nama: 'Mei' }, { id: 6, nama: 'Jun' },
    { id: 7, nama: 'Jul' }, { id: 8, nama: 'Agu' }, { id: 9, nama: 'Sep' },
    { id: 10, nama: 'Okt' }, { id: 11, nama: 'Nov' }, { id: 12, nama: 'Des' },
];

const selectedMonths = ref<number[]>(props.filters.months);
const searchBagian = ref<string>(props.filters.bagian || '');
const expandedRows = ref<string[]>([]);
const expandedUnitKerjas = ref<string[]>([]);
const expandedKaryawan = ref<string | null>(null);

const applyFilter = () => {
    if (selectedMonths.value.length === 0) return alert('Pilih minimal 1 bulan!');
    router.get(route('laporan.diklat'), { months: selectedMonths.value, bagian: searchBagian.value }, { preserveState: true, preserveScroll: true });
};

const teksPeriode = computed(() => {
    if (selectedMonths.value.length === 0) return 'Semua Periode';
    return `${selectedMonths.value.sort((a, b) => a - b).map((m) => listBulan.find((b) => b.id === m)?.nama).join(' - ')} ${props.filters.year}`;
});

const persentaseTotal = computed(() => props.targetAll <= 0 ? 0 : ((props.totalJamDiklat / props.targetAll) * 100).toFixed(1));

function generateExcel() {
    window.location.href = route('laporan.diklat.export', { months: props.filters.months });
}

// --- MODERN CHART OPTIONS ---
const chartStatusSeries = computed(() => {
    let tuntas = 0, belum = 0;
    laporanPerBagian.value.forEach(kat => kat.unitKerjas.forEach(unit => unit.karyawans.forEach(k => k.persentase >= 100 ? tuntas++ : belum++)));
    return [tuntas, belum];
});

const chartStatusOptions = computed(() => ({
    chart: { type: 'donut', fontFamily: 'inherit', toolbar: { show: false } },
    labels: ['Tuntas', 'Belum Tuntas'],
    colors: ['#10B981', '#F59E0B'],
    stroke: { show: false },
    plotOptions: { donut: { size: '75%', labels: { show: true, total: { show: true, label: 'Karyawan', formatter: () => laporanPerBagian.value.reduce((acc, curr) => acc + curr.totalKaryawan, 0) } } } },
    dataLabels: { enabled: false },
    legend: { show: false }
}));

const chartRankingSeries = computed(() => [{ name: 'Jam', data: laporanPerBagian.value.map(item => item.aktualJam) }]);
const chartRankingOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'inherit' },
    plotOptions: { bar: { horizontal: true, borderRadius: 8, barHeight: '60%' } },
    xaxis: { categories: laporanPerBagian.value.map(i => i.kategori), axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: { labels: { style: { fontWeight: 600, fontSize: '12px' } } },
    grid: { show: false },
    colors: ['#6366F1'],
    dataLabels: { enabled: true, formatter: (val: number) => val + 'j', style: { fontSize: '11px', fontWeight: 600 } }
}));

const toggleRow = (kategori: string) => {
    expandedRows.value.includes(kategori) ? expandedRows.value = expandedRows.value.filter(i => i !== kategori) : expandedRows.value.push(kategori);
};
const unitKerjaKey = (kategori: string, unitKerja: string) => `${kategori}::${unitKerja}`;
const toggleUnitKerja = (kategori: string, unitKerja: string) => {
    const key = unitKerjaKey(kategori, unitKerja);
    expandedUnitKerjas.value.includes(key) ? expandedUnitKerjas.value = expandedUnitKerjas.value.filter(i => i !== key) : expandedUnitKerjas.value.push(key);
};
const toggleKaryawan = (nrp: string) => {
    expandedKaryawan.value = expandedKaryawan.value === nrp ? null : nrp;
};
</script>

<template>
    <Head title="Laporan Diklat" />
    <!-- Background halus untuk kesan modern -->
    <AppLayout :breadcrumbs="breadcrumbs" class="bg-slate-50/50">
        <div class="mx-auto max-w-7xl space-y-8 py-8 sm:px-6 lg:px-8">
            
            <!-- HEADER MODERN -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Laporan Diklat</h1>
                    <p class="mt-1 flex items-center gap-2 text-sm text-slate-500">
                        <span class="inline-flex h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        Periode: <span class="font-semibold text-slate-700">{{ teksPeriode }}</span>
                    </p>
                </div>
                <button @click="generateExcel" class="group flex items-center gap-2 rounded-2xl bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition-all hover:bg-slate-50 hover:shadow-md hover:ring-indigo-200">
                    <svg class="h-4 w-4 text-indigo-500 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
            </div>

            <!-- FILTER PILLS (Modern Chips) -->
            <div class="rounded-3xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur-sm">
                <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="relative w-full md:w-80">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" v-model="searchBagian" @keyup.enter="applyFilter" placeholder="Cari nama bagian..." class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-indigo-500" />
                    </div>
                    <button @click="applyFilter" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all hover:bg-indigo-700 hover:shadow-indigo-500/50 active:scale-95">
                        Terapkan Filter
                    </button>
                </div>
                
                <!-- Modern Month Chips -->
                <div class="flex flex-wrap gap-2">
                    <label v-for="bulan in listBulan" :key="bulan.id" class="cursor-pointer select-none">
                        <input type="checkbox" :value="bulan.id" v-model="selectedMonths" class="peer hidden" />
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-medium text-slate-600 transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:shadow-sm hover:border-slate-300">
                            {{ bulan.nama }}
                        </span>
                    </label>
                </div>
            </div>

            <!-- DASHBOARD CARDS -->
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Card 1: Gradient Hero -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-xl shadow-indigo-500/20">
                    <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -bottom-8 -left-8 h-24 w-24 rounded-full bg-indigo-400/20 blur-xl"></div>
                    
                    <h3 class="relative text-sm font-medium text-indigo-100">Total Realisasi Jam</h3>
                    <div class="relative mt-4 flex items-baseline gap-2">
                        <span class="text-5xl font-extrabold tracking-tight">{{ totalJamDiklat }}</span>
                        <span class="text-sm font-medium text-indigo-200">/ {{ targetAll }} Jam</span>
                    </div>
                    <div class="relative mt-6">
                        <div class="flex justify-between text-xs font-medium text-indigo-100 mb-1">
                            <span>Progress Keseluruhan</span>
                            <span>{{ persentaseTotal }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-black/20">
                            <div class="h-2 rounded-full bg-white shadow-[0_0_10px_rgba(255,255,255,0.5)] transition-all duration-1000" :style="{ width: Math.min(Number(persentaseTotal), 100) + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Donut Chart -->
                <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Status Karyawan</h3>
                    <div class="mt-2 flex justify-center">
                        <VueApexCharts type="donut" height="220" :options="chartStatusOptions" :series="chartStatusSeries" />
                    </div>
                </div>

                <!-- Card 3: Ranking Bar -->
                <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Top Bagian Aktif</h3>
                    <div class="mt-4">
                        <VueApexCharts type="bar" height="220" :options="chartRankingOptions" :series="chartRankingSeries" />
                    </div>
                </div>
            </div>

            <!-- MODERN ACCORDION LIST -->
            <div class="space-y-4">
                <h3 class="px-2 text-lg font-bold text-slate-800">Rincian Per Bagian</h3>
                
                <div v-if="laporanPerBagian.length === 0" class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                    Tidak ada data untuk filter yang dipilih.
                </div>

                <div v-for="(item, index) in laporanPerBagian" :key="index" class="group overflow-hidden rounded-3xl border border-slate-200/60 bg-white shadow-sm transition-all hover:shadow-md">
                    <!-- Header Bagian (Clickable) -->
                    <div @click="toggleRow(item.kategori)" class="cursor-pointer p-5 transition-colors hover:bg-slate-50/80">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition-transform group-hover:scale-110">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-slate-800">{{ item.kategori }}</h4>
                                    <p class="text-xs text-slate-500">{{ item.totalKaryawan }} Karyawan Terdaftar</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-xs text-slate-400">Realisasi</p>
                                    <p class="text-sm font-bold text-slate-700"><span class="text-indigo-600">{{ item.aktualJam }}</span> / {{ item.totalTargetJam }} Jam</p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-full" :class="item.persentase >= 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                    <span class="text-xs font-extrabold">{{ item.persentase }}%</span>
                                </div>
                                <svg class="h-5 w-5 text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': expandedRows.includes(item.kategori) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Expanded Content: Modern List -->
                    <div v-if="expandedRows.includes(item.kategori)" class="border-t border-slate-100 bg-slate-50/50 p-5">
                        <div class="space-y-3">
                            <div v-for="unit in item.unitKerjas" :key="unit.unitKerja" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <!-- Header Unit Kerja (Clickable) -->
                                <div @click="toggleUnitKerja(item.kategori, unit.unitKerja)" class="flex cursor-pointer items-center justify-between p-4 transition-colors hover:bg-slate-50">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ unit.unitKerja }}</p>
                                        <p class="text-xs text-slate-500">{{ unit.karyawans.length }} Karyawan</p>
                                    </div>
                                    <svg class="h-4 w-4 text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': expandedUnitKerjas.includes(unitKerjaKey(item.kategori, unit.unitKerja)) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>

                                <div v-if="expandedUnitKerjas.includes(unitKerjaKey(item.kategori, unit.unitKerja))" class="space-y-3 border-t border-slate-100 bg-slate-50/50 p-4">
                                    <div v-for="karyawan in unit.karyawans" :key="karyawan.nrp" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:border-indigo-200 hover:shadow-md">
                                
                                <!-- Baris Utama Karyawan -->
                                <div @click="toggleKaryawan(karyawan.nrp)" class="flex cursor-pointer items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-100 to-violet-100 text-sm font-bold text-indigo-600 shadow-inner">
                                            {{ karyawan.nama.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ karyawan.nama }}</p>
                                            <p class="text-xs font-mono text-slate-400">{{ karyawan.nrp }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-4">
                                        <div class="hidden text-right sm:block">
                                            <span class="text-xs text-slate-400">Target: {{ karyawan.target }}j</span>
                                            <p class="text-sm font-bold text-slate-700">{{ karyawan.aktual }} Jam</p>
                                        </div>
                                        <span :class="['rounded-full px-3 py-1 text-xs font-bold transition-colors', karyawan.persentase >= 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700']">
                                            {{ karyawan.persentase }}%
                                        </span>
                                        <svg class="h-4 w-4 text-slate-300 transition-transform" :class="{ 'rotate-180': expandedKaryawan === karyawan.nrp }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>

                                <!-- Detail Riwayat (Smooth reveal) -->
                                <div v-if="expandedKaryawan === karyawan.nrp" class="mt-4 border-t border-slate-100 pt-4 pl-13">
                                    <h5 class="mb-3 flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Riwayat Diklat
                                    </h5>
                                    <div v-if="karyawan.detail_diklat?.length" class="space-y-2">
                                        <div v-for="d in karyawan.detail_diklat" :key="d.id" class="flex items-center justify-between rounded-xl bg-slate-50 p-3 text-xs">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-slate-700">{{ d.nama_diklat }}</span>
                                                <span class="text-[10px] text-slate-400">{{ d.tanggal }}</span>
                                            </div>
                                            <span class="rounded-lg bg-indigo-50 px-2 py-1 font-mono font-bold text-indigo-600">+{{ d.jam }} Jam</span>
                                        </div>
                                    </div>
                                    <div v-else class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs italic text-slate-400">
                                        Belum ada riwayat diklat pada periode ini.
                                    </div>
                                </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
