<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Laporan Diklat', href: 'laporan.diklat' },
];

interface Filters {
    months: number[];
    year: number;
    bagian: string;
}

interface TargetKategori {
    kategori: string;
    totalKaryawan: number;
    targetPerOrang: number;
    totalTargetJam: number;
    aktualJam: number;
    persentase: number;
    karyawans: KaryawanDetail[];
}

interface DetailRiwayat {
    id: number;
    tanggal: string;
    nama_diklat: string;
    jam: number;
}

interface KaryawanDetail {
    nrp: string;
    nama: string;
    aktual: number;
    target: number;
    persentase: number;
    detail_diklat?: DetailRiwayat[];
}

const props = defineProps<{
    filters: Filters;
    totalPerKategori: TargetKategori[];
    totalJamDiklat: number;
    targetAll: number;
}>();

// Daftar bulan untuk Checkbox
const listBulan = [
    { id: 1, nama: 'Januari' },
    { id: 2, nama: 'Februari' },
    { id: 3, nama: 'Maret' },
    { id: 4, nama: 'April' },
    { id: 5, nama: 'Mei' },
    { id: 6, nama: 'Juni' },
    { id: 7, nama: 'Juli' },
    { id: 8, nama: 'Agustus' },
    { id: 9, nama: 'September' },
    { id: 10, nama: 'Oktober' },
    { id: 11, nama: 'November' },
    { id: 12, nama: 'Desember' },
];

// State untuk menyimpan pilihan checkbox
const selectedMonths = ref<number[]>(props.filters.months);

// Action untuk memicu reload data ke Backend
const applyFilter = () => {
    if (selectedMonths.value.length === 0) {
        alert('Pilih minimal 1 bulan!');
        return;
    }

    // Ganti route url ini dengan name route kamu
    router.get(
        route('laporan.diklat'),
        {
            months: selectedMonths.value,
            // year: props.filters.year,
            bagian: searchBagian.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Helper periode
const teksPeriode = computed(() => {
    if (selectedMonths.value.length === 0) return 'Pilih Bulan';
    const namaBulanTerpilih = selectedMonths.value
        .sort((a, b) => a - b) // Urutkan bulan dari awal ke akhir
        .map((m) => listBulan.find((b) => b.id === m)?.nama)
        .join(', ');

    return `${namaBulanTerpilih} ${props.filters.year}`;
});

const persentaseTotal = computed(() => {
    if (props.targetAll <= 0) return 0;
    return ((props.totalJamDiklat / props.targetAll) * 100).toFixed(2);
});

// const selectedMonths = ref<number[]>(props.filters.months);
const searchBagian = ref<string>(props.filters.bagian || '');

// grafik
const chartSeries = computed(() => {
    return [
        {
            name: 'Target Jam',
            data: props.totalPerKategori.map((item) => item.totalTargetJam),
        },
        {
            name: 'Aktual Jam',
            data: props.totalPerKategori.map((item) => item.aktualJam),
        },
    ];
});

// Opsi tampilan grafik
const chartOptions = computed(() => {
    return {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: false }, // Sembunyikan menu download bawaan chart agar bersih
            fontFamily: 'inherit',
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 4, // Bikin ujung batangnya agak membulat (Tailwind style)
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent'],
        },
        xaxis: {
            categories: props.totalPerKategori.map((item) => item.kategori), // Label bawah ambil dari nama bagian
            labels: {
                style: { colors: '#6B7280' },
            },
        },
        yaxis: {
            title: { text: 'Total Jam', style: { color: '#6B7280' } },
        },
        fill: { opacity: 1 },
        colors: ['#E5E7EB', '#2563EB'], // Warna: Abu-abu (Target), Biru (Aktual)
        tooltip: {
            y: {
                formatter: function (val: number) {
                    return val + ' Jam';
                },
            },
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
        },
    };
});

const expandedRows = ref<string[]>([]);

const expandedKaryawan = ref<string | null>(null);

const toggleRow = (kategori: string) => {
    if (expandedRows.value.includes(kategori)) {
        expandedRows.value = expandedRows.value.filter((i) => i !== kategori);
    } else {
        expandedRows.value.push(kategori);
    }
};

// generate excel
function generateExcel() {
    // Kita buat query string dari filter yang ada (contoh: selectedMonths)
    // Jika route kamu butuh parameter, masukkan ke dalam URL-nya
    const url = route('laporan.diklat.export', {
        months: props.filters.months, // Sesuaikan dengan cara kamu menyimpan state bulan
    });

    // Panggil langsung via browser, bukan via router Inertia
    window.location.href = url;
}
</script>

<template>
    <Head title="Laporan Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6 py-6 sm:px-6 lg:px-8">
            <div class="flex justify-end gap-3 sm:w-auto">
                <input
                    type="text"
                    v-model="searchBagian"
                    @keyup.enter="applyFilter"
                    placeholder="Cari nama bagian..."
                    class="w-full justify-between rounded-md border-gray-300 p-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-64"
                />
                <button
                    @click="applyFilter"
                    class="flex w-28 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                >
                    Terapkan
                </button>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Laporan Diklat Karyawan
                    </h2>
                    <p class="mt-1 text-gray-500">
                        Periode:
                        <span class="font-medium text-gray-700">{{
                            teksPeriode
                        }}</span>
                    </p>
                </div>
            </div>

            <!-- AREA FILTER BULAN -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <h3 class="font-semibold text-gray-800">Filter Bulan</h3>
                    <div class="flex gap-3">

                        <button
                            @click="applyFilter"
                            class="flex w-36 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                        >
                            Terapkan Filter
                        </button>
                        <button
                            @click="generateExcel"
                            class="flex w-36 justify-center gap-2 rounded-xl bg-gradient-to-r from-green-600 via-green-800 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                        >
                            Unduh Excel
                        </button>
                    </div>
                </div>
                <div
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6"
                >
                    <label
                        v-for="bulan in listBulan"
                        :key="bulan.id"
                        class="flex cursor-pointer items-center space-x-2 rounded-lg border border-transparent p-2 transition-all select-none hover:border-gray-200 hover:bg-gray-50"
                    >
                        <input
                            type="checkbox"
                            :value="bulan.id"
                            v-model="selectedMonths"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-700">{{
                            bulan.nama
                        }}</span>
                    </label>
                </div>
                <p
                    v-if="selectedMonths.length === 0"
                    class="mt-2 text-xs text-red-500"
                >
                    Setidaknya satu bulan harus dipilih.
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Card Target -->
                <div
                    class="flex flex-col justify-center rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
                >
                    <span class="text-sm font-medium text-gray-500"
                        >Total Target Jam (Periode Terpilih)</span
                    >
                    <span class="mt-2 text-3xl font-bold text-gray-800"
                        >{{ targetAll }}
                        <span class="text-base font-normal text-gray-500"
                            >Jam</span
                        ></span
                    >
                </div>

                <!-- Card Aktual -->
                <div
                    class="flex flex-col justify-center rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
                >
                    <span class="text-sm font-medium text-gray-500"
                        >Total Aktual Jam (Periode Terpilih)</span
                    >
                    <span class="mt-2 text-3xl font-bold text-blue-600"
                        >{{ totalJamDiklat }}
                        <span class="text-base font-normal text-gray-500"
                            >Jam</span
                        ></span
                    >
                </div>

                <!-- Card Persentase -->
                <div
                    class="flex flex-col justify-center rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
                >
                    <span class="text-sm font-medium text-gray-500"
                        >Pencapaian Total</span
                    >
                    <div class="mt-2 flex items-end gap-2">
                        <span class="text-3xl font-bold text-emerald-600"
                            >{{ persentaseTotal }}%</span
                        >
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
                v-if="totalPerKategori.length > 0"
            >
                <h3 class="mb-2 font-semibold text-gray-800">
                    Statistik Target vs Aktual Per Bagian
                </h3>
                <!-- Render komponen grafiknya di sini -->
                <VueApexCharts
                    type="bar"
                    height="350"
                    :options="chartOptions"
                    :series="chartSeries"
                />
            </div>

            <!-- Tabel Detail Per Bagian (Sama seperti sebelumnya) -->
            <div
                class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <h3 class="font-semibold text-gray-800">
                        Rincian Per Bagian & Karyawan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead
                            class="bg-gray-50 text-xs text-gray-500 uppercase"
                        >
                            <tr>
                                <th class="px-6 py-4">Bagian / Kategori</th>
                                <th class="px-6 py-4 text-center">
                                    Total Karyawan
                                </th>
                                <th class="px-6 py-4 text-right">
                                    Total Target
                                </th>
                                <th class="px-6 py-4 text-right">Aktual Jam</th>
                                <th class="px-6 py-4">Pencapaian</th>
                            </tr>
                        </thead>
                        <tbody
                            v-for="(item, index) in totalPerKategori"
                            :key="index"
                            class="border-b border-gray-100 last:border-0"
                        >
                            <tr
                                @click="toggleRow(item.kategori)"
                                class="cursor-pointer transition-colors hover:bg-gray-50/80"
                                :class="{
                                    'bg-blue-50/30': expandedRows.includes(
                                        item.kategori,
                                    ),
                                }"
                            >
                                <td
                                    class="flex items-center gap-2 px-6 py-4 font-bold text-gray-800"
                                >
                                    <span
                                        class="text-gray-400 transition-transform duration-200"
                                        :class="{
                                            'rotate-90': expandedRows.includes(
                                                item.kategori,
                                            ),
                                        }"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                    {{ item.kategori }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ item.totalKaryawan }} Orang
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{ item.totalTargetJam }} Jam
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-semibold text-blue-600"
                                >
                                    {{ item.aktualJam }} Jam
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-2 w-full rounded-full bg-gray-200"
                                        >
                                            <div
                                                class="h-2 rounded-full bg-emerald-500"
                                                :style="{
                                                    width:
                                                        Math.min(
                                                            item.persentase,
                                                            100,
                                                        ) + '%',
                                                }"
                                            ></div>
                                        </div>
                                        <span
                                            class="min-w-[3rem] text-xs font-bold"
                                            >{{ item.persentase }}%</span
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="expandedRows.includes(item.kategori)">
                                <td colspan="5" class="bg-gray-50/50 px-8 py-4">
                                    <div
                                        class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
                                    >
                                        <table class="w-full text-xs">
                                            <thead
                                                class="bg-gray-100 text-gray-600 uppercase"
                                            >
                                                <tr>
                                                    <th
                                                        class="px-4 py-2 text-left font-semibold"
                                                    >
                                                        NRP / Nama Karyawan
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-right font-semibold"
                                                    >
                                                        Target
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-right font-semibold"
                                                    >
                                                        Aktual
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-center font-semibold"
                                                    >
                                                        Pencapaian
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                v-for="karyawan in item.karyawans"
                                                :key="karyawan.nrp"
                                            >
                                                <tr
                                                    @click="
                                                        toggleKaryawan(
                                                            karyawan.nrp,
                                                        )
                                                    "
                                                    class="cursor-pointer border-b transition-colors last:border-0 hover:bg-blue-50/50"
                                                >
                                                    <td
                                                        class="px-4 py-3 font-medium text-gray-700"
                                                    >
                                                        <div
                                                            class="flex items-center gap-2"
                                                        >
                                                            <div
                                                                class="h-1.5 w-1.5 rounded-full bg-blue-400"
                                                            ></div>
                                                            <span
                                                                class="font-mono text-blue-600 underline"
                                                                >{{
                                                                    karyawan.nrp
                                                                }}</span
                                                            >
                                                            <span>-</span>
                                                            <span>{{
                                                                karyawan.nama
                                                            }}</span>
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-4 py-3 text-right"
                                                    >
                                                        {{ karyawan.target }}
                                                        Jam
                                                    </td>
                                                    <td
                                                        class="px-4 py-3 text-right font-bold text-blue-600"
                                                    >
                                                        {{ karyawan.aktual }}
                                                        Jam
                                                    </td>
                                                    <td
                                                        class="px-4 py-3 text-center"
                                                    >
                                                        <span
                                                            :class="[
                                                                'inline-block rounded-full px-2 py-0.5 text-[10px] font-bold uppercase',
                                                                karyawan.persentase >=
                                                                100
                                                                    ? 'bg-emerald-100 text-emerald-700'
                                                                    : 'bg-orange-100 text-orange-700',
                                                            ]"
                                                        >
                                                            {{
                                                                karyawan.persentase
                                                            }}%
                                                        </span>
                                                    </td>
                                                </tr>

                                                <tr
                                                    v-if="
                                                        expandedKaryawan ===
                                                        karyawan.nrp
                                                    "
                                                    class="bg-blue-50/20"
                                                >
                                                    <td
                                                        colspan="4"
                                                        class="px-6 py-4"
                                                    >
                                                        <div
                                                            class="rounded-lg border border-blue-100 bg-white p-4 shadow-inner"
                                                        >
                                                            <h4
                                                                class="mb-3 flex items-center gap-2 text-xs font-bold text-blue-800 uppercase"
                                                            >
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-4 w-4"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke="currentColor"
                                                                >
                                                                    <path
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                                                    />
                                                                </svg>
                                                                Riwayat Diklat
                                                                Periode Terpilih
                                                            </h4>
                                                            <div
                                                                v-if="
                                                                    karyawan.detail_diklat &&
                                                                    karyawan
                                                                        .detail_diklat
                                                                        .length >
                                                                        0
                                                                "
                                                                class="space-y-2"
                                                            >
                                                                <div
                                                                    v-for="diklat in karyawan.detail_diklat"
                                                                    :key="
                                                                        diklat.id
                                                                    "
                                                                    class="flex justify-between border-b border-blue-50 pb-2 last:border-0 last:pb-0"
                                                                >
                                                                    <div>
                                                                        <p
                                                                            class="text-[11px] font-semibold text-gray-800"
                                                                        >
                                                                            {{
                                                                                diklat.nama_diklat
                                                                            }}
                                                                        </p>
                                                                        <p
                                                                            class="text-[10px] text-gray-500"
                                                                        >
                                                                            Periode:
                                                                            {{
                                                                                diklat.tanggal
                                                                            }}
                                                                        </p>
                                                                    </div>
                                                                    <div
                                                                        class="text-right"
                                                                    >
                                                                        <p
                                                                            class="text-[11px] font-bold text-blue-600"
                                                                        >
                                                                            {{
                                                                                diklat.jam
                                                                            }}
                                                                            Jam
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div
                                                                v-else
                                                                class="py-4 text-center text-gray-400 italic"
                                                            >
                                                                Tidak ada
                                                                riwayat diklat
                                                                pada periode
                                                                ini.
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>

                        <tbody v-if="totalPerKategori.length === 0">
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center text-gray-400"
                                >
                                    <div class="flex flex-col items-center">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="mb-2 h-10 w-10 opacity-20"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        Tidak ada data diklat untuk bagian/bulan
                                        terpilih.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
