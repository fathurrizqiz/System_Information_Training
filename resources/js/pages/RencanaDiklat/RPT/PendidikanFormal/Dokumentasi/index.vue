<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pendidikan Formal', href: '/pendidikan-formal' },
    { title: 'Dokumentasi', href: '#' },
];

interface Dokumentasi {
    id: number;
    detail_program_id: number;
    file_path: string;
    nama_file: string;
}

const props = defineProps<{
    dokumentasi: Dokumentasi[];
    detail_program_id: number;
}>();

const form = useForm({
    detail_program_id: props.detail_program_id,
    file_path: null as File | null,
    nama_file: '',
});

// state
const showModal = ref(false);
const previewImage = ref<string | null>(null);

function openModal() {
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function handleFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    form.file_path = target.files?.[0] || null;
}

function submit() {
    const formData = new FormData();
    formData.append('detail_program_id', props.detail_program_id.toString());
    if (form.file_path) {
        formData.append('file_path', form.file_path);
    }

    formData.append('nama_file', form.nama_file);

    router.post(`/DetailInternal/Dokumentasi/store`, formData, {
        onSuccess: () => {
            toast.success('Dokumentasi berhasil disimpan!');
            closeModal();
            router.reload();
        },
        onError: (errors) => {
            toast.error('Gagal menyimpan dokumentasi.');
            console.error(errors);
        },
    });
}

function openPreview(path: string) {
    previewImage.value = `/storage/${path}`;
}

function closePreview() {
    previewImage.value = null;
}
function isImage(path: string) {
    return /\.(jpg|jpeg|png)$/i.test(path);
}

function isPdf(path: string) {
    return /\.pdf$/i.test(path);
}

//
</script>

<template>
    <Head title="Dokumentasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-5 my-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Dokumentasi</h2>
                <button
                    @click="openModal"
                    class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white shadow-md transition-all hover:bg-indigo-700 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Dokumentasi
                </button>
            </div>

            <!-- Daftar Dokumentasi -->
            <div
                v-if="dokumentasi.length > 0"
                class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="doc in dokumentasi"
                    :key="doc.id"
                    class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md"
                >
                    <img
                        v-if="isImage(doc.file_path)"
                        :src="`/storage/${doc.file_path}`"
                        :alt="`Dokumentasi ${doc.id}`"
                        class="h-48 w-full object-cover cursor-pointer"
                        @click="openPreview(doc.file_path)"
                    />

                    <div
                        v-else-if="isPdf(doc.file_path)"
                        class="mt-20 flex flex-col items-center justify-center text-gray-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="68"
                            height="68"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-file-text-icon lucide-file-text"
                        >
                            <path
                                d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"
                            />
                            <path d="M14 2v5a1 1 0 0 0 1 1h5" />
                            <path d="M10 9H8" />
                            <path d="M16 13H8" />
                            <path d="M16 17H8" />
                        </svg>

                        <a
                            :href="`/storage/${doc.file_path}`"
                            target="_blank"
                            class="text-sm text-indigo-600 hover:underline"
                        >
                            Lihat PDF
                        </a>
                    </div>
                    <div class="p-3">
                        <p class="text-sm text-gray-500">{{ doc.nama_file }}</p>
                    </div>
                </div>
            </div>

            <div v-else class="mt-10 text-center text-gray-500">
                <p>Belum ada dokumentasi. Tambahkan file sekarang.</p>
            </div>
        </div>

        <!-- Modal Upload -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm transition-opacity"
                @click="closeModal"
            >
                <div
                    class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
                    @click.stop
                >
                    <h3 class="mb-4 text-xl font-semibold text-gray-800">
                        Upload Dokumentasi
                    </h3>
                    <div class="mb-5">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            for="file-upload"
                            >Pilih File (Gambar/PDF)</label
                        >
                        <input
                            id="file-upload"
                            type="file"
                            name="file_path"
                            accept="image/*,application/pdf"
                            @change="handleFileChange"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Ukuran maks: 10 MB. Format: JPG, PNG, PDF
                        </p>

                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            for="file-upload"
                            >Nama File</label
                        >
                        <Input
                            type="text"
                            name="nama_file"
                            v-model="form.nama_file"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submit"
                            :disabled="!form.file_path"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Upload
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
        <!-- modal preview -->
        <Teleport to="body">
            <div
                v-if="previewImage"
                class="fixed inset-0 z-[999] flex items-center justify-center bg-black/70 backdrop-blur-sm"
                @click="closePreview"
            >
                <img
                    :src="previewImage"
                    class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl"
                    @click.stop
                />
            </div>
        </Teleport>
    </AppLayout>
</template>
