<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    show: boolean;
    program?: any; // Jika null = Tambah, Jika ada isi = Edit
}>();

const emit = defineEmits(['close']);
const isLoading = ref(false);

const form = ref({
    nama_program: '',
    tahun: new Date().getFullYear().toString(),
});

// Watch props untuk mengisi data jika mode Edit
watch(() => props.program, (newVal) => {
    if (newVal) {
        form.value = { 
            nama_program: newVal.nama_program, 
            tahun: newVal.tahun 
        };
    } else {
        form.value = { nama_program: '', tahun: new Date().getFullYear().toString() };
    }
}, { immediate: true });

const submit = () => {
    if (!form.value.nama_program) return toast.error('Nama program wajib diisi');
    
    isLoading.value = true;
    const url = props.program 
        ? `/HLC/Home/updateProgram/${props.program.id}` 
        : '/HLC/Home/storeProgram';

    router.post(url, form.value, {
        onSuccess: () => {
            toast.success(props.program ? 'Berhasil diperbarui' : 'Berhasil ditambah');
            emit('close');
        },
        onFinish: () => isLoading.value = false
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="$emit('close')"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-bold">{{ program ? 'Edit Program' : 'Tambah Program' }}</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium">Nama Program</label>
                    <input v-model="form.nama_program" type="text" class="w-full rounded-lg border-slate-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Tahun</label>
                    <input v-model="form.tahun" type="number" class="w-full rounded-lg border-slate-300" />
                </div>
            </div>
            <div class="flex justify-end gap-3 bg-slate-50 px-6 py-4">
                <button @click="$emit('close')" class="px-4 py-2 text-sm">Batal</button>
                <button @click="submit" :disabled="isLoading" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                    {{ isLoading ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </div>
    </div>
</template>