<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type NavItem = {
    title: string;
    href: string;
};

defineProps<{
    items: NavItem[];
}>();

// const currentUrl = computed(() => usePage().url);
const currentPath = computed(() => new URL(usePage().url, window.location.origin).pathname);
</script>

<template>
    <nav class="me-5 flex justify-end gap-10">
        <Link
            v-for="item in items"
            :key="item.title"
            :href="item.href"
            :class="['border-black hover:cursor-pointer dark:border-white', currentPath === item.href ? 'border-b-2' : 'hover:border-b-2']"
        >
            {{ item.title }}
        </Link>
    </nav>
</template>
