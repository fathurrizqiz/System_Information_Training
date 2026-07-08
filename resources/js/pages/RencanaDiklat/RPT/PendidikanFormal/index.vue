<script setup lang="ts">
import HeaderMenu from '@/components/HeaderMenu.vue';
import Input from '@/components/ui/input/Input.vue';
// import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

// --- Type Definitions ---
// We now expect 'details' to be included from the backend
interface DiklatDetail {
    id: number;
    program_id: number | string;
    nama_diklat: string;
    keterangan: string;
    pengajar: string;
    aksi?: {
        id: number;
        jam_diklat: number;
        ended_at: string | null;
    };
}

interface DiklatProgram {
    id: number;
    nama_program: string;
    kategori: string;
    tahun: string;
    details: DiklatDetail[]; // Changed from 'rows' to 'details'
}

const props = defineProps<{
    programs: DiklatProgram[];
}>();

// --- State ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Rencana Program Tahunan', href: '/rencana-diklat' },
    { title: 'Pendidikan Formal', href: '/pendidikan-formal' },
];

// State for the "Add Program" modal
const isProgramModalOpen = ref(false);
const newProgram = ref({
    name: '',
    category: 'Internal',
    customCategory: '',
    year: new Date().getFullYear().toString(),
});

// State for the "Add Detail" modal
const isDetailModalOpen = ref(false);
const currentProgramId = ref<number | null>(null);

// --- Forms ---
// Form for creating a new Program
const programForm = useForm({
    nama_program: '',
    kategori: '',
    tahun: '',
});

// Form for creating a new Program Detail
const detailForm = useForm({
    program_id: 0,
    nama_diklat: '',
    keterangan: '',
    pengajar: '',
});

// --- Methods ---

// --- Program Modal Methods ---
const openAddProgramModal = () => {
    isProgramModalOpen.value = true;
    newProgram.value = {
        name: '',
        category: 'Internal',
        customCategory: '',
        year: new Date().getFullYear().toString(),
    };
};

const closeAddProgramModal = () => {
    isProgramModalOpen.value = false;
};

const addProgram = () => {
    const finalCategory =
        newProgram.value.category === 'Lainnya'
            ? newProgram.value.customCategory
            : newProgram.value.category;

    if (!newProgram.value.name || !finalCategory) {
        toast.error('Nama program dan kategori harus diisi!');
        return;
    }

    programForm.nama_program = newProgram.value.name;
    programForm.kategori = finalCategory;
    programForm.tahun = newProgram.value.year;

    programForm.post('/RencanaDiklat/RPT/PF/store', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Program Berhasil Ditambah!');
            closeAddProgramModal();
            router.reload();
        },
        onError: (errors) => {
            // Display validation errors from the backend
            Object.values(errors).forEach((error) => {
                toast.error(
                    Array.isArray(error) ? error[0] : (error as string),
                );
            });
        },
    });
};

// --- Detail Modal Methods ---
const openDetailModal = (programId: number) => {
    currentProgramId.value = programId;
    detailForm.program_id = programId;
    // Reset form fields in case they were previously filled
    detailForm.nama_diklat = '';
    detailForm.keterangan = '';
    detailForm.pengajar = '';
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    currentProgramId.value = null;
    detailForm.reset(); // Resets form and clears errors
};

const submitDetail = () => {
    console.log('Submit ditekan');

    if (!detailForm.nama_diklat?.trim()) {
        console.log('Nama kosong');
        toast.error('Nama Diklat harus diisi!');
        return;
    }

    detailForm.post('/RencanaDiklat/RPT/PF/DetailStore', {
        onSuccess: () => {
            toast.success('Detail Diklat berhasil ditambahkan!');
            closeDetailModal();
            router.reload();
        },
        onError: () => {
            toast.error('Gagal menambahkan detail diklat.');
        },
    });
};

// --- Other Methods ---
const deleteProgram = (id: number) => {
    if (!id) return;
    const confir = window.confirm(
        'Apakah anda yakin ingin menghapus program ini?',
    );

    if (confir) {
        router.delete(`/RencanaDiklat/RPT/PF/delete/${id}`, {
            onSuccess: () => {
                toast.success('Program Berhasil dihapus!');
                window.location.reload();
            },
        });
    }
};
const deleteDetail = (id: number) => {
    if (!id) return;
    const confir = window.confirm(
        'Apakah anda yakin ingin menghapus program ini?',
    );

    if (confir) {
        router.delete(`/RencanaDiklat/RPT/PF/detail/delete/${id}`, {
            onSuccess: () => {
                toast.success('Program Berhasil dihapus!');
                window.location.reload();
            },
        });
    }
};

// --- Menu ---
const page = usePage();
const auth = page.props.auth;
const rawRole = auth.user?.role || [];
const roles = Array.isArray(rawRole) ? rawRole : [rawRole];

const menuItems = [
    // ...(roles.includes('admin_kesra') || roles.includes('teknis')
    //     ? [{ title: 'Data Lembur', href: '/karyawan' }]
    //     : []),
    // ...(roles.includes('admin_kesra')
    //     ? [{ title: 'Gaji Karyawan', href: '/gaji' }]
    //     : []),
    { title: 'Internal', href: '/RencanaDiklat/RPT/PF' },
    { title: 'Eksternal', href: '/RencanaDiklat/RPT/PN' },
    { title: 'HLC', href: '/HLC/Home/manajemen' },
];

function detail(id: number) {
    router.visit(`/RencanaDiklat/Internal/detail/aksi/${id}`);
}
function periode(id: number) {
    router.visit(`/RencanaDiklat/Internal/detail/periode/${id}`);
}
</script>

<template>
    <Head title="Detail Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <HeaderMenu class="p-10" :items="menuItems" />
        <div
            class="group relative h-10 w-full overflow-hidden rounded-s-lg shadow-lg md:h-32"
        >
            <!-- 1. Background Image -->
            <img
                src="https://img.magnific.com/free-vector/blue-shiny-ornament-floral-leafs-background_1409-4313.jpg?semt=ais_hybrid&w=740&q=80"
                alt="Pendidikan"
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />

            <!-- 2. Overlay (Penting agar teks terbaca di segala jenis gambar) -->
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"
            ></div>

            <!-- 3. Responsive Text Container -->
            <div class="absolute inset-0 flex flex-col justify-end p-6 md:p-10">
                <h1
                    class="text-xl font-bold tracking-tight text-white drop-shadow-lg md:text-3xl"
                >
                    Pendidikan Formal
                </h1>
                <p
                    class="mt-2 translate-y-4 transform text-sm text-gray-200 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 md:text-base"
                >
                    Kelola data riwayat pendidikan dan sertifikasi resmi Anda di
                    sini.
                </p>
            </div>
        </div>
        <div class="p-5">
            <!-- Header with Add Program Button -->
            <div class="mb-6 flex items-center justify-between">
                <button
                    @click="openAddProgramModal"
                    class="flex w-46 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                >
                    <svg
                        class="mr-2 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        ></path>
                    </svg>
                    Tambah Program
                </button>
            </div>

            <!-- Program List -->
            <div v-if="programs.length > 0" class="space-y-8">
                <div
                    v-for="program in programs"
                    :key="program.id"
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-md"
                >
                    <!-- Program Header -->
                    <div
                        class="mb-4 flex flex-col items-start justify-between border-b border-gray-200 pb-4 sm:flex-row sm:items-center"
                    >
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                {{ program.nama_program }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Kategori:
                                <span class="font-medium text-gray-700">{{
                                    program.kategori
                                }}</span>
                                | Tahun:
                                <span class="font-medium text-gray-700">{{
                                    program.tahun
                                }}</span>
                            </p>
                        </div>
                        <div class="mt-4 flex gap-2 sm:mt-0">
                            <!-- Button to open the Add Detail Modal -->
                            <button
                                @click="openDetailModal(program.id)"
                                class="flex w-38 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                            >
                                <svg
                                    class="mr-1 h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    ></path>
                                </svg>
                                Tambah Detail
                            </button>
                            <button
                                @click="deleteProgram(program.id)"
                                class="text-sm font-medium text-red-600 hover:text-red-800"
                            >
                                Hapus Program
                            </button>
                        </div>
                    </div>

                    <!-- Read-Only Details Table -->
                    <div
                        v-if="props.programs && props.programs.length > 0"
                        class="overflow-x-auto"
                    >
                        <table class="w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        No.
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Nama Diklat
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Keterangan
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Pengajar
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="(details, index) in program.details"
                                    :key="details.id"
                                    class="cursor-pointer hover:bg-gray-200"
                                    @click="periode(details.id)"
                                >
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ details.nama_diklat }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ details.keterangan }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ details.pengajar }}
                                    </td>
                                    <td
                                        class="flex gap-3 px-4 py-3 text-sm text-gray-900"
                                    >
                                        <button
                                            @click.stop="detail(details.id)"
                                            class="flex w-20 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                                        >
                                            Aksi
                                        </button>
                                        <button
                                            @click.stop="
                                                deleteDetail(details.id)
                                            "
                                            class="flex w-20 justify-center gap-2 rounded-xl bg-gradient-to-r from-red-600 via-rose-500 to-violet-600 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                                        >
                                            Hapus
                                        </button>
                                        <div v-if="details.aksi">
                                            <span
                                                v-if="!details.aksi.ended_at"
                                                class="inline-flex animate-pulse items-center gap-1 text-[10px] font-bold text-green-600"
                                            >
                                                <span
                                                    class="h-1.5 w-1.5 rounded-full bg-green-600"
                                                ></span>
                                                LIVE: PELATIHAN BERJALAN
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1 text-[10px] font-medium text-gray-400"
                                            >
                                                <svg
                                                    class="h-3 w-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                                SESI SELESAI
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Message if no details exist for the program -->
                    <div v-else class="py-8 text-center text-gray-500">
                        Belum ada detail untuk program ini. Klik "Tambah Detail"
                        untuk menambahkan.
                    </div>
                </div>
            </div>

            <!-- Message if no programs exist -->
            <div v-else class="rounded-xl bg-white py-16 text-center shadow-md">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    ></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    Belum Ada Program Diklat
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Mulai dengan menambah program pendidikan baru.
                </p>
            </div>
        </div>

        <!-- Modal for Adding a New Program -->
        <div
            v-if="isProgramModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div
                class="bg-opacity-75 fixed inset-0 backdrop-blur-md transition-opacity"
                @click="closeAddProgramModal"
            ></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-lg transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Tambah Program Pendidikan Baru
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Nama Program</label
                                >
                                <Input
                                    v-model="newProgram.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="contoh: Diklat Kepemimpinan"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Kategori</label
                                >
                                <Input
                                    v-model="newProgram.category"
                                    value="INTERNAL"
                                    readonly
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Tahun</label
                                >
                                <input
                                    v-model="newProgram.year"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                    >
                        <button
                            @click="addProgram"
                            type="button"
                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Simpan
                        </button>
                        <button
                            @click="closeAddProgramModal"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Adding a New Detail -->
        <div
            v-if="isDetailModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div
                class="bg-opacity-75 fixed inset-0 backdrop-blur-md transition-opacity"
                @click="closeDetailModal"
            ></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-lg transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all"
                >
                    <form @submit.prevent="submitDetail">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Tambah Detail Diklat Baru
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label
                                        for="nama_diklat"
                                        class="block text-sm font-medium text-gray-700"
                                        >Nama Diklat</label
                                    >
                                    <Input
                                        v-model="detailForm.nama_diklat"
                                        id="nama_diklat"
                                        type="text"
                                        
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    />
                                    <p
                                        v-if="detailForm.errors.nama_diklat"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ detailForm.errors.nama_diklat }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="keterangan"
                                        class="block text-sm font-medium text-gray-700"
                                        >Keterangan</label
                                    >
                                    <Input
                                        v-model="detailForm.keterangan"
                                        id="keterangan"
                                        type="text"
                                        
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    />
                                    <p
                                        v-if="detailForm.errors.keterangan"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ detailForm.errors.keterangan }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="pengajar"
                                        class="block text-sm font-medium text-gray-700"
                                        >Pengajar</label
                                    >
                                    <Input
                                        v-model="detailForm.pengajar"
                                        id="pengajar"
                                        type="text"
                                        
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    />
                                    <p
                                        v-if="detailForm.errors.pengajar"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ detailForm.errors.pengajar }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                        >
                            <button
                                type="submit"
                                :disabled="detailForm.processing"
                                class="inline-flex w-full justify-center rounded-md bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Simpan Detail
                            </button>
                            <button
                                type="button"
                                @click="closeDetailModal"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
