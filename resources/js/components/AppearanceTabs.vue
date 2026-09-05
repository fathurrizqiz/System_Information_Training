<script setup lang="ts">
import { ref } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { 
  Monitor, Moon, Sun, User, Bell, Lock, 
  Paintbrush, Shield, Save, Camera 
} from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
  { value: 'light', Icon: Sun, label: 'Light' },
  { value: 'dark', Icon: Moon, label: 'Dark' },
  { value: 'system', Icon: Monitor, label: 'System' },
] as const;

// Form State Dummy
const profile = ref({
  name: 'Eva Effendi',
  email: 'eva.effendi@example.com',
  role: 'Administrator Diklat',
  language: 'id',
  notificationsEmail: true,
  notificationsSystem: true,
});
</script>

<template>
  <div class="max-w-6xl space-y-6 p-6">
    <!-- Header -->
    <div class="border-b border-neutral-200 pb-5 dark:border-neutral-800">
      <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Settings</h1>
      <p class="text-sm text-neutral-500 dark:text-neutral-400">
        Kelola profil, preferensi aplikasi, dan keamanan akun Anda.
      </p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Left Column: Primary Settings -->
      <div class="space-y-6 lg:col-span-2">
        <!-- Profile Card -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
          <div class="flex items-center gap-2 font-semibold text-neutral-900 dark:text-white">
            <User class="h-5 w-5 text-neutral-500" />
            <h2>Profil Pengguna</h2>
          </div>
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            Informasi pribadi yang ditampilkan di workspace Anda.
          </p>

          <div class="mt-6 space-y-4">
            <div class="flex items-center gap-4">
              <div class="relative flex h-16 w-16 items-center justify-center rounded-full bg-neutral-100 text-lg font-bold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                EE
                <button class="absolute bottom-0 right-0 rounded-full border border-neutral-200 bg-white p-1 text-neutral-600 shadow-xs hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                  <Camera class="h-3.5 w-3.5" />
                </button>
              </div>
              <div>
                <h3 class="text-sm font-medium text-neutral-900 dark:text-white">{{ profile.name }}</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ profile.role }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              
              
            </div>
          </div>
        </div>

        <!-- Preferences / Theme Card -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
          <div class="flex items-center gap-2 font-semibold text-neutral-900 dark:text-white">
            <Paintbrush class="h-5 w-5 text-neutral-500" />
            <h2>Preferensi Tampilan</h2>
          </div>
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            Sesuaikan tema aplikasi sesuai tingkat kenyamanan mata Anda.
          </p>

          <div class="mt-6 space-y-4">
            <div>
              <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">Tema Aplikasi</label>
              <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800 border border-neutral-200/60 dark:border-neutral-700/60">
                <button
                  v-for="{ value, Icon, label } in tabs"
                  :key="value"
                  @click="updateAppearance(value)"
                  :class="[
                    'flex items-center rounded-md px-3.5 py-1.5 text-sm transition-all',
                    appearance === value
                      ? 'bg-white shadow-xs font-medium text-neutral-900 dark:bg-neutral-700 dark:text-white'
                      : 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white'
                  ]"
                >
                  <component :is="Icon" class="mr-2 h-4 w-4" />
                  <span>{{ label }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Secondary Settings -->
      <div class="space-y-6">
        <!-- Notifications Card -->
        

        <!-- Security Card -->
        
      </div>
    </div>
  </div>
</template>