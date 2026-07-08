<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import QuestionForm from '@/components/postpree/QuestionForm.vue';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
// import { ArrowLeftIcon, DocumentCheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  detail_id: number;
  test: any;
}>();

const questions = ref(
  (props.test?.questions || []).map((q: any) => ({
    id: q.id,
    text: q.text ?? q.pertanyaan ?? "",
    choices: (q.choices || []).map((c: any) => ({
      id: c.id,
      text: c.text,
      is_correct: Boolean(c.is_correct),
    })),
  }))
);


function save() {
  // Debug 1: tampilkan props.detail_id
  // console.log('Detail ID:', props.detail_id);

  // // Debug 2: tampilkan questions saat ini
  // console.log('Questions payload:', questions.value);

  // Kirim request POST
  router.post('/DiklatInternal/posttest', {
    detail_id: props.detail_id,
    questions: questions.value
  }, {
    onSuccess: () => {
      toast.success('Post-Test Berhasil disimpan!')
    },
    onError: (errors) => {
      toast.error('Pastikan Data Terisi dengan Benar!', errors);
    },
    onFinish: () => {
      console.log('Request selesai');
    }
  });
}

</script>

<template>
  <AppLayout>
    <Head title="Post-Test" />

    <div class="max-w-3xl mx-auto">
      <div class="mb-6 flex items-center gap-3">
        <DocumentCheckIcon class="h-7 w-7 text-emerald-600" />
        <h1 class="text-2xl font-bold text-gray-800">Post-Test</h1>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <QuestionForm v-model="questions" />

        <div class="mt-8 flex justify-end">
          <button
            @click="save"
            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-emerald-500 flex items-center gap-2"
          >
            <CheckCircleIcon class="h-5 w-5" />
            Simpan Post-Test
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>