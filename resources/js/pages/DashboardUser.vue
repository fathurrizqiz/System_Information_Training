<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

// Interface Data
interface StatPerTipe {
    tipe: string;
    total_jam: number;
    count: number;
    warna: string;
    icon: string;
}

interface JadwalInternal {
    id: number;
    nama_diklat: string;
    tanggal: string;
    jam_diklat: number;
    tempat: string;
    tipe: 'Internal' | 'Eksternal' | 'HLC';
    status: string;
}

interface PendingDiklat {
    id: number;
    nama_diklat: string;
    tanggal_mulai: string;
    penyelenggara: string;
    tipe: 'Eksternal' | 'HLC';
    dokumen?: string | null;
}

interface DiklatRincian {
    id?: number; // Tambahan ID untuk target Generate Sertifikat
    nama_diklat: string;
    tanggal: string;
    jam: number;
    penyelenggara: string;
    jenis: 'Mandiri' | 'Eksternal' | 'HLC' | 'Internal';
    sertifikat_path?: string | null; // Tambahan path sertifikat
}

interface BulananData {
    bulan: number;
    nama_bulan: string;
    total_jam: number;
    jumlah_diklat: number;
    rincian: DiklatRincian[];
}

interface StatsJenis {
    mandiri: { total: number; count: number };
    eksternal: { total: number; count: number };
    hlc: { total: number; count: number };
    internal: { total: number; count: number };
}

// Definisi Props Resmi
const props = defineProps<{
    totalJam: number;
    totalJamBulanan: number;
    targetJam: number;
    targetBulanan: number;
    targetJam6Bulan: number;
    totalJamSemesterIni: number;
    persentase: number;
    persentaseBulanan: number;
    persentasePromosi: number;
    statsPerTipe: StatPerTipe[];
    pendingDiklat: PendingDiklat[];
    jadwalInternal: JadwalInternal[];
    namaKaryawan: string;
    kategori: string;
    promosi: boolean;
    pesanPromosi: string;
    bulanTerpujiCount: number;
    bulanHarusLolos: number;
    bulanan: BulananData[];
    statsJenis: StatsJenis;
    allDiklat: DiklatRincian[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
];

// State untuk Tabel Expandable
const selectedBulan = ref<BulananData | null>(null);

// HELPER FUNCTIONS
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const getIconDiklat = (tipe: string) => {
    if (tipe === 'HLC') {
        return `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
        </svg>`;
    }
    return `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
    </svg>`;
};

const getTipeClass = (tipe: string) => {
    if (tipe === 'HLC') return 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300';
    return 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300';
};

const getJenisBadgeClass = (jenis: string) => {
    const classes: Record<string, string> = {
        'Mandiri': 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
        'Eksternal': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
        'HLC': 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300',
        'Internal': 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
    };
    return classes[jenis] || 'bg-slate-100 text-slate-700';
};

// Fungsi untuk membuka/menutup rincian (Pengganti Modal)
const toggleBulan = (bulan: BulananData) => {
    if (selectedBulan.value?.bulan === bulan.bulan) {
        selectedBulan.value = null; // Tutup jika diklik lagi
    } else {
        selectedBulan.value = bulan; // Buka rincian
    }
};

// Fungsi Generate Sertifikat
function generateSertifikat(pesertaId: number | undefined) {
    if (pesertaId == null || isNaN(pesertaId)) {
        toast.error('ID peserta tidak valid atau tidak ditemukan');
        return;
    }
    if (!confirm('Generate sertifikat untuk peserta ini?')) return;
    
    router.post(`/sertifikat/generate/${pesertaId}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Sertifikat berhasil digenerate!');
        },
        onError: (errors) => {
            const pesanError = errors.message || 'Gagal! Karyawan belum memenuhi syarat atau belum mengikuti pelatihan.';
            toast.error(pesanError);
        }
    });
}

// Logic Progress Ring Tahunan
const RING_RADIUS = 40;
const RING_CIRCUMFERENCE = 2 * Math.PI * RING_RADIUS;
const ringOffset = computed(() => {
    const pct = Math.min(props.persentase, 100);
    return RING_CIRCUMFERENCE * (1 - pct / 100);
});

const statusWarna = computed(() => {
    if (props.persentase >= 100) return 'text-emerald-600 dark:text-emerald-400 stroke-emerald-500';
    if (props.persentase >= 75) return 'text-blue-600 dark:text-blue-400 stroke-blue-500';
    if (props.persentase >= 50) return 'text-amber-600 dark:text-amber-400 stroke-amber-500';
    return 'text-red-600 dark:text-red-400 stroke-red-500';
});

// Logic Progress Ring Bulanan
const RING_RADIUS_BULANAN = 40;
const RING_CIRCUMFERENCE_BULANAN = 2 * Math.PI * RING_RADIUS_BULANAN;
const ringOffsetBulanan = computed(() => {
    const pct = Math.min(props.persentaseBulanan, 100);
    return RING_CIRCUMFERENCE_BULANAN * (1 - pct / 100);
});

const statusWarnaBulanan = computed(() => {
    if (props.persentaseBulanan >= 100) return 'text-emerald-600 dark:text-emerald-400 stroke-emerald-500';
    if (props.persentaseBulanan >= 75) return 'text-blue-600 dark:text-blue-400 stroke-blue-500';
    if (props.persentaseBulanan >= 50) return 'text-amber-600 dark:text-amber-400 stroke-amber-500';
    return 'text-red-600 dark:text-red-400 stroke-red-500';
});

// Hitung total kegiatan aktif
const totalKegiatanAktif = computed(() => {
    const totalDiklatSelesai = props.statsPerTipe.reduce((acc, curr) => acc + curr.count, 0);
    return totalDiklatSelesai + props.pendingDiklat.length;
});

// EXCEL EXPORT LOGIC
// --- STATE & FUNGSI UNTUK EXPORT EXCEL ---
const showExportModal = ref(false);
const exportType = ref('current'); // 'current', 'all', 'custom'
const selectedExportMonths = ref<number[]>([]);

const namaBulanIndo = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const openExportModal = () => {
    // Reset state saat modal dibuka
    exportType.value = 'current';
    selectedExportMonths.value = [];
    showExportModal.value = true;
};

const closeExportModal = () => {
    showExportModal.value = false;
};

const downloadExcel = () => {
    let monthsToExport: number[] = [];
    const currentMonth = new Date().getMonth() + 1;

    if (exportType.value === 'current') {
        monthsToExport = [currentMonth];
    } else if (exportType.value === 'all') {
        monthsToExport = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    } else {
        monthsToExport = selectedExportMonths.value;
        if (monthsToExport.length === 0) {
            toast.warning('Pilih minimal satu bulan untuk diekspor!');
            return;
        }
    }

    // Bangun URL Query Parameter (contoh: ?months[]=1&months[]=2)
    const params = new URLSearchParams();
    monthsToExport.forEach(m => params.append('months[]', m.toString()));

    // Gunakan window.location agar browser langsung mengunduh file
    window.location.href = `/Laporan/Diklat/Export?${params.toString()}`;
    
    closeExportModal();
    toast.info('Laporan sedang diunduh...');
};

function jadwalTerdekat(){
    router.get('/JadwalDiklat/Internal');
}
</script>

<template>
    <Head title="Dashboard Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                    Selamat Datang, {{ namaKaryawan }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Kategori: {{ kategori }} &bull; Pantau perkembangan kompetensi Anda di sini.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">
                                Pencapaian Tahun Ini
                            </p>
                            <p class="mt-2 text-2xl leading-tight font-extrabold text-slate-900 dark:text-white">
                                {{ totalJam }}
                            </p>
                            <p class="mt-2 text-slate-200 font-serif dark:text-white">
                                / {{ targetJam * 12 }} Jam
                            </p>
                            <p class="mt-1 text-sm font-semibold" :class="statusWarna">
                                {{ Math.round(persentase) }}%
                            </p>
                        </div>
                        <div class="relative shrink-0">
                            <svg viewBox="0 0 100 100" class="h-16 w-16 -rotate-90 transform">
                                <circle cx="50" cy="50" :r="RING_RADIUS" fill="none" stroke-width="8" class="stroke-slate-100 dark:stroke-slate-800" />
                                <circle cx="50" cy="50" :r="RING_RADIUS" fill="none" stroke-width="8" stroke-linecap="round" :class="[statusWarna, 'transition-all duration-1000 ease-out']" :stroke-dasharray="RING_CIRCUMFERENCE" :stroke-dashoffset="ringOffset" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">
                                Pencapaian Bulan Ini
                            </p>
                            <p class="mt-2 text-3xl leading-tight font-extrabold text-slate-900 dark:text-white">
                                {{ totalJamBulanan }}
                                <span class="block text-xs font-normal text-slate-400">/ {{ targetBulanan }} Jam</span>
                            </p>
                            <p class="mt-2 text-sm font-semibold" :class="statusWarnaBulanan">
                                {{ Math.round(persentaseBulanan) }}%
                            </p>
                        </div>
                        <div class="relative shrink-0">
                            <svg viewBox="0 0 100 100" class="h-16 w-16 -rotate-90 transform">
                                <circle cx="50" cy="50" :r="RING_RADIUS_BULANAN" fill="none" stroke-width="8" class="stroke-slate-100 dark:stroke-slate-800" />
                                <circle cx="50" cy="50" :r="RING_RADIUS_BULANAN" fill="none" stroke-width="8" stroke-linecap="round" :class="[statusWarnaBulanan, 'transition-all duration-1000 ease-out']" :stroke-dasharray="RING_CIRCUMFERENCE_BULANAN" :stroke-dashoffset="ringOffsetBulanan" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm dark:border-amber-900/50 dark:bg-amber-900/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-medium tracking-wider text-amber-700 uppercase dark:text-amber-400">
                                Menunggu Persetujuan
                            </p>
                            <p class="mt-2 text-3xl font-extrabold text-amber-800 dark:text-amber-300">
                                {{ pendingDiklat.length }}
                            </p>
                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-500">Diklat</p>
                        </div>
                        <div class="rounded-xl bg-amber-500 p-3 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">
                                Jadwal Terdekat
                            </p>
                            <p v-if="jadwalInternal.length > 0" class="mt-2 text-sm font-bold text-slate-900 dark:text-white leading-tight">
                                {{ formatDate(jadwalInternal[0].tanggal) }}
                            </p>
                            <p v-else class="mt-2 text-sm text-slate-400 italic">Tidak ada jadwal dekat.</p>
                            <p class="mt-1 text-xs text-slate-400">Diklat Internal</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400">
                                Total Kegiatan
                            </p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">
                                {{ totalKegiatanAktif }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">Selesai & Menunggu</p>
                        </div>
                        <div class="rounded-xl bg-violet-50 p-3 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="relative overflow-hidden rounded-2xl border p-6 shadow-sm transition-all duration-700 ease-out"
                :class="[
                    promosi
                        ? 'border-transparent bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white shadow-lg shadow-pink-500/30'
                        : 'border-slate-200 bg-white text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-white',
                ]"
            >
                <div v-if="promosi" class="absolute -top-10 -right-10 h-48 w-48 animate-pulse rounded-full bg-white/20 blur-3xl"></div>
                <div v-if="promosi" class="absolute -bottom-10 -left-10 h-40 w-40 animate-pulse rounded-full bg-white/20 blur-3xl" style="animation-delay: 1s"></div>

                <div class="relative z-10 flex flex-col gap-5">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full transition-colors" :class="promosi ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800'">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8" :class="promosi ? 'animate-bounce text-yellow-300' : 'text-slate-400'">
                                    <path fill-rule="evenodd" d="M5.166 2.621v.858c-1.035.148-2.059.33-3.071.543a.75.75 0 0 0-.584.859 6.753 6.753 0 0 0 6.138 5.6 27.355 27.355 0 0 0 1.052.148c.11.116.225.231.346.342l.534.489a2.766 2.766 0 0 0 2.457 0l.534-.489a27.18 27.18 0 0 0 1.398-.49 6.753 6.753 0 0 0 6.137-5.6.75.75 0 0 0-.584-.86 48.243 48.243 0 0 0-3.072-.543v-.858a.75.75 0 0 0-.75-.75h-9a.75.75 0 0 0-.75.75Zm4.364 12.879a.75.75 0 0 0-1.05-.039l-2.062 1.879a.75.75 0 0 0-.256.62l.582 4.075a.75.75 0 0 0 1.25.56l2.355-2.064a.75.75 0 0 0 .185-.828l-.75-2.616a.75.75 0 0 0-.254-.588Zm3.94 0a.75.75 0 0 1 1.05-.039l2.062 1.879a.75.75 0 0 1 .256.62l-.582 4.075a.75.75 0 0 1-1.25.56l-2.355-2.064a.75.75 0 0 1-.185-.828l.75-2.616a.75.75 0 0 1 .254-.588Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold tracking-tight">Capaian Promosi Kategori (6 Bulan)</h2>
                                <p class="mt-0.5 text-sm font-medium" :class="promosi ? 'text-white/90' : 'text-slate-500 dark:text-slate-400'">
                                    {{ pesanPromosi }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-extrabold tracking-tight">
                                {{ Math.min(Math.round(persentasePromosi), 100) }}%
                            </span>
                            <p class="text-[10px] font-medium tracking-wider uppercase opacity-75">Progress Promosi</p>
                        </div>
                    </div>
                    <div class="w-full">
                        <div class="h-3 w-full rounded-full overflow-hidden" :class="promosi ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800'">
                            <div 
                                class="h-full rounded-full transition-all duration-1000 ease-out"
                                :class="promosi ? 'bg-white shadow-[0_0_12px_rgba(255,255,255,0.6)]' : 'bg-fuchsia-600 dark:bg-fuchsia-500'"
                                :style="{ width: `${Math.min(persentasePromosi, 100)}%` }"
                            ></div>
                        </div>
                        <div class="mt-2 flex justify-between text-xs font-medium" :class="promosi ? 'text-white/80' : 'text-slate-400 dark:text-slate-500'">
                            <span>Pencapaian Semester: <b>{{ totalJamSemesterIni }} Jam</b></span>
                            <span>Target Kategori (6 Bulan): <b>{{ targetJam6Bulan }} Jam</b></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE BULANAN (Menghilangkan Modal, Expand Inline) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-bold text-slate-900 dark:text-white">
                    Rekapitulasi Jam Diklat Per Bulan ({{ new Date().getFullYear() }})
                </h2>
                
                <!-- Tombol Buka Modal Export -->
                    <button 
                        @click="openExportModal"
                        class="inline-flex mb-3 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        Export Excel
                    </button>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-3 text-left font-semibold text-slate-700 dark:text-slate-300">Bulan</th>
                                <th v-for="item in bulanan" :key="item.bulan" class="pb-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ item.nama_bulan }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-3 font-medium text-slate-600 dark:text-slate-400">Total Jam</td>
                                <td v-for="item in bulanan" :key="item.bulan" class="py-3 text-center">
                                    <!-- Logika Tombol diubah agar bisa switch aktif/tidak -->
                                    <button 
                                        @click="item.total_jam > 0 ? toggleBulan(item) : null"
                                        class="rounded-lg px-3 py-1.5 font-semibold transition-all focus:outline-none"
                                        :class="[
                                            item.total_jam > 0 
                                                ? selectedBulan?.bulan === item.bulan
                                                    ? 'bg-blue-600 text-white shadow-md dark:bg-blue-500'
                                                    : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60' 
                                                : 'text-slate-400 dark:text-slate-600 cursor-default'
                                        ]"
                                    >
                                        {{ item.total_jam }}
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 font-medium text-slate-600 dark:text-slate-400">Jumlah Diklat</td>
                                <td v-for="item in bulanan" :key="item.bulan" class="py-3 text-center text-slate-500 dark:text-slate-500">
                                    {{ item.jumlah_diklat }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    * Klik angka total jam untuk melihat / generate sertifikat di bulan tersebut
                </p>

                <!-- RINCIAN EXPANDABLE DI BAWAH TABEL -->
                <div v-if="selectedBulan" class="mt-6 animate-fade-in">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Rincian Bulan {{ selectedBulan.nama_bulan }}
                        </h3>
                        <button @click="selectedBulan = null" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                            Tutup Rincian
                        </button>
                    </div>

                    <div v-if="selectedBulan.rincian.length > 0" class="flex flex-col gap-3">
                        <div v-for="(diklat, index) in selectedBulan.rincian" :key="index"
                            class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800/50">

                            <!-- Tanggal Badge -->
                            <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-white shadow-sm dark:bg-slate-900">
                                <span class="text-[10px] font-bold uppercase text-slate-500">{{ new Date(diklat.tanggal).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                                <span class="text-lg leading-none font-bold text-slate-700 dark:text-slate-300">{{ new Date(diklat.tanggal).getDate() }}</span>
                            </div>

                            <!-- Content Info -->
                            <div class="flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <h4 class="font-semibold text-slate-900 dark:text-white">{{ diklat.nama_diklat }}</h4>
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase" :class="getJenisBadgeClass(diklat.jenis)">
                                        {{ diklat.jenis }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">{{ diklat.penyelenggara }} &bull; {{ diklat.jam }} Jam</p>
                            </div>

                            <!-- Aksi / Generate Sertifikat (HANYA UNTUK INTERNAL) -->
                            <div class="mt-3 flex items-center gap-2 sm:mt-0" v-if="diklat.jenis === 'Internal'">
                                <template v-if="diklat.sertifikat_path">
                                    <a :href="`/storage/${diklat.sertifikat_path}`" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800/50 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Lihat Sertifikat
                                    </a>
                                </template>
                                <template v-else>
                                    <button @click="generateSertifikat(diklat.id)" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Generate Sertifikat
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END RINCIAN EXPANDABLE -->

            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Sisa Grid Jadwal Mendatang dll tetap tidak berubah -->
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Jadwal Mendatang</h2>
                        <a @click="jadwalTerdekat()" class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-800">Lihat Semua</a>
                    </div>
                    <div v-if="jadwalInternal.length > 0" class="flex flex-col gap-3">
                        <div v-for="item in jadwalInternal" :key="item.id + '-' + item.tipe" class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800">
                            <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                <span class="text-xs font-bold uppercase">{{ new Date(item.tanggal).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                                <span class="text-lg leading-none font-bold">{{ new Date(item.tanggal).getDate() }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="mb-1 flex items-center gap-2">
                                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ item.nama_diklat }}</h3>
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold tracking-wide uppercase" :class="{ 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300': item.tipe === 'Internal', 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300': item.tipe === 'Eksternal', 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300': item.tipe === 'HLC' }">
                                        {{ item.tipe }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">{{ item.tempat }} &bull; {{ item.jam_diklat }} JP</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="{ 'bg-slate-100 text-slate-600': item.status === 'Terjadwal' || item.status === 'Disetujui' || item.status === 'Setuju', 'bg-amber-100 text-amber-700': item.status === 'Menunggu', 'bg-red-100 text-red-700': item.status === 'Ditolak' }">
                                    {{ item.status }}
                                </span>
                                <button class="rounded-lg bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300">Detail</button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 py-12 dark:border-slate-700 dark:bg-slate-800/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-medium text-slate-500">Tidak ada jadwal mendatang.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Status Pengajuan Diklat</h2>
                        <a @click="jadwalTerdekat()" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Lihat Semua</a>
                    </div>
                    <div v-if="pendingDiklat.length > 0" class="flex flex-col gap-3">
                        <div v-for="item in pendingDiklat" :key="item.id + '-' + item.tipe" class="flex items-center gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm transition-all hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                                <div v-html="getIconDiklat(item.tipe)"></div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ item.nama_diklat }}</h3>
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase" :class="getTipeClass(item.tipe)">{{ item.tipe }}</span>
                                </div>
                                <p class="text-xs text-slate-500">{{ item.penyelenggara }} &bull; Mulai {{ formatDate(item.tanggal_mulai) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-amber-200 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">Menunggu</span>
                                <a v-if="item.dokumen" :href="`/storage/${item.dokumen}`" target="_blank" class="rounded-lg border border-slate-200 bg-white p-1.5 text-slate-500 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-blue-400" title="Lihat Dokumen">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 py-12 dark:border-slate-700 dark:bg-slate-800/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-slate-500">Semua pengajuan sudah diproses.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODAL EXPORT EXCEL -->
        <div v-if="showExportModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeExportModal">
            <div class="w-full max-w-md animate-fade-in overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                <div class="border-b border-slate-200 p-5 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Export Laporan Diklat</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Pilih periode bulan yang ingin diekspor ke format Excel.</p>
                </div>
                
                <div class="p-5">
                    <div class="flex flex-col gap-3">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                            <input type="radio" v-model="exportType" value="current" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700 dark:text-slate-300">Bulan Ini ({{ namaBulanIndo[new Date().getMonth()] }})</span>
                        </label>
                        
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                            <input type="radio" v-model="exportType" value="all" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700 dark:text-slate-300">Semua Bulan (Januari - Desember)</span>
                        </label>
                        
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                            <input type="radio" v-model="exportType" value="custom" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700 dark:text-slate-300">Pilih Bulan Sendiri</span>
                        </label>
                    </div>

                    <!-- Pilihan Kustom Bulan Muncul Jika 'Pilih Bulan Sendiri' di-klik -->
                    <div v-if="exportType === 'custom'" class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        <label v-for="(bulan, index) in namaBulanIndo" :key="index" class="flex cursor-pointer items-center gap-2 rounded-lg bg-slate-50 p-2 text-sm transition-colors hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700">
                            <input type="checkbox" v-model="selectedExportMonths" :value="index + 1" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="text-slate-700 dark:text-slate-300">{{ bulan.substring(0, 3) }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                    <button @click="closeExportModal" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700">
                        Batal
                    </button>
                    <button @click="downloadExcel" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh Laporan
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Transisi agar UI terasa tidak kaku saat rincian terbuka */
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>