<script setup lang="ts">
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Input from '@/components/ui/input/Input.vue';
import { formatDate } from '@/helpers/date';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Calendar, ChevronLeft, ChevronRight, Filter } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

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
        nama_diklat:
            item.program?.nama_diklat ||
            item.nama_diklat ||
            'Tanpa Nama Diklat',
        display_name: item.dokumen
            ? `${item.program?.nama_diklat || item.nama_diklat || 'Tanpa Nama'}`
            : item.program?.nama_diklat ||
              item.nama_diklat ||
              'Tanpa Nama Diklat',
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
const perPage = ref(props.diklat.per_page || 10);

// Swipe state for mobile
const currentIndex = ref(0);
const touchStartX = ref(0);
const touchEndX = ref(0);

const canSwipePrev = computed(() => currentIndex.value > 0);
const canSwipeNext = computed(
    () => currentIndex.value < daftarDiklat.value.length - 1,
);

const currentDiklat = computed(() => {
    return daftarDiklat.value[currentIndex.value] || null;
});

watch(
    () => daftarDiklat.value.length,
    (length) => {
        currentIndex.value = Math.min(
            currentIndex.value,
            Math.max(0, length - 1),
        );
    },
);

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
            per_page: perPage.value,
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

    touchStartX.value = 0;
    touchEndX.value = 0;
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

// Tambahkan helper ini di script setup
const initials = computed(() =>
    props.karyawan.nama_karyawan
        .split(' ')
        .slice(0, 2)
        .map((n) => n[0])
        .join('')
        .toUpperCase(),
);

function statusBadge(status: string) {
    const s = (status || '').toLowerCase().replace(/\s/g, '_');
    if (['approved', 'selesai', 'completed', 'lulus'].includes(s))
        return 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800';
    if (['menunggu_persetujuan', 'pending', 'berlangsung', 'proses'].includes(s))
        return 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:ring-amber-800';
    if (['rejected', 'ditolak', 'gagal'].includes(s))
        return 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:ring-rose-800';
    return 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700';
}

function sourceBadge(source: string) {
    const map: Record<string, string> = {
        user: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800',
        admin:
            'bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:ring-purple-800',
        eksternal:
            'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:ring-orange-800',
    };
    return map[source] ?? map.user;
}

function sourceLabel(source: string) {
    return source === 'user'
        ? 'User Input'
        : source === 'admin'
          ? 'Admin Input'
          : 'Eksternal';
}
</script>

<template>
    <Head title="Detail Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 md:gap-6 md:p-6">
            <!-- ============================================== -->
            <!-- EMPLOYEE PROFILE CARD - dengan header gradient -->
            <!-- ============================================== -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Gradient Accent Bar -->
                <div
                    class="h-1.5 w-full bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400"
                ></div>

                <!-- Header -->
                <div
                    class="flex items-center gap-4 border-b border-slate-100 p-5 md:px-6 dark:border-slate-800"
                >
                    <!-- Avatar Inisial -->
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-500 text-lg font-bold text-white shadow-lg shadow-blue-500/20"
                    >
                        {{ initials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2
                            class="truncate text-lg font-bold tracking-tight text-slate-900 md:text-xl dark:text-white"
                        >
                            {{ props.karyawan.nama_karyawan }}
                        </h2>
                        <p
                            class="truncate text-xs text-slate-500 dark:text-slate-400"
                        >
                            NRP {{ props.karyawan.nrp }} ·
                            {{ genderLabel }}
                        </p>
                    </div>
                </div>

                <!-- Info Grid -->
                <div
                    class="grid grid-cols-2 gap-px bg-slate-100 lg:grid-cols-4 dark:bg-slate-800"
                >
                    <div class="bg-white p-4 dark:bg-slate-900">
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Unit Kerja
                        </p>
                        <p
                            class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-slate-100"
                        >
                            {{ props.karyawan.unit_kerja }}
                        </p>
                    </div>
                    <div class="bg-white p-4 dark:bg-slate-900">
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Bagian
                        </p>
                        <p
                            class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-slate-100"
                        >
                            {{ props.karyawan.bagian }}
                        </p>
                    </div>
                    <div class="bg-white p-4 dark:bg-slate-900">
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Jabatan
                        </p>
                        <p
                            class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-slate-100"
                        >
                            {{ props.karyawan.posisi_jabatan }}
                        </p>
                    </div>
                    <div class="bg-white p-4 dark:bg-slate-900">
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Kategori
                        </p>
                        <span
                            class="mt-1 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-indigo-200 ring-inset dark:bg-indigo-900/30 dark:text-indigo-400 dark:ring-indigo-800"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-indigo-500"
                            ></span>
                            {{ props.karyawan.klinis_non_klinis }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- MAIN SECTION -->
            <!-- ============================================== -->
            <div
                class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- ============================================== -->
                <!-- TOOLBAR -->
                <!-- ============================================== -->
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 p-5 md:p-6 dark:border-slate-800"
                >
                    <!-- PROGRESS ROW -->
                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <div class="flex-1">
                            <div
                                class="mb-1.5 flex items-baseline justify-between"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-400"
                                >
                                    <span
                                        class="text-[10px] font-bold tracking-wider uppercase"
                                        >Target {{ props.kategori }}</span
                                    >
                                </div>
                                <div class="flex items-baseline gap-1">
                                    <span
                                        class="text-lg font-bold text-slate-900 dark:text-white"
                                        >{{ props.totalJam }}</span
                                    >
                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                        >/ {{ props.target }} Jam</span
                                    >
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div
                                class="relative h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :class="
                                        props.percentage >= 100
                                            ? 'bg-gradient-to-r from-emerald-400 to-emerald-500'
                                            : 'bg-gradient-to-r from-blue-600 to-cyan-500'
                                    "
                                    :style="{
                                        width:
                                            Math.min(props.percentage, 100) +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                        <!-- Percentage Badge -->
                        <div
                            class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg"
                                :class="
                                    props.percentage >= 100
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'
                                "
                            >
                                <span class="text-xs font-bold"
                                    >{{ props.percentage }}%</span
                                >
                            </div>
                            <div class="hidden sm:block">
                                <p
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Status
                                </p>
                                <p
                                    class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        props.percentage >= 100
                                            ? 'Tercapai'
                                            : 'Berjalan'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIONS BAR -->
                    <div class="flex flex-col gap-3 lg:flex-row">
                        <!-- Search -->
                        <div class="relative flex-1">
                            <svg
                                class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
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
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari diklat, pengajar, penyelenggara..."
                                class="w-full rounded-xl border-slate-200 py-2.5 pr-4 pl-10 text-sm dark:border-slate-700 dark:bg-slate-800"
                            />
                        </div>

                        <!-- Controls -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Per Page -->
                            <select
                                v-model.number="perPage"
                                @change="applyFilters"
                                aria-label="Jumlah data per halaman"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                <option :value="10">10 / hal</option>
                                <option :value="25">25 / hal</option>
                                <option :value="50">50 / hal</option>
                            </select>

                            <!-- Filter Button -->
                            <button
                                @click="toggleFilters"
                                class="inline-flex items-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-medium shadow-sm transition"
                                :class="
                                    showFilters
                                        ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'
                                "
                            >
                                <Filter class="h-4 w-4" />
                                <span class="hidden sm:inline">Filter</span>
                            </button>

                            <!-- Jadwal Dropdown -->
                            <select
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                onchange="if(this.value) window.location.href=this.value;"
                            >
                                <option value="" disabled selected>
                                    Jadwal...
                                </option>
                                <option value="/JadwalDiklat/Internal">
                                    Internal
                                </option>
                                <option value="/JadwalDiklat/Internal">
                                    Eksternal
                                </option>
                                <option value="/JadwalDiklat/Internal">
                                    HLC
                                </option>
                            </select>

                            <!-- Tambah -->
                            <button
                                @click="tambah"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-500 hover:scale-[1.02] hover:bg-right hover:shadow-xl hover:shadow-blue-500/30"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 4v16m8-8H4"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>

                    <!-- FILTERS PANEL -->
                    <div
                        v-if="showFilters"
                        class="grid gap-4 rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 md:grid-cols-4 dark:border-slate-700 dark:from-slate-800 dark:to-slate-800/50"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Tanggal Mulai</label
                            >
                            <input
                                v-model="filterDateFrom"
                                type="date"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Tanggal Selesai</label
                            >
                            <input
                                v-model="filterDateTo"
                                type="date"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Status</label
                            >
                            <select
                                v-model="filterStatus"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900"
                            >
                                <option value="">Semua</option>
                                <option value="approved">Selesai</option>
                                <option value="menunggu_persetujuan">
                                    Berlangsung
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="resetFilters"
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- DESKTOP TABLE VIEW -->
                <!-- ============================================== -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="sticky top-0 bg-slate-50/80 backdrop-blur dark:bg-slate-800/80"
                        >
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th
                                    class="px-6 py-3.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    No
                                </th>
                                <th
                                    class="px-6 py-3.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    Tgl Pelaksanaan
                                </th>
                                <th
                                    class="px-6 py-3.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    Nama Diklat
                                </th>
                                <th
                                    class="px-6 py-3.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    Jam
                                </th>
                                <th
                                    class="px-6 py-3.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3.5 text-right text-[10px] font-bold tracking-wider text-slate-500 uppercase"
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
                                class="group transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/40"
                            >
                                <td
                                    class="px-6 py-4 text-xs font-medium text-slate-400"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="text-xs font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ formatDate(item.tanggal_mulai) }}
                                    </div>
                                    <div
                                        class="text-[11px] text-slate-400 dark:text-slate-500"
                                    >
                                        s/d
                                        {{ formatDate(item.tanggal_selesai) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ item.display_name }}
                                    </div>
                                    <div
                                        class="mt-1 flex items-center gap-2 text-xs text-slate-500"
                                    >
                                        <span class="truncate">{{
                                            item.pengajar
                                        }}</span>
                                        <button
                                            v-if="item.dokumen"
                                            @click="lihatDokumen(item.dokumen)"
                                            class="shrink-0 rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400"
                                        >
                                            Undangan
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ item.jam_diklat }}
                                        <span
                                            class="ml-0.5 text-[10px] font-medium text-slate-400"
                                            >Jam</span
                                        >
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset"
                                        :class="statusBadge(item.status)"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-current opacity-70"
                                        ></span>
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1 opacity-100 transition group-hover:opacity-100 md:opacity-60"
                                    >
                                        <a
                                            v-if="item.file_path"
                                            :href="
                                                route('diklat.preview', item.id)
                                            "
                                            title="Preview"
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-900/30"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                    stroke-width="1.8"
                                                />
                                            </svg>
                                        </a>
                                        <a
                                            v-if="item.source === 'user'"
                                            :href="route('diklat.edit', item.id)"
                                            title="Edit"
                                            class="rounded-lg p-2 text-emerald-600 transition hover:bg-emerald-50 dark:hover:bg-emerald-900/30"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    stroke-width="1.8"
                                                />
                                            </svg>
                                        </a>
                                        <button
                                            v-if="item.source === 'user'"
                                            @click="openModal(item.id)"
                                            title="Hapus"
                                            class="rounded-lg p-2 text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-900/30"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    stroke-width="1.8"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ============================================== -->
                <!-- MOBILE SWIPE VIEW -->
                <!-- ============================================== -->
                <div
                    v-if="daftarDiklat.length > 0"
                    class="md:hidden"
                    @touchstart="handleTouchStart"
                    @touchmove="handleTouchMove"
                    @touchend="handleTouchEnd"
                >
                    <div class="relative p-4">
                        <!-- Top Bar: Progress dots + Nav -->
                        <div
                            class="mb-4 flex items-center justify-between gap-2"
                        >
                            <!-- Dot Indicators (max 8) -->
                            <div class="flex flex-1 items-center gap-1.5">
                                <span
                                    v-for="(_, i) in daftarDiklat.slice(0, 8)"
                                    :key="i"
                                    class="h-1.5 rounded-full transition-all"
                                    :class="
                                        i === currentIndex
                                            ? 'w-6 bg-blue-600'
                                            : 'w-1.5 bg-slate-300 dark:bg-slate-700'
                                    "
                                ></span>
                                <span
                                    v-if="daftarDiklat.length > 8"
                                    class="ml-1 text-[10px] font-bold text-slate-400"
                                >
                                    +{{ daftarDiklat.length - 8 }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button
                                    @click.stop="goToPrevious"
                                    @touchstart.stop
                                    @touchend.stop
                                    :disabled="!canSwipePrev"
                                    class="rounded-lg bg-slate-100 p-2 text-slate-600 transition disabled:opacity-30 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </button>
                                <button
                                    @click.stop="goToNext"
                                    @touchstart.stop
                                    @touchend.stop
                                    :disabled="!canSwipeNext"
                                    class="rounded-lg bg-slate-100 p-2 text-slate-600 transition disabled:opacity-30 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Card -->
                        <div
                            v-if="currentDiklat"
                            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
                        >
                            <!-- Accent bar per source -->
                            <div
                                class="h-1 w-full"
                                :class="{
                                    'bg-blue-500':
                                        currentDiklat.source === 'user',
                                    'bg-purple-500':
                                        currentDiklat.source === 'admin',
                                    'bg-orange-500':
                                        currentDiklat.source === 'eksternal',
                                }"
                            ></div>

                            <div class="p-5">
                                <!-- Header -->
                                <div
                                    class="mb-4 flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0 flex-1">
                                        <span
                                            class="mb-1 inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold ring-1 ring-inset"
                                            :class="
                                                sourceBadge(
                                                    currentDiklat.source,
                                                )
                                            "
                                        >
                                            {{
                                                sourceLabel(
                                                    currentDiklat.source,
                                                )
                                            }}
                                        </span>
                                        <h4
                                            class="text-base leading-tight font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ currentDiklat.display_name }}
                                        </h4>
                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ currentDiklat.pengajar }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex shrink-0 gap-1">
                                        <a
                                            v-if="currentDiklat.file_path"
                                            :href="
                                                route(
                                                    'diklat.preview',
                                                    currentDiklat.id,
                                                )
                                            "
                                            class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                                        >
                                            <svg
                                                class="h-4 w-4"
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
                                            v-if="
                                                currentDiklat.source === 'user'
                                            "
                                            :href="
                                                route(
                                                    'diklat.edit',
                                                    currentDiklat.id,
                                                )
                                            "
                                            class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                                        >
                                            <svg
                                                class="h-4 w-4"
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
                                            v-if="
                                                currentDiklat.source === 'user'
                                            "
                                            @click="
                                                openModal(currentDiklat.id)
                                            "
                                            class="rounded-lg bg-rose-50 p-2 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400"
                                        >
                                            <svg
                                                class="h-4 w-4"
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

                                <!-- Info -->
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700"
                                        >
                                            <Calendar
                                                class="h-3.5 w-3.5 text-slate-500"
                                            />
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >
                                                Pelaksanaan
                                            </p>
                                            <p
                                                class="truncate text-xs font-semibold text-slate-700 dark:text-slate-300"
                                            >
                                                {{
                                                    formatDate(
                                                        currentDiklat.tanggal_mulai,
                                                    )
                                                }}
                                                -
                                                {{
                                                    formatDate(
                                                        currentDiklat.tanggal_selesai,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div
                                            class="rounded-lg bg-slate-50 p-2.5 dark:bg-slate-900/50"
                                        >
                                            <p
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >
                                                Durasi
                                            </p>
                                            <p
                                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ currentDiklat.jam_diklat }}
                                                <span
                                                    class="text-[10px] font-medium text-slate-400"
                                                    >Jam</span
                                                >
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-lg bg-slate-50 p-2.5 dark:bg-slate-900/50"
                                        >
                                            <p
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                            >
                                                Status
                                            </p>
                                            <span
                                                class="mt-0.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset"
                                                :class="
                                                    statusBadge(
                                                        currentDiklat.status,
                                                    )
                                                "
                                            >
                                                {{ currentDiklat.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Swipe Hint -->
                        <div
                            class="mt-3 text-center text-[10px] font-medium tracking-wide text-slate-400 uppercase"
                        >
                            ← Swipe untuk navigasi →
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- EMPTY STATE -->
                <!-- ============================================== -->
                <div
                    v-if="daftarDiklat.length === 0"
                    class="flex flex-col items-center justify-center p-12 text-center"
                >
                    <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                    >
                        <svg
                            class="h-8 w-8 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <h3
                        class="mb-1 text-sm font-bold text-slate-800 dark:text-slate-200"
                    >
                        Belum ada data diklat
                    </h3>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                        Tidak ada data yang sesuai dengan filter atau pencarian
                        Anda.
                    </p>
                    <button
                        @click="resetFilters"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                    >
                        Reset Filter
                    </button>
                </div>

                <!-- ============================================== -->
                <!-- FOOTER -->
                <!-- ============================================== -->
                <div
                    class="border-t border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/50"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <span
                            class="text-center text-xs font-medium text-slate-500 sm:text-left"
                        >
                            Menampilkan
                            <span
                                class="font-bold text-slate-700 dark:text-slate-300"
                                >{{ daftarDiklat.length }}</span
                            >
                            hasil
                        </span>
                        <div
                            v-if="daftarDiklat.length > 0"
                            class="flex items-center justify-center gap-2"
                        >
                            <button
                                type="button"
                                @click.stop="goToPrevious"
                                @touchstart.stop
                                @touchend.stop
                                :disabled="!canSwipePrev"
                                aria-label="Data sebelumnya"
                                class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </button>
                            <span
                                class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ currentIndex + 1 }} /
                                {{ daftarDiklat.length }}
                            </span>
                            <button
                                type="button"
                                @click.stop="goToNext"
                                @touchstart.stop
                                @touchend.stop
                                :disabled="!canSwipeNext"
                                aria-label="Data berikutnya"
                                class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </button>
                        </div>
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
