<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookCopy, BookLock, BookMarked, BookOpen, CalendarCheck, CalendarCheck2, ChartLine, FileBadge, Folder, GraduationCap, Inbox, LayoutGrid, Library, Settings, ShieldAlert, ShieldCheck, Signature, TriangleAlert, UsersRound } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

interface MyPageProps {
    auth: {
        user: {
            id: number;
            name: string;
            roles: string | string[];
        } | null;
    };
    notifications?: {
        persetujuan_count?: number;
        jadwal_count?: number;
        inbox_count?: number; // Pastikan key ini ada di definisi tipe
    };
}

const page = usePage<MyPageProps>();

// PERBAIKAN UTAMA DI SINI: Gunakan snake_case sesuai backend
const persetujuanCount = computed(() => page.props.notifications?.persetujuan_count || 0);
const jadwalCount = computed(() => page.props.notifications?.jadwal_count || 0);
const countInboxData = computed(() => page.props.notifications?.inbox_count || 0); // Ubah dari InboxCount ke inbox_count

const isImpersonating = computed(() => page.props.auth?.is_impersonating || false);

// Ambil roles dengan aman
const rawRole = page.props.auth?.user?.roles || []; 
const roles = Array.isArray(rawRole) ? rawRole : [rawRole];

const mainNavItems = computed(() => [
    {
        title: 'User Management',
        href: '/super-admin/home', 
        icon: ShieldAlert,
        roles: ['super-admin'],
    },
    {
        title: 'Dashboard Diklat (admin)',
        href: dashboard(),
        icon: LayoutGrid,
        roles: ['admin_diklat'],
    },
    {
        title: 'Dashboard Diklat (user)',
        href: route('dashboard.user'),
        icon: LayoutGrid,
    },
    {
        title: 'Diklat',
        href: '/Diklat',
        icon: BookMarked,
    },
    {
        title: 'Sertifikat Internal',
        href: '/DiklatInternal/user',
        icon: FileBadge,
    },
    {
        title: 'Persetujuan',
        href: '/Persetujuan',
        icon: Signature,
        roles: ['admin_diklat'],
        // Badge hanya muncul jika > 0
        badge: persetujuanCount.value > 0 ? persetujuanCount.value : null,
    },
    {
        title: 'Library Materi',
        href: '/Materi',
        icon: Library,
    },
    {
        title: 'Rencana Diklat',
        href: '/RencanaDiklat/RPT/PF',
        icon: GraduationCap,
        roles: ['admin_diklat'],
    },
    {
        title: 'Jadwal Diklat',
        href: '/JadwalDiklat/Internal',
        icon: CalendarCheck,
        badge: jadwalCount.value > 0 ? jadwalCount.value : null,
    },
    {
        title: 'Evaluasi',
        href: '/Diklat/Evaluasi',
        icon: ChartLine,
        roles: ['admin_diklat'],
    },
    {
        title: 'Laporan',
        href: '/Laporan/Diklat',
        icon: BookLock,
        roles: ['admin_diklat'],
    },
    {
        title: 'Master Data',
        href: '/MasterData/home',
        icon: BookCopy,
        roles: ['admin_diklat'],
    },
    {
        title: 'Whattsapp Settings',
        href: '/Settings',
        icon: Settings,
        roles: ['admin_diklat'],
    },
    {
        title: 'Inbox',
        href: '/HLC/Home/user',
        icon: Inbox,
        // PERBAIKAN: Gunakan variable countInboxData yang sudah diperbaiki
        badge: countInboxData.value > 0 ? countInboxData.value : null,
    },
]);

const filteredNavItems = computed(() => {
    return mainNavItems.value.filter(item => {
        if (!item.roles) return true;
        // Pastikan item.roles adalah array sebelum melakukan .some
        const itemRoles = Array.isArray(item.roles) ? item.roles : [item.roles];
        return itemRoles.some(role => roles.includes(role));
    });
});

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="filteredNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- Pastikan footerNavItems didefinisikan atau hapus jika tidak dipakai -->
            <NavFooter :items="[]" /> 
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>