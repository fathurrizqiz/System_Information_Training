<script setup lang="ts">
import HeaderMenu from '@/components/HeaderMenu.vue';
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

/* =====================
   MENU
===================== */
const menuItems = [
    { title: 'Daftar Bagian', href: '/MateriDiklat/approve' },
    { title: 'Presensi', href: '/MateriDiklat/reject' },
    { title: 'Sertifikat', href: '/MateriDiklat/reject' },
    { title: 'Dokumentasi', href: '/MateriDiklat/reject' },
];

/* =====================
   INTERFACES
===================== */
interface PeriodePeserta {
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

interface Detail {
    id: number;
    nama_diklat: string;
    keterangan: string;
    pengajar: string;
}

interface PeriodeUtama {
    id: number;
    tanggal: string;
    nama_pengajar: string;
    tempat: string;
}

/* =====================
   PROPS
===================== */
const props = defineProps<{
    detail: Detail;
    periodes: PeriodeUtama[];
    rows: PeriodePeserta[];
    bagians: PeriodePeserta[]; // Ini berisi data distinct dari backend
    selectedPeriodeId?: number | null;
    selectedBagian?: string[];
}>();

/* =====================
   BREADCRUMB
===================== */
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pendidikan Formal', href: '/pendidikan-formal' },
    { title: 'Detail', href: '#' },
    { title: 'Detail Periode', href: '#' },
];

/* =====================
   STATE
===================== */
const selectedPeriodeId = ref<number | null>(props.selectedPeriodeId ?? null);

// Mode pencarian: 'bagian' atau 'pegawai'
const searchMode = ref<'bagian' | 'pegawai'>('bagian');

const search = ref('');
const selectedBagian = ref<string[]>(props.selectedBagian ?? []);
const selectDelete = ref<number[]>([]);

const rows = ref<PeriodePeserta[]>(
    props.rows
        ? Array.isArray(props.rows)
            ? [...props.rows]
            : Object.values(props.rows)
        : [],
);

/* =====================
   WATCH PERIODE & DATA
===================== */
watch(selectedPeriodeId, (id) => {
    if (!id) return;
    router.get(
        route('Detail.periode', props.detail.id),
        {
            periode_id: id,
            bagian: selectedBagian.value.length > 0 ? selectedBagian.value : undefined,
        },
        { preserveState: true, replace: true },
    );
});

watch(
    () => props.rows,
    (newRows) => {
        rows.value = Array.isArray(newRows) ? [...newRows] : Object.values(newRows || {});
    },
    { deep: true },
);

watch(
    () => props.errors,
    (newErrors) => {
        if (newErrors && newErrors.bagian) {
            toast.error(newErrors.bagian);
        }
    },
    { deep: true },
);

/* =====================
   COMPUTED
===================== */

// 1. Ambil daftar unik BAGIAN saja dari props.bagians
const uniqueBagianList = computed(() => {
    const seen = new Set<string>();
    return props.bagians.filter(item => {
        if (!item.bagian || seen.has(item.bagian)) return false;
        seen.add(item.bagian);
        return true;
    });
});

// 2. Ambil daftar unik NAMA KARYAWAN saja dari props.bagians
const uniqueKaryawanList = computed(() => {
    const seen = new Set<string>();
    return props.bagians.filter(item => {
        if (!item.nama_karyawan || seen.has(item.nama_karyawan)) return false;
        seen.add(item.nama_karyawan);
        return true;
    });
});

// 3. Filter hasil pencarian berdasarkan MODE yang aktif
const filteredOptions = computed(() => {
    const keyword = search.value.trim().toLowerCase();
    
    // Tentukan sumber data berdasarkan mode
    const sourceList = searchMode.value === 'bagian' ? uniqueBagianList.value : uniqueKaryawanList.value;

    if (!keyword) {
        return sourceList;
    }

    return sourceList.filter(item => {
        if (searchMode.value === 'bagian') {
            return item.bagian.toLowerCase().includes(keyword);
        } else {
            // Mode pegawai: cari by nama
            return item.nama_karyawan.toLowerCase().includes(keyword);
        }
    });
});

// Filter tabel utama (hanya menampilkan jika sudah dipilih)
const karyawanFiltered = computed(() => {
    // Jika tidak ada filter pilihan, tampilkan semua row yang ada di props (berdasarkan periode)
    if (selectedBagian.value.length === 0) return rows.value;

    // Logika filter: 
    // Karena selectedBagian bisa berisi Nama Bagian ATAU Nama Karyawan,
    // kita cek apakah baris tersebut cocok dengan salah satu pilihan.
    return rows.value.filter((r) => {
        return selectedBagian.value.some(selected => {
            // Cek apakah 'selected' itu nama bagian dari row ini
            if (r.bagian === selected) return true;
            // Cek apakah 'selected' itu nama karyawan dari row ini
            if (r.nama_karyawan === selected) return true;
            return false;
        });
    });
});

/* =====================
   METHODS
===================== */
function toggleSelection(value: string) {
    if (selectedBagian.value.includes(value)) {
        selectedBagian.value = selectedBagian.value.filter((v) => v !== value);
    } else {
        selectedBagian.value.push(value);
    }
}

function switchMode(mode: 'bagian' | 'pegawai') {
    searchMode.value = mode;
    search.value = ''; // Reset search saat ganti mode
    // Opsional: Kosongkan pilihan saat ganti mode agar tidak bingung
    // selectedBagian.value = []; 
}

const form = ref({
    periode_id: null as number | null,
    detail_program_id: props.detail.id,
    bagian: [] as string[],
    nama_karyawan: [] as string[],
});

function store() {
    if (!selectedPeriodeId.value) {
        toast.error('Silakan pilih periode terlebih dahulu');
        return;
    }

    if (selectedBagian.value.length === 0) {
        toast.error('Pilih minimal satu bagian atau satu peserta');
        return;
    }

    form.value.periode_id = selectedPeriodeId.value;
    
    // Kirim data ke backend. 
    // Backend Anda menerima array 'bagian' dan 'nama_karyawan'.
    // Kita kirim semua pilihan ke kedua field tersebut agar aman, 
    // karena backend melakukan query OR (whereIn bagian OR whereIn nama).
    form.value.bagian = selectedBagian.value;
    form.value.nama_karyawan = selectedBagian.value;

    router.post('/DiklatInternal/detailperiod/list/store', form.value, {
        onError: (err) => {
            if (err.bagian) {
                toast.error(err.bagian);
            }
        },
        onSuccess: () => {
            toast.success('Data berhasil disimpan');
            selectedBagian.value = []; 
        },
    });
}

function hapusTerpilih() {
    if (selectDelete.value.length === 0) return;
    if (!confirm('Yakin hapus data terpilih?')) return;

    router.delete('/DiklatInternal/detailperiod/list/delete', {
        data: {
            ids: selectDelete.value,
            periode_id: selectedPeriodeId.value,
        },
        onSuccess: () => {
            rows.value = rows.value.filter(
                (r) => !selectDelete.value.includes(r.id),
            );
            selectDelete.value = [];
            toast.success('Data berhasil dihapus');
        },
    });
}

function manualRefresh() {
    router.get(
        route('Detail.periode', props.detail.id),
        { periode_id: selectedPeriodeId.value },
        {
            preserveState: true,
            replace: true,
            onSuccess: () => {
                console.log('Data berhasil diupdate:', props.rows);
            },
        },
    );
}
</script>

<template>
    <Head title="Detail Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <HeaderMenu :items="menuItems" />

        <div class="m-5">
            <h1 class="text-xl font-bold">Detail Periode</h1>

            <!-- PILIH PERIODE -->
            <div class="m-10">
                <h3 class="mb-2 font-semibold">Periode</h3>
                <select
                    v-model="selectedPeriodeId"
                    class="w-80 rounded border px-3 py-2"
                >
                    <option :value="null" disabled>-- Pilih Periode --</option>
                    <option
                        v-for="p in props.periodes"
                        :key="p.id"
                        :value="p.id"
                    >
                        {{ p.tanggal }} - {{ p.nama_pengajar }} ({{ p.tempat }})
                    </option>
                </select>
            </div>

            <!-- FILTER BAGIAN / PEGAWAI -->
            <div class="m-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Tambah Peserta</h3>
                    
                    <!-- TOGGLE MODE -->
                    <div class="flex bg-gray-200 rounded-lg p-1">
                        <button
                            @click="switchMode('bagian')"
                            :class="[
                                'px-4 py-1 text-sm rounded-md transition-colors',
                                searchMode === 'bagian' 
                                    ? 'bg-white text-blue-600 shadow-sm font-medium' 
                                    : 'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Per Bagian
                        </button>
                        <button
                            @click="switchMode('pegawai')"
                            :class="[
                                'px-4 py-1 text-sm rounded-md transition-colors',
                                searchMode === 'pegawai' 
                                    ? 'bg-white text-blue-600 shadow-sm font-medium' 
                                    : 'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Per Pegawai
                        </button>
                    </div>
                </div>

                <div class="m-2 flex flex-wrap gap-3">
                    <Input
                        v-model="search"
                        type="text"
                        :placeholder="searchMode === 'bagian' ? 'Cari nama bagian...' : 'Cari nama pegawai...'"
                        :disabled="!selectedPeriodeId"
                        class="max-w-xs"
                    />

                    <button
                        class="rounded bg-green-600 px-3 py-2 text-white disabled:opacity-50 hover:bg-green-700 transition"
                        :disabled="!selectedPeriodeId"
                        @click="store"
                    >
                        Simpan Pilihan
                    </button>

                    <button
                        class="rounded bg-blue-600 px-3 py-2 text-white disabled:opacity-50 hover:bg-blue-700 transition"
                        :disabled="!selectedPeriodeId"
                        @click="selectedBagian = []"
                    >
                        Reset Pilihan
                    </button>
                    
                    <button
                        class="flex gap-2 items-center rounded bg-gray-600 px-3 py-2 text-white disabled:opacity-50 hover:bg-gray-700 transition"
                        :disabled="!selectedPeriodeId"
                        @click="manualRefresh"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/></svg>
                        Refresh
                    </button>
                </div>

                <!-- TAG PILIHAN -->
                <div v-if="selectedBagian.length > 0" class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="tag in selectedBagian"
                        :key="tag"
                        class="cursor-pointer rounded bg-blue-100 border border-blue-300 text-blue-800 px-2 py-1 text-sm hover:bg-red-100 hover:border-red-300 hover:text-red-800 transition"
                        @click="toggleSelection(tag)"
                        title="Klik untuk menghapus"
                    >
                        {{ tag }} &times;
                    </span>
                </div>

                <!-- DROPDOWN HASIL PENCARIAN (Dinamis sesuai Mode) -->
                <div class="mt-4 max-h-60 overflow-y-auto border rounded-md bg-white shadow-sm">
                    <div
                        v-for="item in filteredOptions"
                        :key="searchMode === 'bagian' ? item.bagian : item.nrp"
                        class="flex cursor-pointer items-center gap-3 p-3 hover:bg-gray-50 border-b last:border-0"
                        @click="toggleSelection(searchMode === 'bagian' ? item.bagian : item.nama_karyawan)"
                    >
                        <input
                            type="checkbox"
                            :checked="selectedBagian.includes(searchMode === 'bagian' ? item.bagian : item.nama_karyawan)"
                            class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                            @click.stop
                        />

                        <div class="flex-1">
                            <p class="font-medium text-gray-900">
                                {{ searchMode === 'bagian' ? item.bagian : item.nama_karyawan }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ searchMode === 'bagian' ? 'Bagian' : 'NRP: ' + item.nrp }}
                            </p>
                        </div>
                    </div>
                    <div v-if="filteredOptions.length === 0" class="p-4 text-center text-gray-500 text-sm">
                        Tidak ditemukan hasil pencarian.
                    </div>
                </div>
            </div>

            <!-- TABLE KARYAWAN TERPILIH/DATA -->
            <div class="mt-10">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold">Daftar Karyawan Terdaftar</h2>
                    <span class="text-sm text-gray-500">Total: {{ karyawanFiltered.length }}</span>
                </div>

                <button
                    class="m-2 rounded bg-red-600 px-3 py-2 text-white hover:bg-red-800 disabled:opacity-50 transition"
                    :disabled="selectDelete.length === 0"
                    @click="hapusTerpilih"
                >
                    Hapus Terpilih ({{ selectDelete.length }})
                </button>

                <div class="overflow-x-auto border border-gray-300 rounded-lg mt-2">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                            <tr>
                                <th class="border px-3 py-3 w-10"></th>
                                <th class="border px-3 py-3 w-10">No</th>
                                <th class="border px-3 py-3 text-left">Nama</th>
                                <th class="border px-3 py-3 text-left">TMT</th>
                                <th class="border px-3 py-3 text-left">NRP</th>
                                <th class="border px-3 py-3 text-left">Bagian</th>
                                <th class="border px-3 py-3 text-left">Unit Kerja</th>
                                <th class="border px-3 py-3 text-left">Posisi</th>
                                <th class="border px-3 py-3 text-left">K/N</th>
                                <th class="border px-3 py-3 text-left">JK</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="(k, i) in karyawanFiltered"
                                :key="k.nrp"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="border px-3 py-2 text-center">
                                    <input
                                        type="checkbox"
                                        :value="k.id"
                                        v-model="selectDelete"
                                        class="h-4 w-4 text-red-600 rounded border-gray-300 focus:ring-red-500"
                                    />
                                </td>
                                <td class="border px-3 py-2 text-center">{{ i + 1 }}</td>
                                <td class="border px-3 py-2 font-medium">{{ k.nama_karyawan }}</td>
                                <td class="border px-3 py-2">{{ k.tmt }}</td>
                                <td class="border px-3 py-2">{{ k.nrp }}</td>
                                <td class="border px-3 py-2">{{ k.bagian }}</td>
                                <td class="border px-3 py-2">{{ k.unit_kerja }}</td>
                                <td class="border px-3 py-2">{{ k.posisi_jabatan }}</td>
                                <td class="border px-3 py-2">{{ k.klinis_non_klinis }}</td>
                                <td class="border px-3 py-2">{{ k.jenis_kelamin }}</td>
                            </tr>
                            <tr v-if="karyawanFiltered.length === 0">
                                <td
                                    colspan="10"
                                    class="py-8 text-center text-gray-500 italic"
                                >
                                    Belum ada data karyawan untuk periode ini atau filter yang dipilih.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>