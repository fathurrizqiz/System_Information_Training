<!-- SyllabusItem.vue -->
<script setup lang="ts">
import { type PropType } from 'vue';

interface SyllabusItemNode {
  id: number;
  name: string;
  type: 'folder' | 'file';
  children?: SyllabusItemNode[];
}

const props = defineProps({
  item: { type: Object as PropType<SyllabusItemNode>, required: true }
});

const emit = defineEmits<{
  (e: 'open-modal', parentId: number | null): void;
}>();

const handleAdd = () => emit('open-modal', props.item.id);

// Fungsi untuk dapatkan ikon file berdasarkan ekstensi
const getFileIcon = (filename: string): string => {
  const ext = filename.split('.').pop()?.toLowerCase() || '';
  switch (ext) {
    case 'pdf': return 'fas fa-file-pdf text-red-500';
    case 'doc':
    case 'docx': return 'fas fa-file-word text-blue-600';
    case 'xls':
    case 'xlsx': return 'fas fa-file-excel text-green-600';
    case 'ppt':
    case 'pptx': return 'fas fa-file-powerpoint text-orange-500';
    case 'mp4':
    case 'mov':
    case 'avi': return 'fas fa-file-video text-purple-500';
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif': return 'fas fa-file-image text-pink-500';
    default: return 'fas fa-file text-gray-500';
  }
};
</script>

<template>
  <li class="py-2">
    <div class="flex items-start group">
      <!-- Ikon utama -->
      <div class="relative flex-shrink-0 w-10 h-10 flex items-center justify-center">
        <i
          v-if="item.type === 'folder'"
          class="fas fa-folder text-yellow-500 text-xl"
        ></i>
        <i
          v-else
          :class="['text-xl', getFileIcon(item.name)]"
        ></i>

        <!-- Tombol tambah (hanya folder, muncul saat hover) -->
        <button
          v-if="item.type === 'folder'"
          @click="handleAdd"
          class="absolute -top-1 -right-1 bg-indigo-600 text-white rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-sm hover:bg-indigo-700"
          title="Tambah ke folder ini"
        >
          <i class="fas fa-plus text-xs"></i>
        </button>
      </div>

      <!-- Nama file/folder -->
      <span class="ml-3 text-gray-800 text-sm truncate max-w-[300px]">{{ item.name }}</span>
    </div>

    <!-- Anak-anak dengan indentasi -->
    <ul v-if="item.children?.length" class="ml-8 mt-1 space-y-1">
      <SyllabusItem
        v-for="child in item.children"
        :key="child.id"
        :item="child"
        @open-modal="emit('open-modal', $event)"
      />
    </ul>
  </li>
</template>