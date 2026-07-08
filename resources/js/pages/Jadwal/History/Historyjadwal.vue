<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

// --- Interface & Props ---
interface Peserta {
    id: number;
    bagian: string;
    nrp: string;
}

interface Jadwal {
    id: number;
    tanggal?: string;
    tanggal_mulai?: string;
    detail: { nama_diklat: string } | null;
    nama_diklat?: string;
    nama_pengajar?: string;
    penyelenggara?: string;
    tempat?: string;
    peserta?: Peserta[];
    hlc?: any[];
    eksternal?: any[];
    status?: string;
    dokumen?: string;
    bukti?: string;
    // Tambahkan properti spesifik HLC jika perlu
    bukti_hadir?: string;
    nama_program?: string;
}

const props = defineProps<{
    jadwalInternal: Jadwal[];
    jadwalHLC: any[];
    jadwalEksternal: any[];
    filters: { search: string };
    auth: {
        user: {
            id: number;
            name: string;
            roles: string | string[];
        } | null;
    };
}>();

// --- State ---
const activeTab = ref('internal');
const search = ref(props.filters.search || '');

const menuItems = [
    { title: 'Internal', href: '/RencanaDiklat/RPT/PF' },
    { title: 'Eksternal', href: '/RencanaDiklat/RPT/PN' },
    { title: 'HLC', href: '/HLC/Home/manajemen' },
];

// --- Functions ---
const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

// Debounced Search
const performSearch = debounce((value: string) => {
    router.get(
        '/Jadwal',
        { search: value },
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (newValue) => {
    performSearch(newValue);
});

function back() {
    window.history.back();
}

const lihatDokumen = (dokumen: string) => {
    window.open(`/storage/${dokumen}`, '_blank');
};

// --- Role Handling ---
const rawRole = props.auth.user?.roles || [];
const roles = Array.isArray(rawRole) ? rawRole : [rawRole];

// Komponen untuk Empty State (Sederhana di sini, atau import jika ada)
const EmptyState = {
    template: `
        <div class="flex flex-col items-center justify-center p-12 text-center">
            <svg class="h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <p class="mt-4 text-sm font-medium text-slate-500">Tidak ada data ditemukan</p>
        </div>
    `,
};
</script>

<template>
    <Head title="Histori Jadwal Diklat" />

    <AppLayout>
        <div class="space-y-4 p-4 md:space-y-6 md:p-6 lg:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-1">
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                    Histori Jadwal Diklat
                </h1>
                <p class="text-xs text-slate-500 md:text-sm dark:text-slate-400">
                    Daftar seluruh pelatihan mendatang yang Anda ikuti.
                </p>
            </div>

            <!-- Search & Tabs Container -->
            <div class="sticky top-0 z-10 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:relative dark:border-slate-800 dark:bg-slate-900">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari kegiatan..."
                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 pr-4 pl-10 text-sm focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800"
                    />
                </div>

                <!-- Horizontal Tabs -->
                <div class="no-scrollbar flex gap-2 overflow-x-auto border-t border-slate-100 pt-3 pb-1 dark:border-slate-800">
                    <button
                        v-for="tab in ['internal', 'hlc', 'eksternal']"
                        :key="tab"
                        @click="activeTab = tab"
                        :class="[
                            'flex-1 rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap capitalize transition-all sm:flex-none',
                            activeTab === tab
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800',
                        ]"
                    >
                        {{ tab }}
                    </button>
                </div>
            </div>

            <!-- Back Button -->
            <button class="flex w-fit items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-sm text-white transition hover:bg-blue-600" @click="back()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 14 4 9l5-5" />
                    <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5a5.5 5.5 0 0 1-5.5 5.5H11" />
                </svg>
                Kembali
            </button>

            <!-- Content Area -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                
                <!-- 1. JADWAL INTERNAL -->
                <div v-if="activeTab === 'internal'">
                    <!-- Desktop Table -->
                    <div class="hidden overflow-x-auto md:block">
                        <table v-if="jadwalInternal.length" class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] font-bold tracking-widest text-slate-500 uppercase dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-6 py-4">Nama Kegiatan</th>
                                    <th class="px-6 py-4">Pengajar</th>
                                    <th class="px-6 py-4">Lokasi</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="item in jadwalInternal" :key="item.id" class="transition-colors hover:bg-slate-50">
                                    <td class="px-6 py-4 font-bold text-blue-600">
                                        {{ item.detail?.nama_diklat || '-' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium">{{ item.nama_pengajar }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ item.tempat }}</td>
                                    <td class="px-6 py-4 font-mono text-xs">{{ formatDate(item.tanggal!) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Card View -->
                    <div class="divide-y divide-slate-100 md:hidden dark:divide-slate-800">
                        <div v-for="item in jadwalInternal" :key="item.id" class="space-y-3 p-4">
                            <h4 class="leading-tight font-bold text-blue-600">
                                {{ item.detail?.nama_diklat || '-' }}
                            </h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Pengajar</p>
                                    <p class="font-medium dark:text-slate-300">{{ item.nama_pengajar }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Lokasi</p>
                                    <p class="font-medium dark:text-slate-300">{{ item.tempat || '-' }}</p>
                                </div>
                                <div class="col-span-2 border-t border-slate-50 pt-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Tanggal Pelaksanaan</p>
                                    <p class="font-mono text-slate-900 dark:text-white">{{ formatDate(item.tanggal!) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <EmptyState v-if="!jadwalInternal.length" />
                </div>

                                <!-- 2. JADWAL HLC (FIXED) -->
                <div v-if="activeTab === 'hlc'">
                    <!-- Desktop Table -->
                    <div class="hidden overflow-x-auto md:block">
                        <table v-if="jadwalHLC.length" class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] font-bold tracking-widest text-slate-500 uppercase dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-6 py-4 text-emerald-600">Nama Diklat</th>
                                    <th class="px-6 py-4">Program</th>
                                    <th class="px-6 py-4">Mulai</th>
                                    <th class="px-6 py-4">Selesai</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Undangan</th>
                                    <th class="px-6 py-4">Bukti Hadir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template v-for="prog in jadwalHLC" :key="prog.id">
                                    <!-- Looping hlc items di sini -->
                                    <tr v-for="hlc in prog.hlc" :key="hlc.id" class="transition-colors hover:bg-slate-50">
                                        
                                        <!-- NAMA DIKLAT (Perbaikan: Tampilkan nama, bukan tombol) -->
                                        <td v-if="hlc.nama_diklat" class="px-6 py-4 font-bold text-emerald-600">
                                            {{ hlc.nama_diklat || hlc.nama_program || '-' }}
                                        </td>
                                        <td v-else class="px-6 py-4 font-bold text-emerald-600">
                                            <button class="cursor-pointer text-blue-500" @click="lihatDokumen(hlc.dokumen)">
                                                lihat undangan
                                            </button>
                                        </td>

                                        <td class="px-6 py-4 text-slate-500 italic">
                                            {{ prog.nama_program }}
                                        </td>

                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ formatDate(hlc.tanggal_mulai) }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ formatDate(hlc.tanggal_selesai) }}
                                        </td>

                                        <!-- STATUS -->
                                        <td class="px-6 py-4">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                :class="{
                                                    'bg-green-100 text-green-800': prog.status === 'approved' || prog.status === 'approved',
                                                    'bg-red-100 text-red-800': prog.status === 'rejected' || prog.status === 'tidak hadir',
                                                    'bg-yellow-100 text-yellow-800': prog.status === 'pending',
                                                }">
                                                {{ prog.status ? prog.status.toUpperCase() : '-' }}
                                            </span>
                                        </td>

                                        <!-- UNDANGAN (Perbaikan Potensi 403) -->
                                        <!-- Jika file ada di prog, ganti hlc.dokumen menjadi prog.dokumen -->
                                        <!-- Jika di Eksternal Anda pakai eks.dokumen, maka di sini konsisten pakai hlc.dokumen -->
                                        <td class="px-6 py-4">
                                            <button
                                                v-if="hlc.dokumen"
                                                @click="lihatDokumen(hlc.dokumen)"
                                                class="inline-flex items-center gap-1 rounded bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat
                                            </button>
                                            <span v-else class="text-slate-400 text-xs">-</span>
                                        </td>

                                        <!-- BUKTI HADIR -->
                                        <td class="px-6 py-4">
                                            <a
                                                v-if="hlc.bukti_hadir"
                                                :href="`/storage/${hlc.bukti_hadir}`"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                </svg>
                                                Lihat
                                            </a>
                                            <span v-else class="text-slate-400 text-xs">-</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="divide-y divide-slate-100 md:hidden dark:divide-slate-800">
                        <template v-for="prog in jadwalHLC" :key="prog.id">
                            <div v-for="hlc in prog.hlc" :key="hlc.id" class="space-y-3 p-4">
                                <div class="flex items-start justify-between">
                                    <h4 v-if="hlc.nama_diklat" class="leading-tight font-bold text-emerald-600">
                                        {{ hlc.nama_diklat || hlc.nama_program }}
                                    </h4>
                                    <h4 v-else class="leading-tight font-bold text-emerald-600">
                                        <button class="cursor-pointer text-blue-500" @click="lihatDokumen(hlc.dokumen)">
                                            lihat undangan
                                        </button>
                                    </h4>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                                        :class="{
                                            'bg-green-100 text-green-800': prog.status === 'approved' || prog.status === 'hadir',
                                            'bg-red-100 text-red-800': prog.status === 'rejected' || prog.status === 'tidak hadir',
                                            'bg-yellow-100 text-yellow-800': prog.status === 'pending',
                                        }">
                                        {{ prog.status ? prog.status : '-' }}
                                    </span>
                                </div>
                                
                                <p class="text-[10px] text-slate-500 italic">{{ prog.nama_program }}</p>
                                
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Mulai</p>
                                        <p class="font-mono">{{ formatDate(hlc.tanggal_mulai) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Selesai</p>
                                        <p class="font-mono">{{ formatDate(hlc.tanggal_selesai) }}</p>
                                    </div>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-2 border-t border-slate-50 pt-2">
                                    <!-- Perbaikan Mobile: Gunakan hlc.dokumen agar konsisten dengan Desktop -->
                                    <button
                                        v-if="hlc.dokumen"
                                        @click="lihatDokumen(hlc.dokumen)"
                                        class="flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-[10px] text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Undangan
                                    </button>
                                    <a
                                        v-if="hlc.bukti_hadir"
                                        :href="`/storage/${hlc.bukti_hadir}`"
                                        target="_blank"
                                        class="flex items-center gap-1 rounded bg-blue-50 px-2 py-1 text-[10px] font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                        </svg>
                                        Bukti Hadir
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                    <EmptyState v-if="!jadwalHLC.length" />
                </div>

                <!-- 3. JADWAL EKSTERNAL -->
                <div v-if="activeTab === 'eksternal'">
                    <!-- Desktop Table -->
                    <div class="hidden overflow-x-auto md:block">
                        <table v-if="jadwalEksternal.length" class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] font-bold tracking-widest text-slate-500 uppercase dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-6 py-4 text-purple-600">Nama Diklat</th>
                                    <th class="px-6 py-4">Undangan</th>
                                    <th class="px-6 py-4">Mulai</th>
                                    <th class="px-6 py-4">Bukti Hadir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template v-for="prog in jadwalEksternal" :key="prog.id">
                                    <tr v-for="eks in prog.eksternal" :key="eks.id" class="transition-colors hover:bg-slate-50">
                                        <td class="px-6 py-4 font-bold text-purple-600">
                                            {{ eks.nama_diklat }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <button
                                                v-if="eks.dokumen"
                                                @click="lihatDokumen(eks.dokumen)"
                                                class="inline-flex items-center gap-1 rounded bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ formatDate(eks.tanggal_mulai) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <a
                                                v-if="eks.bukti_hadir"
                                                :href="`/storage/${eks.bukti_hadir}`"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                </svg>
                                                Lihat
                                            </a>
                                            <span v-else class="text-slate-400 text-xs">-</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Card View -->
                    <div class="divide-y divide-slate-100 md:hidden dark:divide-slate-800">
                        <template v-for="prog in jadwalEksternal" :key="prog.id">
                            <div v-for="eks in prog.eksternal" :key="eks.id" class="space-y-2 p-4">
                                <h4 class="leading-tight font-bold text-purple-600">
                                    {{ eks.nama_diklat }}
                                </h4>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-xs font-medium text-slate-500">{{ eks.penyelenggara }}</span>
                                    <span class="rounded bg-purple-50 px-2 py-0.5 font-mono text-xs font-bold text-purple-600">
                                        {{ formatDate(eks.tanggal_mulai) }}
                                    </span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button
                                        v-if="eks.dokumen"
                                        @click="lihatDokumen(eks.dokumen)"
                                        class="rounded bg-slate-100 px-2 py-1 text-[10px] text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        Lihat Undangan
                                    </button>
                                    <a
                                        v-if="eks.bukti_hadir"
                                        :href="`/storage/${eks.bukti_hadir}`"
                                        target="_blank"
                                        class="rounded bg-blue-50 px-2 py-1 text-[10px] font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400"
                                    >
                                        Lihat Bukti
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                    <EmptyState v-if="!jadwalEksternal.length" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>