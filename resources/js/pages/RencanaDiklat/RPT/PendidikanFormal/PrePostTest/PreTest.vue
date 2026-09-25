<script setup lang="ts">
import QuestionForm from '@/components/postpree/QuestionForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    detail_id: number;
    periode_id: number;
    test: any;
}>();

const questions = ref(
    (props.test?.questions || []).map((q: any) => ({
        id: q.id,
        text: q.text ?? q.pertanyaan ?? '',
        choices: (q.choices || []).map((c: any) => ({
            id: c.id,
            text: c.text ?? '',
            is_correct: Boolean(c.is_correct),
        })),
    })),
);

function save() {
    router.post(
        '/DiklatInternal/preetest',
        {
            detail_id: props.detail_id,
            periode_id: props.periode_id, // <-- KIRIM PERIODE_ID KE BACKEND
            questions: questions.value,
        },
        {
            onSuccess: () => {
                toast.success('Pree-Test Berhasil disimpan');
            },
            onError(errors) {
                toast.error('Pastikan Data Terisi dengan Benar!', errors);
            },
        },
    );
}
</script>

<template>
    <AppLayout>
        <Head title="Pre-Test" />

        <div class="mx-auto max-w-3xl">
            <div class="mb-6 flex items-center gap-3">
                <AcademicCapIcon class="h-7 w-7 text-blue-600" />
                <h1 class="text-2xl font-bold text-gray-800">Pre-Test</h1>
            </div>

            <div
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <QuestionForm v-model="questions" />

                <div class="mt-8 flex justify-end">
                    <button
                        @click="save"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white shadow transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                        <CheckCircleIcon class="h-5 w-5" />
                        Simpan Pre-Test
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
