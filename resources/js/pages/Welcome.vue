<script setup lang="ts">
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

interface Ripple {
  x: number;
  y: number;
  size: number;
  id: number;
}

const ripples = ref<Ripple[]>([]);
let rippleId = 0;

const handleClick = (e: MouseEvent) => {
  const target = e.currentTarget as HTMLElement;
  const rect = target.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = e.clientX - rect.left - size / 2;
  const y = e.clientY - rect.top - size / 2;

  ripples.value.push({ x, y, size, id: rippleId++ });

  // Hapus ripple setelah animasi selesai
  setTimeout(() => {
    ripples.value = ripples.value.filter(r => r.id !== rippleId - 1);
  }, 600);

  // Navigasi ke login setelah animasi
  setTimeout(() => {
    router.visit(route("login"));
  }, 400);
};
</script>

<template>
  <div
    @click="handleClick"
    class="relative flex min-h-screen w-full flex-col items-center justify-center overflow-hidden cursor-pointer bg-[#FDFDFC] p-6 text-center text-[#1b1b18] lg:p-8"
  >
    <!-- Background -->
    <img
      class="absolute inset-0 z-0 h-full w-full object-cover"
      src="../../assets/images/FrameFirst.jpg"
      alt="Background"
    />
    <div class="absolute inset-0 bg-black/20 z-0"></div>

    <!-- Ripple container -->
    <div class="absolute inset-0 z-20 overflow-hidden pointer-events-none">
      <span
        v-for="r in ripples"
        :key="r.id"
        class="absolute rounded-full animate-ripple"
        :style="{
          width: r.size + 'px',
          height: r.size + 'px',
          top: r.y + 'px',
          left: r.x + 'px',
          background: 'radial-gradient(circle, rgba(255,255,255,0.5) 0%, rgba(255,255,255,0) 70%)'
        }"
      ></span>
    </div>

    <!-- Main content -->
    <div class="relative z-10 flex flex-col items-center justify-center">
      <h1 class="text-center text-[50px] font-bold text-white sm:text-[48px] md:text-[64px] lg:text-[80px] xl:text-[100px]">
        Eichar
      </h1>
      <p class="mt-4 text-white/80 text-sm sm:text-base">
        Klik di mana saja untuk melanjutkan &gt;&gt;&gt;
      </p>
    </div>
  </div>
</template>

<style>
@keyframes ripple {
  0% {
    transform: scale(0);
    opacity: 0.8;
  }
  100% {
    transform: scale(4);
    opacity: 0;
  }
}

.animate-ripple {
  animation: ripple 0.6s ease-out forwards;
}
</style>
