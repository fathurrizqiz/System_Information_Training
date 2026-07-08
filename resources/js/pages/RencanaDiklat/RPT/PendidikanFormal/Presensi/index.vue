<script setup lang="ts">
import HeaderMenu from '@/components/HeaderMenu.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

const menuItems = [
    { title: 'Datar Bagian', href: '/MateriDiklat/approve' },
    { title: 'Presensi', href: '/MateriDiklat/reject' },
    { title: 'Sertifikat', href: '/MateriDiklat/reject' },
    { title: 'Dokumentasi', href: '/MateriDiklat/reject' },
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
    jam_diklat:number;
    tanggal:string;
}>();
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <HeaderMenu :items="menuItems" />
        <div class="m-10">
            <h1 class="text-xl font-semibold">Presensi kehadiran</h1>
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2"></th>
                        <th class="border px-3 py-2">No</th>
                        <th class="border px-3 py-2">tanggal</th>
                        <th class="border px-3 py-2">nama</th>
                        <th class="border px-3 py-2">NRP</th>
                        <th class="border px-3 py-2">jam</th>
                        <th class="border px-3 py-2">Pre-Score</th>
                        <th class="border px-3 py-2">Post-Score</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(k, index) in karyawan" :key="k.nrp" class="hover:bg-gray-50">
                        <td class="border px-3 py-2">
                            <input type="checkbox" />
                        </td>
                        <td class="border px-3 py-2">{{ index + 1 }}</td>
                        <td class="border px-3 py-2">{{ props.tanggal }}</td>
                        <td class="border px-3 py-2">{{ k.nama_karyawan }}</td>
                        <td class="border px-3 py-2">{{ k.nrp }}</td>
                        <td class="border px-3 py-2">{{ props.jam_diklat }}</td>
                        <td class="border px-3 py-2">{{ k.pre_score }}</td>
                        <td class="border px-3 py-2">{{ k.post_score }}</td>
                        <td class="border px-3 py-2"></td>
                    </tr>

                    <tr>
                        <td colspan="9" class="py-3 text-center text-gray-500">
                            Tidak ada data sesuai filter
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
