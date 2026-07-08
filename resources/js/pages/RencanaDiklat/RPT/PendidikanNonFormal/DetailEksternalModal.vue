<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    show: boolean;
    programId: number;
    detail?: any;
    karyawan: any[];
}>();

const emit = defineEmits(['close']);

const form = ref({
    program_id: 0,
    nama_karyawan: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
    jam_diklat: 0,
    penyelenggara: '',
    nrp: '',
    dokumen: null as File | null,
});

const searchQuery = ref('');
const showResults = ref(false);

// PERBAIKAN 1: Gunakan watch pada props.show agar form selalu ter-reset
// dengan program_id yang paling baru saat modal dibuka
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            if (props.detail) {
                // Isi form dengan data detail (kecuali file dokumen lama, biarkan null dulu jika mau diganti)
                form.value = {
                    ...props.detail,
                    dokumen: null,
                };
                searchQuery.value = `${props.detail.nrp} - ${props.detail.nama_karyawan}`;
            } else {
                form.value = {
                    program_id: props.programId,
                    nama_karyawan: '',
                    tanggal_mulai: '',
                    tanggal_selesai: '',
                    jam_diklat: 0,
                    penyelenggara: '',
                    nrp: '',
                    dokumen: null,
                };
                // form.reset();
                searchQuery.value = '';
            }
        }
    },
);

const filteredEmployees = computed(() => {
    if (!searchQuery.value || form.value.nrp) return [];
    const q = searchQuery.value.toLowerCase();
    return props.karyawan
        .filter(
            (k) =>
                k.nama_karyawan.toLowerCase().includes(q) || k.nrp.includes(q),
        )
        .slice(0, 10);
});

const selectEmployee = (emp: any) => {
    form.value.nrp = emp.nrp;
    form.value.nama_karyawan = emp.nama_karyawan;
    searchQuery.value = `${emp.nrp} - ${emp.nama_karyawan}`;
    showResults.value = false;
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.value.dokumen = target.files[0];
    }
};

const submit = () => {
    if (!form.value.tanggal_mulai || !form.value.tanggal_selesai) {
        toast.error('Tanggal mulai dan tanggal selesai harus diisi');
        return;
    }
    if (!form.value.jam_diklat || form.value.jam_diklat < 1 || form.value.jam_diklat > 9) {
        toast.error('Jam diklat optimalnya antara 1 dan 9 jam');
        return;
    }

    if(!form.value.dokumen && !props.detail) {
        toast.error('Undangan harus diunggah');
        return;
    }
    const url = props.detail
        ? `/RencanaDiklat/RPT/PN/Detail/Update/${props.detail.id}`
        : '/RencanaDiklat/RPT/PN/Detail';

    router.post(url, form.value, {
        onSuccess: () => {
            toast.success('Berhasil disimpan');
            emit('close');
        },
        // PERBAIKAN 2: Tangkap error validasi dari Inertia/Laravel dengan benar
        onError: (errors) => {
            toast.error('Gagal menyimpan: ' + Object.values(errors).join(', '));
        },
    });
};

// fungsi untuk sett tanggal hari ini
const today = new Date().toISOString().split('T')[0];
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
            @click="$emit('close')"
        ></div>
        <div
            class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl"
        >
            <h3 class="mb-4 text-lg font-bold">
                {{ detail ? 'Edit' : 'Tambah' }} Peserta Diklat
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="relative col-span-2">
                    <label class="text-sm font-medium">Cari Karyawan</label>
                    <Input
                        v-model="searchQuery"
                        @input="
                            form.nrp = '';
                            showResults = true;
                        "
                        class="w-full rounded-lg border-slate-300"
                        placeholder="Ketik NRP/Nama..."
                    />

                    <div
                        v-if="showResults && filteredEmployees.length"
                        class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border bg-white shadow-lg"
                    >
                        <div
                            v-for="emp in filteredEmployees"
                            :key="emp.id"
                            @click="selectEmployee(emp)"
                            class="cursor-pointer p-2 text-sm hover:bg-blue-50"
                        >
                            {{ emp.nrp }} - {{ emp.nama_karyawan }}
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium"
                        >Upload Undangan (PDF / JPG)</label
                    >
                    <input
                        type="file"
                        accept=".pdf,.jpg,.jpeg"
                        @change="handleFileUpload"
                        class="w-full rounded-lg border border-slate-300 p-1.5 text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100"
                    />
                    <!-- <p v-if="form.errors.dokumen" class="text-red-500 text-xs mt-1">{{ form.errors.dokumen }}</p> -->
                </div>
                <div class="col-span-1">
                    <label class="text-sm font-medium">Tgl Mulai</label>
                    <Input
                        v-model="form.tanggal_mulai"
                        type="date"
                        :min="today"
                        
                        @keydown.prevent
                        class="w-full rounded-lg border-slate-300"
                    />
                </div>
                <div class="col-span-1">
                    <label class="text-sm font-medium">Tgl Selesai</label>
                    <Input
                        v-model="form.tanggal_selesai"
                        type="date"
                        :min="form.tanggal_mulai || today"
                        
                        @keydown.prevent
                        class="w-full rounded-lg border-slate-300"
                    />
                </div>
                <div>
                    <label class="text-sm font-medium"
                        >Jam Diklat (Per Hari)</label
                    >
                    <Input
                        :value="form.jam_diklat"
                        @input="
                            form.jam_diklat = $event.target.value.replace(
                                /\D/g,
                                '',
                            )
                        "
                        inputmode="numeric"
                        class="w-full rounded-lg border-slate-300"
                    />
                </div>
                <!-- <div>
                    <label class="text-sm font-medium">Penyelenggara</label>
                    <Input v-model="form.penyelenggara" type="text" class="w-full rounded-lg border-slate-300" />
                </div> -->
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button @click="$emit('close')" class="px-4 py-2">Batal</button>
                <button
                    @click="submit"
                    class="lex w-28 justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 bg-[length:200%_100%] bg-left py-3 font-semibold text-white shadow-lg transition-all duration-500 hover:scale-[1.01] hover:bg-right"
                >
                    Simpan
                </button>
            </div>
        </div>
    </div>
</template>
