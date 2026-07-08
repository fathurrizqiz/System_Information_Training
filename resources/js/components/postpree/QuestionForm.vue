<script setup lang="ts">
import { ref, watch } from "vue";

interface Choice {
  id?: number;
  text: string;
  is_correct: boolean;
}

interface Question {
  id?: number;
  text: string;
  // pertanyaan: string;
  choices: Choice[];
}

const props = defineProps<{ 
  modelValue: Question[];
 }>();
const emit = defineEmits(["update:modelValue"]);

const questions = ref<Question[]>(props.modelValue || []);

watch(
  () => props.modelValue,
  (newVal) => (questions.value = newVal || []),
  { deep: true }
);

function addQuestion() {
  questions.value.push({
    text: "",
    choices: [{ text: "", is_correct: false }],
    // pertanyaan:""
  });
  emit("update:modelValue", questions.value);
}

function removeQuestion(index: number) {
  questions.value.splice(index, 1);
  emit("update:modelValue", questions.value);
}

function addChoice(qIndex: number) {
  questions.value[qIndex].choices.push({ text: "", is_correct: false });
  emit("update:modelValue", questions.value);
}

function removeChoice(qIndex: number, cIndex: number) {
  if (questions.value[qIndex].choices.length <= 1) return;
  questions.value[qIndex].choices.splice(cIndex, 1);
  emit("update:modelValue", questions.value);
}

function markAsCorrect(qIndex: number, cIndex: number) {
  questions.value[qIndex].choices.forEach(
    (choice, i) => (choice.is_correct = i === cIndex)
  );
  emit("update:modelValue", questions.value);
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-bold">Daftar Soal</h2>

      <button
        @click="addQuestion"
        class="px-3 py-2 bg-blue-600 text-white rounded-lg"
      >
        + Tambah Soal
      </button>
    </div>

    <div
      v-for="(q, qi) in questions"
      :key="qi"
      class="border rounded-lg p-4 bg-white shadow"
    >
      <!-- Header soal -->
      <div class="flex justify-between mb-3">
        <h3 class="font-semibold">Soal {{ qi + 1 }}</h3>

        <button @click="removeQuestion(qi)">
          <!-- trash icon -->
          <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none">
            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              d="M3 6h18M8 6v12m8-12v12M5 6l1 14h12l1-14M10 3h4l1 3H9l1-3z"/>
          </svg>
        </button>
      </div>

      <!-- Input soal -->
      <textarea
        v-model="q.text"
        class="w-full border p-2 rounded"
        placeholder="Tulis soal..."
      ></textarea>

      <!-- Pilihan -->
      <div class="mt-4 space-y-3">
        <h4 class="font-medium text-gray-700">Pilihan Jawaban:</h4>

        <div
          v-for="(c, ci) in q.choices"
          :key="ci"
          class="flex items-center gap-3"
        >
          <!-- Button mark correct -->
          <button @click="markAsCorrect(qi, ci)" class="w-5 h-5">
            <template v-if="c.is_correct">
              <!-- check-circle -->
              <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                <path d="M8 12l2 2 4-4"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </template>
            <template v-else>
              <!-- empty circle -->
              <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" fill="none"/>
              </svg>
            </template>
          </button>

          <!-- input jawaban -->
          <input
            v-model="c.text"
            class="flex-1 border p-2 rounded"
            placeholder="Teks jawaban..."
          />

          <!-- remove choice -->
          <button
            v-if="q.choices.length > 1"
            @click="removeChoice(qi, ci)"
          >
            <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none">
              <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                d="M3 6h18M8 6v12m8-12v12M5 6l1 14h12l1-14M10 3h4l1 3H9l1-3z"/>
            </svg>
          </button>
        </div>

        <button @click="addChoice(qi)" class="text-blue-600 text-sm mt-2">
          + Tambah Pilihan
        </button>
      </div>
    </div>
  </div>
</template>
