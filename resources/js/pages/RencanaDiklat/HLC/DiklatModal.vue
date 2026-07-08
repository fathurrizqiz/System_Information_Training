<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

// Definisi Interface agar TypeScript happy
interface Karyawan {
    id: number;
    nrp: string;
    nama_karyawan: string;
}

interface DiklatData {
    id?: number;
    program_id: number;
    nama_diklat: string;
    pengajar: string;
    penyelenggara: string;
    nrp: string;
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    jam_diklat: number | string;
    karyawan?: Karyawan | null; // Untuk mode edit, mengambil nama karyawan asal
}

const props = defineProps<{
    show: boolean;
    programId: number; // ID Program induk (wajib untuk Tambah)
    diklat?: DiklatData | null; // Jika ada = Edit, Jika null = Tambah
    karyawans: Karyawan[]; // Data untuk autocomplete
}>();

const emit = defineEmits(['close']);
const isLoading = ref(false);

// --- State Form ---
const form = ref<DiklatData>({
    program_id: props.programId,
    nama_diklat: '',
    pengajar: '',
    penyelenggara: '',
    nrp: '',
    tanggal_mulai: null,
    tanggal_selesai: null,
    jam_diklat: '',
});

// --- State Autocomplete Karyawan ---
const karyawanSearchQuery = ref('');
const isDropdownOpen = ref(false);
const selectedKaryawan = ref<Karyawan | null>(null);

// --- Watcher untuk mengisi data form (Mode Edit vs Tambah) ---
watch(() => props.diklat, (newVal) => {
    // Reset State Autocomplete
    selectedKaryawan.value = null;
    karyawanSearchQuery.value = '';

    if (newVal) {
        // Mode EDIT: Isi form dengan data yang ada
        form.value = { ...newVal };
        
        // Set info karyawan yang sudah terpilih sebelumnya
        if (newVal.karyawan) {
            selectedKaryawan.value = newVal.karyawan;
            karyawanSearchQuery.value = `${newVal.karyawan.nama_karyawan} (${newVal.karyawan.nrp})`;
        } else if (newVal.nrp) {
            // Jika hanya ada NRP, coba cari di list karyawans
            const found = props.karyawans.find(k => k.nrp === newVal.nrp);
            if (found) {
                selectedKaryawan.value = found;
                karyawanSearchQuery.value = `${found.nama_karyawan} (${found.nrp})`;
            }
        }
    } else {
        // Mode TAMBAH: Reset form kosong
        form.value = {
            program_id: props.programId,
            nama_diklat: '',
            pengajar: '',
            penyelenggara: '',
            nrp: '',
            tanggal_mulai: null,
            tanggal_selesai: null,
            jam_diklat: '',
        };
    }
}, { immediate: true });

// Pastikan program_id terupdate jika modal dibuka dari program yang berbeda
watch(() => props.programId, (newId) => {
    if (!props.diklat) {
        form.value.program_id = newId;
    }
});

// --- Logika Autocomplete (Dipindah dari file utama ke sini) ---
const filteredKaryawans = computed(() => {
    if (!karyawanSearchQuery.value || selectedKaryawan.value) {
        return []; // Sembunyikan jika kosong atau sudah memilih
    }
    const query = karyawanSearchQuery.value.toLowerCase();
    return props.karyawans.filter(
        (k) =>
            k.nama_karyawan.toLowerCase().includes(query) ||
            k.nrp.toLowerCase().includes(query),
    ).slice(0, 10); // Batasi 10 hasil agar performa bagus
});

const selectKaryawan = (karyawan: Karyawan) => {
    selectedKaryawan.value = karyawan;
    form.value.nrp = karyawan.nrp;
    karyawanSearchQuery.value = `${karyawan.nama_karyawan} (${karyawan.nrp})`;
    isDropdownOpen.value = false;
};

const clearKaryawan = () => {
    selectedKaryawan.value = null;
    form.value.nrp = '';
    karyawanSearchQuery.value = '';
};

// --- Submit Data ---
const submit = () => {
    if (!form.value.nama_diklat?.trim()) return toast.error('Nama diklat wajib diisi!');
    if (!form.value.tanggal_mulai || !form.value.tanggal_selesai) return toast.error('Tanggal wajib diisi!');

    isLoading.value = true;
    
    // Tentukan URL dan Data berdasarkan mode (Add/Edit)
    const url = props.diklat 
        ? `/HLC/Home/updateDetail/${props.diklat.id}`  // URL EDIT
        : '/HLC/Home/storeDetail';                     // URL ADD

    router.post(url, form.value, {
        onSuccess: () => {
            toast.success(props.diklat ? 'Diklat berhasil diperbarui!' : 'Diklat berhasil ditambahkan!');
            emit('close');
        },
        onError: (errors) => {
            toast.error('Gagal menyimpan: ' + Object.values(errors).flat().join(', '));
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="$emit('close')"></div>
        
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl" @click.stop>
                
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ diklat ? 'Edit Detail Diklat' : 'Tambah Diklat Baru' }}
                    </h3>
                </div>

                <div class="px-6 py-5 space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Nama Diklat</label>
                            <input v-model="form.nama_diklat" type="text" class="mt-1 h-10 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500/20" placeholder="Contoh: Teknik Presentasi Efektif" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Pengajar</label>
                            <input v-model="form.pengajar" type="text" class="mt-1 h-10 w-full rounded-lg border-slate-300 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Jam Diklat (Per Hari)</label>
                            <input v-model="form.jam_diklat" type="number" class="mt-1 h-10 w-full rounded-lg border-slate-300 text-sm" placeholder="Contoh: 8" />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Penyelenggara</label>
                            <input v-model="form.penyelenggara" type="text" class="mt-1 h-10 w-full rounded-lg border-slate-300 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                            <input v-model="form.tanggal_mulai" type="date" class="mt-1 h-10 w-full rounded-lg border-slate-300 text-sm" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                            <input v-model="form.tanggal_selesai" type="date" class="mt-1 h-10 w-full rounded-lg border-slate-300 text-sm" />
                        </div>

                        <div class="sm:col-span-2 mt-2">
                            <label class="block text-sm font-medium text-slate-700">Peserta (Karyawan)</label>
                            <div class="relative mt-1">
                                <input
                                    v-model="karyawanSearchQuery"
                                    @focus="isDropdownOpen = true"
                                    type="text"
                                    class="h-10 w-full rounded-lg border-slate-300 pl-3 pr-10 text-sm focus:border-blue-500 focus:ring-blue-500/20"
                                    placeholder="Ketik nama atau NRP..."
                                />
                                
                                <button v-if="selectedKaryawan" @click="clearKaryawan" type="button" class="absolute right-3 top-2.5 text-slate-400 hover:text-rose-500">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                </button>

                                <ul v-if="isDropdownOpen && filteredKaryawans.length > 0" class="absolute z-10 mt-1 max-h-48 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl outline-none">
                                    <li v-for="karyawan in filteredKaryawans" :key="karyawan.id" @mousedown="selectKaryawan(karyawan)" class="flex cursor-pointer select-none items-center px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                                        <span class="font-medium">{{ karyawan.nama_karyawan }}</span>
                                        <span class="ml-2 text-slate-400">({{ karyawan.nrp }})</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 bg-slate-50 px-6 py-4">
                    <button @click="$emit('close')" type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        Batal
                    </button>
                    <button @click="submit" type="button" :disabled="isLoading" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                        <svg v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24"></svg>
                        <span>{{ isLoading ? 'Menyimpan...' : (diklat ? 'Perbarui Diklat' : 'Simpan Diklat') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>