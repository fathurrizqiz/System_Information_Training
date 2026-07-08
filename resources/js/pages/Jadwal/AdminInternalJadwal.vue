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
    nama_kegiatan?: string;
    detail: { nama_diklat: string } | null;
    nama_pengajar?: string;
    penyelenggara?: string;
    tempat?: string;
    peserta?: Peserta[];
    hlc?: any[];
    eksternal?: any[];
    kehadiranHariIni?: Kehadiran | null;
}

interface Kehadiran {
    id: number;
    tanggal?: string;
    status?: string;
    jam_masuk?: string;
    jam_keluar?: string;
}

const props = defineProps<{
    jadwalInternal: Jadwal[];
    jadwalHLC: any[];
    jadwalEksternal: any[];
    filters: { search: string };
    templates: any[];
    auth: {
        user: {
            id: number;
            name: string;
            roles: string | string[]; // <-- allow single or multiple roles
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

function goHP() {
    router.get('/NoHP');
}

// panggil whattsapp
const selectedTemplate = ref(
    props.templates.length > 0 ? props.templates[0].slug : '',
);
const kirimNotifikasi = (id: number, tipe: string) => {
    if (!selectedTemplate.value) {
        alert('Silakan pilih template terlebih dahulu!');
        return;
    }

    if (
        confirm(
            `Kirim notifikasi menggunakan template "${selectedTemplate.value}"?`,
        )
    ) {
        router.post(
            route('jadwal.send-wa'),
            {
                id: id,
                tipe: tipe,
                template_slug: selectedTemplate.value,
            },
            {
                onSuccess: () => {
                    toast.success('Notifikasi Berhasil dikirim!');
                },
            },
        );
    }
};

const parseLocalDate = (dateStr: string) => {
    return new Date(dateStr + 'T00:00:00');
};

// helper waktu
const isPelatihanAktif = (tanggalMulai: string, tanggalSelesai: string) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const mulai = parseLocalDate(tanggalMulai);
    mulai.setHours(0, 0, 0, 0);

    const selesai = parseLocalDate(tanggalSelesai);
    selesai.setHours(23, 59, 59, 999);

    return today >= mulai && today <= selesai;
};

// konfirmasi hadir HLC
const konfirmasiHLC = (id: number, status: string) => {
    const action = status === 'Hadir' ? 'Hadir' : 'tolak';
    if (
        confirm(
            `Apakah Anda yakin ingin ${action} keikutsertaan Anda dalam HLC ini?`,
        )
    ) {
        router.post(
            route('diklat.hlc.admin.konfirmasi-hadir', id),
            {
                status: status,
            },
            {
                onSuccess: () => {
                    toast.success(`Anda telah ${action} keikutsertaan Anda!`);
                },
            },
        );
    }
};
const konfirmasiEksternal = (id: number, status: string) => {
    const action = status === 'Hadir' ? 'Hadir' : 'tolak';
    if (
        confirm(
            `Apakah Anda yakin ingin ${action} keikutsertaan Anda dalam Eksternal ini?`,
        )
    ) {
        router.post(
            route('diklat.eksternal.admin.konfirmasi-hadir', id),
            {
                status: status,
            },
            {
                onSuccess: () => {
                    toast.success(`Anda telah ${action} keikutsertaan Anda!`);
                },
            },
        );
    }
};

function history() {
    router.get('/JadwalDiklat/Histori');
}
// role
const rawRole = props.auth.user?.roles || [];
const roles = Array.isArray(rawRole) ? rawRole : [rawRole];

const lihatDokumen = (dokumen: string) => {
    window.open(`/storage/${dokumen}`, '_blank');
};

// helper hari terakhir
const isHariTerakhir = (tanggalSelesai: string) => {
    const today = new Date();

    const selesai = new Date(tanggalSelesai);

    return (
        today.getFullYear() === selesai.getFullYear() &&
        today.getMonth() === selesai.getMonth() &&
        today.getDate() === selesai.getDate()
    );
};

// function open modal upload bukti

const showUploadModal = ref(false);

const selectedPelatihan = ref<any>(null);

const formUpload = ref({
    dokumen: null as File | null,
});

const jenisUpload = ref<'hlc' | 'eksternal'>('eksternal');
// buka modal
function openModalUpload(data: any, type: 'hlc' | 'eksternal') {
    selectedPelatihan.value = data;
    jenisUpload.value = type;
    showUploadModal.value = true;
}

// tutup modal
function closeUploadModal() {
    showUploadModal.value = false;

    formUpload.value.dokumen = null;

    selectedPelatihan.value = null;
}

// ambil file
function handleFileUpload(event: Event) {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        formUpload.value.dokumen = target.files[0];
    }
}

// submit upload
// Eksternal
function submitUpload() {
    if (!formUpload.value.dokumen) {
        toast.error('Silakan upload dokumen terlebih dahulu!');
        return;
    }

    const formData = new FormData();

    formData.append('dokumen', formUpload.value.dokumen);

    router.post(
        route('diklat.eksternal.upload-bukti', selectedPelatihan.value.id),
        formData,
        {
            forceFormData: true,

            onSuccess: () => {
                toast.success('Bukti kehadiran berhasil dikirim!');

                closeUploadModal();
            },
        },
    );
}

// HLC
function submitUploadHLC() {
    if (!formUpload.value.dokumen) {
        toast.error('Silakan upload dokumen terlebih dahulu!');
        return;
    }

    const formData = new FormData();

    formData.append('dokumen', formUpload.value.dokumen);

    router.post(
        route('diklat.hlc.upload-bukti', selectedPelatihan.value.id),
        formData,
        {
            forceFormData: true,

            onSuccess: () => {
                toast.success('Bukti kehadiran berhasil dikirim!');

                closeUploadModal();
            },
        },
    );
}

// cek absen hari ini eksternal
const sudahAbsenHariIni = (eks: any) => {
    return !!eks.kehadiran_hari_ini;
};
// cek absen hari ini hlc
const sudahAbsenHariIniHLC = (hlc: any) => {
    return !!hlc.kehadiran_hari_ini;
};

// Eksternal absen hari ini
const absenHariIni = (eks: any) => {
    router.post(
        route('diklat.eksternal.absen', eks.id),
        {},
        {
            onSuccess: () => {
                toast.success('Absen berhasil!');
            },
        },
    );
};

// HLC absen hari ini
const absenHariIniHLC = (hlc: any) => {
    router.post(
        route('diklat.hlc.absen', hlc.id),
        {},
        {
            onSuccess: () => {
                toast.success('Absen berhasil!');
            },
        },
    );
};
</script>

<template>
    <Head title="Jadwal Diklat Saya" />

    <AppLayout>
        <div class="space-y-4 p-4 md:space-y-6 md:p-6 lg:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-1">
                <h1
                    class="text-xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                >
                    Jadwal Diklat Saya
                </h1>
                <p
                    class="text-xs text-slate-500 md:text-sm dark:text-slate-400"
                >
                    Daftar seluruh pelatihan mendatang yang Anda ikuti.
                </p>
            </div>

            <!-- Search & Tabs Container -->
            <div
                class="sticky top-0 z-10 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:relative dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="relative w-full">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari kegiatan..."
                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 pr-4 pl-10 text-sm focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800"
                    />
                </div>
                <div
                    v-if="roles.includes('admin_diklat')"
                    for="template-select"
                    class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 p-4 md:flex-row md:items-center"
                >
                    <label
                        class="text-xs font-bold tracking-widest text-slate-500 uppercase"
                        >Pilih Template Pesan:</label
                    >
                    <select
                        v-model="selectedTemplate"
                        class="h-9 rounded-lg border-slate-300 bg-white text-sm focus:border-blue-500 focus:ring-blue-500/20 md:w-64"
                    >
                        <option value="" disabled>-- Pilih Template --</option>
                        <option
                            v-for="temp in templates"
                            :key="temp.id"
                            :value="temp.slug"
                        >
                            {{ temp.nama_template }}
                        </option>
                    </select>
                    <p class="text-[10px] text-blue-500 italic">
                        *Pilih template terlebih dahulu sebelum klik 'Umumkan
                        WA'
                    </p>
                </div>
                <!-- Horizontal Tabs: Scrollable on Mobile -->
                <div
                    class="no-scrollbar flex gap-2 overflow-x-auto border-t border-slate-100 pt-3 pb-1 dark:border-slate-800"
                >
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
            <div class="flex gap-3">
                <button
                    @click="goHP"
                    class="flex w-48 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#ffffff"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-smartphone-icon lucide-smartphone"
                    >
                        <rect
                            width="14"
                            height="20"
                            x="5"
                            y="2"
                            rx="2"
                            ry="2"
                        />
                        <path d="M12 18h.01" />
                    </svg>
                    Tambah Nomor HP
                </button>
                <button
                    class="flex w-28 justify-center gap-2 rounded-xl bg-gradient-to-r from-green-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                    @click="history"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#ffffff"
                        stroke-width="1"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-clipboard-clock-icon lucide-clipboard-clock"
                    >
                        <path d="M16 14v2.2l1.6 1" />
                        <path d="M16 4h2a2 2 0 0 1 2 2v.832" />
                        <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2" />
                        <circle cx="16" cy="16" r="6" />
                        <rect x="8" y="2" width="8" height="4" rx="1" />
                    </svg>
                    History
                </button>
            </div>

            <!-- Content Area -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- 1. JADWAL INTERNAL -->
                <div v-if="activeTab === 'internal'">
                    <!-- Desktop Table -->
                    <div class="hidden overflow-x-auto md:block">
                        <table
                            v-if="jadwalInternal.length"
                            class="w-full text-left text-sm"
                        >
                            <thead
                                class="bg-slate-50 text-[10px] font-bold tracking-widest text-slate-500 uppercase dark:bg-slate-800/50"
                            >
                                <tr>
                                    <th class="px-6 py-4">Nama Kegiatan</th>
                                    <th class="px-6 py-4">Pengajar</th>
                                    <th class="px-6 py-4">Lokasi</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="item in jadwalInternal"
                                    :key="item.id"
                                    class="transition-colors hover:bg-slate-50"
                                >
                                    <td
                                        class="px-6 py-4 font-bold text-blue-600"
                                    >
                                        {{ item.detail?.nama_diklat }}
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        {{ item.nama_pengajar }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ item.tempat }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs">
                                        {{ formatDate(item.tanggal!) }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs">
                                        <button
                                            v-if="
                                                roles.includes('admin_diklat')
                                            "
                                            @click="
                                                kirimNotifikasi(
                                                    item.id,
                                                    'internal',
                                                )
                                            "
                                            class="flex items-center gap-2 rounded-lg bg-green-500 px-3 py-1.5 text-xs text-white shadow-sm transition hover:bg-green-600"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="#ffffff"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="lucide lucide-megaphone-icon lucide-megaphone"
                                            >
                                                <path
                                                    d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"
                                                />
                                                <path
                                                    d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14"
                                                />
                                                <path d="M8 6v8" />
                                            </svg>
                                            Umumkan WA
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Card View -->
                    <div
                        class="divide-y divide-slate-100 md:hidden dark:divide-slate-800"
                    >
                        <div
                            v-for="item in jadwalInternal"
                            :key="item.id"
                            class="space-y-3 p-4"
                        >
                            <h4 class="leading-tight font-bold text-blue-600">
                                {{ item.nama_kegiatan }}
                            </h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Pengajar
                                    </p>
                                    <p class="font-medium dark:text-slate-300">
                                        {{ item.nama_pengajar }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Lokasi
                                    </p>
                                    <p class="font-medium dark:text-slate-300">
                                        {{ item.tempat }}
                                    </p>
                                </div>
                                <div
                                    class="col-span-2 border-t border-slate-50 pt-1"
                                >
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Tanggal Pelaksanaan
                                    </p>
                                    <p
                                        class="font-mono text-slate-900 dark:text-white"
                                    >
                                        {{ formatDate(item.tanggal!) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <EmptyState v-if="!jadwalInternal.length" />
                </div>

                <!-- 2. JADWAL HLC (Logika Serupa untuk Mobile Card) -->
                <div v-if="activeTab === 'hlc'">
                    <div class="hidden overflow-x-auto md:block">
                        <table
                            v-if="jadwalHLC.length"
                            class="w-full text-left text-sm"
                        >
                            <thead
                                class="bg-slate-50 text-[10px] font-bold tracking-widest text-slate-500 uppercase dark:bg-slate-800/50"
                            >
                                <tr>
                                    <th class="px-6 py-4 text-emerald-600">
                                        Nama Diklat
                                    </th>
                                    <th class="px-6 py-4">Program</th>
                                    <th class="px-6 py-4">Mulai</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <template
                                    v-for="prog in jadwalHLC"
                                    :key="prog.id"
                                >
                                    <tr
                                        v-for="hlc in prog.hlc"
                                        :key="hlc.id"
                                        class="transition-colors hover:bg-slate-50"
                                    >
                                        <td
                                            class="px-6 py-4 font-bold text-emerald-600"
                                        >
                                            <button
                                                @click="
                                                    lihatDokumen(hlc.dokumen)
                                                "
                                                class="underline"
                                            >
                                                Lihat Dokumen
                                            </button>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-slate-500 italic"
                                        >
                                            {{ prog.nama_program }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ formatDate(hlc.tanggal_mulai) }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            <!-- Pelatihan sedang berlangsung -->
                                            <div
                                                v-if="
                                                    isPelatihanAktif(
                                                        hlc.tanggal_mulai,
                                                        hlc.tanggal_selesai,
                                                    )
                                                "
                                            >
                                                <!-- Hari terakhir dan sudah absen -->
                                                <button
                                                    v-if="
                                                        hlc.status ===
                                                            'Setuju' &&
                                                        isHariTerakhir(
                                                            hlc.tanggal_selesai,
                                                        ) &&
                                                        sudahAbsenHariIniHLC(
                                                            hlc,
                                                        )
                                                    "
                                                    @click="
                                                        openModalUpload(
                                                            hlc,
                                                            'hlc',
                                                        )
                                                    "
                                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-700"
                                                >
                                                    Upload Bukti
                                                </button>

                                                <!-- Masih mengikuti pelatihan -->
                                                <button
                                                    v-else-if="
                                                        hlc.status ===
                                                            'Setuju' &&
                                                        !sudahAbsenHariIniHLC(
                                                            hlc,
                                                        )
                                                    "
                                                    @click="
                                                        absenHariIniHLC(hlc)
                                                    "
                                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
                                                >
                                                    Absen Hari Ini
                                                </button>

                                                <span
                                                    v-else-if="
                                                        hlc.status ===
                                                            'Setuju' &&
                                                        sudahAbsenHariIniHLC(
                                                            hlc,
                                                        )
                                                    "
                                                    class="rounded-full bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700"
                                                >
                                                    Sudah Absen Hari Ini
                                                </span>
                                                <!-- Sudah upload bukti -->
                                                <span
                                                    v-else-if="
                                                        hlc.status === 'Hadir'
                                                    "
                                                    class="rounded-full bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700"
                                                >
                                                    Menunggu Verifikasi
                                                </span>

                                                <!-- Sudah diverifikasi -->
                                                <span
                                                    v-else-if="
                                                        hlc.status ===
                                                        'approved'
                                                    "
                                                    class="rounded-full bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700"
                                                >
                                                    Terverifikasi
                                                </span>

                                                <!-- Ditolak -->
                                                <span
                                                    v-else-if="
                                                        hlc.status ===
                                                        'rejected'
                                                    "
                                                    class="rounded-full bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700"
                                                >
                                                    Ditolak
                                                </span>
                                            </div>

                                            <!-- Belum mulai -->
                                            <div
                                                v-else-if="
                                                    new Date() <
                                                    new Date(
                                                        hlc.tanggal_mulai +
                                                            'T00:00:00',
                                                    )
                                                "
                                            >
                                                <span
                                                    class="rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-500"
                                                >
                                                    Belum Waktunya
                                                </span>
                                            </div>

                                            <!-- Pelatihan selesai -->
                                            <div v-else>
                                                <span
                                                    class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600"
                                                >
                                                    Pelatihan Selesai
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Card View -->
                    <div
                        class="divide-y divide-slate-100 md:hidden dark:divide-slate-800"
                    >
                        <template v-for="prog in jadwalHLC" :key="prog.id">
                            <div
                                v-for="hlc in prog.hlc"
                                :key="hlc.id"
                                class="space-y-2 p-4"
                            >
                                <h4
                                    class="leading-tight font-bold text-emerald-600"
                                >
                                    {{ hlc.nama_diklat }}
                                </h4>
                                <p class="text-[10px] text-slate-500 italic">
                                    {{ prog.nama_program }}
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <svg
                                        class="h-3.5 w-3.5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            stroke-width="2"
                                        />
                                    </svg>
                                    <span class="font-mono text-xs font-bold">{{
                                        formatDate(hlc.tanggal_mulai)
                                    }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <EmptyState v-if="!jadwalHLC.length" />
                </div>

                <!-- 3. JADWAL EKSTERNAL (Logika Serupa untuk Mobile Card) -->
                <div v-if="activeTab === 'eksternal'">
                    <div class="hidden overflow-x-auto md:block">
                        <table
                            v-if="jadwalEksternal.length"
                            class="w-full text-left text-sm"
                        >
                            <thead
                                class="bg-slate-50 text-[10px] font-bold tracking-widest text-slate-500 uppercase dark:bg-slate-800/50"
                            >
                                <tr>
                                    <th class="px-6 py-4 text-purple-600">
                                        Nama Diklat
                                    </th>
                                    <th class="px-6 py-4">Program</th>
                                    <th class="px-6 py-4">Mulai</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <template
                                    v-for="prog in jadwalEksternal"
                                    :key="prog.id"
                                >
                                    <tr
                                        v-for="eks in prog.eksternal"
                                        :key="eks.id"
                                        class="transition-colors hover:bg-slate-50"
                                    >
                                        <td
                                            class="px-6 py-4 font-bold text-purple-600"
                                        >
                                            <button
                                                @click="
                                                    lihatDokumen(eks.dokumen)
                                                "
                                                class="underline"
                                            >
                                                Lihat Dokumen
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">
                                            {{ eks.nama_diklat }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ formatDate(eks.tanggal_mulai) }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            <!-- Pelatihan sedang berlangsung -->
                                            <div
                                                v-if="
                                                    isPelatihanAktif(
                                                        eks.tanggal_mulai,
                                                        eks.tanggal_selesai,
                                                    )
                                                "
                                            >
                                                <!-- Hari terakhir -->
                                                <button
                                                    v-if="
                                                        eks.status ===
                                                            'Setuju' &&
                                                        isHariTerakhir(
                                                            eks.tanggal_selesai,
                                                        ) && 
                                                        sudahAbsenHariIni(eks)
                                                    "
                                                    @click="
                                                        openModalUpload(
                                                            eks,
                                                            'eksternal',
                                                        )
                                                    "
                                                    class="rounded-lg bg-green-500 px-4 py-2 text-white transition hover:bg-green-600"
                                                >
                                                    Upload Bukti
                                                </button>

                                                <!-- Belum absen hari ini -->
                                                <button
                                                    v-else-if="
                                                        eks.status ===
                                                            'Setuju' &&
                                                        !sudahAbsenHariIni(eks)
                                                    "
                                                    @click="absenHariIni(eks)"
                                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
                                                >
                                                    Absen Hari Ini
                                                </button>

                                                <!-- Sudah absen hari ini -->
                                                <span
                                                    v-else-if="
                                                        eks.status ===
                                                            'Setuju' &&
                                                        sudahAbsenHariIni(eks)
                                                    "
                                                    class="rounded-full bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700"
                                                >
                                                    Sudah Absen Hari Ini
                                                </span>

                                                <!-- Sudah upload bukti -->
                                                <span
                                                    v-else-if="
                                                        eks.status === 'Hadir'
                                                    "
                                                    class="rounded-full bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700"
                                                >
                                                    Menunggu Verifikasi
                                                </span>

                                                <!-- Sudah diverifikasi -->
                                                <span
                                                    v-else-if="
                                                        eks.status ===
                                                        'approved'
                                                    "
                                                    class="rounded-full bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700"
                                                >
                                                    Terverifikasi
                                                </span>

                                                <!-- Ditolak -->
                                                <span
                                                    v-else-if="
                                                        eks.status ===
                                                        'rejected'
                                                    "
                                                    class="rounded-full bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700"
                                                >
                                                    Ditolak
                                                </span>
                                            </div>

                                            <!-- Belum mulai -->
                                            <div
                                                v-else-if="
                                                    new Date() <
                                                    new Date(
                                                        eks.tanggal_mulai +
                                                            'T00:00:00',
                                                    )
                                                "
                                            >
                                                <span
                                                    class="rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-500"
                                                >
                                                    Belum Waktunya
                                                </span>
                                            </div>

                                            <!-- Pelatihan selesai -->
                                            <div v-else>
                                                <span
                                                    class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600"
                                                >
                                                    Pelatihan Selesai
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Card View -->
                    <div
                        class="divide-y divide-slate-100 md:hidden dark:divide-slate-800"
                    >
                        <template
                            v-for="prog in jadwalEksternal"
                            :key="prog.id"
                        >
                            <div
                                v-for="eks in prog.eksternal"
                                :key="eks.id"
                                class="space-y-2 p-4"
                            >
                                <h4
                                    class="leading-tight font-bold text-purple-600"
                                >
                                    {{ eks.nama_diklat }}
                                </h4>
                                <div
                                    class="mt-2 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xs font-medium text-slate-500"
                                        >{{ eks.penyelenggara }}</span
                                    >
                                    <span
                                        class="rounded bg-purple-50 px-2 py-0.5 font-mono text-xs font-bold text-purple-600"
                                        >{{
                                            formatDate(eks.tanggal_mulai)
                                        }}</span
                                    >
                                </div>
                            </div>
                        </template>
                    </div>
                    <EmptyState v-if="!jadwalEksternal.length" />
                </div>
            </div>
        </div>

        <!-- Modal Upload Bukti -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900"
            >
                <!-- Header -->
                <div class="mb-5">
                    <h2
                        class="text-lg font-bold text-slate-900 dark:text-white"
                    >
                        Upload Bukti Kehadiran
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Upload file PDF/JPG sebagai bukti mengikuti pelatihan.
                    </p>
                </div>

                <!-- Nama Pelatihan -->
                <div class="mb-4 rounded-xl bg-slate-100 p-3 dark:bg-slate-800">
                    <p
                        class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                    >
                        {{
                            selectedPelatihan?.nama_diklat ||
                            selectedPelatihan?.nama_program
                        }}
                    </p>
                </div>

                <!-- Upload -->
                <div class="mb-6">
                    <label
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        File Bukti
                    </label>

                    <input
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        @change="handleFileUpload"
                        class="block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-white hover:file:bg-blue-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    />

                    <p class="mt-2 text-xs text-slate-400">
                        Format: PDF, JPG, JPEG, PNG • Maks 2MB
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button
                        @click="closeUploadModal"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>

                    <button
                        @click="
                            jenisUpload === 'hlc'
                                ? submitUploadHLC()
                                : submitUpload()
                        "
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                    >
                        Upload Bukti
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Menghilangkan scrollbar pada tab horizontal mobile */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
