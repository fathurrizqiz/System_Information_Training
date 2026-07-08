<script setup lang="ts">
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import Input from "@/components/ui/input/Input.vue";
import { toast } from "vue3-toastify";

interface Choice {
    id: number;
    text: string;
}

interface Question {
    id: number;
    pertanyaan: string;
    choices: Choice[];
    nrp: string;
}

interface Karyawan {
    nrp: string;
    nama_karyawan: string;
    bagian?: string; 
}

interface Props {
    test: {
        id: number;
        type: string;
        questions: Question[];
    };
    detail_id: number;
    user_nrp: string | null;
    karyawans: Karyawan[]; 
    allowed_bagians: string[];
}

const props = defineProps<Props>();

const answers = ref<{ [key: number]: number | null }>({});
const loading = ref(false);
const nrp = ref(props.user_nrp || '');

// --- STATE UNTUK AUTOCOMPLETE ---
const showDropdown = ref(false);

const filteredKaryawan = computed(() => {
    if (!nrp.value) return [];
    const query = nrp.value.toLowerCase();
    
    // Filter berdasarkan NRP atau Nama, limit 5 hasil agar tidak terlalu panjang
    return props.karyawans.filter(k => 
        k.nrp.toLowerCase().includes(query) || 
        (k.nama_karyawan && k.nama_karyawan.toLowerCase().includes(query))
    ).slice(0, 5);
});

const selectNrp = (selected: Karyawan) => {
    nrp.value = selected.nrp;
    showDropdown.value = false;
};

// Delay penutupan dropdown agar klik item sempat tereksekusi sebelum onBlur
const hideDropdown = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
};
// ---------------------------------

const submitTest = () => {
    if (!nrp.value) {
        toast.error('NRP harus diisi sebelum mengirim jawaban.');
        return;
    }
    if (Object.keys(answers.value).length !== props.test.questions.length) {
        toast.error('Pastikan semua pertanyaan telah dijawab sebelum mengirim.');
        return;
    }

    loading.value = true;

    router.post(
        "/DiklatInternal/test/submit",
        {
            answers: answers.value,
            type: props.test.type,
            detail_id: props.detail_id,
            nrp: nrp.value,
        },
        {
            onSuccess: () => {
                toast.success("Jawaban berhasil disimpan.");
            },
            onError: (errors) => {
                console.error("Request errors:", errors);
                loading.value = false;
            },
        }
    );
};

</script>

<template>
    <div class="max-w-3xl mx-auto p-6">

        <!-- Header -->
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-800">
                {{ props.test.type === 'pree' ? 'Pre-Test' : 'Post-Test' }}
            </h2>
            <p class="text-gray-500 text-sm">Silakan jawab semua pertanyaan di bawah ini</p>
        </div>

        <!-- Identitas / NRP Autocomplete -->
        <div class="mb-6 p-5 border border-gray-200 rounded-lg bg-white shadow-sm">
            <h2 class="font-semibold text-gray-800 mb-2">Identitas Peserta</h2>
            
            <!-- JIKA USER SUDAH LOGIN (NRP TERISI DARI PROPS) -->
            <div v-if="props.user_nrp">
                <Input 
                    v-model="nrp" 
                    disabled 
                    class="bg-gray-100 cursor-not-allowed"
                />
                <p class="text-xs text-green-600 mt-1">✓ Anda sudah login sebagai NRP: {{ props.user_nrp }}</p>
            </div>

            <!-- JIKA USER BELUM LOGIN (MUNCULKAN AUTOCOMPLETE) -->
            <div v-else class="relative">
                <Input 
                    v-model="nrp" 
                    placeholder="Ketik NRP atau Nama Anda..."
                    @focus="showDropdown = true"
                    @blur="hideDropdown"
                    autocomplete="off"
                />
                
                <!-- Dropdown Autocomplete -->
                <ul 
                    v-if="showDropdown && filteredKaryawan.length > 0" 
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto"
                >
                    <li 
                        v-for="k in filteredKaryawan" 
                        :key="k.nrp"
                        @click="selectNrp(k)"
                        class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm flex justify-between"
                    >
                        <span class="font-medium text-gray-800">{{ k.nrp }}</span>
                        <span class="text-gray-500">{{ k.nama_karyawan }}</span>
                    </li>
                </ul>
                <ul 
                    v-else-if="showDropdown && nrp.length > 0 && filteredKaryawan.length === 0" 
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg"
                >
                    <li class="px-4 py-2 text-sm text-gray-500">NRP tidak ditemukan</li>
                </ul>
            </div>
        </div>

        <!-- Questions -->
        <div 
            v-for="(q, index) in props.test.questions" 
            :key="q.id"
            class="mb-6 p-5 border border-gray-200 rounded-lg bg-white shadow-sm"
        >
            <h3 class="font-semibold mb-3 text-gray-800">
                {{ index + 1 }}. {{ q.pertanyaan }}
            </h3>

            <div class="space-y-2">
                <label 
                    v-for="choice in q.choices" 
                    :key="choice.id"
                    class="flex items-center space-x-3 p-2 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                >
                    <input 
                        type="radio"
                        :name="'question_' + q.id"
                        :value="choice.id"
                        v-model="answers[q.id]"
                        class="w-4 h-4 text-blue-600"
                    />
                    <span class="text-gray-700">{{ choice.text }}</span>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mt-10">
            <button 
                @click="submitTest"
                :disabled="loading"
                class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 disabled:bg-gray-400 transition-colors"
            >
                {{ loading ? 'Menyimpan...' : 'Kirim Jawaban' }}
            </button>
        </div>

    </div>
</template>

<style scoped>
/* Optional: Custom scrollbar styling for the autocomplete dropdown */
ul::-webkit-scrollbar {
    width: 6px;
}
ul::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}
</style>