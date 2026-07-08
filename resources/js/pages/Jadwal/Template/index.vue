<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

interface Template {
    id: number;
    nama_template: string;
    slug: string;
    pesan: string;
}

const props = defineProps<{
    templates: Template[];
}>();

const form = useForm({
    nama_template: '',
    slug: '',
    pesan: '',
});

const submit = () => {
    form.post(route('template.store'), {
        onSuccess: () => {
            form.reset();
            toast.success('Template berhasil ditambahkan');
        },
        onError: () => {
            toast.error('Gagal menambahkan template. Pastikan semua field diisi dengan benar.');
        }
    });
};

// Auto-generate slug dari nama template
const generateSlug = () => {
    form.slug = form.nama_template
        .toLowerCase()
        .replace(/ /g, '-')
        .replace(/[^\w-]+/g, '');
};

const deleteTemplate = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus template ini?')) {
        form.delete(route('template.destroy', id),{
            onSuccess: () => {
                toast.success('Template berhasil dihapus');
            },
        });
    }
};
</script>

<template>
    <Head title="WhatsApp Templates" />

    <AppLayout>
        <div class="mx-auto max-w-7xl p-6">
            <h2 class="mb-6 text-2xl font-bold text-gray-800">
                Template Pesan WhatsApp
            </h2>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- DAFTAR TEMPLATE -->
                <div class="space-y-4">
                    <div
                        v-for="temp in templates"
                        :key="temp.id"
                        class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-2 flex items-start justify-between">
                            <h4 class="font-bold text-gray-800">
                                {{ temp.nama_template }}
                            </h4>
                            <span
                                class="rounded bg-gray-100 px-2 py-1 font-mono text-[10px] text-gray-500"
                                >{{ temp.slug }}</span
                            >
                        </div>
                        <p
                            class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-3 font-mono text-sm whitespace-pre-line text-gray-600"
                        >
                            {{ temp.pesan }}
                        </p>
                        <button
                            @click="deleteTemplate(temp.id)"
                            class="bg-red-500 text-white flex mt-3 text-sm hover:bg-red-600 p-2 rounded"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            Hapus
                        </button>
                    </div>
                    
                    <div
                        v-if="templates.length === 0"
                        class="py-10 text-center text-gray-400 italic"
                    >
                        Belum ada template yang dibuat.
                    </div>
                    
                </div>

                <!-- FORM TAMBAH TEMPLATE -->
                <div
                    class="sticky top-6 h-fit rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
                >
                    <h3 class="mb-4 font-bold text-gray-700">
                        Buat Template Baru
                    </h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                >Nama Template</label
                            >
                            <Input
                                v-model="form.nama_template"
                                @input="generateSlug"
                                type="text"
                                placeholder="Misal: Notifikasi Jadwal Baru"
                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-green-500"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                >Slug (ID Sistem)</label
                            >
                            <Input
                                v-model="form.slug"
                                type="text"
                                readonly
                                class="w-full rounded-lg border-gray-200 bg-gray-50 font-mono text-sm text-gray-500"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold tracking-widest text-gray-400 uppercase"
                                >Isi Pesan</label
                            >
                            <textarea
                                v-model="form.pesan"
                                rows="6"
                                class="w-full rounded-lg border-gray-200 font-mono text-sm focus:ring-green-500"
                                placeholder="Halo {nama}, jadwal diklat {judul} akan dilaksanakan pada {tanggal}..."
                            ></textarea>
                        </div>

                        <!-- TIPS PLACEHOLDER -->
                        <div
                            class="rounded-lg border border-blue-100 bg-blue-50 p-3"
                        >
                            <p
                                class="mb-2 text-[10px] font-bold text-blue-600 uppercase"
                            >
                                Placeholder Tersedia:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="rounded border border-blue-200 bg-white px-2 py-1 font-mono text-[10px] text-blue-700"
                                    >{nama}</span
                                >
                                <span
                                    class="rounded border border-blue-200 bg-white px-2 py-1 font-mono text-[10px] text-blue-700"
                                    >{judul}</span
                                >
                                <span
                                    class="rounded border border-blue-200 bg-white px-2 py-1 font-mono text-[10px] text-blue-700"
                                    >{tanggal}</span
                                >
                                <span
                                    class="rounded border border-blue-200 bg-white px-2 py-1 font-mono text-[10px] text-blue-700"
                                    >{lokasi}</span
                                >
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-lg bg-green-600 py-3 font-bold text-white shadow-lg shadow-green-100 transition hover:bg-green-700"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Template'
                            }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
