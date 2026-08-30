<script setup lang="ts">
import HeaderMenu from '@/components/HeaderMenu.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

const menuItems = [
    { title: 'Materi Diklat', href: '/MateriDiklat/approve' }, // Sesuaikan route-nya nanti
    { title: 'Presensi', href: '#' }, // Aktif
    { title: 'Sertifikat', href: '/MateriDiklat/sertifikat' }, // Sesuaikan route-nya nanti
    { title: 'Dokumentasi', href: '/MateriDiklat/dokumentasi' }, // Sesuaikan route-nya nanti
];

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pendidikan Formal', href: '/pendidikan-formal' },
    { title: 'Detail', href: '#' },
    { title: 'Detail Periode', href: '#' },
];

interface Karyawan {
    nrp: string;
    nama_karyawan: string;
    pre_score: number;
    post_score: number;
}

const props = defineProps<{
    karyawan: Karyawan[];
    jam_diklat: number;
    tanggal: string;
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <HeaderMenu :items="menuItems" />
        
        <div class="m-6 md:m-10">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Presensi & Nilai Peserta</h1>
                    <p class="text-sm text-gray-500">
                        Tanggal: <span class="font-semibold text-gray-700">{{ props.tanggal }}</span> | 
                        Durasi: <span class="font-semibold text-gray-700">{{ props.jam_diklat }} Jam</span>
                    </p>
                </div>
                <!-- Tombol Opsional: Misalnya Export ke Excel -->
                <!-- <button class="rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                    Export Excel
                </button> -->
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <tr>
                            <th class="border-b border-gray-200 px-4 py-3 text-center">No</th>
                            <th class="border-b border-gray-200 px-4 py-3">Nama Peserta</th>
                            <th class="border-b border-gray-200 px-4 py-3">NRP</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-center">Pre-Test</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-center">Post-Test</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-center">Status</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        <!-- LOOPING DATA -->
                        <tr v-for="(k, index) in karyawan" :key="k.nrp" class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-gray-600">{{ index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ k.nama_karyawan }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ k.nrp }}</td>
                            
                            <!-- Skor dengan pewarnaan kondisional -->
                            <td class="px-4 py-3 text-center">
                                <span :class="k.pre_score >= 70 ? 'text-green-600 font-semibold' : 'text-red-500'">
                                    {{ k.pre_score }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="k.post_score >= 70 ? 'text-green-600 font-semibold' : 'text-red-500'">
                                    {{ k.post_score }}
                                </span>
                            </td>
                            
                            <!-- Status Kelulusan Sederhana (Opsional) -->
                            <td class="px-4 py-3 text-center">
                                <span v-if="k.post_score >= 70" class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                    Lulus
                                </span>
                                <span v-else class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                    Remedial
                                </span>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="px-4 py-3 text-center">
                                <button class="rounded bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-100">
                                    Detail
                                </button>
                            </td>
                        </tr>

                        <!-- EMPTY STATE (Hanya muncul jika data kosong) -->
                        <tr v-if="karyawan.length === 0">
                            <td colspan="7" class="py-10 text-center text-gray-500">
                                <p class="text-lg font-medium">Belum ada data</p>
                                <p class="text-sm">Belum ada peserta yang mengisi test pada periode ini.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>