<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

// Definisi tipe data untuk Props
interface AuthUser {
    nama: string;
    bagian: string;
    nrp: string;
}

const props = defineProps<{
    auth_user: AuthUser;
}>();

// Inisialisasi form Inertia dengan tipe data otomatis
const form = useForm({
    nama: props.auth_user.nama,
    bagian: props.auth_user.bagian,
    nomor_wa: '',
    nrp: props.auth_user.nrp,
});

const submit = () => {
    form.post(route('nohp.store.user'), {
        onSuccess: () => {
            form.reset('nomor_wa');
            toast.success('Nomor WhatsApp berhasil disimpan dan diverifikasi.');
        },
    });
};
</script>

<template>
    <AppLayout>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-6 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 transition-all duration-300">
            
            
            <div class="mb-6 text-center sm:text-left">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                    Verifikasi Nomor WhatsApp
                </h2>
                <p class="mt-1.5 text-sm text-gray-500">
                    Pastikan nomor yang Anda masukkan aktif untuk menerima jadwal kerja.
                </p>
            </div>

            
            <form @submit.prevent="submit" class="space-y-5">
                
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                   
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Karyawan</label>
                        <input 
                            type="text" 
                            v-model="form.nama" 
                            disabled 
                            class="mt-1 block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-600 text-sm cursor-not-allowed focus:border-gray-200 focus:ring-0"
                        />
                    </div>

                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">NRP</label>
                        <input 
                            type="text" 
                            v-model="form.nrp" 
                            disabled 
                            class="mt-1 block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-600 text-sm cursor-not-allowed focus:border-gray-200 focus:ring-0"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Bagian / Divisi</label>
                        <input 
                            type="text" 
                            v-model="form.bagian" 
                            disabled 
                            class="mt-1 block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-600 text-sm cursor-not-allowed focus:border-gray-200 focus:ring-0"
                        />
                    </div>
                </div>

                <hr class="border-gray-100 my-2" />

                
                <div>
                    <label for="nomor_wa" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Nomor WhatsApp Baru
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-sm">
                        <Input
                            id="nomor_wa"
                            type="tel" 
                            inputmode="numeric"
                            v-model="form.nomor_wa" 
                            placeholder="Contoh: 62895384223639"
                            class="block w-full text-base sm:text-sm rounded-xl border-gray-300 px-4 py-3 sm:py-2.5 focus:border-emerald-500 focus:ring-emerald-500 transition-colors"
                            :class="{'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.nomor_wa}"
                            :disabled="form.processing"
                        />
                    </div>
                    
                    <!-- Indikator Informasi / Error -->
                    <p v-if="!form.errors.nomor_wa" class="mt-1.5 text-xs text-gray-400">
                        Format otomatis disesuaikan ke kode negara (62).
                    </p>
                    <span v-else class="text-xs font-medium text-red-600 mt-1.5 flex items-center gap-1">
                        {{ form.errors.nomor_wa }}
                    </span>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full inline-flex justify-center items-center px-4 py-3 sm:py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm shadow-emerald-100"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Memproses...' : 'Simpan Nomor HP' }}
                    </button>
                </div>

            </form>
        </div>
    </div>
    </AppLayout>
</template>