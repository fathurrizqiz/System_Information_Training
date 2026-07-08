<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Diklat Internal', href: '#' },
    { title: 'Aksi Lebih Lanjut', href: '' },
    { title: 'Program ', href: '' },
];

const props = defineProps<{
    periode: any[];
    karyawan: any[];
    detail_id: number;
}>();

const form = ref({
    detail_id: 0,
    tanggal: '',
    nama_pengajar: '',
    tempat: '',
});

// input untuk filter
const search = ref('');
const showDropdown = ref(false);

// filter karyawan berdasarkan input
const filteredKaryawan = computed(() => {
    if (!search.value) return [];
    return props.karyawan.filter((k) =>
        k.nama_karyawan.toLowerCase().includes(search.value.toLowerCase()),
    );
});

// ketika memilih salah satu
function selectPengajar(nama: string) {
    form.value.nama_pengajar = nama;
    search.value = nama;
    showDropdown.value = false;
}

// ----------------------
//  FUNGSI SUBMIT YG BENAR
// ----------------------
function submit() {
    if (!form.value.tanggal || !form.value.nama_pengajar || !form.value.tempat) {
        toast.error('Lengkapi Periode!');
        return;
    }
    if (!form.value.tanggal) {
        toast.error('Lengkapi Tanggal Periode!');
        return;
    }
    if (!form.value.nama_pengajar) {
        toast.error('Lengkapi Nama Pengajar!');
        return;
    }
    if (!form.value.tempat) {
        toast.error('Lengkapi Tempat!');
        return;
    }
    
    router.post(
        '/RencanaDiklat/Internal/detail/periode/store',
        {
            detail_id: props.detail_id,
            tanggal: form.value.tanggal,
            nama_pengajar: form.value.nama_pengajar,
            tempat: form.value.tempat,
        },
        {
            onSuccess: () => toast.success('Berhasil tambah periode'),
            onError: (err) => console.error(err),
        },
    );
}

function deletePeriode(id:number){
    router.delete(`/RencanaDiklat/Internal/detail/periode/delete/${id}`, {
        onSuccess: () =>{
            toast.success('Data Periode Berhasil dihapus')
        }
    })
}

// fungsi untuk sett tanggal hari ini
const today = new Date().toISOString().split("T")[0]
</script>

<template>
    <Head title="Periode Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5">
            <h2 class="mb-5 text-xl font-bold">Periode Pelaksanaan</h2>
            <p>DETAIL ID: {{ props.detail_id }}</p>

            <!-- FORM TAMBAH PERIODE -->
            <div class="mb-8 grid grid-cols-3 gap-5">
                <div>
                    <h4>Tanggal Periode</h4>
                    <Input type="date" v-model="form.tanggal" :min="today" @keydown.prevent />
                </div>
                <div class="relative">
                    <h4>Nama Pengajar</h4>

                    <!-- input -->
                    <Input
                        type="text"
                        v-model="search"
                        @focus="showDropdown = true"
                        @input="showDropdown = true"
                        placeholder="Cari nama karyawan..."
                    />

                    <!-- dropdown -->
                    <div
                        v-if="showDropdown && filteredKaryawan.length"
                        class="absolute z-50 mt-1 w-full rounded border border-gray-300 bg-white shadow"
                    >
                        <div
                            v-for="(k, index) in filteredKaryawan"
                            :key="index"
                            @click="selectPengajar(k.nama_karyawan)"
                            class="cursor-pointer px-3 py-2 hover:bg-blue-100"
                        >
                            <div class="font-medium">{{ k.nama_karyawan }}</div>
                            <div class="text-xs text-gray-500">
                                {{ k.bagian }}
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4>Tempat</h4>
                    <Input type="text" v-model="form.tempat" />
                </div>
            </div>

            <button
                @click="submit"
                class="mb-10 rounded bg-emerald-600 px-5 py-2 text-white hover:bg-emerald-700"
            >
                Tambah Periode
            </button>

            <!-- TABLE PERIODE -->
            <table class="w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs">No.</th>
                        <th class="px-4 py-3 text-left text-xs">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs">Pengajar</th>
                        <th class="px-4 py-3 text-left text-xs">Tempat</th>
                        <th class="px-4 py-3 text-left text-xs">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="(p, index) in props.periode" :key="index">
                        <td class="px-4 py-3">{{ index + 1 }}</td>
                        <td class="px-4 py-3">{{ p.tanggal }}</td>
                        <td class="px-4 py-3">{{ p.nama_pengajar }}</td>
                        <td class="px-4 py-3">{{ p.tempat }}</td>
                        <td class="px-4 py-3">
                            <button @click="deletePeriode(p.id)"
                                class="rounded bg-red-600 px-4 py-1 text-white hover:bg-red-700 cursor-pointer"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
