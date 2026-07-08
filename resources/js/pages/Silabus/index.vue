<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import SyllabusItem from './SyllabusItem.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Silabus', href: '/silabus' },
];

// --- TIPE DATA ---
interface SyllabusItemNode {
    id: number;
    name: string;
    type: 'folder' | 'file';
    children?: SyllabusItemNode[];
    file_path?: string | null;
}

// --- STATE ---
const syllabusData = ref<SyllabusItemNode[]>([]);
const isModalOpen = ref(false);
const newItemType = ref<'folder' | 'file'>('folder');
const newItemName = ref('');
const newItemFile = ref<File | null>(null);
const parentItemId = ref<number | null>(null);
const isLoading = ref(false);

// --- FETCH DATA DARI API ---
const fetchSyllabus = async () => {
    try {
        const res = await axios.get('/api/silabus');
        syllabusData.value = res.data.data;
    } catch (err) {
        console.error('Gagal memuat silabus:', err);
    }
};

// --- MODAL ---
const openModal = (parentId: number | null = null) => {
    parentItemId.value = parentId;
    isModalOpen.value = true;
    resetForm();
};
const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};
const resetForm = () => {
    newItemType.value = 'folder';
    newItemName.value = '';
    newItemFile.value = null;
};

// --- INPUT FILE ---
const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) newItemFile.value = target.files[0];
};

// --- SIMPAN DATA KE SERVER ---
const handleSubmit = async () => {
    if (!newItemName.value.trim()) {
        alert('Nama tidak boleh kosong!');
        return;
    }
    if (newItemType.value === 'file' && !newItemFile.value) {
        alert('Anda harus memilih file!');
        return;
    }

    isLoading.value = true;

    try {
        const formData = new FormData();
        formData.append('name', newItemName.value);
        formData.append('type', newItemType.value);
        if (parentItemId.value) formData.append('parent_id', parentItemId.value.toString());
        if (newItemFile.value) formData.append('file', newItemFile.value);

        await axios.post('/api/silabus', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        await fetchSyllabus(); // refresh list
        closeModal();
    } catch (err) {
        console.error('Gagal menyimpan item:', err);
        alert('Gagal menyimpan item. Coba lagi.');
    } finally {
        isLoading.value = false;
    }
};

// --- MOUNT ---
onMounted(fetchSyllabus);
</script>

<template>
    <Head title="Manajemen Silabus" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gray-50 font-sans">
            <!-- Header -->
            <header class="border-b bg-white">
                <div class="mx-auto flex max-w-6xl items-center px-6 py-4">
                    <i class="fas fa-book-open mr-3 text-xl text-indigo-600"></i>
                    <h1 class="text-lg font-semibold text-gray-900">Manajemen Silabus</h1>
                </div>
            </header>

            <!-- Konten Utama -->
            <main class="mx-auto max-w-6xl px-6 py-6">
                <div class="rounded-lg border border-gray-200 bg-white">
                    <!-- Toolbar -->
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                        <span class="text-sm font-medium text-gray-700">Materi Silabus</span>
                        <button
                            @click="openModal(null)"
                            class="flex items-center rounded bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-700"
                        >
                            <i class="fas fa-folder-plus mr-1"></i> Tambah Folder/File
                        </button>
                    </div>

                    <!-- Daftar Item -->
                    <div class="p-5">
                        <ul class="space-y-2">
                            <SyllabusItem
                                v-for="item in syllabusData"
                                :key="item.id"
                                :item="item"
                                @open-modal="openModal"
                            />
                        </ul>

                        <div v-if="syllabusData.length === 0" class="py-10 text-center text-gray-500">
                            <i class="fas fa-folder-open mb-3 text-4xl text-gray-300"></i>
                            <p class="text-sm">Belum ada materi. Tambahkan folder atau file.</p>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Modal Tambah Item -->
            <Transition name="fade">
                <div
                    v-if="isModalOpen"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4"
                    @click="closeModal"
                >
                    <div @click.stop class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-medium text-gray-900">Tambah Item Baru</h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <form @submit.prevent="handleSubmit" class="space-y-4">
                            <!-- Pilih Tipe -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Tipe Item</label>
                                <div class="flex space-x-6">
                                    <label class="flex cursor-pointer flex-col items-center">
                                        <div
                                            class="flex h-16 w-16 flex-col items-center justify-center rounded-lg border-2"
                                            :class="newItemType === 'folder' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'"
                                        >
                                            <i class="fas fa-folder mb-1 text-2xl text-yellow-500"></i>
                                            <span class="text-xs text-gray-700">Folder</span>
                                        </div>
                                        <input type="radio" v-model="newItemType" value="folder" class="sr-only" />
                                    </label>

                                    <label class="flex cursor-pointer flex-col items-center">
                                        <div
                                            class="flex h-16 w-16 flex-col items-center justify-center rounded-lg border-2"
                                            :class="newItemType === 'file' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'"
                                        >
                                            <i class="fas fa-file mb-1 text-2xl text-gray-500"></i>
                                            <span class="text-xs text-gray-700">File</span>
                                        </div>
                                        <input type="radio" v-model="newItemType" value="file" class="sr-only" />
                                    </label>
                                </div>
                            </div>

                            <!-- Nama -->
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
                                <input
                                    v-model="newItemName"
                                    type="text"
                                    class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-indigo-500"
                                    :placeholder="`Nama ${newItemType}`"
                                />
                            </div>

                            <!-- Upload File -->
                            <div v-if="newItemType === 'file'">
                                <label class="mb-1 block text-sm font-medium text-gray-700">File</label>
                                <input type="file" @change="handleFileChange" class="w-full text-sm text-gray-600" />
                            </div>

                            <!-- Tombol -->
                            <div class="flex justify-end space-x-3 pt-2">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isLoading"
                                    class="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{ isLoading ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
