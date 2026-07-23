<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

// Definisi Interface untuk Type Safety
interface KaryawanAutocomplete {
    id: number;
    nama_karyawan: string;
    bagian: string;
    nrp?: string | null;
}

interface NoHpKaryawan {
    id: number;
    nama: string;
    nomor_wa: string;
    email?: string | null;
    bagian?: string | null;
    nrp?: string | null;
}

const props = defineProps<{
    karyawan: KaryawanAutocomplete[];
    noHpKaryawan: NoHpKaryawan[];
}>();

const searchQuery = ref<string>('');
const showDropdown = ref<boolean>(false);

// Form dengan Type Safety dari Inertia
const form = useForm({
    nama: '' as string,
    nomor_wa: '' as string,
    bagian: '' as string,
    nrp: '' as string | null,
    email: '' as string | null,
});

// Logic Autocomplete
const filteredKaryawan = computed(() => {
    if (searchQuery.value === '') return [];
    return props.karyawan.filter(
        (user) =>
            user.nama_karyawan
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            user.bagian
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            user.nrp?.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const selectKaryawan = (user: KaryawanAutocomplete): void => {
    form.nama = user.nama_karyawan;
    form.bagian = user.bagian;
    form.nrp = user.nrp;
    searchQuery.value = user.nama_karyawan;
    showDropdown.value = false;
};

const submit = (): void => {
    form.post(route('nohp.store'), {
        onSuccess: () => {
            toast.success('Data berhasil ditambahkan');
            form.reset();
            searchQuery.value = '';
        },
        onError: () => {
            toast.error('Gagal menambahkan nomor. Pastikan data valid.');
        },
    });
};

const hapusData = (id: number): void => {
    if (confirm('Hapus nomor ini?')) {
        router.delete(route('nohp.destroy', id), {
            onSuccess: () => {
                toast.success('Data berhasil dihapus');
            },
        });
    }
};

function goTemplate() {
    router.get(route('template.index'));
}
</script>

<template>
    <Head title="Manajemen Data Notifikasi" />

    <AppLayout>
        <div class="mx-auto max-w-7xl p-6">
            <h2 class="mb-6 text-2xl font-bold tracking-tight text-gray-800">
                Manajemen Data Notifikasi
            </h2>

            <button
                @click="goTemplate"
                class="m-2 rounded-lg bg-blue-500 px-4 py-2 font-bold text-white transition hover:bg-blue-600"
            >
                Template Pesan WA
            </button>
            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- KOLOM KIRI: TABEL DAFTAR NOMOR -->
                <div
                    class="w-full overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm lg:w-2/3"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-50 bg-gray-50/50 p-4"
                    >
                        <h3 class="font-semibold text-gray-700">
                            Daftar Nomor Terdaftar
                        </h3>
                        <span
                            class="rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-bold text-gray-700"
                        >
                            {{ noHpKaryawan.length }} Orang
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-gray-50 text-sm text-gray-600 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-4 font-semibold">
                                        Karyawan
                                    </th>
                                    <th class="px-6 py-4 font-semibold">
                                        Nomor WhatsApp
                                    </th>
                                    <th class="px-6 py-4 font-semibold">
                                        Email
                                    </th>
                                    <th
                                        class="px-6 py-4 text-center font-semibold"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="item in noHpKaryawan"
                                    :key="item.id"
                                    class="transition hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800">
                                            {{ item.nama }}
                                        </div>
                                        <div
                                            class="text-xs font-medium tracking-wider text-gray-500 uppercase"
                                        >
                                            {{ item.bagian }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full border border-green-100 bg-green-50 px-3 py-1 font-mono text-sm font-medium text-green-700"
                                        >
                                            {{ item.nomor_wa }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full border border-green-100 bg-green-50 px-3 py-1 font-mono text-sm font-medium text-green-700"
                                        >
                                            {{ item.email }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            @click="hapusData(item.id)"
                                            class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="noHpKaryawan.length === 0">
                                    <td
                                        colspan="3"
                                        class="px-6 py-12 text-center text-gray-400"
                                    >
                                        <div class="flex flex-col items-center">
                                            <svg
                                                class="mb-2 h-10 w-10 opacity-20"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor text-gray-400"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                                />
                                            </svg>
                                            <p class="italic">
                                                Belum ada nomor yang
                                                didaftarkan.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- KOLOM KANAN: FORM TAMBAH -->
                <div class="w-full lg:w-1/3">
                    <div
                        class="sticky top-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
                    >
                        <div class="mb-5">
                            <h3 class="font-bold text-gray-700">
                                Tambah Nomor
                            </h3>
                            <p class="text-xs text-gray-500">
                                Pilih nama karyawan untuk registrasi nomor WA.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Autocomplete Nama -->
                            <div class="relative">
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                    >Cari Karyawan</label
                                >
                                <input
                                    v-model="searchQuery"
                                    @focus="showDropdown = true"
                                    type="text"
                                    placeholder="Ketik nama..."
                                    class="w-full rounded-lg border border-gray-200 px-4 py-2.5 transition outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                />

                                <!-- Dropdown Autocomplete -->
                                <div
                                    v-if="
                                        showDropdown &&
                                        filteredKaryawan.length > 0
                                    "
                                    class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-100 bg-white shadow-xl"
                                >
                                    <div
                                        v-for="user in filteredKaryawan"
                                        :key="user.id"
                                        @click="selectKaryawan(user)"
                                        class="cursor-pointer border-b border-gray-50 px-4 py-2.5 text-sm transition last:border-0 hover:bg-green-50"
                                    >
                                        <span
                                            class="font-medium text-gray-700"
                                            >{{ user.nama_karyawan }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Input Nomor WA -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                    >Nomor WA</label
                                >
                                <div class="relative">
                                    <span
                                        class="absolute top-2.5 left-4 font-mono text-sm text-gray-400"
                                        >+</span
                                    >
                                    <input
                                        v-model="form.nomor_wa"
                                        type="tel"
                                        placeholder="628123xxx"
                                        class="w-full rounded-lg border border-gray-200 py-2.5 pr-4 pl-7 font-mono text-sm transition outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.nomor_wa"
                                    class="mt-1.5 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.nomor_wa }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                    >Email</label
                                >
                                <div class="relative">
                                    <span
                                        class="absolute top-2.5 left-4 font-mono text-sm text-gray-400"
                                        >+</span
                                    >
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="...@gmail.com"
                                        class="w-full rounded-lg border border-gray-200 py-2.5 pr-4 pl-7 font-mono text-sm transition outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.email"
                                    class="mt-1.5 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Input Jabatan -->
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                    >Jabatan</label
                                >
                                <input
                                    v-model="form.bagian"
                                    type="text"
                                    placeholder="Contoh: Frontend Developer"
                                    class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm transition outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                    >NRP</label
                                >
                                <input
                                    v-model="form.nrp"
                                    type="text"
                                    placeholder="Contoh: 123456789"
                                    class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm transition outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                />
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="mt-2 w-full rounded-lg bg-gray-900 px-4 py-3 font-bold text-white shadow-lg shadow-gray-200 transition hover:bg-black disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Proses...'
                                        : 'Daftarkan Nomor'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
