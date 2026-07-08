<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    data: {
        id:number
    }
    token:string
}>();

const evaluasimateri = ref('');
const evaluasipengajar = ref('');

function submitEvaluasi() {
    if (!evaluasimateri.value.trim()) {
        alert('Evaluasi tidak boleh kosong');
        return;
    }
    if (!evaluasipengajar.value.trim()) {
        alert('Evaluasi tidak boleh kosong');
        return;
    }

    router.post('/test/evaluasi/post', {
        detail_id: props.data.id,
        evaluasimateri: evaluasimateri.value,
        evaluasipengajar: evaluasipengajar.value,
        token:props.token, 

    },{
        onSuccess: () => {
            toast.success('Evaluasi Berhasil Disimpan!, Terima kasih telah partisipasi!')
        }
    });
}
</script>
<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-lg bg-white rounded-lg shadow-md p-6">
            <!-- Title -->
            <h1 class="text-2xl font-bold text-center mb-2">
                Evaluasi Diklat
            </h1>
            <p class="text-center text-gray-500 mb-6">
                Mohon berikan evaluasi Anda untuk peningkatan kualitas diklat
            </p>

            <!-- Form -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Evaluasi Materi
                    </label>

                    <textarea
                        v-model="evaluasimateri"
                        rows="5"
                        placeholder="Tulis evaluasi Anda di sini..."
                        class="w-full rounded-md border border-gray-300 p-3 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Evaluasi Pemateri
                    </label>

                    <textarea
                        v-model="evaluasipengajar"
                        rows="5"
                        placeholder="Tulis evaluasi Anda di sini..."
                        class="w-full rounded-md border border-gray-300 p-3 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <button
                    @click="submitEvaluasi"
                    class="w-full rounded-md bg-green-600 py-2 font-semibold text-white hover:bg-green-700 transition"
                >
                    Kirim Evaluasi
                </button>
            </div>
        </div>
    </div>
</template>

