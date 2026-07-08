<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pendidikan Formal', href: '/pendidikan-formal' },
    { title: 'Detail', href: '#' },
];

interface TokenFlash {
    token_link?: {
        pree?: string;
        post?: string;
        evaluasi?: string;
    };
    success?: string;
    error?: string;
}

interface Periode {
    id: number;
    tanggal: Date;
    nama_pengajar: string;
    tempat: string;
}

const props = defineProps<{
    detail_id: number;
    periode: Periode[];
    token_link?: {
        pree?: string;
        post?: string;
        evaluasi?: string;
    };
    isPeriodeRunning: boolean;
    ValidasiStart?: (string | number)[];
    runningPeriodeId?: number;
    isRunning?: boolean;
}>();

const selectedPeriode = ref(props.runningPeriodeId?.toString() || '');
const jam = ref('');
// const isPeriodeActive = computed(() => {
//     if (!props.isPeriodeRunning || !props.runningPeriodeId) {
//         return false;
//     }
//     return selectedPeriode.value === String(props.runningPeriodeId);
// });

const validasiStarted = computed(() => {
    const data = props.ValidasiStart || [];
    return new Set(data.map((id) => String(id)));
});

function start() {
    if (!selectedPeriode.value) {
        toast.error('Pilih periode terlebih dahulu!');
        return;
    }

    if (validasiStarted.value.has(String(selectedPeriode.value))) {
        toast.error('Periode sudah pernah dimulai');
        return;
    }
    if (!jam.value || Number(jam.value) <= 0) {
        toast.error('Masukkan durasi diklat yang valid');
        return;
    }

    router.post(
        '/DiklatInternal/periode/start',
        {
            periode_id: selectedPeriode.value,
            jam_diklat: jam.value,
        },
        {
            preserveState: true,
            onSuccess: () => {
                toast.success('Diklat berhasil dimulai.');
                // refresh halaman
                router.visit(route('aksi-internal', { id: props.detail_id }), {
                    preserveScroll: true,
                });
            },
        },
    );
}

function endPeriode() {
    if (!selectedPeriode.value) {
        toast.error('Pilih periode terlebih dahulu!');
        return;
    }

    if (
        !confirm(
            'Yakin ingin mengakhiri pelatihan ini? Token akan dinonaktifkan.',
        )
    ) {
        return;
    }

    router.post(
        '/DiklatInternal/periode/end',
        {
            periode_id: selectedPeriode.value,
        },
        {
            preserveState: true,
            onSuccess: () => {
                toast.success('Pelatihan berhasil diakhiri.');
                router.visit(route('aksi-internal', { id: props.detail_id }), {
                    preserveScroll: true,
                });
            },
            onError: (errors) => {
                toast.error(
                    errors.periode_id?.[0] || 'Gagal mengakhiri pelatihan.',
                );
            },
        },
    );
}
const page = usePage(); // ambil semua props dari Inertia

const flash = page.props.flash as TokenFlash | undefined;

function detailPeriode() {
    if (!selectedPeriode.value) {
        toast.error('Pilih periode terlebih dahulu!');
        return;
    }

    router.visit(`/DiklatInternal/detailperiod/list/${props.detail_id}`);
}
function presensi() {
    if (!selectedPeriode.value) {
        toast.error('Pilih periode terlebih dahulu!');
        return;
    }
    router.visit(`/DiklatInternal/detail/presensi/${selectedPeriode.value}`);
}

function bukaTemplate() {
    if (!selectedPeriode.value) {
        toast.error('Pilih periode terlebih dahulu!');
        return;
    }

    router.visit(
        `/DiklatInternal/detail/pembahasan/template/${selectedPeriode.value}`,
    );
}
function bukaDokumentasi() {
    if (!selectedPeriode.value) {
        toast.error('Pilih periode terlebih dahulu!');
        return;
    }

    router.visit(`/DetailInternal/Dokumentasi/view/${selectedPeriode.value}`);
}
</script>

<template>
    <Head title="Detail Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="mb-6 text-2xl font-semibold text-gray-800">
                Aksi Diklat
            </h1>

            <!-- Pilih Periode -->
            <div
                class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
            >
                <div class="m-2 flex justify-between">
                    <h2 class="mb-3 text-lg font-medium text-gray-700">
                        Pilih Periode
                    </h2>
                </div>
                <select
                    v-model="selectedPeriode"
                    class="w-full rounded-md border border-gray-300 p-2.5 text-sm"
                >
                    <option value="">-- Pilih periode pelaksanaan --</option>

                    <option
                        v-for="p in props.periode"
                        :key="p.id"
                        :value="p.id"
                    >
                        {{ p.tanggal }} - {{ p.nama_pengajar }} ({{ p.tempat }})
                    </option>
                </select>

                <div class="mt-4 flex items-center justify-between">
                    <span class="text-gray-600">Status Periode:</span>
                    <span
                        v-if="!isRunning"
                        class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700"
                        >Belum dimulai</span
                    >
                    <span
                        v-else
                        class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700"
                        >Sedang Berlangsung</span
                    >
                </div>
            </div>

            <!-- Jam Diklat -->
            <div
                class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
            >
                <h2 class="mb-3 text-lg font-medium text-gray-700">
                    Durasi Diklat (Jam)
                </h2>
                <Input
                    @input="jam = $event.target.value.replace(/\D/g, '')"
                    inputmode="numeric"
                    min="1"
                    v-model="jam"
                    class="w-32 rounded-md border border-gray-300 p-2.5 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none"
                    placeholder="0"
                />
            </div>

            <div class="mb-8 flex justify-end">
                <button
                    v-if="!props.isRunning"
                    type="button"
                    class="cursor-pointer rounded bg-blue-600 px-5 py-3 text-white hover:bg-blue-800"
                    :disabled="!selectedPeriode"
                    @click="start"
                >
                    {{
                        selectedPeriode ? 'Mulai Periode' : 'Pilih periode dulu'
                    }}
                </button>

                <button
                    v-else
                    type="button"
                    class="cursor-pointer rounded bg-red-600 px-5 py-3 text-white hover:bg-red-800"
                    @click="endPeriode"
                >
                    Akhiri Periode
                </button>

                <div></div>
            </div>
            <div
                v-if="($page.props.flash as any)?.token_link_pree"
                class="mt-4 bg-blue-100 p-3"
            >
                <strong>Link Pree-test:</strong>
                <a
                    :href="($page.props.flash as any).token_link_pree"
                    class="text-blue-700 underline"
                >
                    {{ ($page.props.flash as any).token_link_pree }}
                </a>
            </div>

            <div v-if="flash?.token_link?.pree" class="mt-4 bg-blue-100 p-3">
                <strong>Link Pree-test:</strong>
                <a
                    :href="flash.token_link.pree"
                    class="text-blue-700 underline"
                >
                    {{ flash.token_link.pree }}
                </a>
            </div>

            <div
                class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
            >
                <div>
                    <h3 class="font-semibold">Link Pree-Test :</h3>
                    <div v-if="token_link?.pree">
                        <a :href="token_link.pree">{{ token_link.pree }}</a>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold">Link Post-Test :</h3>
                    <div v-if="token_link?.post">
                        <a :href="token_link.post">{{ token_link.post }}</a>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold">Link Evaluasi :</h3>
                    <div v-if="token_link?.evaluasi">
                        <a :href="token_link.evaluasi">{{
                            token_link.evaluasi
                        }}</a>
                    </div>
                </div>
            </div>
            <!-- <div
                v-else-if="isPeriodeActive && !token_link"
                class="mb-6 rounded bg-yellow-50 p-4 text-sm text-yellow-600"
            >
                Periode sedang berjalan, tetapi token belum tersedia. Harap
                hubungi admin.
            </div> -->

            <!-- Manajemen Test -->
            <div
                class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
            >
                <h2 class="mb-4 text-lg font-medium text-gray-700">
                    Manajemen Test
                </h2>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button
                        @click="
                            router.get(
                                `/DiklatInternal/pree/${props.detail_id}`,
                            )
                        "
                        class="flex-1 rounded-md bg-teal-600 px-4 py-2.5 text-white"
                    >
                        Pre-test
                    </button>
                    <button
                        @click="
                            router.get(
                                `/DiklatInternal/post/${props.detail_id}`,
                            )
                        "
                        class="flex-1 rounded-md bg-blue-600 px-4 py-2.5 text-white"
                    >
                        Post-test
                    </button>
                </div>
            </div>

            <!-- Template Sertifikat -->
            <div
                class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
            >
                <h2 class="mb-3 text-lg font-medium text-gray-700">
                    Template Sertifikat
                </h2>
                <button
                    @click="bukaTemplate"
                    class="rounded-md bg-gray-800 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-900"
                >
                    Buat Template
                </button>
            </div>

            <!-- Navigasi Cepat -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
            >
                <h2 class="mb-4 text-lg font-medium text-gray-700">
                    Navigasi Cepat
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <button
                        @click="detailPeriode"
                        class="rounded-md bg-gray-100 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                    >
                        Detail Periode
                    </button>
                    <button
                        @click="presensi"
                        class="rounded-md bg-gray-100 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                    >
                        Data Presensi
                    </button>
                    <button
                        @click="bukaTemplate"
                        class="rounded-md bg-gray-100 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                    >
                        Sertifikat
                    </button>
                    <button
                        @click="bukaDokumentasi"
                        class="rounded-md bg-gray-100 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                    >
                        Dokumentasi
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
