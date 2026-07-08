// resources/js/types/inertia.d.ts
import { PageProps as InertiaPageProps } from '@inertiajs/core';

// Definisikan tipe filter & pagination di sini (atau import dari filter.ts)
export interface CommonFilters {
    searchQuery?: string;
    searchJabatan?: string;
    searchBidang?: string;
    perpage?: number;
}

export interface PaginationData {
    currentPage: number;
    lastPage: number;
    total: number;
    perPage: number;
}

export interface Karyawan {
    id: number;
    nama_karyawan: string;
    nrp: string;
    posisi_jabatan: string;
}

export interface KaryawanForm {
    nama_karyawan: string;
    nrp: string;
    posisi_jabatan: string;
}

export interface GajiKaryawan {
    id: number;
    karyawan_id: number;
    nrp: string;
    kolom: string;
    bulan: string;
    gaji_pokok: number;
    gaji_pokok_variable: number | null;
    tunjangan_jabatan_tetap: number | null;
    gaji_pokok_real: number;
    upah: number;
    karyawan: Karyawan; // Data relasi
}

export interface GajiKaryawanForm {
    karyawan_id: string | number;
    nrp: string;
    kolom: string;
    bulan: string;
    gaji_pokok: string | number;
    gaji_pokok_variable: string | number | null;
    tunjangan_jabatan_tetap: string | number | null;
}

export interface LemburKaryawanForm {
    karyawan_id: string;
    nrp: string;
    tempat_lembur: string;
    waktu_mulai_lembur: string;
    jam_mulai_lembur: string;
    ruangan_lembur: string;
    jam_lembur_1_input: string;
    total_jam: string; // This is an input again
    jam_lembur_2_input: string; // For reference
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps {
        auth: {
            user: {
                id: number;
                name: string;
                nrp: string;
                roles: string | string[];
            } | null;
        };
        // Tambahkan sebagai opsional
        filters?: CommonFilters;
        pagination?: PaginationData;
    }
    interface TokenFlash {
        token_link?: {
            pree?: string;
            post?: string;
            flash?:string;
        };
        success?: string;
        error?: string;
    }

    const flash = $page.props.flash as TokenFlash;
}
