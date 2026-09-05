<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { MagnifyingGlassIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface TrashItem {
    id: number;
    nama_data: string;
    type: string;
    type_label: string;
    detail?: string | null;
    deleted_at: string;
    deleted_by: number | string | null;
}

const props = defineProps<{
    trash: TrashItem[];
}>();

// State Pencarian & Filter Kategori
const searchQuery = ref('');
const selectedCategory = ref('all');

// Daftar Kategori Unik untuk Dropdown Filter
const categories = computed(() => {
    const list = new Map<string, string>();
    props.trash.forEach((item) => {
        if (!list.has(item.type)) {
            list.set(item.type, item.type_label);
        }
    });
    return Array.from(list.entries()).map(([type, label]) => ({ type, label }));
});

// Filter Data berdasarkan Search & Kategori
const filteredTrash = computed(() => {
    return props.trash.filter((item) => {
        const matchesCategory =
            selectedCategory.value === 'all' ||
            item.type === selectedCategory.value;
        const query = searchQuery.value.toLowerCase();
        const matchesSearch =
            !query ||
            item.nama_data?.toLowerCase().includes(query) ||
            item.type_label?.toLowerCase().includes(query) ||
            (item.detail && item.detail.toLowerCase().includes(query));
        return matchesCategory && matchesSearch;
    });
});

const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const restoreItem = (id: number, type: string) => {
    if (confirm('Pulihkan data ini?')) {
        router.post(
            route('trash.restore', { type: type, id: id }),
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const forceDeleteItem = (id: number, type: string) => {
    if (confirm('Hapus permanen? Data tidak bisa kembali!')) {
        router.delete(route('trash.force-delete', { type: type, id: id }), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div
                    class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <h1
                            class="flex items-center gap-2 text-2xl font-bold text-gray-800"
                        >
                            <TrashIcon class="h-8 w-8 text-red-500" />
                            Recycle Bin (Super Admin)
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Total data terhapus:
                            <span class="font-bold text-red-600">{{
                                filteredTrash.length
                            }}</span>
                            dari {{ props.trash.length }} data
                        </p>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filter Kategori -->
                        <select
                            v-model="selectedCategory"
                            class="w-fit rounded-lg border-gray-300 bg-white p-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="all">Semua Kategori</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.type"
                                :value="cat.type"
                            >
                                {{ cat.label }}
                            </option>
                        </select>

                        <!-- Input Pencarian -->
                        <div class="relative min-w-[220px]">
                            <MagnifyingGlassIcon
                                class="pointer-events-none absolute top-2.5 left-3 h-4 w-4 text-gray-400"
                            />
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari data..."
                                class="pl-9 text-sm"
                            />
                        </div>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div
                    class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-600"
                        >
                            <thead
                                class="bg-gray-100 text-xs font-semibold text-gray-700 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-3">Nama Data</th>
                                    <th class="px-6 py-3">Kategori</th>
                                    <th class="px-6 py-3">Dihapus Oleh</th>
                                    <th class="px-6 py-3">Waktu Dihapus</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <!-- Empty State -->
                                <tr v-if="filteredTrash.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-gray-400"
                                    >
                                        <TrashIcon
                                            class="mx-auto mb-2 h-10 w-10 text-gray-300"
                                        />
                                        Trash kosong, tidak ada data yang
                                        dihapus.
                                    </td>
                                </tr>

                                <!-- Data Rows -->
                                <tr
                                    v-for="item in filteredTrash"
                                    :key="`${item.type}-${item.id}`"
                                    class="transition hover:bg-gray-50"
                                >
                                    <!-- Nama Data & Detail -->
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ item.nama_data }}
                                        </div>
                                        <div
                                            v-if="item.detail"
                                            class="mt-0.5 text-xs text-gray-400"
                                        >
                                            {{ item.detail }}
                                        </div>
                                    </td>

                                    <!-- Kategori Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="rounded bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-700/10 ring-inset"
                                        >
                                            {{ item.type_label }}
                                        </span>
                                    </td>

                                    <!-- Info User Penghapus -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="item.deleted_by"
                                            class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-blue-700/10 ring-inset"
                                        >
                                            {{ item.deleted_by }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-gray-400 italic"
                                        >
                                            Sistem / Tidak Tercatat
                                        </span>
                                    </td>

                                    <!-- Waktu Penghapusan -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-gray-500"
                                    >
                                        {{ formatDate(item.deleted_at) }}
                                    </td>

                                    <!-- Tombol Aksi -->
                                    <td
                                        class="space-x-2 px-6 py-4 text-center whitespace-nowrap"
                                    >
                                        <button
                                            @click="
                                                restoreItem(item.id, item.type)
                                            "
                                            class="cursor-pointer rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-500"
                                        >
                                            Restore
                                        </button>
                                        <button
                                            @click="
                                                forceDeleteItem(
                                                    item.id,
                                                    item.type,
                                                )
                                            "
                                            class="cursor-pointer rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-500"
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
