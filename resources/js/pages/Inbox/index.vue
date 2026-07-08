<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    inboxItems: any[];
    inboxExternalItems: any[];
    impersonateRequests: any[];
}>();

// --- State Loading & Modal ---
const isLoading = ref<number | null>(null);
const loadingAction = ref<'setuju' | 'tolak' | null>(null);

// State untuk Modal Penolakan
const showRejectModal = ref(false);
const itemToReject = ref<{ id: number; type: 'hlc' | 'external' } | null>(null);
const rejectReason = ref('');

// HELPERS
const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';
    const parsedDate = new Date(date);
    if (isNaN(parsedDate.getTime())) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(parsedDate);
};

// ACTIONS
const handleAction = (
    id: number,
    action: 'setuju' | 'tolak',
    type: 'hlc' | 'external',
) => {
    // Jika aksinya TOLAK, buka modal dulu, jangan kirim request langsung
    if (action === 'tolak') {
        itemToReject.value = { id, type };
        rejectReason.value = ''; // Reset alasan
        showRejectModal.value = true;
        return;
    }

    // Jika aksinya SETUJU, langsung proses
    processAction(id, 'setuju', type);
};

// Fungsi khusus untuk memproses request ke backend
const processAction = (
    id: number,
    action: 'setuju' | 'tolak',
    type: 'hlc' | 'external',
    reason: string = '', // Parameter alasan (opsional untuk setuju)
) => {
    isLoading.value = id;
    loadingAction.value = action;

    const baseRoute = type === 'hlc' ? '/HLC/Home' : '/Eksternal/Home';
    // Catatan: Pastikan Route di backend sesuai
    // Jika backend Anda menggunakan Route::post('/hlc/home/tolak/{id}'), gunakan url biasa
    // Jika menggunakan named route, gunakan route()
    
    // Kita gunakan URL construction manual sesuai kode Anda sebelumnya
    const routeName =
        action === 'setuju'
            ? `${baseRoute}/konfirmasi/${id}`
            : `${baseRoute}/tolak/${id}`;

    // Data yang dikirim
    const dataPayload = action === 'tolak' ? { alasan: reason } : {};

    router.post(
        routeName,
        dataPayload,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    action === 'setuju'
                        ? 'Diklat berhasil dimasukkan ke jadwal'
                        : 'Undangan berhasil ditolak',
                );
                // Tutup modal jika sukses
                if (action === 'tolak') {
                    closeRejectModal();
                }
            },
            onError: (errors) => {
                toast.error('Terjadi kesalahan');
                console.error(errors);
            },
            onFinish: () => {
                isLoading.value = null;
                loadingAction.value = null;
            },
        },
    );
};

// Fungsi Konfirmasi dari dalam Modal
const confirmReject = () => {
    if (!itemToReject.value) return;
    
    // Validasi sederhana
    if (!rejectReason.value.trim()) {
        toast.error('Mohon isi alasan penolakan');
        return;
    }

    processAction(
        itemToReject.value.id, 
        'tolak', 
        itemToReject.value.type, 
        rejectReason.value
    );
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    itemToReject.value = null;
    rejectReason.value = '';
};

// impersonate response
const respondImpersonate = (
    requestId: number,
    action: 'approved' | 'rejected',
) => {
    router.post(
        route('impersonation.respond', requestId),
        { action },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    action === 'approved'
                        ? 'Akses diberikan ke admin.'
                        : 'Akses ditolak.',
                );
            },
        },
    );
};

const lihatDokumen = (dokumen: string) => {
    window.open(`/storage/${dokumen}`, '_blank');
};
</script>

<template>
    <Head title="Inbox Undangan" />

    <AppLayout>
        <div class="mx-auto max-w-5xl p-4 md:p-6">
            <!-- HEADER -->
            <div
                class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Kotak Masuk Diklat
                    </h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Persetujuan rekomendasi program diklat HLC dan
                        Eksternal.
                    </p>
                </div>

                <div
                    class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-2 text-sm font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                >
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    {{
                        (inboxItems?.length || 0) +
                        (inboxExternalItems?.length || 0)
                    }}
                    Undangan
                </div>
            </div>

            <!-- CONTENT -->
            <div
                v-if="inboxItems?.length > 0 || inboxExternalItems?.length > 0"
                class="space-y-12"
            >
                <!-- impersonate requests -->
                <section v-if="impersonateRequests?.length > 0" class="mb-8">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="h-1 w-8 rounded-full bg-red-500"></div>
                        <h2
                            class="text-xl font-bold text-slate-800 dark:text-slate-200"
                        >
                            Permintaan Akses Admin
                        </h2>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="req in impersonateRequests"
                            :key="'imp-' + req.id"
                            class="rounded-3xl border-2 border-red-200 bg-red-50 p-6 dark:border-red-900/50 dark:bg-red-950/20"
                        >
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-600 dark:bg-red-900/30"
                                    >
                                        <svg
                                            class="h-7 w-7"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-bold tracking-wider text-red-600 uppercase dark:text-red-400"
                                        >
                                            Permintaan Akses Akun
                                        </p>
                                        <p
                                            class="mt-1 text-base font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ req.admin?.name }} meminta akses
                                            ke akun Anda
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Berlaku hingga:
                                            {{
                                                new Date(
                                                    req.expires_at,
                                                ).toLocaleTimeString('id-ID')
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button
                                        @click="
                                            respondImpersonate(
                                                req.id,
                                                'approved',
                                            )
                                        "
                                        class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
                                    >
                                        ✓ Izinkan
                                    </button>
                                    <button
                                        @click="
                                            respondImpersonate(
                                                req.id,
                                                'rejected',
                                            )
                                        "
                                        class="inline-flex items-center gap-2 rounded-2xl border border-red-300 bg-white px-5 py-3 text-sm font-semibold text-red-600 hover:bg-red-50 dark:bg-slate-900"
                                    >
                                        ✕ Tolak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- SECTION 1: HLC (INTERNAL) -->
                <section v-if="inboxItems?.length > 0">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="h-1 w-8 rounded-full bg-blue-500"></div>
                        <h2
                            class="text-xl font-bold text-slate-800 dark:text-slate-200"
                        >
                            Rekomendasi HLC
                        </h2>
                    </div>

                    <div class="space-y-5">
                        <div v-for="item in inboxItems" :key="'hlc-' + item.id">
                            <!-- 1. TAMPILAN JIKA MERUPAKAN PESAN PERINGATAN SISTEM (BELUM ISI NO HP) -->
                            <div
                                v-if="item.tipe === 'system_warning'"
                                class="animate-pulse rounded-3xl border-2 border-red-300 bg-amber-50 p-6 shadow-xl"
                            >
                                <div
                                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600"
                                        >
                                            ⚠
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs font-bold tracking-wider text-amber-600 uppercase"
                                            >
                                                Peringatan Sistem
                                            </p>

                                            <p class="mt-1 text-base font-bold">
                                                {{ item.judul }}
                                            </p>

                                            <p
                                                class="mt-1 text-sm text-slate-600"
                                            >
                                                {{ item.pesan }}
                                            </p>
                                        </div>
                                    </div>

                                    <a
                                        :href="item.url_action"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-amber-600 px-5 py-3 text-sm font-semibold text-white"
                                    >
                                        Isi Nomor HP
                                    </a>
                                </div>
                            </div>
                        
                        <div
                        v-else
                            
                            
                            class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div class="flex flex-1 gap-4">
                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                                    >
                                        <svg
                                            class="h-7 w-7"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                            />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="mb-2 flex items-center gap-2"
                                        >
                                            <span
                                                class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold tracking-wider text-blue-700 uppercase dark:bg-blue-900/30 dark:text-blue-300"
                                                >Internal HLC</span
                                            >
                                        </div>
                                        <h2
                                            class="text-xl font-bold text-slate-900 dark:text-white"
                                        >
                                            <span v-if="item.nama_diklat">
                                                {{ item.nama_diklat }}
                                            </span>
                                            <button
                                                v-else
                                                @click="
                                                    lihatDokumen(item.dokumen)
                                                "
                                                class="cursor-pointer rounded bg-blue-500 px-3 py-1 text-white hover:bg-blue-600"
                                            >
                                                Lihat undangan
                                            </button>
                                        </h2>
                                        <p
                                            class="mt-1 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            Penyelenggara:
                                            <span class="font-semibold">{{
                                                item.penyelenggara || 'lihat undangan'
                                            }}</span>
                                        </p>

                                        <div
                                            class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3"
                                        >
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Jadwal mulai
                                                </p>
                                                <p
                                                    class="mt-1 text-sm font-semibold text-slate-800 dark:text-white"
                                                >
                                                    {{
                                                        formatDate(
                                                            item.tanggal_mulai,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Jadwal selesai
                                                </p>
                                                <p
                                                    class="mt-1 text-sm font-semibold text-slate-800 dark:text-white"
                                                >
                                                    {{
                                                        formatDate(
                                                            item.tanggal_selesai,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Durasi
                                                </p>
                                                <p
                                                    class="mt-1 text-sm font-semibold text-blue-600 dark:text-blue-400"
                                                >
                                                    {{ item.jam_diklat }} Jam
                                                </p>
                                            </div>
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Status
                                                </p>
                                                <div
                                                    class="mt-1 inline-flex rounded-full bg-amber-100 px-3 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                                                >
                                                    PENDING
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Action Buttons -->
                                <div
                                    class="flex shrink-0 flex-row gap-3 lg:flex-col"
                                >
                                    <button
                                        @click="
                                            handleAction(
                                                item.id,
                                                'setuju',
                                                'hlc',
                                            )
                                        "
                                        :disabled="isLoading === item.id"
                                        class="inline-flex min-w-[130px] items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 disabled:opacity-50"
                                    >
                                        {{
                                            isLoading === item.id &&
                                            loadingAction === 'setuju'
                                                ? 'Memproses...'
                                                : 'Setujui'
                                        }}
                                    </button>
                                    <button
                                        @click="
                                            handleAction(
                                                item.id,
                                                'tolak',
                                                'hlc',
                                            )
                                        "
                                        :disabled="isLoading === item.id"
                                        class="inline-flex min-w-[130px] items-center justify-center rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-semibold text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 dark:bg-slate-900"
                                    >
                                        {{
                                            isLoading === item.id &&
                                            loadingAction === 'tolak'
                                                ? 'Memproses...'
                                                : 'Tolak'
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2: EKSTERNAL -->
                <section v-if="inboxExternalItems?.length > 0">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="h-1 w-8 rounded-full bg-purple-500"></div>
                        <h2
                            class="text-xl font-bold text-slate-800 dark:text-slate-200"
                        >
                            Program Eksternal
                        </h2>
                    </div>

                    <div class="space-y-5">
                        <div
                            v-for="item in inboxExternalItems"
                            :key="'ext-' + item.id"
                            class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div class="flex flex-1 gap-4">
                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                                    >
                                        <svg
                                            class="h-7 w-7"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                                            />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="mb-2 flex items-center gap-2"
                                        >
                                            <span
                                                class="rounded-full bg-purple-50 px-3 py-1 text-[10px] font-bold tracking-wider text-purple-700 uppercase dark:bg-purple-900/30 dark:text-purple-300"
                                                >Eksternal</span
                                            >
                                        </div>
                                        <h2
                                            class="text-xl font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.nama_diklat }}
                                        </h2>
                                        <p
                                            class="mt-1 text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            <button
                                                @click="
                                                    lihatDokumen(item.dokumen)
                                                "
                                                class="cursor-pointer rounded bg-blue-500 px-3 py-1 text-white hover:bg-blue-600"
                                            >
                                                Lihat undangan
                                            </button>
                                        </p>
                                        Penyelenggara:
                                        <span class="font-semibold">{{
                                            item.penyelenggara || 'lihat undangan'
                                        }}</span>

                                        <div
                                            class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3"
                                        >
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Jadwal Mulai
                                                </p>
                                                <p
                                                    class="mt-1 text-sm font-semibold text-slate-800 dark:text-white"
                                                >
                                                    {{
                                                        formatDate(
                                                            item.tanggal_mulai,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Jadwal Selesai
                                                </p>
                                                <p
                                                    class="mt-1 text-sm font-semibold text-slate-800 dark:text-white"
                                                >
                                                    {{
                                                        formatDate(
                                                            item.tanggal_selesai,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Durasi
                                                </p>
                                                <p
                                                    class="mt-1 text-sm font-semibold text-purple-600 dark:text-purple-400"
                                                >
                                                    {{ item.jam_diklat }} Jam
                                                </p>
                                            </div>
                                            <div
                                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-500"
                                                >
                                                    Status
                                                </p>
                                                <div
                                                    class="mt-1 inline-flex rounded-full bg-amber-100 px-3 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                                                >
                                                    PENDING
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Action Buttons -->
                                <div
                                    class="flex shrink-0 flex-row gap-3 lg:flex-col"
                                >
                                    <button
                                        @click="
                                            handleAction(
                                                item.id,
                                                'setuju',
                                                'external',
                                            )
                                        "
                                        :disabled="isLoading === item.id"
                                        class="inline-flex min-w-[130px] items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 disabled:opacity-50"
                                    >
                                        {{
                                            isLoading === item.id &&
                                            loadingAction === 'setuju'
                                                ? 'Memproses...'
                                                : 'Setujui'
                                        }}
                                    </button>
                                    <button
                                        @click="
                                            handleAction(
                                                item.id,
                                                'tolak',
                                                'external',
                                            )
                                        "
                                        :disabled="isLoading === item.id"
                                        class="inline-flex min-w-[130px] items-center justify-center rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-semibold text-red-600 transition-all hover:bg-red-50 disabled:opacity-50 dark:bg-slate-900"
                                    >
                                        {{
                                            isLoading === item.id &&
                                            loadingAction === 'tolak'
                                                ? 'Memproses...'
                                                : 'Tolak'
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- EMPTY STATE -->
            <div
                v-else
                class="rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 py-20 text-center dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-white text-3xl shadow-sm dark:bg-slate-800"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-inbox"
                    >
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                        <path
                            d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"
                        />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                    Kotak Masuk Kosong
                </h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Belum ada rekomendasi diklat baru untuk Anda.
                </p>
            </div>
        </div>

        <!-- MODAL ALASAN PENOLAKAN -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
        >
            <div
                class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900"
            >
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                        Konfirmasi Penolakan
                    </h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Mohon berikan alasan mengapa Anda menolak undangan
                        diklat ini. Alasan akan disimpan ke dalam sistem.
                    </p>
                </div>

                <div class="mb-6">
                    <label
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        Alasan Penolakan
                    </label>
                    <textarea
                        v-model="rejectReason"
                        rows="4"
                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500"
                        
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="closeRejectModal"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmReject"
                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                    >
                        Kirim Penolakan
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>