<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import axios from 'axios';
import {
    Award,
    BookOpen,
    FileText,
    Layers,
    Loader2,
    Search as SearchIcon,
    Trash2,
} from 'lucide-vue-next';
import { nextTick, onMounted, ref } from 'vue';

interface SearchResult {
    files: any[];
    sertifikat: any[];
    pelatihan: any[];
    program_pelatihan: any[];
}

interface Message {
    id: number;
    type: 'user' | 'assistant';
    content: string;
    results?: SearchResult;
}

const searchTypes = [
    { label: ' File', value: 'file' },
    { label: ' Sertifikat', value: 'sertifikat' },
    { label: ' Pelatihan', value: 'pelatihan' },
    { label: ' Program', value: 'program_pelatihan' },
];

const keyword = ref('');
const selectedType = ref('all');
const includeTrash = ref(false);
const loading = ref(false);
const messages = ref<Message[]>([]);
const currentStep = ref<'select_type' | 'input_keyword' | 'showing_results'>(
    'select_type',
);
let messageIdCounter = 0;
const chatContainer = ref<HTMLDivElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);

function initChat() {
    messages.value = [];
    currentStep.value = 'select_type';
    addAssistantMessage('Halo! Saya AI Eichar. Apa yang ingin Anda cari?');
}

function addAssistantMessage(content: string, results?: SearchResult) {
    messageIdCounter++;
    messages.value.push({
        id: messageIdCounter,
        type: 'assistant',
        content,
        results,
    });
    scrollToBottom();
}

function addUserMessage(content: string) {
    messageIdCounter++;
    messages.value.push({ id: messageIdCounter, type: 'user', content });
    scrollToBottom();
}

function handleTypeSelection(type: string, label: string) {
    selectedType.value = type;
    addUserMessage(`Saya ingin mencari ${label}`);
    setTimeout(() => {
        addAssistantMessage(
            `Baik! Fokus: **${label}**. Silakan ketik kata kunci:`,
        );
        currentStep.value = 'input_keyword';
        nextTick(() => searchInputRef.value?.focus());
    }, 500);
}

async function executeSearch() {
    if (!keyword.value || keyword.value.length < 2) return;
    const searchKeyword = keyword.value;
    addUserMessage(`Mencari "${searchKeyword}"...`);
    keyword.value = '';
    loading.value = true;

    try {
        const response = await axios.post('/execute', {
            keyword: searchKeyword,
            type: selectedType.value,
            include_trash: includeTrash.value,
        });

        const results = response.data.data;
        const total = response.data.total;

        let resultText =
            total === 0
                ? `Maaf, tidak ditemukan hasil untuk "${searchKeyword}" .`
                : `Ditemukan **${total} hasil** untuk "${searchKeyword}".`;

        addAssistantMessage(resultText, results);
        currentStep.value = 'showing_results';
    } catch (error) {
        addAssistantMessage('Terjadi kesalahan saat mencari.');
    } finally {
        loading.value = false;
    }
}

// function handleItemClick(item: any) {
//   let route = ''
//   switch (item.type) {
//     case 'file':
//     case 'pelatihan_karyawan':
//       route = `/DiklatInternal/detail/${item.id}`
//       break
//     case 'pelatihan_eksternal':
//     case 'sertifikat_eksternal':
//       route = `/Admin/Eksternal/preview/${item.id}`
//       break
//     case 'pelatihan_hlc':
//     case 'sertifikat_hlc':
//       route = `/HLC/Home/detail/${item.id}`
//       break
//   }

//   if (route) {
//     addUserMessage(`Buka: ${item.title}`)
//     setTimeout(() => router.visit(route), 800)
//   }
// }

function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value)
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    });
}

onMounted(() => initChat());
</script>

<template>
    <AppLayout title="AI">
        <div class="flex h-[calc(100vh-4rem)] flex-col bg-gray-50">
            <!-- Header -->
            <div class="z-10 border-b bg-white px-6 py-4 shadow-sm">
                <h1
                    class="flex items-center gap-2 text-xl font-bold text-gray-800"
                >
                    <SearchIcon class="h-6 w-6 text-blue-600" /> ECH.AI
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Temukan lokasi file, sertifikat, atau pelatihan Anda.
                </p>
            </div>

            <!-- Chat Area -->
            <div
                ref="chatContainer"
                class="flex-1 space-y-6 overflow-y-auto p-6"
            >
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="flex w-full"
                    :class="
                        msg.type === 'user' ? 'justify-end' : 'justify-start'
                    "
                >
                    <!-- Assistant Bubble -->
                    <div
                        v-if="msg.type === 'assistant'"
                        class="flex max-w-[85%] gap-3"
                    >
                        <div
                            class="mt-1 flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white"
                        >
                            AI
                        </div>
                        <div class="space-y-3">
                            <div
                                class="rounded-2xl rounded-tl-none border bg-white p-4 text-gray-800 shadow-sm"
                            >
                                <p
                                    class="whitespace-pre-line"
                                    v-html="
                                        msg.content.replace(
                                            /\*\*(.*?)\*\*/g,
                                            '<strong>$1</strong>',
                                        )
                                    "
                                ></p>
                            </div>

                            <!-- Results Cards (DISABLED CLICK) -->
                            <div v-if="msg.results" class="space-y-4 pl-2">
                                <!-- Files Section -->
                                <div
                                    v-if="msg.results.files.length"
                                    class="space-y-2"
                                >
                                    <div
                                        class="ml-1 flex items-center gap-1 text-xs font-bold text-gray-500 uppercase"
                                    >
                                        <FileText class="h-3 w-3" /> File
                                        Dokumen
                                    </div>
                                    <div
                                        v-for="f in msg.results.files"
                                        :key="f.id"
                                        class="flex cursor-not-allowed items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 opacity-75 select-none"
                                    >
                                        <div
                                            class="rounded-lg bg-blue-50 p-2 text-blue-600"
                                        >
                                            <FileText class="h-5 w-5" />
                                        </div>
                                        <div class="flex-1">
                                            <h4
                                                class="text-sm font-medium text-gray-700"
                                            >
                                                {{ f.title }}
                                            </h4>
                                            <p class="text-xs text-gray-500">
                                                Tanggal: {{ f.tanggal }}
                                            </p>
                                            <p
                                                class="mt-1 text-[10px] font-semibold text-blue-600"
                                            >
                                                Lokasi: Pelatihan Karyawan
                                                (Internal)
                                            </p>
                                        </div>
                                        <div
                                            v-if="f.in_trash"
                                            class="flex items-center gap-1 rounded-full border border-red-100 bg-red-50 px-2 py-1 text-[10px] font-bold text-red-600"
                                        >
                                            <Trash2 class="h-3 w-3" /> Di Trash
                                        </div>
                                    </div>
                                </div>

                                <!-- Sertifikat Section -->
                                <div
                                    v-if="msg.results.sertifikat.length"
                                    class="space-y-2 border-t pt-2"
                                >
                                    <div
                                        class="ml-1 flex items-center gap-1 text-xs font-bold text-gray-500 uppercase"
                                    >
                                        <Award class="h-3 w-3" /> Sertifikat
                                    </div>
                                    <div
                                        v-for="s in msg.results.sertifikat"
                                        :key="s.id"
                                        class="flex cursor-not-allowed items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 opacity-75 select-none"
                                    >
                                        <div
                                            class="rounded-lg bg-green-50 p-2 text-green-600"
                                        >
                                            <Award class="h-5 w-5" />
                                        </div>
                                        <div class="flex-1">
                                            <h4
                                                class="text-sm font-medium text-gray-700"
                                            >
                                                {{ s.title }}
                                            </h4>
                                            <p
                                                class="mt-1 text-[10px] font-semibold text-green-600"
                                            >
                                                📍 Lokasi:
                                                {{
                                                    s.type.includes('eksternal')
                                                        ? 'Pelatihan Eksternal'
                                                        : 'Pelatihan HLC'
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="s.in_trash"
                                            class="flex items-center gap-1 rounded-full border border-red-100 bg-red-50 px-2 py-1 text-[10px] font-bold text-red-600"
                                        >
                                            <Trash2 class="h-3 w-3" /> Di Trash
                                        </div>
                                    </div>
                                </div>

                                <!-- Pelatihan Section -->
                                <div
                                    v-if="msg.results.pelatihan.length"
                                    class="space-y-2 border-t pt-2"
                                >
                                    <div
                                        class="ml-1 flex items-center gap-1 text-xs font-bold text-gray-500 uppercase"
                                    >
                                        <BookOpen class="h-3 w-3" /> Riwayat
                                        Pelatihan
                                    </div>
                                    <div
                                        v-for="t in msg.results.pelatihan"
                                        :key="t.id"
                                        class="flex cursor-not-allowed items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 opacity-75 select-none"
                                    >
                                        <div
                                            class="rounded-lg bg-purple-50 p-2 text-purple-600"
                                        >
                                            <BookOpen class="h-5 w-5" />
                                        </div>
                                        <div class="flex-1">
                                            <h4
                                                class="text-sm font-medium text-gray-700"
                                            >
                                                {{ t.title }}
                                            </h4>
                                            <p class="text-xs text-gray-500">
                                                {{ t.penyelenggara }}
                                            </p>
                                            <p
                                                class="mt-1 text-[10px] font-semibold text-purple-600"
                                            >
                                                Lokasi:
                                                {{
                                                    t.type.includes('karyawan')
                                                        ? 'Internal'
                                                        : t.type.includes(
                                                                'eksternal',
                                                            )
                                                          ? 'Eksternal'
                                                          : 'HLC'
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="t.in_trash"
                                            class="flex items-center gap-1 rounded-full border border-red-100 bg-red-50 px-2 py-1 text-[10px] font-bold text-red-600"
                                        >
                                            <Trash2 class="h-3 w-3" /> Di Trash
                                        </div>
                                    </div>
                                    <!-- Program Section -->
                                    <div
                                        v-if="
                                            msg.results.program_pelatihan.length
                                        "
                                        class="space-y-2 border-t pt-2"
                                    >
                                        <div
                                            class="ml-1 flex items-center gap-1 text-xs font-bold text-gray-500 uppercase"
                                        >
                                            <Layers class="h-3 w-3" /> Program
                                            Pelatihan
                                        </div>
                                        <div
                                            v-for="p in msg.results
                                                .program_pelatihan"
                                            :key="p.id"
                                            class="flex cursor-not-allowed items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 opacity-75 select-none"
                                        >
                                            <div
                                                class="rounded-lg bg-orange-50 p-2 text-orange-600"
                                            >
                                                <Layers class="h-5 w-5" />
                                            </div>
                                            <div class="flex-1">
                                                <h4
                                                    class="text-sm font-medium text-gray-700"
                                                >
                                                    {{ p.title }}
                                                </h4>
                                                <p
                                                    class="text-xs text-gray-500"
                                                    v-if="p.tahun"
                                                >
                                                    Tahun: {{ p.tahun }}
                                                </p>
                                                <p
                                                    class="mt-1 text-[10px] font-semibold text-orange-600"
                                                >
                                                    📍 Lokasi:
                                                    {{
                                                        p.type ===
                                                        'program_eksternal'
                                                            ? 'Program Eksternal'
                                                            : 'Program HLC'
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                v-if="p.in_trash"
                                                class="flex items-center gap-1 rounded-full border border-red-100 bg-red-50 px-2 py-1 text-[10px] font-bold text-red-600"
                                            >
                                                <Trash2 class="h-3 w-3" /> Di
                                                Trash
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Bubble -->
                    <div v-else class="max-w-[80%]">
                        <div
                            class="rounded-2xl rounded-tr-none bg-blue-600 p-3 text-white shadow-md"
                        >
                            <p class="text-sm">{{ msg.content }}</p>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="flex gap-3 pl-11">
                    <Loader2 class="h-5 w-5 animate-spin text-blue-600" />
                    <span class="text-sm text-gray-500"
                        >Sedang mencari di database...</span
                    >
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t bg-white p-4">
                <div
                    v-if="currentStep === 'select_type'"
                    class="mx-auto grid max-w-3xl grid-cols-2 gap-3 md:grid-cols-4"
                >
                    <button
                        v-for="type in searchTypes"
                        :key="type.value"
                        @click="handleTypeSelection(type.value, type.label)"
                        class="rounded-xl border p-3 text-sm font-medium transition-all hover:border-blue-300 hover:bg-blue-50"
                    >
                        {{ type.label }}
                    </button>
                </div>

                <div v-else class="relative mx-auto max-w-3xl">
                    <input
                        ref="searchInputRef"
                        v-model="keyword"
                        @keyup.enter="executeSearch"
                        type="text"
                        placeholder="Ketik kata kunci..."
                        class="w-full rounded-xl border py-3 pr-24 pl-4 focus:ring-2 focus:ring-blue-500"
                    />
                    <button
                        @click="executeSearch"
                        :disabled="!keyword"
                        class="absolute top-2 right-2 rounded-lg bg-blue-600 px-4 py-1.5 text-sm text-white disabled:opacity-50"
                    >
                        Cari
                    </button>
                    <div class="mt-2 flex items-center gap-2">
                        <input
                            v-model="includeTrash"
                            type="checkbox"
                            class="rounded text-blue-600"
                        />
                        <span class="text-xs text-gray-600"
                            >Sertakan item di Trash</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
