<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { toast } from 'vue3-toastify';

const props = defineProps<{
    periode_id: number
    existing?: string[]
}>()

const materi = ref<string[]>(
    props.existing && props.existing.length
        ? props.existing
        : ['']
)

function tambah() {
    materi.value.push('')
}

function hapus(index: number) {
    materi.value.splice(index, 1)
}

function simpan() {

    if (materi.value.some(m => !m.trim())) {
        toast.error('Isi materi Diklat Minimal 1!');
        return;
    }

    router.post('/DiklatInternal/detail/pembahasan/template/store', {
        periode_id: props.periode_id,
        materi: materi.value
    }, {
        onSuccess: () => {
            toast.success('Materi Berhasil Disimpan')
        }
    })
}
</script>
<template>
    <Head title="Template Pembahasan Sertifikat" />

    <AppLayout>
        <div class="p-6 max-w-3xl mx-auto">
            <h1 class="text-2xl font-semibold mb-6">
                Template Pembahasan Sertifikat
            </h1>

            <div class="bg-white border rounded-lg p-5 shadow-sm">
                <h2 class="text-lg font-medium mb-4">
                    Daftar Pembahasan Diklat
                </h2>

                <div
                    v-for="(m, i) in materi"
                    :key="i"
                    class="flex gap-2 mb-3"
                >
                    <input
                        v-model="materi[i]"
                        type="text"
                        class="flex-1 rounded border p-2"
                        placeholder="Nama materi / pembahasan"
                    />

                    <button
                        v-if="materi.length > 1"
                        @click="hapus(i)"
                        class="text-red-600 text-sm"
                    >
                        Hapus
                    </button>
                </div>

                <div class="flex justify-between mt-6">
                    <button
                        @click="tambah"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        + Tambah Materi
                    </button>

                    <button
                        @click="simpan"
                        class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900"
                    >
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
