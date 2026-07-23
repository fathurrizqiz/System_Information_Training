<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { computed, onMounted } from 'vue';

const props = defineProps<{
    detail: any;
    comments: {
        text: string;
        aspect: string; // 'materi' | 'pemateri'
        sentiment: string; // 'positive' | 'neutral' | 'negative'
    }[];
    sentiment: {
        materi: {
            positive: number;
            neutral: number;
            negative: number;
        };
        pemateri: {
            positive: number;
            neutral: number;
            negative: number;
        };
    };
}>();

// =========================================================
// PERSENTASE SENTIMEN
// =========================================================
const materiTotal =
    props.sentiment.materi.positive + props.sentiment.materi.negative + props.sentiment.materi.neutral;

const pemateriTotal =
    props.sentiment.pemateri.positive + props.sentiment.pemateri.negative + props.sentiment.pemateri.neutral;

const materiPositivePercent = materiTotal ? (props.sentiment.materi.positive / materiTotal) * 100 : 0;
const materiNegativePercent = materiTotal ? (props.sentiment.materi.negative / materiTotal) * 100 : 0;
const materiNeutralPercent = materiTotal ? (props.sentiment.materi.neutral / materiTotal) * 100 : 0;

const pemateriPositivePercent = pemateriTotal ? (props.sentiment.pemateri.positive / pemateriTotal) * 100 : 0;
const pemateriNegativePercent = pemateriTotal ? (props.sentiment.pemateri.negative / pemateriTotal) * 100 : 0;
const pemateriNeutralPercent = pemateriTotal ? (props.sentiment.pemateri.neutral / pemateriTotal) * 100 : 0;

// =========================================================
// KATA YANG SERING MUNCUL (word frequency)
// =========================================================
const STOPWORDS = new Set([
    'jelek', 'bagus', 'baik', 'sangat kurang', 'kurang',
]);

interface WordCount {
    word: string;
    count: number;
}

const topWords = computed<WordCount[]>(() => {
    const freq: Record<string, number> = {};

    for (const c of props.comments) {
        const words = c.text
            .toLowerCase()
            .replace(/[^\w\s]/g, ' ')
            .split(/\s+/)
            .filter((w) => w.length >= 3 && !STOPWORDS.has(w));

        for (const w of words) {
            freq[w] = (freq[w] || 0) + 1;
        }
    }

    return Object.entries(freq)
        .map(([word, count]) => ({ word, count }))
        .sort((a, b) => b.count - a.count)
        .slice(0, 15);
});

const maxWordCount = computed(() => (topWords.value.length ? topWords.value[0].count : 1));

// =========================================================
// LABEL HELPER UNTUK TABEL
// =========================================================
function aspectLabel(aspect: string) {
    return aspect === 'materi' ? 'Materi' : aspect === 'pemateri' ? 'Pemateri' : aspect;
}

function sentimentLabel(sentiment: string) {
    return sentiment === 'positive' ? 'Positif' : sentiment === 'neutral' ? 'Netral' : 'Negatif';
}

function sentimentClass(sentiment: string) {
    return sentiment === 'positive'
        ? ' text-green-700'
        : sentiment === 'neutral'
          ? 'text-yellow-700'
          : 'text-red-700';
}

function aspectClass(aspect: string) {
    return aspect === 'materi' ? 'bg-blue-300 text-white' : 'bg-blue-400 text-white';
}

// =========================================================
// CHARTS
// =========================================================
onMounted(() => {
    const ctxMateri = document.getElementById('chartMateri') as HTMLCanvasElement;
    const ctxPemateri = document.getElementById('chartPemateri') as HTMLCanvasElement;
    const ctxWords = document.getElementById('chartWords') as HTMLCanvasElement;

    if (ctxMateri) {
        new Chart(ctxMateri, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Negative', 'Neutral'],
                datasets: [
                    {
                        data: [
                            props.sentiment.materi.positive,
                            props.sentiment.materi.negative,
                            props.sentiment.materi.neutral,
                        ],
                        backgroundColor: ['#16a34a', '#dc2626', '#ca8a04'],
                    },
                ],
            },
        });
    }

    if (ctxPemateri) {
        new Chart(ctxPemateri, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Negative', 'Neutral'],
                datasets: [
                    {
                        data: [
                            props.sentiment.pemateri.positive,
                            props.sentiment.pemateri.negative,
                            props.sentiment.pemateri.neutral,
                        ],
                        backgroundColor: ['#16a34a', '#dc2626', '#ca8a04'],
                    },
                ],
            },
        });
    }

    if (ctxWords && topWords.value.length) {
        new Chart(ctxWords, {
            type: 'bar',
            data: {
                labels: topWords.value.map((w) => w.word),
                datasets: [
                    {
                        label: 'Frekuensi',
                        data: topWords.value.map((w) => w.count),
                        backgroundColor: '#3b82f6',
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } },
                },
            },
        });
    }
});
</script>

<template>
    <Head title="Detail Evaluasi" />

    <AppLayout>
        <div class="space-y-6 p-6">
            <h1 class="text-2xl font-bold">
                {{ detail.nama_diklat }}
            </h1>

            <!-- CHART SENTIMEN -->
            <div class="flex gap-5">
                <div class="rounded-lg border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 font-semibold">Materi</h2>
                    <canvas id="chartMateri"></canvas>
                    <div class="mt-3 flex justify-between text-xs">
                        <span class="text-green-600">{{ materiPositivePercent.toFixed(1) }}%</span>
                        <span class="text-yellow-600">{{ materiNeutralPercent.toFixed(1) }}%</span>
                        <span class="text-red-600">{{ materiNegativePercent.toFixed(1) }}%</span>
                    </div>
                </div>

                <div class="rounded-lg border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold">Pemateri</h2>
                    <canvas id="chartPemateri"></canvas>
                    <div class="mt-3 flex justify-between text-xs">
                        <span class="text-green-600">{{ pemateriPositivePercent.toFixed(1) }}%</span>
                        <span class="text-yellow-600">{{ pemateriNeutralPercent.toFixed(1) }}%</span>
                        <span class="text-red-600">{{ pemateriNegativePercent.toFixed(1) }}%</span>
                    </div>
                </div>
            </div>

            <!-- KATA YANG SERING MUNCUL -->
            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">Kata yang Sering Muncul</h2>

                <div v-if="!topWords.length" class="text-gray-500">Belum ada data komentar</div>

                <div v-else class="space-y-4">
                    <!-- <canvas id="chartWords" :height="topWords.length * 28"></canvas> -->

                    <!-- Tag cloud sederhana sebagai tampilan alternatif -->
                    <div class="flex flex-wrap gap-2 border-t pt-4">
                        <span
                            v-for="w in topWords"
                            :key="w.word"
                            class="rounded-full bg-blue-50 px-3 py-1 text-blue-700"
                            :style="{
                                fontSize: `${0.75 + (w.count / maxWordCount) * 0.75}rem`,
                            }"
                        >
                            {{ w.word }} <span class="text-xs text-blue-400">({{ w.count }})</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABEL KOMENTAR -->
            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold">Semua Komentar</h2>

                <div v-if="!comments.length" class="text-gray-500">Tidak ada komentar</div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="p-3 font-semibold">Komentar</th>
                                <th class="p-3 font-semibold">Label</th>
                                <th class="p-3 font-semibold">Hasil Klasifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(comment, i) in comments" :key="i" class="border-b hover:bg-gray-50">
                                <td class="p-3">{{ comment.text }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="aspectClass(comment.aspect)"
                                    >
                                        {{ aspectLabel(comment.aspect) }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="sentimentClass(comment.sentiment)"
                                    >
                                        {{ sentimentLabel(comment.sentiment) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>