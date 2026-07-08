<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Diklat',
        href: route('dashboard'),
    },
    {
        title: 'Persetujuan',
        href: route('persetujuan.index'),
    },
];

const approvalMenus = ref([
    {
        title: 'Diklat Mandiri',
        description: 'Pengajuan pelatihan yang diikuti secara mandiri oleh karyawan di luar jadwal resmi rumah sakit.',
        icon: 'user-academic',
        routeName: 'diklat.approve.index',
        stats: 12,
        statsLabel: 'Menunggu Review',
        colors: {
            bg: 'bg-blue-50 dark:bg-blue-900/20',
            icon: 'bg-blue-500 text-white',
            text: 'text-blue-600 dark:text-blue-400',
            border: 'hover:border-blue-300 dark:hover:border-blue-800',
            shadow: 'hover:shadow-blue-100 dark:hover:shadow-blue-900/30'
        },
        
    },
    {
        title: 'Diklat HLC',
        description: 'Persetujuan kegiatan Hotel Learning Center (HLC) atau pelatihan internal yang bersifat intensif.',
        icon: 'building',
        routeName: 'persetujuan.hlc',
        stats: 5,
        statsLabel: 'Menunggu Review',
        colors: {
            bg: 'bg-violet-50 dark:bg-violet-900/20',
          icon: 'bg-violet-500 text-white',
            text: 'text-violet-600 dark:text-violet-400',
            border: 'hover:border-violet-300 dark:hover:border-violet-800',
            shadow: 'hover:shadow-violet-100 dark:hover:shadow-violet-900/30'
        }
    },
    {
        title: 'Diklat Eksternal',
        description: 'Pengajuan untuk mengikuti pelatihan, seminar, atau workshop yang diselenggarakan oleh pihak ketiga.',
        icon: 'globe',
        routeName: 'persetujuan.eksternal',
        stats: 8,
        statsLabel: 'Menunggu Review',
        colors: {
            bg: 'bg-emerald-50 dark:bg-emerald-900/20',
            icon: 'bg-emerald-500 text-white',
            text: 'text-emerald-600 dark:text-emerald-400',
            border: 'hover:border-emerald-300 dark:hover:border-emerald-800',
            shadow: 'hover:shadow-emerald-100 dark:hover:shadow-emerald-900/30'
        }
    }
]);

const goToMenu = (routeName: string) => {
    router.get(route(routeName));
};
</script>

<template>
    <Head title="Persetujuan Diklat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pusat Persetujuan Diklat</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Pilih kategori persetujuan untuk memproses pengajuan diklat karyawan.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                
                <button
                    v-for="menu in approvalMenus"
                    :key="menu.title"
                    @click="goToMenu(menu.routeName)"
                    class="group relative flex flex-col items-start overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                    :class="[menu.colors.border, menu.colors.shadow]"
                >
                    
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full opacity-10 transition-transform duration-300 group-hover:scale-150 bg-current"
                         :class="menu.colors.icon.split(' ')[1]">
                    </div>

                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl shadow-sm transition-transform duration-200 group-hover:scale-110"
                         :class="menu.colors.icon">
                        
                        <svg v-if="menu.icon === 'user-academic'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>

                        <svg v-else-if="menu.icon === 'building'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
                            <path d="M9 22v-4h6v4"/>
                            <path d="M8 6h.01"/>
                            <path d="M16 6h.01"/>
                            <path d="M12 6h.01"/>
                            <path d="M12 10h.01"/>
                            <path d="M12 14h.01"/>
                            <path d="M16 10h.01"/>
                            <path d="M16 14h.01"/>
                            <path d="M8 10h.01"/>
                            <path d="M8 14h.01"/>
                        </svg>

                        <svg v-else-if="menu.icon === 'globe'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                            <path d="M2 12h20"/>
                        </svg>
                    </div>

                    <h2 class="mb-2 text-lg font-bold text-slate-900 transition-colors dark:text-white">
                        {{ menu.title }}
                    </h2>
                    <p class="mb-6 flex-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        {{ menu.description }}
                    </p>

                    <div class="flex w-full items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
                        <div>
                            <span class="text-2xl font-extrabold" :class="menu.colors.text">{{ menu.stats }}</span>
                            <p class="text-xs text-slate-400">{{ menu.statsLabel }}</p>
                        </div>
                        
                        <div class="flex h-10 w-10 items-center justify-center rounded-full transition-all duration-200 group-hover:translate-x-1"
                             :class="menu.colors.bg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="menu.colors.text" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </button>

            </div>
        </div>
    </AppLayout>
</template>