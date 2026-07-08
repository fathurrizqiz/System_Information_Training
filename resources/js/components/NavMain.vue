<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar, // Tambahkan ini untuk cek kondisi sidebar
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const { state } = useSidebar(); // Ambil state 'expanded' atau 'collapsed'
</script>

<template>
    <SidebarGroup class="px-2 py-2">
        <SidebarGroupLabel class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
            Platform
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                    class="transition-all duration-200 hover:bg-gray-100 group"
                >
                    <Link :href="item.href" class="flex items-center w-full">
                        <!-- Kontainer Ikon -->
                        <div class="flex items-center justify-center">
                            <component :is="item.icon" class="w-5 h-5" />
                        </div>

                        <!-- Teks & Badge (Hanya muncul jika Sidebar melebar) -->
                        <div v-if="state === 'expanded'" class="ml-3 flex flex-1 items-center justify-between overflow-hidden">
                            <span class="truncate text-sm font-medium">{{ item.title }}</span>
                            
                            <!-- Badge Elegan -->
                            <span 
                                v-if="item.badge" 
                                class="ml-2 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-bold text-white shadow-sm ring-2 ring-white"
                            >
                                {{ item.badge }}
                            </span>
                        </div>
                        
                        <!-- Mini Dot Badge (Muncul saat Sidebar mengecil/collapsed) -->
                        <div 
                            v-if="state === 'collapsed' && item.badge" 
                            class="absolute top-1 right-1 flex h-2 w-2 rounded-full bg-red-600 ring-1 ring-white"
                        ></div>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>