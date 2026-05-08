<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// 1. Layout Persistent
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Warning from '@/Components/Icon/Warning.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head } from '@inertiajs/vue3';
import moment from 'moment';
import { ref, onMounted } from 'vue';

const isLoaded = ref(false);

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const props = defineProps({
  logs: {}, // Object Pagination
});

const formatDate = (date) => {
  if (!date) return '-';
  return moment(date).format('DD-MM-YYYY, hh:mm:ss a');
};
</script>

<template>
  <Head title="Logs" />
  
  <div class="w-full">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-8 mt-8">
      <div
        class="flex justify-between px-6 py-4 items-center text-gray-800 dark:text-gray-200 
               bg-white/40 dark:bg-gradient-to-b dark:from-slate-700/30 dark:to-slate-900/60 backdrop-blur-xl border border-white/40 dark:border-white/20 
               shadow-lg rounded-lg transition-all duration-1000 ease-out"
        :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
      >
        <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">
              System Logs
            </h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Track system activities and events.</p>
        </div>
      </div>
    </div>

    <div
      class="w-full py-8 transition-all duration-700 ease-out delay-100"
      :class="{ 'opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-8">
        
        <div class="flex flex-col items-start">
            
            <div class="relative z-10 -mb-[1px]">
                <div class="w-fit px-6 h-12 bg-white/40 dark:bg-slate-700/50 dark:to-slate-800/60 backdrop-blur-xl border-t border-l border-r border-white/40 dark:border-white/20 rounded-t-lg shadow-sm relative flex items-center gap-3">
                    <Warning class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                    <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">Activity Logs</span>
                    <div class="absolute -bottom-[1px] left-0 right-0 h-[2px] bg-white/40 dark:bg-slate-800/80 z-20"></div>
                </div>
            </div>

            <div class="w-full overflow-x-auto bg-white/40 dark:bg-gradient-to-b dark:from-slate-800/60 dark:to-slate-950/80 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-xl rounded-b-lg rounded-tr-lg relative z-0">
                <table class="w-full text-left dark:text-slate-200 table-auto border-collapse">
                    <thead class="bg-white/50 dark:bg-slate-800/90 backdrop-blur-md border-b border-white/20 dark:border-white/10">
                    <tr>
                        <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">
                            Date
                        </th>
                        <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">
                            User
                        </th>
                        <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">
                            Target
                        </th>
                        <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">
                            Description
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-white/20 dark:divide-white/5">
                    <tr v-for="log in logs.data" :key="log.id" class="hover:bg-white/30 dark:hover:bg-primary-500/10 transition duration-200">
                        <td class="p-5 align-middle text-sm text-gray-500 dark:text-slate-400 font-mono">
                            {{ formatDate(log.created_at) }}
                        </td>
                        <td class="p-5 align-middle min-w-[200px]">
                            <span class="font-medium text-gray-700 dark:text-slate-200">{{ log.user?.name || '-' }}</span>
                        </td>
                        <td class="p-5 align-middle min-w-[200px]">
                            <span class="inline-block px-2 py-1 rounded text-sm font-bold bg-slate-100/50 dark:bg-slate-800/50 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300">
                                {{ log.target || '-' }}
                            </span>
                        </td>
                        <td class="p-5 align-middle text-sm text-gray-600 dark:text-slate-400">
                            {{ log.description || '-' }}
                        </td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="4" class="p-8 text-center text-gray-400 dark:text-gray-500 italic">No logs available.</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end w-full">
               <Pagination :links="logs.links" />
            </div>

        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* Scrollbar Default (Light Mode) */
::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent; 
}
::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5); /* Abu-abu untuk Light Mode */
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.8);
}

/* Scrollbar Dark Mode */
:global(.dark) ::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1); 
}
:global(.dark) ::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.2);
}
</style>