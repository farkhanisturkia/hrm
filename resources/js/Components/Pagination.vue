<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length >= 3" class="flex flex-wrap justify-center -mb-1 mt-4 space-x-1">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null" 
                 class="mr-1 mb-1 px-3 py-2 text-sm leading-4 text-gray-400 border rounded bg-white dark:bg-gray-800 dark:border-white/10" 
                 v-html="link.label" />
            
            <Link v-else 
                  class="mr-1 mb-1 px-3 py-2 text-sm leading-4 border rounded hover:bg-primary-50 focus:border-primary-500 focus:text-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition duration-150 ease-in-out" 
                  :class="{ 
                      'bg-primary-600 text-white border-primary-600 dark:bg-primary-600': link.active, 
                      'bg-white text-gray-700 border-gray-300': !link.active 
                  }" 
                  :href="link.url" 
                  v-html="link.label" />
        </template>
    </div>
</template>