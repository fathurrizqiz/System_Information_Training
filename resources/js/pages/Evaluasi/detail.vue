<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { onMounted } from 'vue';

const props = defineProps<{
    detail: any;
    comments: {
        text: string;
        sentiment: string;
        
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

// hitung persentase
const materiTotal =
    props.sentiment.materi.positive + props.sentiment.materi.negative + props.sentiment.materi.neutral;

const pemateriTotal =
    props.sentiment.pemateri.positive + props.sentiment.pemateri.negative + props.sentiment.pemateri.neutral;

const materiPositivePercent = materiTotal
    ? (props.sentiment.materi.positive / materiTotal) * 100
    : 0;

const materiNegativePercent = materiTotal
    ? (props.sentiment.materi.negative / materiTotal) * 100
    : 0;

const materiNeutralPercent = materiTotal
    ? (props.sentiment.materi.neutral / materiTotal) * 100
    : 0;

const pemateriPositivePercent = pemateriTotal
    ? (props.sentiment.pemateri.positive / pemateriTotal) * 100
    : 0;

const pemateriNegativePercent = pemateriTotal
    ? (props.sentiment.pemateri.negative / pemateriTotal) * 100
    : 0;

const pemateriNeutralPercent = pemateriTotal
    ? (props.sentiment.pemateri.neutral / pemateriTotal) * 100
    : 0;

// Chart
onMounted(() => {
    const ctxMateri = document.getElementById('chartMateri') as HTMLCanvasElement;
    const ctxPemateri = document.getElementById('chartPemateri') as HTMLCanvasElement;

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
                            props.sentiment.materi.neutral
                        ],
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
                            props.sentiment.pemateri.neutral
                        ],
                    },
                ],
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

            <div class="flex gap-5">

                <div class="rounded-lg border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 font-semibold">Materi</h2>
    
                    <canvas id="chartMateri"></canvas>
    
                    <div class="mt-3 flex justify-between text-xs">
                        <span class="text-green-600">
                            {{ materiPositivePercent.toFixed(1) }}%
                        </span>
                        <span class="text-yellow-600">
                            {{ materiNeutralPercent.toFixed(1) }}%
                        </span>
                        <span class="text-red-600">
                            {{ materiNegativePercent.toFixed(1) }}%
                        </span>
                    </div>
                </div>
    
               
                <div class="rounded-lg border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold">Pemateri</h2>
    
                    <canvas id="chartPemateri"></canvas>
    
                    <div class="mt-3 flex justify-between text-xs">
                        <span class="text-green-600">
                            {{ pemateriPositivePercent.toFixed(1) }}%
                        </span>
                        <span class="text-yellow-600">
                            {{ pemateriNeutralPercent.toFixed(1) }}%
                        </span>
                        <span class="text-red-600">
                            {{ pemateriNegativePercent.toFixed(1) }}%
                        </span>
                    </div>
                </div>
            </div>


            
            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold">Semua Komentar</h2>

                <div v-if="!comments.length" class="text-gray-500">
                    Tidak ada komentar
                </div>

                <div class="space-y-3">
                    <div
                        v-for="(comment, i) in comments"
                        :key="i"
                        class="rounded border bg-gray-50 p-3"
                    >
                        <div class="flex justify-between">
                            <span>{{ comment.text }}</span>
                            <span class="text-xs text-gray-400">
                                <!-- {{ comment.aspect }} -->
                            </span>
                        </div>

                        <span
                            v-if="comment.sentiment === 'positive'"
                            class="text-green-600"
                        >
                            Positif
                        </span>
                        <span
                            v-else-if="comment.sentiment === 'neutral'"
                            class="text-yellow-600"
                        >
                            Netral
                        </span>
                        <span v-else class="text-red-600">
                            Negatif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>