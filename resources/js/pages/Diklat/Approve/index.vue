<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Persetujuan',
        href: '#',
    },
];

interface DiklatItem {
    id: number;
    nama_diklat: string;
    diklat: string;
    pengajar: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    jam_diklat: number;
    penyelenggara: string;
    file_path: string | null;
    status: 'pending' | 'approved' | 'rejected';
    alasan_penolakan: string | null;
    link_file?: string | null; 
    nama_karyawan: string | null;
}

const props = defineProps<{
    diklat: DiklatItem[];
    filters: {
        search?: string;
        status?: string;
    };
}>();

const showRejectModal = ref(false);
const rejectReason = ref('');
const currentDiklatId = ref<number | null>(null);

const formatDate = (dateString: string): string => {
    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const approveDiklat = (id: number) => {
    Swal.fire({
        title: 'Apakah anda Yakin?',
        text: 'Ingin menyetujui diklat ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(`/diklat/${id}/approve`, {}, {
                onSuccess: () => {
                    Swal.fire('Berhasil!', 'Diklat disetujui', 'success');
                    location.reload();
                },
            });
        }
    });
};

const openRejectModal = (id: number) => {
    currentDiklatId.value = id;
    showRejectModal.value = true;
    rejectReason.value = '';
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    currentDiklatId.value = null;
    rejectReason.value = '';
};

const submitReject = () => {
    if (!currentDiklatId.value) return;

    router.put(
        `/diklat/${currentDiklatId.value}/reject`,
        { alasan_penolakan: rejectReason.value },
        {
            onSuccess: () => {
                closeRejectModal();
                location.reload();
            },
        },
    );
};

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

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

watch(
    [search, statusFilter],
    debounce(([newSearch, newStatus]) => {
        router.get(
            route('diklat.approve.index'), 
            { search: newSearch || undefined,
                status: newStatus === 'all' ? undefined : newStatus,
             }, // Remove param if empty
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);
</script>

<template>
    <Head title="Persetujuan Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            
            <!-- Page Header: Lebih compact di mobile -->
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight md:text-3xl">
                    Persetujuan Diklat
                </h1>
                <p class="mt-1 text-xs text-slate-500 md:text-sm">
                    Tinjau dan kelola daftar diklat yang menunggu persetujuan Anda.
                </p>
            </div>

            <!-- Toolbar: Stacked & Full Width di mobile -->
            <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-center">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        v-model="search" 
                        placeholder="Cari diklat..." 
                        class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all md:py-2" 
                    />
                    <svg class="absolute left-3 top-3 md:top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div class="flex gap-2">
                    <select 
                        v-model="statusFilter" 
                        class="flex-1 rounded-xl border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all md:w-auto md:py-2"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </div>

            <!-- Card Grid -->
            <div v-if="props.diklat.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div 
                    v-for="item in props.diklat" 
                    :key="item.id"
                    class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200/60 transition-all active:scale-[0.98] md:hover:shadow-md"
                >
                    <!-- Status & Header -->
                    <div class="flex flex-col border-b border-slate-100 p-5">
                        <div class="flex items-start justify-between mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600">
                                {{ item.diklat || '-' }}
                            </span>
                            <div class="shrink-0">
                                <span v-if="item.status === 'approved'" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    DISETUJUI
                                </span>
                                <span v-else-if="item.status === 'rejected'" class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                    DITOLAK
                                </span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                    PENDING
                                </span>
                            </div>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 leading-snug">
                            {{ item.nama_diklat || '-' }}
                        </h3>
                    </div>

                    <div class="flex flex-1 flex-col space-y-4 p-5">
                        <!-- Pengajar Info -->
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold border border-indigo-100">
                                {{ item.nama_karyawan ? item.nama_karyawan.charAt(0).toUpperCase() : '-' }}
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 leading-none mb-1">User</p>
                                <p class="text-sm font-semibold text-slate-800">{{ item.nama_karyawan || '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold border border-indigo-100">
                                {{ item.pengajar ? item.pengajar.charAt(0).toUpperCase() : '-' }}
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 leading-none mb-1">Pengajar</p>
                                <p class="text-sm font-semibold text-slate-800">{{ item.pengajar || '-' }}</p>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-4 border border-slate-100">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Jadwal</p>
                                <p class="text-xs font-bold text-slate-900">{{ formatDate(item.tanggal_mulai) }}</p>
                                <p class="text-[10px] text-slate-500">s/d {{ formatDate(item.tanggal_selesai) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Durasi</p>
                                <p class="text-xs font-bold text-slate-900">{{ item.jam_diklat }} Jam</p>
                                <p class="text-[10px] text-slate-500">Total Kredit</p>
                            </div>
                        </div>

                        <!-- Penyelenggara -->
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Penyelenggara</p>
                            <p class="text-xs font-medium text-slate-700">{{ item.penyelenggara || '-' }}</p>
                        </div>

                        <!-- Alasan Penolakan -->
                        <div v-if="item.status === 'rejected' && item.alasan_penolakan" class="rounded-lg bg-rose-50 p-3 border border-rose-100">
                            <p class="text-[10px] font-bold text-rose-800 uppercase mb-1">Alasan Penolakan:</p>
                            <p class="text-xs text-rose-700 leading-relaxed">{{ item.alasan_penolakan }}</p>
                        </div>
                        <a 
                v-if="item.link_file" 
                :href="item.link_file" 
                target="_blank"
                class="text-blue-600 hover:underline flex items-center gap-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Lihat File
            </a>
                    </div>
                    

                    <!-- Bottom Actions: Full width buttons for mobile -->
                    <div v-if="item.status === 'pending'" class="mt-auto border-t border-slate-100 p-3 bg-slate-50/50">
                        <div class="flex gap-2">
                            <button
                                @click="openRejectModal(item.id)"
                                class="flex-1 rounded-xl px-4 py-2.5 text-xs font-bold text-rose-600 border border-rose-200 bg-white active:bg-rose-50 transition-colors"
                            >
                                TOLAK
                            </button>
                            <button
                                @click="approveDiklat(item.id)"
                                class="flex-[1.5] rounded-xl bg-emerald-500 px-4 py-2.5 text-xs font-bold text-white shadow-sm active:bg-emerald-600 transition-colors"
                            >
                                SETUJUI
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center rounded-3xl bg-white py-20 px-6 text-center border border-slate-100">
                <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 ring-4 ring-slate-50/50">
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Belum ada tugas</h3>
                <p class="mt-2 text-sm text-slate-500 max-w-xs mx-auto">Semua pengajuan diklat sudah diproses. Silakan kembali lagi nanti.</p>
            </div>

            <!-- Modal: Mobile Bottom Sheet Style -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-4"
            >
                <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeRejectModal"></div>

                    <div class="relative w-full max-w-lg transform overflow-hidden rounded-t-3xl sm:rounded-2xl bg-white shadow-2xl transition-all">
                        <!-- Handle bar for mobile feel -->
                        <div class="h-1.5 w-12 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden"></div>

                        <div class="border-b border-slate-100 px-6 py-5">
                            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Penolakan</h3>
                        </div>
                        
                        <form @submit.prevent="submitReject">
                            <div class="px-6 py-6">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Alasan Penolakan</label>
                                <textarea
                                    v-model="rejectReason"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all"
                                    rows="5"
                                    placeholder="Contoh: Kuota sudah penuh atau berkas kurang lengkap..."
                                    required
                                ></textarea>
                            </div>
                            
                            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 bg-slate-50 px-6 py-5 border-t border-slate-100">
                                <button
                                    type="button"
                                    @click="closeRejectModal"
                                    class="w-full sm:w-auto px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors"
                                >
                                    BATAL
                                </button>
                                <button
                                    type="submit"
                                    class="w-full sm:w-auto rounded-xl bg-rose-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-rose-200 active:bg-rose-700 transition-all"
                                >
                                    TOLAK SEKARANG
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>
        </div>
    </AppLayout>
</template>