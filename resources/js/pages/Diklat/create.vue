<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { toast } from 'vue3-toastify';
// import diklat from '@/routes/diklat';
import Input from '@/components/ui/input/Input.vue';

interface Karyawan {
    id: number;
    nama_karyawan: string;
    nrp: string | number;
}

// Menerima data karyawan dari controller
const props = defineProps<{
    karyawan: Karyawan[];
}>();

// State untuk form data
const form = reactive({
    tanggal_mulai: '',
    tanggal_selesai: '',
    nama_diklat: '',
    pengajar: '',
    jam_diklat: '',
    diklat: '',
    evaluasimateri: '',
    evaluasipengajar: '',
    penyelenggara: '',
    file: null as File | null, // Tambahkan properti untuk menampung objek file
});

// Fungsi untuk submit form
function submit() {
    router.post(route('diklat.store'), form, {
        onSuccess: () => {
            toast.success('Data Berhasil Disimpan!');
        },
        onError: (errors) => {
            toast.error(
                'Data Gagal Disimpan!, Pastikan semua kolom terisi dengan benar.',
                errors,
            );
        },
    });
}

// fungsi untuk sett tanggal hari ini
const today = new Date().toISOString().split('T')[0];
</script>

<template>
    <Head title="Tambah Diklat" />

    <div class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow">
        <h1 class="mb-6 text-2xl font-bold">Tambah Data Diklat Baru</h1>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Field Tanggal -->
            <div>
                <label
                    for="tanggal"
                    class="block text-sm font-medium text-gray-700"
                    >Tanggal Mulai</label
                >
                <Input
                    id="tanggal"
                    type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.tanggal_mulai"
                    :min="form.tanggal_mulai || today"
                    @keydown.prevent
                />
            </div>
            <div>
                <label
                    for="tanggal"
                    class="block text-sm font-medium text-gray-700"
                    >Tanggal Selesai</label
                >
                <Input
                    id="tanggal"
                    type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.tanggal_selesai"
                    :min="form.tanggal_mulai || today"
                    @keydown.prevent
                />
            </div>

            <!-- Field Nama Diklat -->
            <div>
                <label
                    for="nama_diklat"
                    class="block text-sm font-medium text-gray-700"
                    >Nama Diklat</label
                >
                <Input
                    id="nama_diklat"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.nama_diklat"
                    placeholder="Contoh: Pelatihan Keselamatan"
                />
            </div>
            <div>
                <label
                    for="diklat"
                    class="block text-sm font-medium text-gray-700"
                    >Diklat</label
                >
                <select name="diklat" v-model="form.diklat">
                    <option value="HLC">HLC</option>
                    <option value="EKSTERNAL">Eksternal</option>
                </select>
            </div>

            <!-- Field Pengajar (Autocomplete Sederhana) -->
            <div>
                <label
                    for="pengajar"
                    class="block text-sm font-medium text-gray-700"
                    >Pengajar</label
                >
                <Input
                    id="pengajar"
                    type="text"
                    list="karyawan-list"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.pengajar"
                    placeholder="Ketik nama pengajar..."
                />
                <datalist id="karyawan-list">
                    <option
                        v-for="k in karyawan"
                        :key="k.id"
                        :value="k.nama_karyawan"
                    ></option>
                </datalist>
            </div>

            <!-- Field Penyelenggara -->
            <div>
                <label
                    for="penyelenggara"
                    class="block text-sm font-medium text-gray-700"
                    >Penyelenggara</label
                >
                <Input
                    id="penyelenggara"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.penyelenggara"
                    placeholder="Contoh: PT. Safety Indonesia"
                />
            </div>

            <!-- Field Keterangan -->
            <div>
                <label
                    for="keterangan"
                    class="block text-sm font-medium text-gray-700"
                    >Jam Diklat</label
                >
                <Input
                    id="keterangan"
                    rows="3"
                    type="number"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.jam_diklat"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Evaluasi Materi</label
                >
                <Input
                    id="keterangan"
                    rows="3"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.evaluasimateri"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"
                    >Evaluasi Pengajar</label
                >
                <Input
                    id="keterangan"
                    rows="3"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.evaluasipengajar"
                />
            </div>

            <div>
                <label
                    for="file"
                    class="block text-sm font-medium text-gray-700"
                    >Upload Sertifikat</label
                >
                <input
                    id="file"
                    type="file"
                    @input="
                        form.file = (
                            $event.target as HTMLInputElement
                        ).files?.[0]
                    "
                    accept=".pdf"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <p class="mt-1 text-xs text-gray-500">
                    File harus berformat PDF. Maksimal ukuran 2MB.
                </p>
                <!-- Tampilkan nama file yang dipilih -->
                <p v-if="form.file" class="mt-2 text-sm text-gray-700">
                    File dipilih: {{ form.file.name }}
                </p>
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="flex w-28 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                >
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</template>
