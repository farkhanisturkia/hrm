<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// 1. Layout Persistent didaftarkan di sini
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Plus from '@/Components/Icon/Plus.vue';
import Pagination from '@/Components/Pagination.vue';
import Pen from '@/Components/Icon/Pen.vue';
import UserPlus from '@/Components/Icon/UserPlus.vue';
import Document from '@/Components/Icon/Document.vue';
import TaskCreateEditForm from '@/Components/Form/TaskCreateEdit.vue';
import TaskAssignForm from '@/Components/Form/TaskAssign.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import moment from 'moment';
import SwitchInput from '@/Components/SwitchInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Search from '@/Components/Icon/Search.vue';
import externalLink from '@/Components/Icon/externalLink.vue';

// --- State ---
const openCreateEditForm = ref(false);
const openAssignForm = ref(false);
const isEditMode = ref(false); 
const selectedTask = ref(null);
const isLoaded = ref(false);

// --- Props ---
const props = defineProps({
  tasks: {},
  projects: {},
  users: {}, 
  communicator: {},
  programmer: {},
  designer: {},
});

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const page = usePage();
const role = computed(() => page.props.auth.user.role);

const queryParams = computed(() => {
  const url = new URL(page.url, window.location.origin);
  return Object.fromEntries(url.searchParams);
});

const search = ref(queryParams.value.search ?? '');

// --- Handlers ---
const handleOpenCreateEditForm = () => {
  selectedTask.value = null;
  isEditMode.value = false;
  openCreateEditForm.value = true;
};

const handleEditTask = (task) => {
  selectedTask.value = task;
  isEditMode.value = true;
  openCreateEditForm.value = true;
};

const handleAssignTask = (task) => {
  selectedTask.value = task;
  openAssignForm.value = true;
};

const handleCloseForm = () => {
  openCreateEditForm.value = false;
  openAssignForm.value = false;
  isEditMode.value = false;
  selectedTask.value = null;
};

const handleUpdateIsActive = (id) => {
  router.post(route('task.changeIsActive', id));
};

const formatDate = (date) => {
  if (!date) return '-';
  return moment(date).format('DD MMMM YYYY');
};

const handleSearch = () => {
  const params = {};
  if (search.value?.trim()) {
    params.search = search.value.trim();
  }
  router.get(route('task.list'), params);
};
</script>

<template>
  <Head title="Tasks" />
  
  <div class="w-full">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0 mt-8">
        <div
          class="flex flex-col md:flex-row justify-between px-6 py-4 items-center gap-4 text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-1000 ease-out"
          :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">Tasks List</h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage project issues and tickets.</p>
          </div>

          <div class="flex flex-col md:flex-row gap-3 justify-end items-center text-sm w-full md:w-auto">
            <div class="relative w-full md:w-80 group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Search class="h-4 w-4 text-gray-400 group-focus-within:text-primary-500 transition-colors" />
                </div>
                <TextInput
                  id="search"
                  type="text"
                  class="pl-10 w-full bg-white/50 dark:bg-slate-900/50 border-white/40 dark:border-white/10 backdrop-blur-sm focus:bg-white/80 dark:focus:bg-slate-900/80 transition-all shadow-sm rounded-lg dark:text-white"
                  v-model="search"
                  placeholder="Search issue or ticket..."
                  @keydown.enter="handleSearch"
                />
            </div>

            <button
              v-if="['other', 'pm', 'co'].includes(role)" 
              @click="handleOpenCreateEditForm"
              class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-md hover:shadow-primary-500/30 transition-all duration-300 transform hover:scale-105 whitespace-nowrap"
            >
              <Plus class="w-5 h-5" />
              <span class="hidden sm:inline font-bold text-sm">New Task</span>
            </button>
          </div>
        </div>
    </div>

    <button
      v-if="['other', 'pm', 'co'].includes(role)" 
      @click="handleOpenCreateEditForm"
      class="fixed sm:hidden right-6 bottom-6 border border-white/20 rounded-full p-4 text-white bg-primary-600 shadow-xl z-40 transition-all duration-500 ease-out hover:scale-110 active:scale-95"
      :class="{ 'translate-y-0 opacity-100 scale-100': isLoaded, 'translate-y-12 opacity-0 scale-75': !isLoaded }"
    >
      <Plus />
    </button>

    <div v-if="openCreateEditForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-5xl w-full p-6 relative animate-in fade-in zoom-in duration-300 overflow-y-auto max-h-[90vh]">
        <TaskCreateEditForm 
            :task="selectedTask" 
            :projects="projects" 
            :projectId="queryParams.project_id" 
            :isEditMode="isEditMode" 
            @close="handleCloseForm" 
        />
      </div>
    </div>

    <div v-if="openAssignForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-200">
        <TaskAssignForm :task="selectedTask" :pl="users" :co="communicator" :pg="programmer" :ds="designer" @close="handleCloseForm" />
      </div>
    </div>

    <div class="w-full py-8">
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        
        <div 
          class="flex flex-col items-start transition-all duration-700 ease-out delay-100"
          :class="{ 'opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
        >

          <div class="relative z-10 -mb-[1px]">
             <div class="w-fit px-6 h-12 bg-white/40 dark:bg-slate-900/60 backdrop-blur-xl border-t border-l border-r border-white/40 dark:border-white/20 rounded-t-lg shadow-sm relative flex items-center gap-3">
                <Document class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">Task Data</span>
                <div class="absolute -bottom-[1px] left-0 right-0 h-[2px] bg-white/40 dark:bg-slate-900/80 z-20"></div>
             </div>
          </div>

          <div
            class="w-full bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-xl rounded-b-lg rounded-tr-lg relative z-0 flex flex-col overflow-hidden"
          >
            <div class="overflow-x-auto custom-scrollbar">
              <table class="w-full text-left dark:text-slate-200 table-auto border-collapse">
                <thead>
                  <tr class="bg-white/50 dark:bg-slate-900/80 backdrop-blur-md border-b border-white/20 dark:border-white/10">
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">Assign</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">Issue</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">Project</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">Ticket Link</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">Start date</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">Due date</th>
                    <th v-if="['other', 'pm', 'co'].includes(role)" class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">
                      Is Active
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/20 dark:divide-white/5">
                  <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-white/30 dark:hover:bg-primary-500/10 transition duration-200">
                    
                    <td class="px-4 py-3 align-middle text-sm min-w-[180px]">
                      <div class="grid grid-cols-2 gap-1.5">
                           <span v-for="(id, idx) in [...(task.programmer || []), ...(task.designer || []), ...(task.communicator || [])]" 
                              :key="idx"
                              class="block px-2 py-1 rounded-md bg-white/50 dark:bg-slate-800/40 border border-gray-200 dark:border-white/10 text-xs text-gray-700 dark:text-slate-300 text-center truncate"
                              :title="users.find(u => u.id === id)?.name || id"
                           >
                              {{ users.find(u => u.id === id)?.name || id }}
                           </span>
                           <span v-if="![...(task.programmer || []), ...(task.designer || []), ...(task.communicator || [])].length" class="text-gray-400 text-sm italic col-span-2 text-center">-</span>
                      </div>
                    </td>

                    <td class="px-4 py-3 align-middle">
                      <div class="flex flex-col gap-1.5 items-start">
                        <a :href="route('task.show', task.id)" 
                          class="flex items-center gap-1 text-sm font-extrabold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 hover:underline decoration-primary-300 underline-offset-2 transition"
                        >
                          {{ task.issue }}
                          <externalLink class="w-4 h-4 text-primary-600" />
                        </a>

                        <div v-if="task.is_git_automated" 
                             class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-mono bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 shadow-sm backdrop-blur-sm"
                             title="Updated via Git Webhook">
                          <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                          </svg>
                          <span class="opacity-90">{{ task.last_commit_hash }}</span>
                        </div>
                      </div>
                    </td>

                    <td class="px-4 py-3 align-middle text-sm text-gray-700 dark:text-slate-200 whitespace-nowrap">{{ task.project?.name || '-' }}</td>
                    <td class="px-4 py-3 align-middle">
                      <a :href="'//' + task.ticket_link" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline truncate block max-w-[200px]">
                        {{ task.ticket_link }}
                      </a>
                    </td>
                    <td class="px-4 py-3 align-middle text-sm text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ formatDate(task.start_date) }}</td>
                    <td class="px-4 py-3 align-middle text-sm whitespace-nowrap">
                      <span :class="[
                          'px-2 py-1 rounded-md text-sm font-bold border',
                          task.due_date && moment().startOf('day').isAfter(moment(task.due_date).startOf('day'))
                              ? 'bg-red-50/50 text-red-600 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30'
                              : 'bg-emerald-50/50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-900/30'
                        ]">
                        {{ formatDate(task.due_date) }}
                      </span>
                    </td>
                    
                    <td v-if="['other', 'pm', 'co'].includes(role)" class="px-4 py-3 align-middle">
                      <div class="flex justify-center">
                        <SwitchInput v-slot:default v-model="task.isActive" @update:modelValue="handleUpdateIsActive(task.id)" />
                      </div>
                    </td>

                    <td class="px-4 py-3 align-middle whitespace-nowrap">
                      <div class="flex items-center justify-center gap-3">
                        <button 
                          v-if="['other', 'pm'].includes(role)"
                          @click="handleAssignTask(task)"
                          class="p-1.5 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition tooltip-trigger"
                          title="Assign User"
                        >
                           <UserPlus class="w-5 h-5" />
                        </button>

                        <button 
                          v-if="['other', 'pm', 'co'].includes(role)"
                          @click="handleEditTask(task)"
                          class="p-1.5 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition tooltip-trigger"
                          title="Edit Task"
                        >
                           <Pen class="w-5 h-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end w-full">
             <Pagination :links="tasks.links" />
          </div>

        </div>

      </div>
    </div>
  </div> 
</template>

<style scoped>
/* Scrollbar Default (Light Mode) */
.custom-scrollbar::-webkit-scrollbar {
  height: 8px;
  display: block;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5); /* Abu-abu untuk Light Mode */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.8);
}

/* Scrollbar Dark Mode */
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1); 
}
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.2);
}
</style>