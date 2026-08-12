<script setup lang="ts">
import QuestionForm from '@/components/postpree/QuestionForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
// import { ArrowLeftIcon, DocumentCheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    detail_id: number;
    test: any;
}>();

const page = usePage();

const questions = ref(
    (props.test?.questions || []).map((q: any) => ({
        id: q.id,
        text: q.text ?? q.pertanyaan ?? '',
        choices: (q.choices || []).map((c: any) => ({
            id: c.id,
            text: c.text,
            is_correct: Boolean(c.is_correct),
        })),
    })),
);

function save() {
    // Debug 1: tampilkan props.detail_id
    // console.log('Detail ID:', props.detail_id);

    // // Debug 2: tampilkan questions saat ini
    // console.log('Questions payload:', questions.value);

    // Kirim request POST
    router.post(
        '/DiklatInternal/posttest',
        {
            detail_id: props.detail_id,
            questions: questions.value,
        },
        {
            onSuccess: (page) => {
        // DEBUG: Lihat apa saja isi props yang dikirim dari Laravel
        console.log('SEMUA PROPS DARI BACKEND:', page.props);

        // Coba cek apakah auto_download_url ada langsung di dalam page.props (tanpa .flash)
        const downloadUrl = (page.props as any)?.auto_download_url || (page.props.flash as any)?.auto_download_url;

        console.log('URL Download yang ditangkap:', downloadUrl);

        if (downloadUrl) {
            toast.success('Post-Test selesai! Sertifikat sedang diunduh otomatis...');
            
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.setAttribute('target', '_blank');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            toast.success('Post-Test Berhasil disimpan!');
        }
    },
            onError: (errors) => {
                toast.error('Pastikan Data Terisi dengan Benar!', errors);
            },
            onFinish: () => {
                console.log('Request selesai');
            },
        },
    );
}
</script>

<template>
    <AppLayout>
        <Head title="Post-Test" />

        <div class="mx-auto max-w-3xl">
            <div class="mb-6 flex items-center gap-3">
                <DocumentCheckIcon class="h-7 w-7 text-emerald-600" />
                <h1 class="text-2xl font-bold text-gray-800">Post-Test</h1>
            </div>

            <div
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <QuestionForm v-model="questions" />

                <div class="mt-8 flex justify-end">
                    <button
                        @click="save"
                        class="flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 font-medium text-white shadow transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    >
                        <CheckCircleIcon class="h-5 w-5" />
                        Simpan Post-Test
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
