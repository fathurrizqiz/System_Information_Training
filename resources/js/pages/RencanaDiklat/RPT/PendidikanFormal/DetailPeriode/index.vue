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
    bagians: string[]; 
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
const bagianOptions = ref<string[]>(props.bagians ?? []);

// Tambahkan null check/fallback []
const search = ref('');
const selectedBagian = ref<string[]>(props.selectedBagian ?? []);
const selectDelete = ref<number[]>([]);

const rows = ref<PeriodePeserta[]>(
    props.rows 
        ? (Array.isArray(props.rows) ? [...props.rows] : Object.values(props.rows)) 
        : []
);
/* =====================
   WATCH PERIODE
===================== */
watch(selectedPeriodeId, (id) => {
    if (!id) return;

    router.get(
        route('Detail.periode', props.detail.id),
        {
            periode_id: id,
            bagian: selectedBagian.value.length > 0 ? selectedBagian.value : undefined
        },
        { preserveState: true, replace: true }
    );
});

watch(() => props.rows, (newRows) => {
    // Gunakan Object.values jika ternyata newRows adalah Object
    rows.value = Array.isArray(newRows) ? [...newRows] : Object.values(newRows || {});
}, { deep: true });


watch(() => props.errors, (newErrors) => {
    if (newErrors && newErrors.bagian) {
        toast.error(newErrors.bagian);
    }
}, { deep: true });

/* =====================
   COMPUTED
===================== */
// Gunakan props.bagians sebagai sumber kebenaran
const allBagian = computed(() => props.bagians);

const filteredBagian = computed(() =>
    allBagian.value.filter((b) =>
        b.toLowerCase().includes(search.value.toLowerCase())
    )
);

const karyawanFiltered = computed(() => {
    if (selectedBagian.value.length === 0) return rows.value;
    return rows.value.filter((r) => selectedBagian.value.includes(r.bagian));
});

/* =====================
   METHODS
===================== */
function toggleBagian(bagian: string) {
    if (selectedBagian.value.includes(bagian)) {
        selectedBagian.value = selectedBagian.value.filter(b => b !== bagian);
    } else {
        selectedBagian.value.push(bagian);
    }
}

const form = ref({
    periode_id: null as number | null,
    detail_program_id: props.detail.id,
    bagian: [] as string[],
});


function store() {
    if (!selectedPeriodeId.value) {
        toast.error('Silakan pilih periode terlebih dahulu');
        return;
    }

    if (selectedBagian.value.length === 0) {
        toast.error('Pilih minimal satu bagian');
        return;
    }

    form.value.periode_id = selectedPeriodeId.value;
    form.value.bagian = selectedBagian.value;

    router.post('/DiklatInternal/detailperiod/list/store', form.value, {
        onError: (err) => {
            // Jika Anda ingin toast spesifik dari error validation Laravel
            if (err.bagian) {
                toast.error(err.bagian);
            }
        },
        onSuccess: () => {
            toast.success('Data berhasil disimpan');
            selectedBagian.value = []; // Opsional: kosongkan pilihan setelah berhasil
        }
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
                (r) => !selectDelete.value.includes(r.id)
            );
            selectDelete.value = [];
            toast.success('Data berhasil dihapus');
        },
    });
}

// refresh manual hehe
function manualRefresh() {
    router.get(
        route('Detail.periode', props.detail.id),
        { periode_id: selectedPeriodeId.value },
        { 
            preserveState: true, 
            replace: true,
            onSuccess: () => {
                console.log('Data berhasil diupdate:', props.rows);
            }
        }
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

            <!-- FILTER BAGIAN -->
            <div class="m-10">
                <h3 class="mb-2 font-semibold">Bagian</h3>

                <div class="m-2 flex flex-wrap gap-3">
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Cari bagian"
                        :disabled="!selectedPeriodeId"
                    />

                    <button
                        class="rounded bg-green-600 px-3 py-2 text-white disabled:opacity-50"
                        :disabled="!selectedPeriodeId"
                        @click="store"
                    >
                        Simpan
                    </button>

                    <button
                        class="rounded bg-blue-600 px-3 py-2 text-white disabled:opacity-50"
                        :disabled="!selectedPeriodeId"
                        @click="selectedBagian = [...allBagian]"
                    >
                        Semua Bagian
                    </button>

                    <button
                        class="rounded bg-red-600 px-3 py-2 text-white disabled:opacity-50"
                        :disabled="!selectedPeriodeId"
                        @click="selectedBagian = []"
                    >
                        Hapus Semua
                    </button>
                    <button
                        class="flex gap-3 rounded bg-green-600 px-3 py-2 text-white disabled:opacity-50"
                        :disabled="!selectedPeriodeId"
                        @click="manualRefresh"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-repeat-icon lucide-repeat"><path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/></svg>
                        Refresh
                    </button>
                </div>

                <!-- TAG BAGIAN TERPILIH -->
                <div class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="tag in selectedBagian"
                        :key="tag"
                        class="cursor-pointer rounded bg-blue-200 px-2 py-1 text-sm"
                        @click="toggleBagian(tag)"
                    >
                        {{ tag }} ✕
                    </span>
                </div>

                <!-- DROPDOWN HASIL PENCARIAN -->
                <div
                    v-if="search.length > 0 && selectedPeriodeId"
                    class="mt-2 max-h-48 overflow-auto rounded border bg-white shadow"
                >
                    <div
                        v-for="b in filteredBagian"
                        :key="b"
                        class="flex cursor-pointer items-center gap-2 p-2 hover:bg-gray-100"
                        @click="toggleBagian(b)"
                    >
                        <input
                            type="checkbox"
                            :checked="selectedBagian.includes(b)"
                            @click.stop
                        />
                        {{ b }}
                    </div>
                    <div
                        v-if="filteredBagian.length === 0"
                        class="p-2 text-gray-500"
                    >
                        Tidak ditemukan
                    </div>
                </div>
            </div>

            <!-- TABLE KARYAWAN -->
            <div class="mt-10">
                <h2 class="mb-3 text-lg font-semibold">Daftar Karyawan</h2>

                <button
                    class="m-2 rounded bg-red-600 px-3 py-2 text-white hover:bg-red-800 disabled:opacity-50"
                    :disabled="selectDelete.length === 0"
                    @click="hapusTerpilih"
                >
                    Hapus Terpilih
                </button>

                <table class="min-w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-3 py-2"></th>
                            <th class="border px-3 py-2">No</th>
                            <th class="border px-3 py-2">Nama</th>
                            <th class="border px-3 py-2">TMT</th>
                            <th class="border px-3 py-2">NRP</th>
                            <th class="border px-3 py-2">Bagian</th>
                            <th class="border px-3 py-2">Unit Kerja</th>
                            <th class="border px-3 py-2">Posisi Jabatan</th>
                            <th class="border px-3 py-2">Klinis / Non-Klinis</th>
                            <th class="border px-3 py-2">Jenis Kelamin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(k, i) in karyawanFiltered"
                            :key="k.nrp"
                            class="hover:bg-gray-50"
                        >
                            <td class="border px-3 py-2">
                                <input
                                    type="checkbox"
                                    :value="k.id"
                                    v-model="selectDelete"
                                />
                            </td>
                            <td class="border px-3 py-2">{{ i + 1 }}</td>
                            <td class="border px-3 py-2">{{ k.nama_karyawan }}</td>
                            <td class="border px-3 py-2">{{ k.tmt }}</td>
                            <td class="border px-3 py-2">{{ k.nrp }}</td>
                            <td class="border px-3 py-2">{{ k.bagian }}</td>
                            <td class="border px-3 py-2">{{ k.unit_kerja }}</td>
                            <td class="border px-3 py-2">{{ k.posisi_jabatan }}</td>
                            <td class="border px-3 py-2">{{ k.klinis_non_klinis }}</td>
                            <td class="border px-3 py-2">{{ k.jenis_kelamin }}</td>
                        </tr>
                        <tr v-if="karyawanFiltered.length === 0">
                            <td colspan="10" class="py-4 text-center text-gray-500">
                                Tidak ada data
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>