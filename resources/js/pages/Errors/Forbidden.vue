<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    message: string;
}>();

function goBack() {
    window.history.back();
}
</script>

<template>
    <Head title="Akses Ditolak" />

    <main class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-50 via-cyan-50 to-blue-100 px-4 py-10 dark:from-slate-950 dark:via-slate-900 dark:to-blue-950">
        <section class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-xl shadow-slate-300/40 sm:p-12 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/20">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400">
                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM12 15.75h.008v.008H12v-.008Z" />
                </svg>
            </div>

            <p class="mt-7 text-sm font-bold tracking-[0.25em] text-red-600 dark:text-red-400">ERROR 403</p>
            <h1 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl dark:text-white">Akses ditolak</h1>
            <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ message }}</p>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Silakan kembali ke halaman sebelumnya atau hubungi administrator jika akses ini diperlukan.</p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                
                <div @click="goBack" class="cursor-pointer rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                    Kembali
                </div>
            </div>
        </section>
    </main>
</template>
