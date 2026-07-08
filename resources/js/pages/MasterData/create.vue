<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

interface Props {
  errors?: Record<string, string>
  flash?: {
    success?: string
  }
}

const props = defineProps<Props>()

const form = useForm({
  kategori: '',
  target_jam: ''
})

function submit() {
  form.post('/MasterData/store')
  
}
</script>

<template>
  <div class="p-8 max-w-md mx-auto bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Tambah Target Jam</h1>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-gray-700">Kategori</label>
        <input
          v-model="form.kategori"
          type="text"
          class="w-full border rounded px-3 py-2"
        />
        <div v-if="form.errors.kategori" class="text-red-500 text-sm">
          {{ form.errors.kategori }}
        </div>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700">Target Jam</label>
        <input
          v-model="form.target_jam"
          type="number"
          class="w-full border rounded px-3 py-2"
        />
        <div v-if="form.errors.target_jam" class="text-red-500 text-sm">
          {{ form.errors.target_jam }}
        </div>
      </div>

      <button
        type="submit"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Simpan
      </button>
    </form>

    <div v-if="props.flash?.success" class="mt-4 text-green-600 font-semibold">
      {{ props.flash.success }}
    </div>
  </div>
</template>
