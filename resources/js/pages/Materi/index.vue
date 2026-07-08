<script setup lang="ts">
// import HeaderMenu from '@/components/HeaderMenu.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';



interface MateriItem {
    id: number;
    title: string;
    type: 'folder' | 'file';
    status: string;
    file_path?: string | null;
    parent_id?: number | null;
}

// Props dari controller
const props = defineProps<{
    materi: MateriItem[];
    currentFolder: MateriItem | null;
    breadcrumb: MateriItem[];
}>();



//  Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Library', href: '/dashboard' },
];

const page = usePage();
const auth = page.props.auth;
const rawRole = auth.user?.roles || [];
const roles = Array.isArray(rawRole) ? rawRole : [rawRole];
// const menuItems = [
//     { title: 'Perpustakaan Diklat', href: '/MateriDiklat/approve' },
//     { title: 'Materi Ditolak', href: '/MateriDiklat/reject' },
// ];

//  State
const materiList = ref(props.materi || []);
const isVerifyModalOpen = ref(false);
const isRejectModalOpen = ref(false);
const tambah = ref(false);

const selectedMateri = ref<any>(null);
const rejectionReason = ref('');

// Upload state
const uploadType = ref('');
const uploadTitle = ref('');
const uploadFile = ref<File | null>(null);



// 🔹 Upload materi baru atau folder
const handleUpload = () => {
  if (!uploadType.value) return toast.error('Pilih tipe dulu (Folder atau File)')
  if (!uploadTitle.value.trim()) return toast.error('Nama tidak boleh kosong')

  const formData = new FormData()
  formData.append('type', uploadType.value)
  formData.append('name', uploadTitle.value)
  
  // Tambahkan parent_id jika ada
  if (props.currentFolder) {
    formData.append('parent_id', props.currentFolder.id.toString())
  }

  if (uploadType.value === 'file' && uploadFile.value) {
    formData.append('file', uploadFile.value)
  }

  router.post('/Materi/store', formData, {
    forceFormData: true, // 🧩 WAJIB
    preserveScroll: true,
    onSuccess: () => {
      toast.success(' Materi berhasil ditambahkan!')
      tambah.value = false
      uploadType.value = ''
      uploadTitle.value = ''
      uploadFile.value = null
      location.reload();
    //   // Reload halaman untuk menampilkan data terbaru
    //   window.location.reload()
    },
    onError: () => {
      toast.error(' Gagal upload. Coba lagi.')
    },
  })
}

// 🔹 Navigasi ke folder
const navigateToFolder = (folderId: number) => {
    window.location.href = `/Materi/folder/${folderId}`;
};

// 🔹 Navigasi ke parent folder
const navigateToParent = () => {
    if (props.currentFolder && props.currentFolder.parent_id) {
        window.location.href = `/Materi/folder/${props.currentFolder.parent_id}`;
    } else {
        window.location.href = `/Materi`;
    }
};

// handle upload
// Pastikan definisinya ada di <script setup>

const handleFileUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target?.files?.length) {
        uploadFile.value = target.files[0];
    }
};


// 🔹 Buka modal verifikasi
const openVerifyModal = (item: any) => {
    selectedMateri.value = item;
    isVerifyModalOpen.value = true;
};

// 🔹 Verifikasi dokumen
const handleVerify = (id: number) => {
    router.put(
        `/Materi/verify/${id}`,
        {},
        {
            onSuccess: () => {
                toast.success('Dokumen diverifikasi.');
                isVerifyModalOpen.value = false;
                window.location.reload();
                location.reload();
            },
            onError: () => toast.error(' Gagal memverifikasi dokumen.'),
        },
    );
};

// 🔹 Buka modal tolak
const openRejectModal = (item: any) => {
    selectedMateri.value = item;
    rejectionReason.value = '';
    isRejectModalOpen.value = true;
};

// 🔹 Submit alasan penolakan
const submitRejection = (id: number) => {
    // if (!rejectionReason.value.trim())
    //     return toast.error('Alasan tidak boleh kosong.');

    router.put(
        `/Materi/reject/${id}`,
        {
            reason: rejectionReason.value,
        },
        {
            onSuccess: () => {
                toast.error(' Dokumen ditolak.');
                isRejectModalOpen.value = false;
                window.location.reload();
            },
            onError: () => toast.error('Gagal menolak dokumen.'),
        },
    );
};

// 🔹 Hapus materi
const deleteMateri = (id: number) => {
    if (!confirm('Yakin ingin menghapus materi ini?')) return;

    router.delete(`/Materi/delete/${id}`, {
        onSuccess: () => {
            toast.success(' Materi dihapus.');
            window.location.reload();
        },
        onError: () => toast.error('Gagal menghapus materi.'),
    });
};
</script>

<template>
    <Head title="Perpustakaan Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 md:px-6 md:py-8">
            <div class="mx-auto max-w-7xl">

                <!-- HEADER: Stacked on mobile -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-extrabold text-gray-900 md:text-2xl">Perpustakaan Diklat</h1>
                        <p class="text-xs text-gray-500 md:text-sm">Kelola dan akses semua materi pelatihan</p>
                    </div>

                    <button
                        @click="tambah = true"
                        class="flex w-38 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Materi
                    </button>
                </div>

                <!-- BREADCRUMB: Scrollable horizontal on mobile -->
                <div class="flex items-center gap-2 text-xs mb-4 overflow-x-auto whitespace-nowrap pb-2 no-scrollbar border-b border-gray-100 md:text-sm md:border-0">
                    <a href="/Materi" class="text-blue-600 font-medium">Home</a>

                    <template v-for="(item, idx) in breadcrumb" :key="item.id">
                        <span class="text-gray-400">/</span>
                        <a
                            v-if="idx < breadcrumb.length - 1"
                            :href="`/Materi/folder/${item.id}`"
                            class="text-blue-600 font-medium"
                        >
                            {{ item.title }}
                        </a>
                        <span v-else class="font-bold text-gray-800">{{ item.title }}</span>
                    </template>
                </div>

                <!-- Back Button -->
                <button
                    v-if="currentFolder"
                    @click="navigateToParent"
                    class="mb-6 flex items-center gap-2 text-sm font-bold text-blue-600 active:opacity-60 transition-opacity"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </button>

                <!-- GRID LIST: 2 columns on mobile, more on desktop -->
                <div v-if="materiList.length === 0" class="py-20 text-center">
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-50">
                        <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">Tidak ada materi di folder ini.</p>
                </div>

                <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 md:gap-6">
                    <div
                        v-for="item in materiList"
                        :key="item.id"
                        class="group flex flex-col justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all active:ring-2 active:ring-blue-500/20 md:hover:border-blue-300"
                    >
                        <!-- Content Area -->
                        <div @click="item.type === 'folder' ? navigateToFolder(item.id) : null" class="cursor-pointer">
                            <div class="mb-3 flex justify-center">
                                <!-- Folder Icon -->
                                <svg v-if="item.type === 'folder'" class="h-14 w-14 text-yellow-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                </svg>
                                <!-- File Icon -->
                                <div v-else class="relative h-14 w-14 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>

                            <p class="text-center text-sm font-bold text-gray-800 line-clamp-2 min-h-[40px]">
                                {{ item.title }}
                            </p>
                            
                            <!-- Status Badge -->
                            <div class="mt-2 flex justify-center">
                                <span 
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider"
                                    :class="{
                                        'bg-amber-50 text-amber-600': item.status === 'pending',
                                        'bg-green-50 text-green-600': item.status === 'verified' || item.status === 'approved'
                                    }"
                                >
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons: Full width for touch targets -->
                        <div class="mt-5 flex flex-col gap-2 border-t border-gray-50 pt-4">
                            <template v-if="(item.status === 'pending' && item.type === 'file') && roles.includes('admin_diklat')">
                                <button @click="handleVerify(item.id)" class="w-full rounded-lg bg-green-600 py-2 text-[11px] font-bold text-white active:bg-green-700 uppercase">Verifikasi</button>
                                <button @click="submitRejection(item.id)" class="w-full rounded-lg bg-red-600 py-2 text-[11px] font-bold text-white active:bg-red-700 uppercase">Tolak</button>
                            </template>

                            <a
                                v-if="item.type === 'file' && item.file_path"
                                :href="`/storage/${item.file_path}`"
                                target="_blank"
                                class="flex items-center justify-center w-full rounded-lg bg-indigo-50 py-2 text-[11px] font-bold text-indigo-600 active:bg-indigo-100 uppercase"
                            >
                                Preview
                            </a>

                            <button
                                @click="deleteMateri(item.id)"
                                class="w-full rounded-lg py-2 text-[11px] font-bold text-gray-400 active:text-red-500 uppercase"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Modal Templates (Mobile optimized with bottom alignment on small screens) -->
        <Teleport to="body">
            <!-- Modal Upload (Contoh Satu Saja, berlaku untuk semua) -->
            <div v-if="tambah" class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-sm sm:items-center p-0 sm:p-4" @click="tambah = false">
                <div class="w-full max-w-md rounded-t-3xl bg-white p-6 shadow-2xl transition-all sm:rounded-2xl" @click.stop>
                    <!-- Handle bar for mobile -->
                    <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-gray-200 sm:hidden"></div>
                    
                    <h2 class="mb-4 text-xl font-bold text-gray-800">Tambah Materi</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase text-gray-400">Tipe Konten</label>
                            <select v-model="uploadType" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                                <option value="" disabled>Pilih tipe</option>
                                <option value="folder">Folder Baru</option>
                                <option value="file">File Dokumen</option>
                            </select>
                        </div>

                        <div v-if="uploadType">
                             <label class="mb-1 block text-xs font-bold uppercase text-gray-400">{{ uploadType === 'folder' ? 'Nama Folder' : 'Judul File' }}</label>
                             <input v-model="uploadTitle" type="text" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" :placeholder="uploadType === 'folder' ? 'Marketing 2024' : 'Panduan SOP'" />
                        </div>

                        <div v-if="uploadType === 'file'">
                            <label class="mb-1 block text-xs font-bold uppercase text-gray-400">Lampiran File</label>
                            <input type="file" @change="handleFileUpload" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button @click="handleUpload" class="w-full rounded-xl bg-blue-600 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 sm:w-auto sm:px-8">Simpan Materi</button>
                        <button @click="tambah = false" class="w-full rounded-xl py-3 text-sm font-bold text-gray-400 sm:w-auto sm:px-8">Batal</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- (Lanjutkan implementasi yang sama untuk Modal Verifikasi & Reject) -->
    </AppLayout>
</template>

<style scoped>
/* Hide scrollbar for breadcrumbs horizontal scroll */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>