<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Datamaster', href: '#' }];

interface Employee {
    id: number;
    nama_karyawan: string;
    tmt: string;
    nrp: string;
    bagian: string;
    unit_kerja: string;
    posisi_jabatan: string;
    klinis_non_klinis: string;
    jenis_kelamin: string;
}

interface Totals {
    totalKaryawan: number;
    byCategory: Record<string, number>;
}

interface TargetJam {
    [key: string]: number; // Dinamis: bisa ada kategori tambahan di DB
}

const props = defineProps<{
    data: Employee[];
    totals: Totals;
    targetJam: TargetJam;
    kategoriList: string[];
}>();

// 📊 State data karyawan
const employees = ref(props.data || []);

// 🔍 State untuk filter
const searchQuery = ref('');
const selectedCategory = ref('Semua');

// 🔽 Opsi kategori filter (tambahkan 'Semua' di awal)
const categoryOptions = computed(() => ['Semua', ...props.kategoriList]);

// 🧮 Filter data karyawan berdasarkan kategori dan pencarian
const filteredEmployees = computed(() => {
    let result = employees.value;

    // Filter pencarian
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (emp) =>
                emp.nama_karyawan.toLowerCase().includes(query) ||
                emp.nrp.toLowerCase().includes(query) ||
                emp.bagian.toLowerCase().includes(query) ||
                emp.unit_kerja.toLowerCase().includes(query) ||
                emp.posisi_jabatan.toLowerCase().includes(query) ||
                emp.klinis_non_klinis.toLowerCase().includes(query),
        );
    }

    // Filter kategori
    if (selectedCategory.value !== 'Semua') {
        result = result.filter(
            (emp) => emp.klinis_non_klinis === selectedCategory.value,
        );
    }

    return result;
});

// 🕒 Format tanggal (TMT)
const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
};

// State untuk modal edit
const editingCategory = ref<string | null>(null);
const newTargetJam = ref<number>(0);

// Fungsi membuka modal edit
const openEditModal = (kategori: string) => {
    editingCategory.value = kategori;
    newTargetJam.value = props.targetJam[kategori] ?? 0;
};

// Fungsi menyimpan perubahan
const saveTargetJam = () => {
    if (!editingCategory.value) return;

    // Kirim ke Laravel via Inertia POST
    router.put(
        '/MasterData/update',
        {
            kategori: editingCategory.value,
            target_jam: newTargetJam.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Jam Berhasil Diperbaharui!');
                const updated = { ...props.targetJam };
                updated[editingCategory.value!] = newTargetJam.value;
                // Karena props tidak bisa diubah langsung, kita perlu cara lain → lihat catatan di bawah
                closeEditModal();
            },
        },
    );
};

// Tutup modal
const closeEditModal = () => {
    editingCategory.value = null;
    newTargetJam.value = 0;
};

// add karyawan
const isAddModalOpen = ref(false);
const isEditing = ref(false); // State untuk penanda mode Edit
const currentEditId = ref<number | null>(null); // Menyimpan ID karyawan yang diedit

const addForm = useForm({
    nama_karyawan: '',
    tmt: '',
    nrp: '',
    bagian: '',
    unit_kerja: '',
    posisi_jabatan: '',
    klinis_non_klinis: '',
    jenis_kelamin: '',
});

// Fungsi Buka Modal Tambah
const openAddModal = () => {
    isEditing.value = false;
    currentEditId.value = null;
    addForm.reset();
    isAddModalOpen.value = true;
};

// Fungsi Buka Modal Edit
const openEditEmployeeModal = (employee: Employee) => {
    isEditing.value = true;
    currentEditId.value = employee.id;

    // Isi form dengan data karyawan yang dipilih
    addForm.nama_karyawan = employee.nama_karyawan;
    addForm.tmt = employee.tmt;
    addForm.nrp = employee.nrp;
    addForm.bagian = employee.bagian;
    addForm.unit_kerja = employee.unit_kerja;
    addForm.posisi_jabatan = employee.posisi_jabatan;
    addForm.klinis_non_klinis = employee.klinis_non_klinis;
    addForm.jenis_kelamin = employee.jenis_kelamin;

    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
    isEditing.value = false;
    addForm.reset();
    addForm.clearErrors();
};

const submitKaryawan = () => {
    if (isEditing.value && currentEditId.value) {
        // Mode Update
        addForm.put(`/MasterData/update-karyawan/${currentEditId.value}`, {
            onSuccess: () => {
                toast.success('Data karyawan berhasil diperbarui!');
                closeAddModal();
            },
        });
    } else {
        // Mode Store (Tambah)
        addForm.post('/MasterData/store', {
            onSuccess: () => {
                toast.success('Karyawan berhasil ditambahkan!');
                closeAddModal();
            },
        });
    }
};

const destroyKaryawan = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
        router.delete(`/MasterData/destroy-karyawan/${id}`, {
            onSuccess: () => {
                toast.success('Karyawan berhasil dihapus!');
            },
            onError: () => {
                toast.error('Gagal menghapus karyawan. Silakan coba lagi.');
            },
        });
    }
};
// rekam jejak
const showModal = ref(false);
const selectedKaryawan = ref<any>(null);

const openHistory = (karyawan: any) => {
    selectedKaryawan.value = karyawan;
    showModal.value = true;
};

const getBadgeClass = (jenis: string) => {
    switch (jenis) {
        case 'Internal':
            return 'bg-purple-100 text-purple-700';
        case 'Internal (Mandiri)':
            return 'bg-indigo-100 text-indigo-700';
        case 'HLC':
            return 'bg-blue-100 text-blue-700';
        case 'Eksternal':
            return 'bg-emerald-100 text-emerald-700';
        default:
            return 'bg-slate-100 text-slate-700';
    }
};
</script>

<template>
    <Head title="Master Data" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 md:p-6 lg:p-8">
            <!-- Page Header -->
            <div
                class="flex flex-col gap-2 border-b border-slate-200 pb-6 dark:border-slate-800"
            >
                <h1
                    class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                >
                    Master Data
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Kelola target jam diklat per kategori dan tinjau database
                    karyawan secara menyeluruh.
                </p>
            </div>

            <!-- ===================================== -->
            <!-- SECTION: Target Jam Diklat            -->
            <!-- ===================================== -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <svg
                        class="h-5 w-5 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>
                    <h2
                        class="text-lg font-bold text-slate-800 dark:text-slate-100"
                    >
                        Target Jam Diklat per Kategori
                    </h2>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Klinis -->
                    <div
                        class="relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Klinis</span
                            >
                            <button
                                @click="openEditModal('KLINIS')"
                                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                title="Edit Target"
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
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-4 flex items-baseline gap-1.5">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                                >{{ targetJam['KLINIS'] }}</span
                            >
                            <span class="text-sm font-medium text-slate-500"
                                >Jam</span
                            >
                        </div>
                    </div>

                    <!-- Non Klinis -->
                    <div
                        class="relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Non Klinis</span
                            >
                            <button
                                @click="openEditModal('NON KLINIS')"
                                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                title="Edit Target"
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
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-4 flex items-baseline gap-1.5">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                                >{{ targetJam['NON KLINIS'] }}</span
                            >
                            <span class="text-sm font-medium text-slate-500"
                                >Jam</span
                            >
                        </div>
                    </div>

                    <!-- Manajerial Klinis -->
                    <div
                        class="relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Manajerial Klinis</span
                            >
                            <button
                                @click="openEditModal('MANAJERIAL KLINIS')"
                                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                title="Edit Target"
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
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-4 flex items-baseline gap-1.5">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                                >{{ targetJam['MANAJERIAL KLINIS'] }}</span
                            >
                            <span class="text-sm font-medium text-slate-500"
                                >Jam</span
                            >
                        </div>
                    </div>

                    <!-- Manajerial Non Klinis -->
                    <div
                        class="relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Manajerial Non Klinis</span
                            >
                            <button
                                @click="openEditModal('MANAJERIAL NON KLINIS')"
                                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                title="Edit Target"
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
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-4 flex items-baseline gap-1.5">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"
                                >{{ targetJam['MANAJERIAL NON KLINIS'] }}</span
                            >
                            <span class="text-sm font-medium text-slate-500"
                                >Jam</span
                            >
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================== -->
            <!-- SECTION: Statistik Karyawan           -->
            <!-- ===================================== -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <svg
                        class="h-5 w-5 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                        ></path>
                    </svg>
                    <h2
                        class="text-lg font-bold text-slate-800 dark:text-slate-100"
                    >
                        Statistik Karyawan
                    </h2>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5"
                >
                    <!-- Total Keseluruhan -->
                    <div
                        class="col-span-2 flex flex-col justify-center rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm md:col-span-1 dark:border-blue-900/50 dark:bg-blue-900/20"
                    >
                        <span
                            class="text-xs font-semibold tracking-wider text-blue-600 uppercase dark:text-blue-400"
                            >Total Karyawan</span
                        >
                        <div
                            class="mt-2 text-4xl font-black text-blue-700 dark:text-blue-300"
                        >
                            {{ totals.totalKaryawan }}
                        </div>
                    </div>

                    <!-- Total by Category -->
                    <div
                        v-for="(total, kategori) in totals.byCategory"
                        :key="kategori"
                        class="flex flex-col justify-center rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <span
                            class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                            >{{ kategori }}</span
                        >
                        <div
                            class="mt-2 text-2xl font-bold text-slate-900 dark:text-white"
                        >
                            {{ total }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================== -->
            <!-- SECTION: Tabel Data Karyawan          -->
            <!-- ===================================== -->
            <section
                class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Toolbar & Filters -->
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800"
                >
                    <h2
                        class="text-lg font-bold text-slate-800 dark:text-white"
                    >
                        Data Karyawan
                    </h2>
                    <button
                        @click="openAddModal"
                        class="flex w-48 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
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
                                d="M12 4v16m8-8H4"
                            ></path>
                        </svg>
                        Tambah Karyawan
                    </button>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <!-- Search Box -->
                        <div class="relative w-full lg:w-80">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Cari nama, NRP, bagian..."
                                class="h-10 w-full rounded-lg border border-slate-300 bg-slate-50 pr-4 pl-10 text-sm transition-colors focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800"
                            />
                            <svg
                                class="absolute top-2.5 left-3 h-5 w-5 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                ></path>
                            </svg>
                        </div>

                        <!-- Filter Kategori -->
                        <div class="w-full sm:w-48">
                            <select
                                v-model="selectedCategory"
                                class="h-10 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 text-sm transition-colors focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800"
                            >
                                <option
                                    v-for="option in categoryOptions"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800"
                    >
                        <thead
                            class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"
                        >
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    Nama
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    NRP
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    Bagian
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    Unit Kerja
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    Posisi Jabatan
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    Kategori
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    TMT
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    L/P
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-4 font-semibold tracking-wider uppercase"
                                >
                                    AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900"
                        >
                            <tr
                                v-for="employee in filteredEmployees"
                                @click="openHistory(employee)"
                                :key="employee.id"
                                class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/25"
                            >
                                <td
                                    class="px-6 py-4 font-medium whitespace-nowrap text-slate-900 dark:text-slate-200"
                                >
                                    {{ employee.nama_karyawan }}
                                </td>
                                <td
                                    class="px-6 py-4 font-mono whitespace-nowrap text-slate-500 dark:text-slate-400"
                                >
                                    {{ employee.nrp }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300"
                                >
                                    {{ employee.bagian }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300"
                                >
                                    {{ employee.unit_kerja }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300"
                                >
                                    {{ employee.posisi_jabatan }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold shadow-sm': true,
                                            'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400':
                                                employee.klinis_non_klinis ===
                                                'Klinis',
                                            'border border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-400':
                                                employee.klinis_non_klinis ===
                                                'Non Klinis',
                                            'border border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400':
                                                employee.klinis_non_klinis ===
                                                'Manajerial Klinis',
                                            'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-400':
                                                employee.klinis_non_klinis ===
                                                'Manajerial Non Klinis',
                                        }"
                                    >
                                        {{ employee.klinis_non_klinis }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300"
                                >
                                    {{ formatDate(employee.tmt) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300"
                                >
                                    {{ employee.jenis_kelamin }}
                                </td>
                                <td
                                    class="flex gap-2 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                >
                                    <button
                                        @click="openEditEmployeeModal(employee)"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        @click="destroyKaryawan(employee.id)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State List -->
                    <div
                        v-if="filteredEmployees.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center"
                    >
                        <svg
                            class="mb-4 h-12 w-12 text-slate-300 dark:text-slate-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            ></path>
                        </svg>
                        <p class="text-slate-500 dark:text-slate-400">
                            Tidak ada data karyawan yang sesuai dengan filter.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <!-- ===================================== -->
        <!-- MODAL: Edit Target Jam                -->
        <!-- ===================================== -->
        <teleport to="body">
            <div
                v-if="editingCategory"
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div
                    class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                    @click="closeEditModal"
                ></div>
                <div
                    class="flex min-h-full items-center justify-center p-4 text-center sm:p-0"
                >
                    <div
                        class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all"
                        @click.stop
                    >
                        <!-- Modal Header -->
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h3 class="text-lg font-bold text-slate-900">
                                Edit Target Jam
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Kategori:
                                <span class="font-semibold text-slate-700">{{
                                    editingCategory
                                }}</span>
                            </p>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-5">
                            <label
                                class="mb-1 block text-sm font-medium text-slate-700"
                                >Target Jam Baru</label
                            >
                            <input
                                v-model.number="newTargetJam"
                                type="number"
                                min="0"
                                class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="Masukkan target jam"
                            />
                        </div>

                        <!-- Modal Actions -->
                        <div
                            class="flex justify-end gap-3 bg-slate-50 px-6 py-4"
                        >
                            <button
                                @click="closeEditModal"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-200"
                            >
                                Batal
                            </button>
                            <button
                                @click="saveTargetJam"
                                class="flex w-48 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>
        <!-- ADD karyawan -->
        <teleport to="body">
            <div
                v-if="isAddModalOpen"
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div
                    class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                    @click="closeAddModal"
                ></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
                        @click.stop
                    >
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h3 class="text-lg font-bold text-slate-900">
                                Tambah Karyawan Baru
                            </h3>
                        </div>

                        <form @submit.prevent="submitKaryawan">
                            <div
                                class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2"
                            >
                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >Nama Lengkap</label
                                    >
                                    <input
                                        v-model="addForm.nama_karyawan"
                                        type="text"
                                        class="mt-1 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        required
                                    />
                                    <p
                                        v-if="addForm.errors.nama_karyawan"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ addForm.errors.nama_karyawan }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >NRP</label
                                    >
                                    <input
                                        v-model="addForm.nrp"
                                        type="text"
                                        class="mt-1 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >TMT (Tanggal Masuk)</label
                                    >
                                    <input
                                        v-model="addForm.tmt"
                                        type="date"
                                        class="mt-1 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >Bagian</label
                                    >
                                    <input
                                        v-model="addForm.bagian"
                                        type="text"
                                        class="mt-1 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >Unit Kerja</label
                                    >
                                    <input
                                        v-model="addForm.unit_kerja"
                                        type="text"
                                        class="mt-1 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >Posisi Jabatan</label
                                    >
                                    <input
                                        v-model="addForm.posisi_jabatan"
                                        type="text"
                                        class="mt-1 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        required
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >Kategori (Klinis/Non)</label
                                    >
                                    <select
                                        v-model="addForm.klinis_non_klinis"
                                        class="mt-1 h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        required
                                    >
                                        <option value="" disabled>
                                            Pilih Kategori
                                        </option>
                                        <option
                                            v-for="cat in kategoriList"
                                            :key="cat"
                                            :value="cat"
                                        >
                                            {{ cat }}
                                        </option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-slate-700"
                                        >Jenis Kelamin</label
                                    >
                                    <div class="mt-2 flex gap-4">
                                        <label
                                            class="flex items-center gap-2 text-sm text-slate-600"
                                        >
                                            <input
                                                type="radio"
                                                v-model="addForm.jenis_kelamin"
                                                value="Laki-laki"
                                                class="text-blue-600 focus:ring-blue-500"
                                            />
                                            Laki-laki
                                        </label>
                                        <label
                                            class="flex items-center gap-2 text-sm text-slate-600"
                                        >
                                            <input
                                                type="radio"
                                                v-model="addForm.jenis_kelamin"
                                                value="Perempuan"
                                                class="text-blue-600 focus:ring-blue-500"
                                            />
                                            Perempuan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex justify-end gap-3 bg-slate-50 px-6 py-4"
                            >
                                <button
                                    type="button"
                                    @click="closeAddModal"
                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-200"
                                >
                                    Batal
                                </button>
                                <h3 class="text-lg font-bold text-slate-900">
                                    {{
                                        isEditing
                                            ? 'Edit Data Karyawan'
                                            : 'Tambah Karyawan Baru'
                                    }}
                                </h3>

                                <button
                                    type="submit"
                                    :disabled="addForm.processing"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{
                                        addForm.processing
                                            ? 'Menyimpan...'
                                            : isEditing
                                              ? 'Simpan Perubahan'
                                              : 'Simpan Karyawan'
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </teleport>
        <!-- Modal Component -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            {{ selectedKaryawan?.nama_karyawan }}
                        </h2>
                        <p class="text-sm text-slate-500">
                            NRP: {{ selectedKaryawan?.nrp }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-blue-600">
                            {{ selectedKaryawan?.total_jam_diklat }}
                        </div>
                        <div
                            class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                        >
                            Total Jam Akumulasi
                        </div>
                    </div>
                </div>

                <div
                    class="max-h-[400px] overflow-y-auto rounded-xl border border-slate-100"
                >
                    <table class="w-full text-xs">
                        <thead class="sticky top-0 bg-slate-50">
                            <tr
                                class="text-[9px] font-bold text-slate-500 uppercase"
                            >
                                <th class="px-4 py-3 text-left">Pelatihan</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-center">Jam</th>
                                <th class="px-4 py-3 text-right">Sumber</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr
                                v-for="jejak in selectedKaryawan?.rekam_jejak"
                                :key="jejak.id"
                                class="hover:bg-slate-50/50"
                            >
                                <td
                                    class="px-4 py-3 font-semibold text-slate-700"
                                >
                                    {{ jejak.nama_diklat }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ formatDate(jejak.tanggal_mulai || jejak.tanggal) }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold">
                                    {{ jejak.jam }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span :class="getBadgeClass(jejak.jenis)">
                                        {{ jejak.jenis }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!selectedKaryawan?.rekam_jejak?.length">
                                <td
                                    colspan="4"
                                    class="py-8 text-center text-slate-400 italic"
                                >
                                    Belum ada rekam jejak diklat yang disetujui.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
