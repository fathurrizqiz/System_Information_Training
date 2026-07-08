<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import QuestionForm from '@/components/postpree/QuestionForm.vue';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
// import { ArrowLeftIcon, AcademicCapIcon } from '@heroicons/vue';

const props = defineProps<{
  detail_id: number;
  test: any;
}>();

const questions = ref(props.test?.questions || []);

function save() {
  // console.log('Saving questions:', questions.value, 'detail_id:', props.detail_id);
  router.post('/DiklatInternal/preetest', {
    detail_id: props.detail_id,
    questions: questions.value
  },{
    onSuccess:() => {
      toast.success('Pree-Test Berhasil disimpan');
    },
    onError(errors){
      toast.error('Pastikan Data Terisi dengan Benar!:', errors);
    }
  });
}

</script>

<template>
  <AppLayout>
    <Head title="Pre-Test" />

    <div class="max-w-3xl mx-auto">
      <div class="mb-6 flex items-center gap-3">
        <AcademicCapIcon class="h-7 w-7 text-blue-600" />
        <h1 class="text-2xl font-bold text-gray-800">Pre-Test</h1>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <QuestionForm v-model="questions" />

        <div class="mt-8 flex justify-end">
          <button
            @click="save"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2"
          >
            <CheckCircleIcon class="h-5 w-5" />
            Simpan Pre-Test
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>