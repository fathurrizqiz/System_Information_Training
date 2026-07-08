<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { UserPlus, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    nrp: string;
    employee_id: string;
    roles: Role[];
}

const props = defineProps<{
    users: User[];
    allRoles: Role[];
    filters: {
        search: string;
    };
}>();

// State UI
const showUserModal = ref(false);
// State untuk impersonate
const loadingImpersonate = ref(null); // menyimpan requestId
const targetName = ref('');
const pollInterval = ref();
const countdown = ref(300); // 5 menit
const search = ref(props.filters?.search || '');
let searchTimeout: ReturnType<typeof setTimeout>;

const openCreateModal = () => {
    showUserModal.value = true;
};

// search engine
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('superadmin.users.index'),
            { search: value },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    }, 300);
});

const deleteUser = (id: number) => {
    if (
        confirm(
            'Apakah Anda yakin ingin menghapus user ini? Data login akan hilang secara permanen.',
        )
    ) {
        router.delete(
            route('superadmin.users.destroy', id, {
                onSuccess: () => {
                    toast.success('User berhasil dihapus');
                },
            }),
        );
    }
};

function resetPassword(id: number) {
    if (
        confirm(
            'Apakah Anda yakin ingin mereset password user ini? Password akan direset ke default "password123".',
        )
    ) {
        router.put(
            route('superadmin.users.reset-password', id, {
                onSuccess: () => {
                    toast.success('Password berhasil direset', {});
                },
            }),
        );
    }
}

const closeModal = () => {
    showUserModal.value = false;
    store.reset();
};

const loginAs = async (userId, userName) => {
    const res = await axios.post(route('superadmin.login-as', userId));
    loadingImpersonate.value = res.data.request_id;
    targetName.value = res.data.target_name;
    countdown.value = 300;

    // Mulai countdown
    const countdownTimer = setInterval(() => {
        countdown.value--;
    }, 1000);

    // Polling tiap 3 detik
    pollInterval.value = setInterval(async () => {
        const poll = await axios.get(
            route('superadmin.impersonate-status', loadingImpersonate.value),
        );

        if (poll.data.status === 'approved') {
            clearInterval(pollInterval.value);
            clearInterval(countdownTimer);
            window.location.href = poll.data.redirect;
        } else if (['rejected', 'expired'].includes(poll.data.status)) {
            clearInterval(pollInterval.value);
            clearInterval(countdownTimer);
            loadingImpersonate.value = null;
            toast.error(
                poll.data.status === 'rejected'
                    ? 'Akses ditolak oleh pengguna.'
                    : 'Request kadaluarsa.',
            );
        }
    }, 3000);
};

const cancelImpersonate = () => {
    clearInterval(pollInterval.value);
    loadingImpersonate.value = null;
};
const formatCountdown = (s) =>
    `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;

const store = useForm({
    name: '',
    nrp: '',
    employee_id: null,
    password: '',
    password_confirmation: '',
    role: 'karyawan', // Default
});

const submit = () => {
    if (
        !store.name ||
        !store.nrp ||
        !store.password ||
        !store.password_confirmation ||
        !store.role
    ) {
        toast.error(
            'Gagal membuat user. Pastikan semua field diisi dengan benar.',
            {},
        );
        return;
    }
    store.post(route('superadmin.users.store'), {
        onSuccess: () => {
            toast.success('User berhasil dibuat', {});
            store.reset();
        },
        onError: () => {
            toast.error(
                'Gagal membuat user. Pastikan semua field diisi dengan benar.',
                {},
            );
        },
    });
};
</script>

<template>
    <AppLayout>
        <div class="min-h-screen p-8">
            <!-- Header Section -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Master User & Login
                    </h1>
                    <p class="mt-1">
                        Kelola kredensial login dan identitas karyawan.
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 rounded-2xl bg-slate-700 px-6 py-3 font-bold text-white shadow-lg shadow-cyan-500/20 transition-all hover:bg-cyan-400 active:scale-95"
                >
                    <UserPlus class="h-5 w-5" />
                    Tambah User Baru
                </button>
            </div>
            <div class="mb-6">
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Cari Nama, dan NRP..."
                    class="w-full max-w-md rounded-xl border border-slate-300 bg-slate-100 p-3 text-white placeholder-slate-500 transition outline-none focus:ring-2 focus:ring-cyan-500"
                />
            </div>

            <!-- Table Section -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-700 shadow-xl"
            >
                <table class="w-full border-collapse text-left">
                    <thead
                        class="bg-slate-700 text-xs font-bold tracking-widest text-white uppercase"
                    >
                        <tr>
                            <th class="p-5">Informasi User</th>
                            <th class="p-5">NRP</th>
                            <th class="p-5">Role</th>
                            <th class="p-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="transition-colors hover:bg-slate-700/20"
                        >
                            <td class="p-5">
                                <div class="font-bold">{{ user.name }}</div>
                                <div class="text-xs text-slate-500">
                                    ID: #{{ user.id }}
                                </div>
                            </td>
                            <td class="p-5 font-mono text-sm text-slate-700">
                                {{ user.nrp }}
                            </td>
                            <td class="p-5">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-if="user.roles?.length === 0"
                                        class="text-xs text-slate-500 italic"
                                    >
                                        Karyawan
                                    </span>

                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        class="..."
                                    >
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="flex justify-center gap-3">
                                    <button
                                        @click="resetPassword(user.id)"
                                        class="rounded-lg bg-yellow-700 p-2 transition-all hover:bg-yellow-500/20 hover:text-yellow-400 hover:shadow-lg"
                                        title="Reset Password"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="#ffffff"
                                            stroke-width="1.25"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-cloud-sync-icon lucide-cloud-sync"
                                        >
                                            <path
                                                d="m17 18-1.535 1.605a5 5 0 0 1-8-1.5"
                                            />
                                            <path d="M17 22v-4h-4" />
                                            <path
                                                d="M20.996 15.251A4.5 4.5 0 0 0 17.495 8h-1.79a7 7 0 1 0-12.709 5.607"
                                            />
                                            <path d="M7 10v4h4" />
                                            <path
                                                d="m7 14 1.535-1.605a5 5 0 0 1 8 1.5"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteUser(user.id)"
                                        class="rounded-lg bg-red-700 p-2 transition-all hover:bg-red-500/20 hover:text-red-400 hover:shadow-lg"
                                        title="Hapus User"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="#ffffff"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-eraser-icon lucide-eraser"
                                        >
                                            <path
                                                d="M21 21H8a2 2 0 0 1-1.42-.587l-3.994-3.999a2 2 0 0 1 0-2.828l10-10a2 2 0 0 1 2.829 0l5.999 6a2 2 0 0 1 0 2.828L12.834 21"
                                            />
                                            <path
                                                d="m5.082 11.09 8.828 8.828"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="loginAs(user.id, user.name)"
                                        class="rounded-lg bg-emerald-600 p-2 text-white transition-all hover:bg-emerald-500"
                                        title="Login Sebagai User Ini"
                                    >
                                        <!-- Icon user-search atau sejenisnya -->
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="#000000"
                                            stroke-width="1"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-hat-glasses-icon lucide-hat-glasses"
                                        >
                                            <path d="M14 18a2 2 0 0 0-4 0" />
                                            <path
                                                d="m19 11-2.11-6.657a2 2 0 0 0-2.752-1.148l-1.276.61A2 2 0 0 1 12 4H8.5a2 2 0 0 0-1.925 1.456L5 11"
                                            />
                                            <path d="M2 11h20" />
                                            <circle cx="17" cy="18" r="3" />
                                            <circle cx="7" cy="18" r="3" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Form (Create Only) -->
            <div
                v-if="showUserModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-md"
            >
                <div
                    class="w-full max-w-lg animate-in rounded-3xl border border-slate-700 bg-slate-800 p-8 shadow-2xl duration-200 fade-in zoom-in"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white">
                            Tambah User Baru
                        </h3>
                        <button
                            @click="closeModal"
                            class="text-slate-400 transition hover:text-white"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-slate-400"
                                >Nama Lengkap</label
                            >
                            <input
                                v-model="store.name"
                                type="text"
                                class="w-full rounded-xl border border-slate-700 bg-white p-3 transition outline-none focus:ring-2 focus:ring-cyan-500"
                            />
                            <div
                                v-if="store.errors.name"
                                class="mt-1 text-xs text-red-400"
                            >
                                {{ store.errors.name }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-sm font-medium text-slate-400"
                                    >NRP</label
                                >
                                <input
                                    v-model="store.nrp"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-700 bg-white p-3 transition outline-none focus:ring-2 focus:ring-cyan-500"
                                />
                                <div
                                    v-if="store.errors.nrp"
                                    class="mt-1 text-xs text-red-400"
                                >
                                    {{ store.errors.nrp }}
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 border-slate-700" />

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-sm font-medium text-slate-400"
                                >
                                    Password
                                </label>
                                <input
                                    v-model="store.password"
                                    type="password"
                                    class="w-full rounded-xl border border-slate-700 bg-white p-3 transition outline-none focus:ring-2 focus:ring-cyan-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-sm font-medium text-slate-400"
                                    >Konfirmasi</label
                                >
                                <input
                                    v-model="store.password_confirmation"
                                    type="password"
                                    class="w-full rounded-xl border border-slate-700 bg-white p-3 transition outline-none focus:ring-2 focus:ring-cyan-500"
                                />
                            </div>
                        </div>

                        <div class="mt-8 flex gap-4">
                            <button
                                @click="closeModal"
                                type="button"
                                class="flex-1 py-3 font-bold text-slate-400 transition hover:text-white"
                            >
                                Batal
                            </button>
                            <button
                                @click="submit"
                                type="submit"
                                :disabled="store.processing"
                                class="flex-1 rounded-2xl bg-cyan-500 py-3 font-bold text-slate-900 shadow-lg shadow-cyan-500/20 transition-all hover:bg-cyan-400 active:scale-95"
                            >
                                Buat User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Loading Overlay -->
        <Teleport to="body">
            <div
                v-if="loadingImpersonate"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-sm rounded-3xl bg-white p-8 text-center shadow-2xl dark:bg-slate-900"
                >
                    <!-- Spinner -->
                    <div class="relative mx-auto mb-6 h-20 w-20">
                        <svg
                            class="h-20 w-20 -rotate-90 animate-spin text-blue-600"
                            viewBox="0 0 80 80"
                        >
                            <circle
                                cx="40"
                                cy="40"
                                r="34"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="6"
                                stroke-linecap="round"
                                stroke-dasharray="213"
                                stroke-dashoffset="53"
                            />
                        </svg>
                        <div
                            class="absolute inset-0 flex items-center justify-center text-lg font-bold text-blue-600"
                        >
                            {{ formatCountdown(countdown) }}
                        </div>
                    </div>

                    <h3
                        class="text-xl font-bold text-slate-900 dark:text-white"
                    >
                        Menunggu Persetujuan
                    </h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Permintaan akses ke akun
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-300"
                            >{{ targetName }}</span
                        >
                        telah dikirim.
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Pengguna perlu membuka Inbox untuk menyetujui.
                    </p>

                    <button
                        @click="cancelImpersonate"
                        class="mt-6 w-full rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400"
                    >
                        Batalkan
                    </button>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
