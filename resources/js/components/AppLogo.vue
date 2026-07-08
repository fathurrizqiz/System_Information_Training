<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
  <div class="flex w-full items-center gap-3 p-2 rounded-lg hover:bg-sidebar-accent/50 transition-colors">
    
    <div class="flex size-11 items-center justify-center rounded-lg p-1 text-sidebar-primary-foreground shrink-0 shadow-md">
      <AppLogoIcon class="size-5" />
    </div>

    <div class="flex flex-col flex-1 min-w-0 text-left text-sm leading-none">
      <span class="truncate font-semibold text-sidebar-foreground">
        Eichar D
      </span>
      <span class="truncate text-xs text-sidebar-foreground/60 mt-0.5">
        Workspace
      </span>
    </div>

  </div>
</template>
