<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// 1. Definisikan Interface untuk Type Safety
interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    nrp: string;
    roles: Role[];
}

// 2. Gunakan Interface pada Props
const props = defineProps<{
    users: User[];
    allRoles: Role[];
}>();

// Form untuk tambah Role baru
const roleForm = useForm({
    name: '',
});

const submitNewRole = () => {
    roleForm.post(route('superadmin.storeRole'), {
        onSuccess: () => roleForm.reset(),
    });
};

// Logika Edit Role User (Modal)
const showModal = ref(false);

// Berikan type User atau null pada selectedUser
const selectedUser = ref<User | null>(null);

const userRoleForm = useForm<{
    roles: string[];
}>({
    roles: [],
});

const openEditModal = (user: User) => {
    console.log('Membuka modal untuk:', user.name); // Cek di console browser
    selectedUser.value = user;

    // Reset dan isi ulang array roles dengan nama role terbaru
    userRoleForm.defaults({
        roles: user.roles.map((role: Role) => role.name),
    });
    userRoleForm.reset();

    showModal.value = true;
};
const updateRoles = () => {
    if (!selectedUser.value) return;

    // Mengirim ke route superadmin.assign dengan parameter ID user
    userRoleForm.post(route('superadmin.assign', selectedUser.value.id), {
        preserveScroll: true, // Agar halaman tidak kembali ke atas setelah simpan
        onSuccess: () => {
            showModal.value = false;
            selectedUser.value = null;
            // Opsi: Tambahkan notifikasi sukses di sini
        },
        onError: (errors) => {
            console.error('Gagal update role:', errors);
        }
    });
};
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-white p-8 font-san">
            <header class="mb-10">
                <h1 class="text-3xl font-extrabold tracking-tight">
                    Manajemen Akses Karyawan
                </h1>
                <p class="mt-2 text-slate-400">
                    Kelola peran dan otoritas setiap anggota tim.
                </p>
            </header>

            <!-- Form Tambah Role -->
            <div
                class="mb-8 rounded-2xl border border-white p-6 shadow-lg"
            >
                <h2
                    class="mb-4 flex items-center gap-2 text-lg font-semibold "
                >
                    <span class="h-6 w-2 rounded-full "></span>
                    Tambah Role Baru
                </h2>
                <form @submit.prevent="submitNewRole" class="flex gap-4">
                    <input
                        v-model="roleForm.name"
                        type="text"
                        placeholder="Contoh: Manager Gudang"
                        class="flex-1 rounded-xl border bg-white p-3 transition outline-none focus:ring-2 focus:ring-cyan-500"
                    />
                    <button
                        :disabled="roleForm.processing"
                        class="rounded-xl bg-slate-500 px-8 font-bold  transition-all hover:shadow-xl text-white active:scale-95 disabled:bg-slate-600"
                    >
                        {{ roleForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </form>
                <div
                    v-if="roleForm.errors.name"
                    class="mt-2 text-sm text-red-400"
                >
                    {{ roleForm.errors.name }}
                </div>
            </div>

            
            <!-- Tabel User -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800 shadow-xl"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead
                            class="bg-white text-xs font-bold tracking-widest shadow-lg uppercase"
                        >
                            <tr>
                                <th class="p-5">Role</th>
                                
                            </tr>
                        </thead>
                        <tbody class="divide-y bg-white">
                            <tr
                                v-for="all in allRoles"
                                :key="all.id"
                                class="transition-colors hover:bg-slate-700/20"
                            >
                                <td class="p-5 font-medium">{{ all.name }}</td>
                               
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div
                class="overflow-hidden mt-5 rounded-2xl border border-slate-700 bg-slate-800 shadow-xl"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead
                            class="bg-white text-xs font-bold tracking-widest shadow-lg uppercase"
                        >
                            <tr>
                                <th class="p-5">Nama Karyawan</th>
                                <th class="p-5">NRP</th>
                                <th class="p-5">Roles</th>
                                <th class="p-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y bg-white">
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="transition-colors hover:bg-slate-700/20"
                            >
                                <td class="p-5 font-medium">{{ user.name }}</td>
                                <td class="p-5 text-sm text-slate-400">
                                    {{ user.nrp }}
                                </td>
                                <td class="p-5">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-if="user.roles.length === 0"
                                            class="text-xs text-slate-500 italic"
                                            >Karyawan</span
                                        >
                                        <span
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            class="rounded-md border border-cyan-500/20 bg-cyan-500/10 px-2 py-1 text-[10px] font-bold text-cyan-400 uppercase"
                                        >
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-5 text-center">
                                    <button
                                        @click="openEditModal(user)"
                                        class="rounded-lg bg-slate-700 px-4 py-2 text-xs font-bold transition-all  text-white"
                                    >
                                        Assign Role
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Edit Role -->
            <div
                v-if="showModal && selectedUser"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-md"
            >
                <div
                    class="scale-in-center w-full max-w-md rounded-3xl border border-slate-700 bg-slate-800 p-8 shadow-2xl"
                >
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold">Atur Role</h3>
                        <p class="mt-1 text-sm text-slate-400">
                            Mengubah akses untuk
                            <span class="font-semibold text-cyan-400">{{
                                selectedUser.name
                            }}</span>
                        </p>
                    </div>

                    <form @submit.prevent="updateRoles">
                        <!-- Bagian dalam modal Anda -->
                        <div
                            class="custom-scrollbar grid max-h-[300px] grid-cols-1 gap-3 overflow-y-auto pr-2"
                        >
                            <label
                                v-for="role in allRoles"
                                :key="role.id"
                                class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-700 p-4 transition-all hover:bg-slate-700"
                                :class="{
                                    'border-cyan-500/50 bg-cyan-500/5':
                                        userRoleForm.roles.includes(role.name),
                                }"
                            >
                                <span
                                    class="text-sm font-semibold"
                                    :class="{
                                        'text-cyan-400':
                                            userRoleForm.roles.includes(
                                                role.name,
                                            ),
                                    }"
                                >
                                    {{ role.name }}
                                </span>

                                <!-- Input ini yang menangkap pilihan Anda -->
                                <input
                                    type="checkbox"
                                    :value="role.name"
                                    v-model="userRoleForm.roles"
                                    class="h-5 w-5 rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-slate-800"
                                />
                            </label>
                        </div>

                        <div class="flex gap-4">
                            <button
                                @click="showModal = false"
                                type="button"
                                class="flex-1 py-3 font-bold text-slate-400 transition hover:text-white"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="userRoleForm.processing"
                                class="flex-1 rounded-2xl bg-cyan-500 py-3 font-bold text-slate-900 shadow-lg shadow-cyan-500/20 transition-all hover:bg-cyan-400 active:scale-95 disabled:bg-slate-600"
                            >
                                {{
                                    userRoleForm.processing
                                        ? 'Updating...'
                                        : 'Simpan Perubahan'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 10px;
}
.scale-in-center {
    animation: scale-in-center 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}
@keyframes scale-in-center {
    0% {
        transform: scale(0.9);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
