<script setup lang="ts">
import HeaderMenu from '@/components/HeaderMenu.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// State modal
const isVerifyModalOpen = ref(false);
const isRejectModalOpen = ref(false);

// Data dokumen
const document = {
    title: 'Service Excellent',
    uploader: 'EVA EVENDI',
};

// Alasan penolakan
const rejectionReason = ref('');

// --- Modal Verifikasi ---
const openVerifyModal = () => {
    isVerifyModalOpen.value = true;
};

const closeVerifyModal = () => {
    isVerifyModalOpen.value = false;
};

// --- Aksi Verifikasi ---
const handleVerify = () => {
    alert(`✅ Dokumen "${document.title}" telah diverifikasi!`);
    closeVerifyModal();
};

// --- Aksi Tolak ---
const handleRejectClick = () => {
    const confirmed = confirm('Apakah Anda yakin ingin menolak dokumen ini?');
    if (confirmed) {
        closeVerifyModal(); // Tutup modal verifikasi
        rejectionReason.value = ''; // Reset alasan
        isRejectModalOpen.value = true; // Buka modal alasan
    }
};

// --- Modal Alasan Penolakan ---
const closeRejectModal = () => {
    isRejectModalOpen.value = false;
};

const submitRejection = () => {
    if (!rejectionReason.value.trim()) {
        alert('Alasan penolakan tidak boleh kosong!');
        return;
    }

    // ✅ Kirim ke API (placeholder)
    console.log('Dokumen ditolak dengan alasan:', rejectionReason.value);
    alert(
        `❌ Dokumen "${document.title}" ditolak.\nAlasan: ${rejectionReason.value}`,
    );

    closeRejectModal();
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Rencana Program Tahunan', href: '/rencana-diklat' },
    { title: 'Pendidikan Formal', href: '/pendidikan-formal' },
];

// const page = usePage();
// const auth = page.props.auth;
// const rawRole = auth.user?.role || [];
// const roles = Array.isArray(rawRole) ? rawRole : [rawRole];

const menuItems = [
    { title: 'Perpustakaan Diklat', href: '/MateriDiklat/approve' },
    { title: 'Metri Ditolak', href: '/RencanaDiklat/RPT/PF' },
];
</script>

<template>
    <Head title="Perpustakaan Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <HeaderMenu :items="menuItems" />

        <div class="px-6 py-8">
            <div class="mx-auto max-w-7xl">
                <!-- Header Section -->
                <div class="mb-8 flex justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Materi Ditolak
                        </h1>
                        
                    </div>
                    <div>
                        <div
                            @click="openVerifyModal"
                            class="h-10 w-32 rounded-md bg-red-500 hover:bg-red-700"
                        >
                            <h1 class="py-2 text-center text-white">
                                Belum Verifikasi
                            </h1>
                        </div>
                    </div>
                </div>

                <!-- Document List -->
                <div
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                >
                    <div class="divide-y divide-gray-100">
                        <!-- Single Document Item -->
                        <div
                            class="group p-5 transition-colors duration-150 hover:bg-gray-50"
                        >
                            <div class="flex items-start justify-between">
                                <!-- File Info -->
                                <div class="flex items-start gap-4">
                                    <!-- File Icon -->
                                    <div class="mt-1 flex-shrink-0">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="text-blue-600"
                                            >
                                                <path
                                                    d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"
                                                />
                                                <path
                                                    d="M14 2v4a2 2 0 0 0 2 2h4"
                                                />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div>
                                        <h3
                                            class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600"
                                        >
                                            Service Excellent
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Diunggah oleh
                                            <span
                                                class="font-medium text-gray-700"
                                                >EVA EVENDI</span
                                            >
                                        </p>
                                        <p class="mt-1 text-sm text-red-500">
                                            Alasan Penolakan :
                                            <span
                                                class="font-medium text-red-500"
                                                >Tidak mendukung standar Pelatihan</span
                                            >
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700"
                                        title="Lihat"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path
                                                d="M21 17v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2"
                                            />
                                            <path
                                                d="M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2"
                                            />
                                            <circle cx="12" cy="12" r="1" />
                                            <path
                                                d="M18.944 12.33a1 1 0 0 0 0-.66 7.5 7.5 0 0 0-13.888 0 1 1 0 0 0 0 .66 7.5 7.5 0 0 0 13.888 0"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700"
                                        title="Edit"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path
                                                d="m18 5-2.414-2.414A2 2 0 0 0 14.172 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2"
                                            />
                                            <path
                                                d="M21.378 12.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"
                                            />
                                            <path d="M8 18h1" />
                                        </svg>
                                    </button>
                                    <button
                                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700"
                                        title="Unduh"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path
                                                d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"
                                            />
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="M12 18v-6" />
                                            <path d="m9 15 3 3 3-3" />
                                        </svg>
                                    </button>
                                    <button
                                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-red-50 hover:text-red-600"
                                        title="Hapus"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                            />
                                            <path d="M3 6h18" />
                                            <path
                                                d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tambahkan lebih banyak item di sini nanti (loop v-for) -->
                    </div>
                </div>

                <!-- Empty State (opsional, untuk saat data kosong) -->
                <!--
        <div v-if="documents.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada dokumen</h3>
          <p class="mt-1 text-sm text-gray-500">Dokumen yang Anda unggah akan muncul di sini.</p>
        </div>
        -->
            </div>
        </div>
        <!-- modal -->
        <!-- Modal Overlay -->
        <Teleport to="body">
            <div
                v-if="isVerifyModalOpen"
                class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm p-4"
                @click="closeVerifyModal"
            >
                <div
                    class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-lg"
                    @click.stop
                >
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-bold text-gray-900">
                            Verifikasi Dokumen
                        </h2>
                    </div>
                    <div class="px-6 py-5">
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >Judul Dokumen</label
                                >
                                <p class="mt-1 font-medium text-gray-900">
                                    {{ document.title }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >Diunggah Oleh</label
                                >
                                <p class="mt-1 text-gray-700">
                                    {{ document.uploader }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
                        <button
                            type="button"
                            class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                            @click="closeVerifyModal"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700"
                            @click="handleRejectClick"
                        >
                            Tolak
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-green-700"
                            @click="handleVerify"
                        >
                            Verifikasi
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🔸 Modal Alasan Penolakan -->
            <div
                v-if="isRejectModalOpen"
                class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm p-4"
                @click="closeRejectModal"
            >
                <div
                    class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-lg"
                    @click.stop
                >
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-bold text-gray-900">
                            Alasan Penolakan
                        </h2>
                    </div>
                    <div class="px-6 py-5">
                        <p class="mb-4 text-sm text-gray-600">
                            Mohon berikan alasan mengapa dokumen ini ditolak:
                        </p>
                        <textarea
                            v-model="rejectionReason"
                            placeholder="Contoh: Format tidak sesuai, dokumen tidak lengkap, dll."
                            class="h-24 w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-red-500 focus:outline-none"
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
                        <button
                            type="button"
                            class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                            @click="closeRejectModal"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700"
                            @click="submitRejection"
                        >
                            Kirim Penolakan
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
