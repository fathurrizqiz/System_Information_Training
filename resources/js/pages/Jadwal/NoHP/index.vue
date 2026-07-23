<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { toast } from 'vue3-toastify';

// Definisi Interface untuk Type Safety
interface KaryawanAutocomplete {
    id: number;
    nama_karyawan: string;
    bagian: string;
    nrp?: string | null;
}

interface NoHpKaryawan {
    id: number;
    nama: string;
    nomor_wa: string;
    email?: string | null;
    bagian?: string | null;
    nrp?: string | null;
}

const props = defineProps<{
    karyawan: KaryawanAutocomplete[];
    noHpKaryawan: NoHpKaryawan[];
}>();

const searchQuery = ref<string>('');
const showDropdown = ref<boolean>(false);

// Form dengan Type Safety dari Inertia
const form = useForm({
    nama: '' as string,
    nomor_wa: '' as string,
    bagian: '' as string,
    nrp: '' as string | null,
    email: '' as string | null,
});

// Logic Autocomplete
const filteredKaryawan = computed(() => {
    if (searchQuery.value === '') return [];
    return props.karyawan.filter((user) => 
        user.nama_karyawan.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        user.bagian.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
        user.nrp?.toLowerCase().includes(searchQuery.value.toLowerCase()) 
    );
});

const selectKaryawan = (user: KaryawanAutocomplete): void => {
    form.nama = user.nama_karyawan;
    form.bagian = user.bagian;
    form.nrp = user.nrp;
    searchQuery.value = user.nama_karyawan;
    showDropdown.value = false;
};

const submit = (): void => {
    form.post(route('nohp.store'), {
        onSuccess: () => {
            toast.success('Nomor berhasil ditambahkan');
            form.reset();
            searchQuery.value = '';
        },
        onError: () => {
            toast.error('Gagal menambahkan nomor. Pastikan data valid.');
        }
    });
};

const hapusData = (id: number): void => {
    if (confirm('Hapus nomor ini?')) {
        router.delete(route('nohp.destroy', id),{
            onSuccess: () => {
                toast.success('Nomor berhasil dihapus');
            }
        });
    }
};

function goTemplate() {
    router.get(route('template.index'));
}

</script>

<template>
    <Head title="Manajemen Nomor WhatsApp" />

    <AppLayout>
    <div class="p-6 max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 tracking-tight">Manajemen Nomor WhatsApp</h2>

        <button @click="goTemplate" class="bg-blue-500 m-2 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
            Template Pesan WA
        </button>
        <div class="flex flex-col lg:flex-row gap-6">
            
            <!-- KOLOM KIRI: TABEL DAFTAR NOMOR -->
            <div class="w-full lg:w-2/3 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">Daftar Nomor Terdaftar</h3>
                    <span class="px-2.5 py-0.5 bg-gray-200 text-gray-700 text-xs font-bold rounded-full">
                        {{ noHpKaryawan.length }} Orang
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Karyawan</th>
                                <th class="px-6 py-4 font-semibold">Nomor WhatsApp</th>
                                <th class="px-6 py-4 font-semibold">Email</th>
                                <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in noHpKaryawan" :key="item.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800">{{ item.nama }}</div>
                                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ item.bagian }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-50 text-green-700 border border-green-100 font-mono">
                                        {{ item.nomor_wa }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-50 text-green-700 border border-green-100 font-mono">
                                        {{ item.email }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="hapusData(item.id)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="noHpKaryawan.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-10 w-10 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p class="italic">Belum ada nomor yang didaftarkan.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KOLOM KANAN: FORM TAMBAH -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 sticky top-6">
                    <div class="mb-5">
                        <h3 class="font-bold text-gray-700">Tambah Nomor</h3>
                        <p class="text-xs text-gray-500">Pilih nama karyawan untuk registrasi nomor WA.</p>
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Autocomplete Nama -->
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-widest">Cari Karyawan</label>
                            <input 
                                v-model="searchQuery" 
                                @focus="showDropdown = true"
                                type="text" 
                                placeholder="Ketik nama..."
                                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition border-gray-200"
                            />
                            
                            <!-- Dropdown Autocomplete -->
                            <div v-if="showDropdown && filteredKaryawan.length > 0" 
                                 class="absolute z-20 w-full mt-1 bg-white border border-gray-100 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <div 
                                    v-for="user in filteredKaryawan" 
                                    :key="user.id"
                                    @click="selectKaryawan(user)"
                                    class="px-4 py-2.5 hover:bg-green-50 cursor-pointer text-sm border-b border-gray-50 last:border-0 transition"
                                >
                                    <span class="font-medium text-gray-700">{{ user.nama_karyawan }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Input Nomor WA -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-widest">Nomor WA</label>
                            <div class="relative">
                                <span class="absolute left-4 top-2.5 text-gray-400 text-sm font-mono">+</span>
                                <input 
                                    v-model="form.nomor_wa" 
                                    type="tel" 
                                    placeholder="628123xxx"
                                    class="w-full pl-7 pr-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition border-gray-200 font-mono text-sm"
                                />
                            </div>
                            <p v-if="form.errors.nomor_wa" class="text-red-500 text-xs mt-1.5 font-medium">{{ form.errors.nomor_wa }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-widest">Email</label>
                            <div class="relative">
                                <span class="absolute left-4 top-2.5 text-gray-400 text-sm font-mono">+</span>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    placeholder="...@gmail.com"
                                    class="w-full pl-7 pr-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition border-gray-200 font-mono text-sm"
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1.5 font-medium">{{ form.errors.email }}</p>
                        </div>

                        <!-- Input Jabatan -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-widest">Jabatan</label>
                            <input 
                                v-model="form.bagian" 
                                type="text" 
                                placeholder="Contoh: Frontend Developer"
                                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition border-gray-200 text-sm"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-widest">NRP</label>
                            <input 
                                v-model="form.nrp" 
                                type="text" 
                                placeholder="Contoh: 123456789"
                                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition border-gray-200 text-sm"
                            />
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 px-4 rounded-lg transition disabled:opacity-50 shadow-lg shadow-gray-200 mt-2"
                        >
                            {{ form.processing ? 'Proses...' : 'Daftarkan Nomor' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
    </AppLayout>
</template>