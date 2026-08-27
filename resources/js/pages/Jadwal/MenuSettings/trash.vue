<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    DocumentTextIcon,
    FolderIcon,
    PhoneIcon,
    TagIcon,
    TrashIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// 1. Pastikan nama props SAMA PERSIS dengan key di controller Inertia::render()
const props = defineProps<{
    diklatkaryawan?: any[];
    diklateksternal?: any[];
    diklathlc?: any[];
    library?: any[]; // Pastikan ini 'library' bukan 'materiLibrary' dll
    diklatinternal?: any[];
    karyawans?: any[];
    noHP?: any[];
    programInternal?: any[];
    programEksternal?: any[];
    programHLC?: any[];
    waTemplate?: any[];
    auth: {
        user: {
            id: number;
            name: string;
            roles: string | string[]; // <-- allow single or multiple roles
        } | null;
    };
}>();

const rawRoles = props.auth.user?.roles || [];
// Spatie roles are objects like { id, name, guard_name }, so extract .name
const roleNames = computed(() =>
    rawRoles.map((r: any) => (typeof r === 'string' ? r : r.name))
);

const isAdmin = computed(() =>
    roleNames.value.includes('admin_diklat') || roleNames.value.includes('super-admin')
);

// State Tab Aktif
const activeTab = ref('library');

// Definisi Tab (Pastikan id sama dengan nama props)
const tabs = [
    { id: 'diklatkaryawan', label: 'Diklat Karyawan', icon: DocumentTextIcon },
    {
        id: 'diklateksternal',
        label: 'Diklat Eksternal',
        icon: DocumentTextIcon
    },
    { id: 'diklathlc', label: 'Diklat HLC', icon: DocumentTextIcon },
    { id: 'library', label: 'Materi Library', icon: FolderIcon }, 
    { id: 'diklatinternal', label: 'Diklat Internal', icon: DocumentTextIcon, adminOnly:true },
    { id: 'karyawans', label: 'Data Karyawan', icon: UsersIcon, adminOnly:true },
    { id: 'noHP', label: 'No HP', icon: PhoneIcon, adminOnly:true },
    { id: 'programInternal', label: 'Program Internal', icon: TagIcon, adminOnly:true },
    { id: 'programEksternal', label: 'Program Eksternal', icon: TagIcon, adminOnly:true },
    { id: 'programHLC', label: 'Program HLC', icon: TagIcon, adminOnly:true },
    { id: 'waTemplate', label: 'WA Template', icon: PhoneIcon, adminOnly:true },
];

const visibleTabs = computed(() =>
    tabs.filter(tab => !tab.adminOnly || isAdmin.value)
);


// Helper aman untuk mengambil data
const currentData = computed(() => {
    const data = props[activeTab.value as keyof typeof props];
    return Array.isArray(data) ? data : [];
});


// Pastikan import route helper jika pakai ziggy, atau hardcode URL sementara
const restoreItem = (id: number, type: string) => {
    console.log('type:', type, 'id:', id);
    if (confirm('Pulihkan data ini?')) {
        router.post(route('trash.restore', { type: type, id: id }), {}, {
            preserveScroll: true,
            onSuccess: () => {}
        });
    }
};

const forceDeleteItem = (id: number, type: string) => {
    if (confirm('Hapus permanen? Data tidak bisa kembali!')) {
        router.delete(route('trash.force-delete', { type: type, id: id }), {
            preserveScroll: true,
            onSuccess: () => {}
        });
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('id-ID');
};
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-6">
                    <h1
                        class="flex items-center gap-2 text-2xl font-bold text-gray-800"
                    >
                        <TrashIcon class="h-8 w-8 text-red-500" />
                        Recycle Bin
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Total data terhapus di tab ini:
                        <span class="font-bold text-red-600">{{
                            currentData.length
                        }}</span>
                    </p>
                </div>

                <!-- Tabs -->
                <div class="mb-6 overflow-x-auto border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6">
                        <button
                            v-for="tab in visibleTabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700',
                                'flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                            ]"
                        >
                            <component :is="tab.icon" class="h-4 w-4" />
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Content Table -->
                <div
                    class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Nama / Judul
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Detail
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Dihapus Oleh
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Waktu
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <!-- Empty State -->
                                <tr v-if="currentData.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-gray-400"
                                    >
                                        <FolderIcon
                                            class="mx-auto mb-2 h-12 w-12 opacity-50"
                                        />
                                        Tidak ada data di recycle bin untuk
                                        kategori ini.
                                    </td>
                                </tr>

                                <!-- Data Rows -->
                                <tr
                                    v-for="item in currentData"
                                    :key="item.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            <!-- Logika tampilan dinamis berdasarkan tipe data -->
                                            <span
                                                v-if="activeTab === 'library'"
                                                >{{ item.title }}</span
                                            >
                                            <span
                                                v-else-if="
                                                    activeTab === 'karyawans'
                                                "
                                                >{{ item.nama_karyawan }}</span
                                            >
                                            <span
                                                v-else-if="activeTab === 'noHP'"
                                                >{{ item.nama }}</span
                                            >
                                            <span
                                                v-else-if="
                                                    activeTab === 'waTemplate'
                                                "
                                                >{{ item.nama_template }}</span
                                            >
                                            <span v-else>{{
                                                item.nama_diklat ||
                                                item.nama_program ||
                                                'N/A'
                                            }}</span>
                                        </div>
                                        <!-- <div class="text-xs text-gray-400">
                                            ID: {{ item.id }}
                                        </div> -->
                                    </td>

                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-500"
                                    >
                                        <span v-if="item.nrp"
                                            >NRP: {{ item.nrp }}</span
                                        >
                                        <span v-else-if="item.type"
                                            >Type: {{ item.type }}</span
                                        >
                                        <span v-else>-</span>
                                    </td>

                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-500"
                                    >
                                        {{ item.deleted_by || 'Sistem' }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-500"
                                    >
                                        {{ formatDate(item.deleted_at) }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                    >
                                        <button 
                                            @click="restoreItem(item.id, activeTab)"
                                            class="p-1 m-2 rounded bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-2 cursor-pointer font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                                        >
                                            Restore
                                        </button>
                                        <button
                                            @click="forceDeleteItem(item.id, activeTab)" v-if="isAdmin"
                                            class="bg-red-500 text-white p-2 rounded hover:bg-red-700 cursor-pointer"
                                        >
                                            Hapus Permanen
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
