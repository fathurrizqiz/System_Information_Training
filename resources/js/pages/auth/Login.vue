<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-r from-slate-100 to-cyan-50 px-4"
    >
        <!-- Logo -->
        <div class="mt-10 mb-10 flex items-center gap-5">
            <img
                src="/icon_baru.png"
                alt="Logo"
                class="h-24 w-24 object-contain"
            />

            <h1
                class="mt-3 bg-gradient-to-r from-blue-700 to-cyan-400 bg-clip-text text-6xl font-bold text-transparent"
            >
                Eichar
            </h1>
        </div>

        <!-- Login Card -->
        <div class="w-full max-w-md rounded-3xl bg-white p-10 shadow-xl">
            <div class="text-center">
                <h2 class="text-4xl font-bold text-slate-900">Welcome back</h2>

                <p class="mt-2 text-slate-500">
                    Sign in to access your Eichar workspace
                </p>
            </div>

            <form @submit.prevent="handleLogin" class="mt-10 space-y-6">
                <!-- NRP -->
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        NRP
                    </label>

                    <input
                        v-model="form.nrp"
                        type="text"
                        placeholder="Enter your NRP"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-black focus:border-blue-500 focus:outline-none"
                    />

                    <div
                        v-if="form.errors.nrp"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.nrp }}
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter Password"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 pr-12 focus:border-blue-500 focus:outline-none"
                        />
                        

                    <div
                        v-if="form.errors.password"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.password }}
                    </div>
                </div>

                <!-- Remember -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2">
                        <input @click="showPassword = !showPassword" type="checkbox" />
                        Lihat Password
                    </label>

                    <a
                        href="#"
                        class="font-medium text-cyan-600 hover:text-cyan-700"
                    >
                        Forgot password?
                    </a>
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-emerald-400 py-3 font-semibold text-white shadow-lg transition hover:scale-[1.01]"
                >
                    {{ form.processing ? 'Signing in...' : 'Sign in →' }}
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-10 text-center text-sm text-slate-500">
            © {{ new Date().getFullYear() }} Eichar HR System.
            <br />
            Developed & maintained by Fathur.
        </div>
    </div>
    <div>

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
