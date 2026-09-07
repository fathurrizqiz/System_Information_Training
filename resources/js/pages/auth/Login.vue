<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-50 font-sans antialiased">
    <!-- Card Outer Container -->
    <div class="flex w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-xl shadow-slate-200/50">
      
      <!-- SISI KIRI: Form Login (Bersih & Fokus) -->
      <div class="w-full p-8 sm:p-12 md:w-1/2">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-slate-800">Selamat Datang</h2>
          <p class="mt-1 text-sm text-slate-500">
            Masukan NRP dan Password untuk mengakses Portal Diklat.
          </p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Input NRP -->
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-600">
              NRP / ID Karyawan
            </label>
            <input
              v-model="form.nrp"
              type="text"
              placeholder="Contoh: 123456"
              class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
            <span v-if="form.errors.nrp" class="mt-1 text-xs text-red-600">
              {{ form.errors.nrp }}
            </span>
          </div>

          <!-- Input Password -->
          <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-600">
              Password
            </label>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
            <span v-if="form.errors.password" class="mt-1 text-xs text-red-600">
              {{ form.errors.password }}
            </span>
          </div>

          <!-- Control Options -->
          <div class="flex items-center justify-between text-xs text-slate-600">
            <label class="flex cursor-pointer items-center gap-2">
              <input
                v-model="showPassword"
                type="checkbox"
                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
              />
              Tampilkan Password
            </label>
            <a href="#" class="font-medium text-blue-600 hover:text-blue-700">Lupa Password?</a>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-lg bg-slate-800 py-2.5 text-sm font-semibold text-white shadow transition duration-150 hover:bg-slate-700 active:bg-slate-900 disabled:opacity-50"
          >
            {{ form.processing ? 'Memproses...' : 'Masuk ke Sistem' }}
          </button>
        </form>

        <!-- Help/Support Link -->
        <p class="mt-8 text-center text-xs text-slate-400">
          Kendala akun? <a href="#" class="text-slate-600 underline">Hubungi Admin Diklat</a>
        </p>
      </div>

      <!-- SISI KANAN: Panel Branding Professional (Sederhana & Elegan) -->
      <div class="hidden md:flex md:w-1/2 flex-col justify-between bg-slate-800 p-12 text-white">
        <div class="flex items-center gap-3">
          <img src="/icon_baru.png" alt="Logo" class="h-8 w-8 object-contain brightness-0 invert" />
          <span class="text-xl font-semibold tracking-wide text-slate-100">Eichar</span>
        </div>

        <div>
          <span class="rounded bg-slate-700/60 px-2.5 py-1 text-xs font-medium text-blue-300">
            Human Capital & Training RS Hermina
          </span>
          <h1 class="mt-3 text-2xl font-bold text-white">
            Sistem Informasi Diklat Integratif
          </h1>
          <p class="mt-2 text-xs leading-relaxed text-slate-300">
            Kelola jadwal pelatihan, materi pembelajaran, dan sertifikasi kompetensi pegawai secara tersentralisasi.
          </p>
        </div>

        <div class="text-xs justify-center  text-slate-400">
          © {{ new Date().getFullYear() }} Eichar System. All rights reserved.
          <p>Development IT RS Hermina</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const form = useForm({
    nrp: '',
    password: '',
});

const handleLogin = () => {
    form.post(route('login'), {
        onSuccess: () => {
            toast.success('Login berhasil!');
            router.reload();
        },
        onError: () => {
            toast.error('Login gagal. Pastikan NRP dan Password benar.');
        },
        onFinish: () => {
            form.reset('password');
        },
    });
};

const showPassword = ref(false);
</script>
