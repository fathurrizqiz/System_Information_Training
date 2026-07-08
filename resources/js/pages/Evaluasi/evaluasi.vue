<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Evaluasi', href: '/Diklat/Evaluasi' },
];

interface Evaluasi {
    id: number;
    nama_diklat: string;
    evaluasimateri: string;
    evaluasipengajar: string;
}

interface Sentiment {
    positive: number;
    negative: number;
}

interface Detail {
    id: number;
    nama_diklat: string;
    sentiment?: Sentiment | null;
}

interface Program {
    id: number;
    nama_program: string;
    details: Detail[];
}

const props = defineProps<{
    evaluasiKaryawan: Evaluasi[];
    evaluasiInternal: Program[];
}>();
</script>

<template>
    <Head title="Evaluasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-8 p-4 md:p-6 lg:p-8">
            
            <!-- Page Header -->
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                    Evaluasi Pelatihan
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Pantau ringkasan sentimen program dan rincian evaluasi dari karyawan.
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                
                <section class="lg:col-span-5 xl:col-span-4">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                            Ringkasan Program
                        </h2>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!evaluasiInternal || !evaluasiInternal.length" class="rounded-xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada data evaluasi internal.</p>
                    </div>

                    <!-- List Program -->
                    <div class="space-y-4">
                        <div
                            v-for="program in evaluasiInternal"
                            :key="program.id"
                            class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                        >
                            <!-- Header Program -->
                            <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                                    {{ program.nama_program }}
                                </h3>
                            </div>

                            <div v-if="!program.details || !program.details.length" class="text-sm italic text-slate-500">
                                Tidak ada diklat terkait program ini.
                            </div>

                            <!-- List Detail Diklat per Program -->
                            <div class="space-y-3">
                                <div
                                    v-for="detail in program.details"
                                    :key="detail.id"
                                    class="group relative flex flex-col gap-3 rounded-lg border border-slate-100 bg-slate-50/50 p-4 transition-colors hover:border-blue-200 hover:bg-blue-50/30 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-800 dark:hover:bg-blue-900/20"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="font-medium leading-snug text-slate-700 dark:text-slate-200">
                                            {{ detail.nama_diklat }}
                                        </p>
                                        
                                      
                                        <span v-if="detail.sentiment" class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            Dievaluasi
                                        </span>
                                        <span v-else class="shrink-0 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                            Belum
                                        </span>
                                    </div>

                                    <!-- Block Sentimen (Jika ada) -->
                                    <div
                                        v-if="detail.sentiment && detail.sentiment.positive !== undefined"
                                        class="cursor-pointer rounded-md bg-white p-3 shadow-sm ring-1 ring-slate-200 transition-all group-hover:ring-blue-300 dark:bg-slate-900 dark:ring-slate-700 dark:group-hover:ring-blue-700"
                                        @click="$inertia.visit(`/Diklat/Evaluasi/detail/${detail.id}`)"
                                    >
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Sentimen</span>
                                            <svg class="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </div>
                                        <div class="flex items-center gap-4 text-sm font-medium">
                                            <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ detail.sentiment?.positive }}
                                            </div>
                                            <div class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ detail.sentiment?.negative }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                
                <section class="lg:col-span-7 xl:col-span-8">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                            Feedback Peserta (Karyawan)
                        </h2>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-6 dark:border-slate-800 dark:bg-slate-900">
                        
                        <!-- Empty State -->
                        <div v-if="!evaluasiKaryawan || !evaluasiKaryawan.length" class="py-8 text-center">
                            <p class="text-slate-500 dark:text-slate-400">Belum ada feedback dari peserta diklat.</p>
                        </div>

                        <!-- Grid Feedback Karyawan -->
                        <div class="grid gap-4 md:grid-cols-2">
                            <div
                                v-for="(item, index) in evaluasiKaryawan"
                                :key="index"
                                class="flex flex-col gap-3 rounded-xl border border-slate-200 p-4 transition-colors hover:border-slate-300 hover:shadow-sm dark:border-slate-700 dark:hover:border-slate-600"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="font-semibold text-slate-800 dark:text-slate-100">
                                        {{ item.nama_diklat }}
                                    </h4>
                                    
                                    <span v-if="item.evaluasimateri" class="shrink-0 rounded bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-600 uppercase tracking-wider dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Selesai
                                    </span>
                                    <span v-else class="shrink-0 rounded bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-600 uppercase tracking-wider dark:bg-amber-900/30 dark:text-amber-400">
                                        Pending
                                    </span>
                                </div>

                                <!-- Konten Evaluasi -->
                                <div class="mt-2 flex flex-col gap-3">
                                    <!-- Evaluasi Materi -->
                                    <div v-if="item.evaluasimateri" class="rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                                        <span class="text-xs font-semibold text-slate-500 uppercase dark:text-slate-400">Materi</span>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">
                                            "{{ item.evaluasimateri }}"
                                        </p>
                                    </div>
                                    
                                    <!-- Evaluasi Pengajar -->
                                    <div v-if="item.evaluasipengajar" class="rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                                        <span class="text-xs font-semibold text-slate-500 uppercase dark:text-slate-400">Pengajar</span>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">
                                            "{{ item.evaluasipengajar }}"
                                        </p>
                                    </div>

                                    <!-- Fallback jika belum ada feedback sama sekali -->
                                    <div v-if="!item.evaluasimateri && !item.evaluasipengajar" class="text-sm italic text-slate-400">
                                        Peserta belum memberikan ulasan materi maupun pengajar.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </AppLayout>
</template>