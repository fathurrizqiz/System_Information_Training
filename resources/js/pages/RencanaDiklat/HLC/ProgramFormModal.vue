<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
    program?: any; // Jika ada, berarti mode Edit
}>();

const emit = defineEmits(['close']);
const form = ref({ nama_program: '', tahun: '' });

// Isi form jika mode edit
watch(() => props.program, (newVal) => {
    if (newVal) form.value = { ...newVal };
}, { immediate: true });

const submit = () => {
    const url = props.program ? `/HLC/Home/updateProgram/${props.program.id}` : '/HLC/Home/storeProgram';
    router.post(url, form.value, {
        onSuccess: () => emit('close')
    });
};
</script>

<template>
    <div v-if="show" class="modal-overlay">
        <input v-model="form.nama_program" />
        <button @click="submit">Simpan</button>
    </div>
</template>