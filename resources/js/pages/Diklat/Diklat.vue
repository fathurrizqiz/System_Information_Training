<script setup lang="ts">
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Input from '@/components/ui/input/Input.vue';
import { formatDate } from '@/helpers/date';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import { ChevronLeft, ChevronRight, Filter, Calendar } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Diklat Karyawan',
        href: '#',
    },
];

interface Karyawan {
    nama_karyawan: string;
    nrp: string;
    bagian: string;
    unit_kerja: string;
    posisi_jabatan: string;
    klinis_non_klinis: string;
    jenis_kelamin: string;
}

interface Diklat {
    id: number;
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    nama_diklat: string | null;
    pengajar: string;
    penyelenggara: string | null;
    jam_diklat: number;
    diklat: string;
    status: string;
    file_path: string | null;
    dokumen?: string | null;
    created_at: string;
    updated_at: string;
    source: 'user' | 'admin';
}

interface Admin {
    id: number;
    nama_diklat: string;
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    pengajar: string;
    penyelenggara: string;
    diklat: string;
    jam_diklat: number;
    status: string;
    file_path: string | null;
    dokumen?: string | null;
    source: 'user' | 'admin';
}

interface DiklatEksternal {
    id: number;
    nama_diklat?: string;
    program_id: number;
    nama_karyawan: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    jam_diklat: number;
    penyelenggara: string;
    nrp: string;
    status: string;
    dokumen?: string | null;
    program?: {
        nama_diklat: string;
    };
}

interface PaginatedData {
    data: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    diklat: PaginatedData;
    totalJam: number;
    target: number;
    percentage: number;
    kategori: string;
    karyawan: Karyawan;
    admin: PaginatedData;
    eksternal: PaginatedData;
    search: string;
    filters: {
        date_from?: string;
        date_to?: string;
        status?: string;
        source?: string;
    };
}>();

const genderLabel = computed(() => {
    const g = props.karyawan.jenis_kelamin;
    if (g == 'L') return 'LAKI-LAKI';
    if (g == 'P') return 'PEREMPUAN';
    return '-';
});

// Combine all diklat with proper naming
const daftarDiklat = computed(() => {
    const userDiklat = (props.diklat?.data || []).map((item: any) => ({
        ...item,
        nama_diklat: item.nama_diklat || item.diklat || 'Tanpa Nama Diklat',
        display_name: item.dokumen 
            ? `${item.nama_diklat || item.diklat || 'Tanpa Nama'} `
            : item.nama_diklat || item.diklat || 'Tanpa Nama Diklat',
        source: 'user',
    }));

    const adminDiklat = (props.admin?.data || []).map((item: any) => ({
        ...item,
        nama_diklat: item.nama_diklat || item.diklat || 'Tanpa Nama Diklat',
        display_name: item.dokumen 
            ? `${item.nama_diklat || item.diklat || 'Tanpa Nama'} `
            : item.nama_diklat || item.diklat || 'Tanpa Nama Diklat',
        source: 'admin',
    }));

    const diklatEksternal = (props.eksternal?.data || []).map((item: any) => ({
        ...item,
        nama_diklat: item.program?.nama_diklat || item.nama_diklat || 'Tanpa Nama Diklat',
        display_name: item.dokumen 
            ? `${item.program?.nama_diklat || item.nama_diklat || 'Tanpa Nama'}`
            : item.program?.nama_diklat || item.nama_diklat || 'Tanpa Nama Diklat',
        source: 'eksternal',
    }));

    // Merge and sort by date (newest first)
    const allDiklat = [...userDiklat, ...adminDiklat, ...diklatEksternal];
    return allDiklat.sort((a, b) => {
        const dateA = new Date(a.tanggal_mulai || 0).getTime();
        const dateB = new Date(b.tanggal_mulai || 0).getTime();
        return dateB - dateA;
    });
});

// Filter state
const searchQuery = ref(props.search || '');
const showFilters = ref(false);
const filterDateFrom = ref(props.filters.date_from || '');
const filterDateTo = ref(props.filters.date_to || '');
const filterStatus = ref(props.filters.status || '');
const filterSource = ref(props.filters.source || '');

// Swipe state for mobile
const currentIndex = ref(0);
const touchStartX = ref(0);
const touchEndX = ref(0);

const canSwipePrev = computed(() => currentIndex.value > 0);
const canSwipeNext = computed(() => currentIndex.value < daftarDiklat.value.length - 1);

const currentDiklat = computed(() => {
    return daftarDiklat.value[currentIndex.value] || null;
});

// Debounce helper
function debounce(func: (...args: any[]) => void, wait: number) {
    let timeout: NodeJS.Timeout;
    return function executedFunction(...args: any[]) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Watch for search changes
watch(
    searchQuery,
    debounce((newSearch: string) => {
        applyFilters();
    }, 300),
);

// Apply all filters
function applyFilters() {
    router.get(
        route('diklat.home'),
        {
            search: searchQuery.value || undefined,
            date_from: filterDateFrom.value || undefined,
            date_to: filterDateTo.value || undefined,
            status: filterStatus.value || undefined,
            source: filterSource.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

// Reset filters
function resetFilters() {
    searchQuery.value = '';
    filterDateFrom.value = '';
    filterDateTo.value = '';
    filterStatus.value = '';
    filterSource.value = '';
    applyFilters();
}

// Toggle filters panel
function toggleFilters() {
    showFilters.value = !showFilters.value;
}

// Swipe handlers
function handleTouchStart(event: TouchEvent) {
    touchStartX.value = event.touches[0].clientX;
}

function handleTouchMove(event: TouchEvent) {
    touchEndX.value = event.touches[0].clientX;
}

function handleTouchEnd() {
    const diff = touchStartX.value - touchEndX.value;
    const threshold = 50;

    if (Math.abs(diff) > threshold) {
        if (diff > 0 && canSwipeNext.value) {
            // Swipe left - next
            currentIndex.value++;
        } else if (diff < 0 && canSwipePrev.value) {
            // Swipe right - previous
            currentIndex.value--;
        }
    }
}

function goToPrevious() {
    if (canSwipePrev.value) {
        currentIndex.value--;
    }
}

function goToNext() {
    if (canSwipeNext.value) {
        currentIndex.value++;
    }
}

// Navigation
function tambah() {
    router.visit(`/Diklat/create`);
}

// Delete modal
const showModal = ref<boolean>(false);
const selectedId = ref<number | null>(null);

function openModal(id: number) {
    selectedId.value = id;
    showModal.value = true;
}

function destroy(id: number | null) {
    if (id === null) return;

    router.delete(`/Diklat/destroy/${id}`, {
        onSuccess: () => {
            toast.success('Data Berhasil Dihapus');
            showModal.value = false;
            location.reload();
        },
    });
}

const lihatDokumen = (dokumen: string) => {
    window.open(`/storage/${dokumen}`, '_blank');
};
</script>

<template>
    <Head title="Detail Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 md:gap-6 md:p-6">
            <!-- Employee Profile Card -->
            <div
                class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-100 p-5 md:px-6 dark:border-slate-800"
                >
                    <h2
                        class="text-xl font-bold tracking-tight text-slate-800 md:text-2xl dark:text-white"
                    >
                        {{ props.karyawan.nama_karyawan }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Detail Informasi Karyawan
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 p-5 md:p-6 lg:grid-cols-4">
                    <div class="flex flex-col gap-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >NRP</span
                        >
                        <span
                            class="text-sm font-medium text-slate-900 dark:text-slate-200"
                            >{{ props.karyawan.nrp }}</span
                        >
                    </div>
                    <div class="flex flex-col gap-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Unit Kerja</span
                        >
                        <span
                            class="text-sm font-medium text-slate-900 dark:text-slate-200"
                            >{{ props.karyawan.unit_kerja }}</span
                        >
                    </div>
                    <div class="flex flex-col gap-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Bagian</span
                        >
                        <span
                            class="text-sm font-medium text-slate-900 dark:text-slate-200"
                            >{{ props.karyawan.bagian }}</span
                        >
                    </div>
                    <div class="flex flex-col gap-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Klinis/Non</span
                        >
                        <span
                            class="inline-flex w-fit items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                        >
                            {{ props.karyawan.klinis_non_klinis }}
                        </span>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1 lg:col-span-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Jabatan</span
                        >
                        <span
                            class="text-sm font-medium text-slate-900 dark:text-slate-200"
                            >{{ props.karyawan.posisi_jabatan }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Main Section -->
            <div
                class="flex flex-col rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Toolbar -->
                <div
                    class="flex flex-col gap-5 border-b border-slate-100 p-5 md:p-6 dark:border-slate-800"
                >
                    <!-- Progress Info -->
                    <div class="flex flex-col gap-2">
                        <div
                            class="text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Target
                            <span
                                class="font-bold text-slate-900 dark:text-white"
                                >{{ props.kategori }}</span
                            >:
                            <div class="mt-1">
                                <span class="text-lg font-bold">{{
                                    props.totalJam
                                }}</span>
                                / {{ props.target }} Jam
                                <span
                                    :class="
                                        props.percentage >= 100
                                            ? 'text-emerald-600'
                                            : 'text-blue-600'
                                    "
                                    >({{ props.percentage }}%)</span
                                >
                            </div>
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                        >
                            <div
                                class="h-full transition-all duration-1000"
                                :class="
                                    props.percentage >= 100
                                        ? 'bg-emerald-500'
                                        : 'bg-blue-600'
                                "
                                :style="{
                                    width:
                                        Math.min(props.percentage, 100) + '%',
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Actions Bar -->
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1 sm:w-64">
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari diklat..."
                                class="w-full rounded-xl border-slate-200 py-2 pr-4 pl-10 text-sm dark:border-slate-700 dark:bg-slate-800"
                            />
                            <svg
                                class="absolute top-2.5 left-3 h-5 w-5 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="toggleFilters"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700"
                            >
                                <Filter class="h-4 w-4" />
                            </button>

                            <select
                                class="flex-1 rounded-xl border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                onchange="if(this.value) window.location.href=this.value;"
                            >
                                <option value="" disabled selected>
                                    Jadwal...
                                </option>
                                <option value="/JadwalDiklat/Internal">
                                    Internal
                                </option>
                                <option value="/JadwalDiklat/Eksternal">
                                    Eksternal
                                </option>
                                <option value="/JadwalDiklat/HLC">HLC</option>
                            </select>

                            <button
                                @click="tambah"
                                class="flex w-28 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 4v16m8-8H4"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <span class="hidden sm:inline">Tambah</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filters Panel -->
                    <div
                        v-if="showFilters"
                        class="grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800 md:grid-cols-4"
                    >
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">
                                Tanggal Mulai
                            </label>
                            <input
                                v-model="filterDateFrom"
                                type="date"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">
                                Tanggal Selesai
                            </label>
                            <input
                                v-model="filterDateTo"
                                type="date"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">
                                Status
                            </label>
                            <select
                                v-model="filterStatus"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                            >
                                <option value="">Semua</option>
                                <option value="approved">Selesai</option>
                                <option value="menunggu_persetujuan">Berlangsung</option>
                               
                            </select>
                        </div>
                        <!-- <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">
                                Sumber
                            </label>
                            <select
                                v-model="filterSource"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                            >
                                <option value="">Semua</option>
                                <option value="user">User Input</option>
                                <option value="admin">Admin Input</option>
                                <option value="eksternal">Eksternal</option>
                            </select>
                        </div> -->
                        <div class="md:col-span-4">
                            <button
                                @click="resetFilters"
                                class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600"
                            >
                                Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-slate-50 text-slate-500 dark:bg-slate-800/50"
                        >
                            <tr>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase"
                                >
                                    No
                                </th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase"
                                >
                                    Tgl Pelaksanaan
                                </th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase"
                                >
                                    Nama Diklat
                                </th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase"
                                >
                                    Jam
                                </th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-[10px] font-bold uppercase"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="(item, index) in daftarDiklat"
                                :key="item.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/25"
                            >
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td
                                    class="px-6 py-4 font-medium dark:text-slate-200"
                                >
                                    {{ formatDate(item.tanggal_mulai) }} s/d
                                    {{ formatDate(item.tanggal_selesai) }}
                                </td>
                                <td class="px-6 py-4 dark:text-slate-300">
                                    <div
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.display_name}} <button v-if="item.dokumen" @click="lihatDokumen(item.dokumen)" class="text-blue-500 hover:text-blue-700">Lihat Undangan</button>
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ item.pengajar }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded bg-slate-100 px-2 py-1 text-xs font-bold dark:bg-slate-800"
                                        >{{ item.jam_diklat }} Jm</span
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full bg-slate-50 px-2 py-1 text-xs dark:bg-slate-800"
                                        >{{ item.status }}</span
                                    >
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a
                                            v-if="item.file_path"
                                            :href="
                                                route('diklat.preview', item.id)
                                            "
                                            class="rounded-lg p-2 text-blue-600 hover:bg-blue-50"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                    stroke-width="1.5"
                                                />
                                            </svg>
                                        </a>
                                        <a
                                            v-if="item.source === 'user'"
                                            :href="
                                                route('diklat.edit', item.id)
                                            "
                                            class="rounded-lg p-2 text-emerald-600 hover:bg-emerald-50"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    stroke-width="1.5"
                                                />
                                            </svg>
                                        </a>
                                        <button
                                            v-if="item.source === 'user'"
                                            @click="openModal(item.id)"
                                            class="rounded-lg p-2 text-rose-600 hover:bg-rose-50"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    stroke-width="1.5"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Swipe View -->
                <div
                    v-if="daftarDiklat.length > 0"
                    class="md:hidden"
                    @touchstart="handleTouchStart"
                    @touchmove="handleTouchMove"
                    @touchend="handleTouchEnd"
                >
                    <div class="relative p-5">
                        <!-- Swipe Indicator -->
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-500">
                                {{ currentIndex + 1 }} dari {{ daftarDiklat.length }}
                            </span>
                            <div class="flex gap-2">
                                <button
                                    @click="goToPrevious"
                                    :disabled="!canSwipePrev"
                                    class="rounded-lg p-2 disabled:opacity-30"
                                    :class="canSwipePrev ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800' : ''"
                                >
                                    <ChevronLeft class="h-5 w-5" />
                                </button>
                                <button
                                    @click="goToNext"
                                    :disabled="!canSwipeNext"
                                    class="rounded-lg p-2 disabled:opacity-30"
                                    :class="canSwipeNext ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800' : ''"
                                >
                                    <ChevronRight class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Current Diklat Card -->
                        <div
                            v-if="currentDiklat"
                            class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                        >
                            <div class="mb-3 flex items-start justify-between">
                                <span
                                    class="rounded-full bg-indigo-50 px-2 py-1 text-[10px] font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                                >
                                    #{{ currentIndex + 1 }}
                                </span>
                                <div class="flex gap-1">
                                    <a
                                        v-if="currentDiklat.file_path"
                                        :href="route('diklat.preview', currentDiklat.id)"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                stroke-width="2"
                                            />
                                        </svg>
                                    </a>
                                    <a
                                        v-if="currentDiklat.source === 'user'"
                                        :href="route('diklat.edit', currentDiklat.id)"
                                        class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                stroke-width="2"
                                            />
                                        </svg>
                                    </a>
                                    <button
                                        v-if="currentDiklat.source === 'user'"
                                        @click="openModal(currentDiklat.id)"
                                        class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                stroke-width="2"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <h4
                                class="mb-2 text-lg font-bold leading-tight text-slate-900 dark:text-white"
                            >
                                {{ currentDiklat.display_name }}
                            </h4>
                            
                            <p class="mb-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ currentDiklat.pengajar }}
                            </p>

                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-slate-400" />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ formatDate(currentDiklat.tanggal_mulai) }} - {{ formatDate(currentDiklat.tanggal_selesai) }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Durasi:</span>
                                    <span class="rounded bg-slate-100 px-2 py-1 text-xs font-bold dark:bg-slate-700">
                                        {{ currentDiklat.jam_diklat }} Jam
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Status:</span>
                                    <span
                                        class="rounded-full bg-slate-100 px-2 py-1 text-xs dark:bg-slate-700"
                                    >
                                        {{ currentDiklat.status }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Sumber:</span>
                                    <span
                                        class="rounded-full px-2 py-1 text-xs"
                                        :class="{
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': currentDiklat.source === 'user',
                                            'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': currentDiklat.source === 'admin',
                                            'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': currentDiklat.source === 'eksternal',
                                        }"
                                    >
                                        {{ currentDiklat.source === 'user' ? 'User Input' : currentDiklat.source === 'admin' ? 'Admin Input' : 'Eksternal' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Swipe Hint -->
                        <div class="mt-4 text-center text-xs text-slate-400">
                            ← Swipe kiri/kanan untuk navigasi →
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="daftarDiklat.length === 0"
                    class="p-10 text-center text-slate-500"
                >
                    Tidak ada data diklat yang sesuai dengan filter.
                </div>

                <!-- Footer -->
                <div
                    class="rounded-b-2xl border-t border-slate-100 bg-slate-50/50 p-5 dark:border-slate-800 dark:bg-slate-900/50"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <span
                            class="text-center text-xs font-medium text-slate-500 sm:text-left"
                            >Menampilkan {{ daftarDiklat.length }} hasil</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDeleteModal
            :show="showModal"
            @close="showModal = false"
            @confirm="destroy(selectedId)"
        />
    </AppLayout>
</template>