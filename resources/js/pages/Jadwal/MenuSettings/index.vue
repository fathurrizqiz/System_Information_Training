<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
// Import ikon jika Anda menggunakan library seperti Lucide atau Heroicons, 
// tapi di sini saya akan gunakan SVG standar agar langsung jalan.
</script>

<template>
    <Head title="Settings WhatsApp" />

    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Pengaturan WhatsApp
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2">
                    Kelola integrasi notifikasi, nomor penerima, dan template pesan Anda dalam satu tempat.
                </p>
            </div>

           
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- CARD 1: Daftar Nomor -->
                <Link :href="route('nohp.index')" class="group relative overflow-hidden bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-24 h-24 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17 2H7C5.89 2 5 2.89 5 4v16c0 1.1.9 2 2 2h10c1.11 0 2-.9 2-2V4c0-1.11-.89-2-2-2zm-5 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/></svg>
                    </div>
                    <div class="flex flex-col h-full space-y-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Daftar Nomor HP</h3>
                            <p class="text-sm text-slate-500 mt-1">Kelola kontak karyawan yang akan menerima notifikasi otomatis.</p>
                        </div>
                        <div class="pt-4 flex items-center text-blue-600 font-bold text-xs uppercase tracking-widest">
                            Buka Menu <svg class="w-4 h-4 ml-1 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </div>
                </Link>

                
                <Link :href="route('template.index')" class="group relative overflow-hidden bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-24 h-24 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                    </div>
                    <div class="flex flex-col h-full space-y-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Template Pesan</h3>
                            <p class="text-sm text-slate-500 mt-1">Atur kata-kata notifikasi menggunakan placeholder dinamis.</p>
                        </div>
                        <div class="pt-4 flex items-center text-green-600 font-bold text-xs uppercase tracking-widest">
                            Atur Template <svg class="w-4 h-4 ml-1 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </div>
                </Link>

               
                

            </div>
        </div>
    </AppLayout>
</template>