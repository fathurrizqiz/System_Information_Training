<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { toast } from 'vue3-toastify';

interface Diklat {
    id: number;
    tanggal_mulai: number;
    tanggal_selesai: number;
    nama_diklat: string;
    pengajar: string;
    jam_diklat: number;
    diklat: string;
    file_path: string;
    penyelenggara: string;
    evaluasimateri: string;
    evaluasipengajar: string;
}

const props = defineProps<{
    edit: Diklat; // Diubah menjadi object, bukan array
    karyawanList?: any[]; // Prop opsional jika Anda melempar list karyawan untuk datalist pengajar
}>();

// HELPER 
const formatDate = (dateValue: string | number | null | undefined): string => {
    if (!dateValue) return '';
    
    const str = String(dateValue);
    
    if (str.length >= 10 && /^\d{4}-\d{2}-\d{2}/.test(str)) {
        return str.substring(0, 10);
    }
    
    const d = new Date(dateValue);
    if (isNaN(d.getTime())) return '';
    
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
};
const form = ref({
    id: props.edit.id,
    tanggal_mulai: formatDate(props.edit.tanggal_mulai),
    tanggal_selesai: formatDate(props.edit.tanggal_selesai),
    nama_diklat: props.edit.nama_diklat,
    pengajar: props.edit.pengajar,
    jam_diklat: props.edit.jam_diklat,
    diklat: props.edit.diklat,
    file_path: props.edit.file_path, // Menyimpan path file lama
    penyelenggara: props.edit.penyelenggara,
    evaluasimateri: props.edit.evaluasimateri,
    evaluasipengajar: props.edit.evaluasipengajar,
    file: null as File | null,
    _method: 'put', 
});

// Fungsi untuk submit form
function submit(e: Event) {
    e.preventDefault();
    router.put(`/Diklat/update/${form.value.id}`, form.value, {
        onSuccess: () => {
            toast.success('Data Berhasil Diperbaharui!');
        },
        onError: (errors: Record<string, string | string[]>) => {
            const errorMessages = Object.values(errors).flatMap((message) =>
                Array.isArray(message) ? message : [message]
            );

            toast.error(
                errorMessages[0] ?? 'Data Gagal Diperbaharui!, Pastikan semua kolom terisi dengan benar.'
            );
        },
    });
}
</script>

<template>
    <Head title="Edit Diklat" />

    <div class="mx-auto max-w-4xl rounded-lg bg-white p-6 shadow">
        <h1 class="mb-6 text-2xl font-bold">Edit Data Diklat</h1>

        <form @submit.prevent="submit" class="space-y-6">
            
            <!-- Baris 1: Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <Input
                        id="tanggal_mulai"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.tanggal_mulai"
                        required
                    />
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <Input
                        id="tanggal_selesai"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.tanggal_selesai"
                        required
                    />
                </div>
            </div>

            <!-- Baris 2: Nama Diklat & Kategori Diklat -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="nama_diklat" class="block text-sm font-medium text-gray-700">Nama Diklat</label>
                    <Input
                        id="nama_diklat"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.nama_diklat"
                        placeholder="Contoh: Pelatihan Keselamatan"
                        required
                    />
                </div>
                <div>
                    <label for="diklat" class="block text-sm font-medium text-gray-700">Kategori Diklat</label>
                    <Input
                        id="diklat"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.diklat"
                        placeholder="Eksternal"
                        required
                        disabled
                        value="Eksternal"
                    />
                </div>
            </div>

            <!-- Baris 3: Pengajar & Penyelenggara -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="pengajar" class="block text-sm font-medium text-gray-700">Pengajar</label>
                    <Input
                        id="pengajar"
                        type="text"
                        list="karyawan-list"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.pengajar"
                        placeholder="Ketik nama pengajar..."
                        required
                    />
                    <datalist id="karyawan-list">
                        <!-- Pastikan props.karyawanList tersedia jika datanya diambil dari database -->
                        <option v-for="(k, index) in props.karyawanList" :key="index" :value="k.nama_karyawan"></option>
                    </datalist>
                </div>
                <div>
                    <label for="penyelenggara" class="block text-sm font-medium text-gray-700">Penyelenggara</label>
                    <Input
                        id="penyelenggara"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.penyelenggara"
                        placeholder="Contoh: PT. Safety Indonesia"
                        required
                    />
                </div>
            </div>

            <!-- Baris 4: Jam Diklat (Satu baris penuh atau dipisah sesuai keinginan) -->
            <div>
                <label for="jam_diklat" class="block text-sm font-medium text-gray-700">Jam Diklat</label>
                <Input
                    id="jam_diklat"
                    type="number"
                    min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 md:w-1/2"
                    v-model="form.jam_diklat"
                    placeholder="Total Jam Diklat"
                    required
                />
            </div>

            <!-- Baris 5: Evaluasi Materi & Pengajar -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="evaluasimateri" class="block text-sm font-medium text-gray-700">Evaluasi Materi</label>
                    <textarea
                        id="evaluasimateri"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.evaluasimateri"
                        placeholder="Tuliskan evaluasi materi di sini..."
                    ></textarea>
                </div>
                <div>
                    <label for="evaluasipengajar" class="block text-sm font-medium text-gray-700">Evaluasi Pengajar</label>
                    <textarea
                        id="evaluasipengajar"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.evaluasipengajar"
                        placeholder="Tuliskan evaluasi pengajar di sini..."
                    ></textarea>
                </div>
            </div>

            <!-- Baris 6: Upload File PDF -->
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">Lampiran File (PDF)</label>
                <Input
                    id="file"
                    type="file"
                    @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null"
                    accept=".pdf"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <p class="mt-1 text-xs text-gray-500">File harus berformat PDF. Maksimal ukuran 2MB.</p>
                <p v-if="form.file" class="mt-2 text-sm text-gray-700">File akan diupdate: {{ form.file.name }}</p>
                <p v-else-if="form.file_path" class="mt-2 text-sm text-blue-600">
                    <a :href="`/storage/${form.file_path}`" target="_blank" class="hover:underline">Lihat file saat ini</a>
                </p>
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-end border-t pt-4">
                <button
                    type="submit"
                    class="rounded-md bg-indigo-600 px-6 py-2 text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Perbaharui Data
                </button>
            </div>
        </form>
    </div>
</template>