<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import Sidebar from '@/components/AppSidebar.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { LogOut } from 'lucide-vue-next';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const isImpersonating = computed(() => (page.props as any).is_impersonating);
// const impersonatorName = computed(() => (page.props as any).impersonatorName);
// Sementara tambahkan ini
console.log('ALL PROPS:', JSON.stringify(page.props));
</script>

<template>

    <!-- ✅ class impersonate-mode ditaruh di sini -->
    <div :class="{ 'impersonate-mode': isImpersonating }">

        <!-- ✅ Banner kuning di atas -->
        <div v-if="isImpersonating"
            class="fixed top-0 left-0 right-0 z-[9999] flex items-center justify-between
                   bg-black px-4 py-2 text-sm font-semibold text-white shadow-lg">
            <div class="flex items-center gap-2">
                <span class="animate-pulse text-base">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hat-glasses-icon lucide-hat-glasses"><path d="M14 18a2 2 0 0 0-4 0"/><path d="m19 11-2.11-6.657a2 2 0 0 0-2.752-1.148l-1.276.61A2 2 0 0 1 12 4H8.5a2 2 0 0 0-1.925 1.456L5 11"/><path d="M2 11h20"/><circle cx="17" cy="18" r="3"/><circle cx="7" cy="18" r="3"/></svg>
                </span>
                Anda login sebagai
                <span class="rounded bg-black px-2 py-0.5 font-bold">
                    {{ page.props.auth.user.name }}
                </span>
                
                
                <!-- <span class="font-bold"> — dipantau oleh {{ impersonatorName }}</span> -->
            </div>
            <Link
                :href="route('impersonation.leave')"
                method="post"
                as="button"
                class="flex items-center gap-1.5 rounded-lg bg-white/20 px-3 py-1.5
                       text-xs font-bold hover:bg-white/30 transition-all"
            >
                <LogOut class="h-3.5 w-3.5" />
                Kembali ke Admin
            </Link>
        </div>

        <SidebarProvider>
            <Sidebar />
            
            <!-- ✅ Offset agar konten tidak ketutupan banner -->
            <main class="flex-1 overflow-auto" :class="{ 'pt-9': isImpersonating }">
                <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4 lg:hidden">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 h-4" />
                    <span class="text-sm font-medium">Dashboard</span>
                </header>
                <slot />
            </main>
        </SidebarProvider>

    </div>
</template>

<style>
.impersonate-mode::after {
    content: '';
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 9998;
    box-shadow: inset 0 0 0 0px #0000;
}

.impersonate-mode aside {
    border-right: 2px solid #0000 !important;
}
</style>