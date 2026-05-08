<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ isLoaded: Boolean });
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-white to-indigo-200 dark:from-slate-950 dark:via-black dark:to-indigo-900 pt-6 sm:justify-center sm:pt-0">

    <div
      class="flex flex-col items-center space-y-6 w-full max-w-md transition-all duration-1000 ease-out"
      :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-16 opacity-0': !isLoaded }"
    >
      <!-- LOGO -->
      <div class="relative">
        <Link href="/">
            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full blur-lg opacity-70 group-hover:opacity-100 transition duration-500"></div>
            <div class="relative p-0 bg-white dark:bg-slate-900 rounded-full ring-1 ring-slate-200 dark:ring-slate-800">
                <ApplicationLogo class="w-24 scale-150"/>
            </div>
        </Link>
      </div>

      <!-- CARD -->
      <div class="w-full bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm px-6 py-8 shadow-md sm:rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
        <slot />
      </div>
    </div>

  </div>
</template>