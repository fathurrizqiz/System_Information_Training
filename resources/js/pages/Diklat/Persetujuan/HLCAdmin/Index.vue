<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

interface Kehadiran {
    id: number;
    tanggal: string;
    status: string;
}

interface HLC {
    id: number;
    nrp: string;
    status: string;
    nama_karyawan: string;
    nama_diklat: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    jam_diklat: number;
    penyelenggara: string;
    status_verifikasi: string;
    catatan_verifikasi: string | null;
    dokumen: string | null;
    bukti_hadir: string | null;
    total_hadir: number;
    total_tidak_hadir: number;
    is_today_absent: string | null;
    karyawan: { bagian: string, nama_karyawan: string } | null;
    kehadiran: Kehadiran[];
}

const props = defineProps<{
    hlc: HLC[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Diklat', href: route('persetujuan.index') },
    { title: 'Persetujuan HLC', href: route('persetujuan.hlc') },
];

const search = ref('');

const filteredData = ref(props.hlc);

const handleSearch = () => {
    if (!search.value) {
        filteredData.value = props.hlc;
        return;
    }
    const q = search.value.toLowerCase();
    filteredData.value = props.hlc.filter(item =>
        item.nama_karyawan.toLowerCase().includes(q) ||
        item.nrp.toLowerCase().includes(q) ||
        (item.nama_diklat && item.nama_diklat.toLowerCase().includes(q))
    );
};

const selectedData = ref<HLC | null>(null);
const isModalOpen = ref(false);

const openModal = (data: HLC) => {
    selectedData.value = data;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedData.value = null;
};

const form = useForm({
    id: null as number | null,
    status_verifikasi: '',
    catatan_verifikasi: '',
   
});

const submitVerification = () => {
    if (!selectedData.value) return;

    form.id = selectedData.value.id;
    form.put(route('konfirmasi.persetujuan.hlc', form.id), {
        onSuccess: () => {
            toast.success('Verifikasi berhasil disimpan');
            closeModal();
            form.reset();
        },
        onError: () => {
            toast.error('Gagal menyimpan verifikasi');
        }
    });
};

const getStatusVerifClass = (status: string | null) => {
    if (status === 'Disetujui') return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
    if (status === 'Ditolak') return 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    return 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
};

const getAbsenStatusClass = (status: string | null) => {
    if (status === 'Hadir') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300';
    if (status === 'Tidak Hadir' || status === 'Izin' || status === 'Sakit') return 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300';
    return 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Persetujuan Diklat HLC" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Verifikasi Diklat HLC</h1>
                
                <div class="relative w-full sm:w-80">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                    <input 
                        type="text" 
                        v-model="search" 
                        @input="handleSearch"
                        placeholder="Cari Nama, NRP, atau Diklat..." 
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    />
                </div>
            </div>

            <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-medium uppercase tracking-wider text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3.5">Karyawan</th>
                                <th class="px-6 py-3.5">Program Diklat</th>
                                <th class="px-6 py-3.5 text-center">Rekap Absen</th>
                                <th class="px-6 py-3.5 text-center">Jam Terlaksana</th>
                                <th class="px-6 py-3.5 text-center">Bukti</th>
                                <th class="px-6 py-3.5 text-center">Status Verifikasi</th>
                                <th class="px-6 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="item in filteredData" :key="item.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/25">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ item.karyawan?.nama_karyawan }}</p>
                                        <p class="text-xs text-slate-400">{{ item.nrp }} &bull; {{ item.karyawan?.bagian }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-900 dark:text-slate-200">
                                        <span v-if="item.nama_diklat" class="inline-block max-w-[70%] truncate">
                                                {{ item.nama_diklat }}
                                        </span> 
                                        <a v-else :href="`/storage/${item.dokumen}`" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Lihat Undangan</a></p>
                                    <p class="text-xs text-slate-400">{{ item.penyelenggara }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="rounded-md bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">H: {{ item.total_hadir }}</span>
                                        <span class="rounded-md bg-red-50 px-2 py-1 text-xs font-bold text-red-600 dark:bg-red-900/30 dark:text-red-400">T: {{ item.total_tidak_hadir }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    
                                    <span class="text-xs text-slate-400">{{ item.jam_diklat }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a v-if="item.bukti_hadir" :href="`/storage/${item.bukti_hadir}`" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                                        Lihat
                                    </a>
                                    <span v-else class="text-xs text-slate-400">Belum ada</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold" :class="getStatusVerifClass(item.status_verifikasi)">
                                        {{ item.status || 'Menunggu' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="openModal(item)" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                        Detail & Verifikasi
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredData.length === 0" class="flex flex-col items-center justify-center gap-2 p-12 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    <p class="text-sm font-medium">Tidak ada data pengajuan.</p>
                </div>
            </div>

            <!-- Modal Detail & Verifikasi -->
            <Teleport to="body">
                <Transition name="fade">
                    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                        
                        <div class="relative z-10 flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                            
                            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Detail & Verifikasi</h2>
                                <button @click="closeModal" class="rounded-lg p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>

                            <div v-if="selectedData" class="flex-1 overflow-y-auto p-6">
                                
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-semibold text-slate-500 uppercase dark:text-slate-400">Data Karyawan</h3>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between"><span class="text-slate-400">Nama</span><span class="font-medium text-slate-900 dark:text-white">{{ selectedData.karyawan?.nama_karyawan }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-400">NRP</span><span class="font-medium text-slate-900 dark:text-white">{{ selectedData.nrp }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-400">Bagian</span><span class="font-medium text-slate-900 dark:text-white">{{ selectedData.karyawan?.bagian || '-' }}</span></div>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-semibold text-slate-500 uppercase dark:text-slate-400">Data Diklat</h3>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between"><span class="text-slate-400">Diklat</span><span class="font-medium text-slate-900 dark:text-white text-right max-w-[60%]">{{ selectedData.nama_diklat }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-400">Penyelenggara</span><span class="font-medium text-slate-900 dark:text-white text-right max-w-[60%]">{{ selectedData.penyelenggara }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-400">Periode</span><span class="font-medium text-slate-900 dark:text-white">{{ formatDate(selectedData.tanggal_mulai) }} - {{ formatDate(selectedData.tanggal_selesai) }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-400">Total Jam</span><span class="font-medium text-slate-900 dark:text-white">{{ selectedData.jam_diklat }} Jam</span></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-slate-500 uppercase dark:text-slate-400">Riwayat Kehadiran</h3>
                                        <div class="flex gap-2 text-xs font-bold">
                                            <span class="text-emerald-600">H: {{ selectedData.total_hadir }}</span>
                                            <span class="text-red-600">T/IZ/S: {{ selectedData.total_tidak_hadir }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                        <table class="w-full text-sm">
                                            <thead class="bg-slate-50 sticky top-0 dark:bg-slate-800/80">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Tanggal</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-slate-500">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                <tr v-for="absen in selectedData.kehadiran" :key="absen.id">
                                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-300">{{ formatDate(absen.tanggal) }}</td>
                                                    <td class="px-4 py-2 text-center">
                                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="getAbsenStatusClass(absen.status)">{{ absen.status }}</span>
                                                    </td>
                                                </tr>
                                                <tr v-if="selectedData.kehadiran.length === 0">
                                                    <td colspan="2" class="px-4 py-3 text-center text-xs text-slate-400">Belum ada data absensi</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div v-if="selectedData.dokumen" class="flex-1">
                                        <h3 class="mb-2 text-sm font-semibold text-slate-500 uppercase dark:text-slate-400">Dokumen Undangan</h3>
                                        <a :href="`/storage/${selectedData.dokumen}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                                            Lihat Dokumen
                                        </a>
                                    </div>
                                    <div v-if="selectedData.bukti_hadir" class="flex-1">
                                        <h3 class="mb-2 text-sm font-semibold text-slate-500 uppercase dark:text-slate-400">Bukti Kehadiran</h3>
                                        <a :href="`/storage/${selectedData.bukti_hadir}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                                            Lihat Bukti
                                        </a>
                                    </div>
                                </div>

                                <div v-if="selectedData.catatan_verifikasi" class="mt-6 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                    <h3 class="mb-1 text-sm font-semibold text-slate-500 uppercase dark:text-slate-400">Catatan Sebelumnya</h3>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ selectedData.catatan_verifikasi }}</p>
                                </div>

                                <form @submit.prevent="submitVerification" class="mt-6 space-y-4 border-t border-slate-200 pt-6 dark:border-slate-800">
                                    <h3 class="text-sm font-semibold text-slate-500 uppercase dark:text-slate-400">Proses Verifikasi</h3>
                                    
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Status Verifikasi</label>
                                        <select v-model="form.status_verifikasi" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                            <option value="" disabled>Pilih Status</option>
                                            <option value="Disetujui">Disetujui</option>
                                            <option value="Ditolak">Ditolak</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Catatan (Opsional)</label>
                                        <textarea v-model="form.catatan_verifikasi" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"></textarea>
                                    </div>
                                </form>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                                <button @click="closeModal" type="button" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Batal
                                </button>
                                <button v-if="selectedData?.status !== 'approved'" @click="submitVerification" :disabled="form.processing || !form.status_verifikasi" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>Simpan Verifikasi</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>