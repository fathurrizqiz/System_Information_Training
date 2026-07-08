<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center rounded-xl justify-center z-50">
    <div class="bg-white rounded p-5 w-80 shadow-lg">
      <h2 class="text-lg font-semibold mb-3">Konfirmasi Penghapusan</h2>

      <p class="text-sm mb-4">
        Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.
      </p>

      <div class="flex justify-end gap-2">
        <button @click="$emit('close')" class=" hover:bg-gray-200 px-3 py-1 border rounded">
          Batal
        </button>
        <button
          @click="$emit('confirm')"
          class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded"
        >
          Hapus
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  show: boolean;
}>();

defineEmits<{
  (e: "close"): void;
  (e: "confirm"): void;
}>();
</script>
