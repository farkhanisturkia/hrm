<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Plus from '@/Components/Icon/Plus.vue';
import Hamburger from '@/Components/Icon/Hamburger.vue';
import Close from '@/Components/Icon/Close.vue';
import Gear from '@/Components/Icon/Gear.vue';
import Pagination from '@/Components/Pagination.vue';
import Pen from '@/Components/Icon/Pen.vue';
import UserPlus from '@/Components/Icon/UserPlus.vue';
import Document from '@/Components/Icon/Document.vue';
import TaskCreateEditForm from '@/Components/Form/TaskCreateEdit.vue';
import TaskAssignForm from '@/Components/Form/TaskAssign.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import moment from 'moment';
import SwitchInput from '@/Components/SwitchInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Search from '@/Components/Icon/Search.vue';
import externalLink from '@/Components/Icon/externalLink.vue';

const showButtons = ref(false);
const openCreateEditForm = ref(false);
const openAssignForm = ref(false);
const isEditMode = ref(false); 
const selectedTask = ref(null);
const isLoaded = ref(false);

const props = defineProps({
  tasks: {},
  projects: {},
  userData: {},
});

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const page = usePage();
const role = computed(() => page.props.auth.user.role);
const currentUserId = computed(() => page.props.auth.user.id);

const isDesktop = typeof window !== 'undefined' ? window.innerWidth >= 640 : false;
const options = ref(isDesktop);

const handleOpenOptions = () => {
  options.value = !options.value;
};

const getTaskRoleStatus = (task) => {
  const userId = currentUserId.value;
  const handlers = [
    ...(task.programmer || []),
    ...(task.designer || []),
    ...(task.communicator || []),
    ...(task.pm ? [task.pm] : [])
  ];

  const reviewers = Array.isArray(task.reviewer) 
    ? task.reviewer 
    : (task.reviewer ? [task.reviewer] : []);

  if (handlers.includes(userId)) {
    return 'Need to handle';
  }
  
  if (reviewers.includes(userId)) {
    return 'Need to review';
  }

  return '-';
};

const queryParams = computed(() => {
  const url = new URL(page.url, window.location.origin);
  return Object.fromEntries(url.searchParams);
});

const search = ref(queryParams.value.search ?? '');
const selectedProject = ref(
  queryParams.value.project_id ? Number(queryParams.value.project_id) : ''
);
const selectedCreator = ref(
  queryParams.value.creator_id ? Number(queryParams.value.creator_id) : ''
);
const selectedAssign = ref(
  queryParams.value.assign_id ? Number(queryParams.value.assign_id) : ''
);

const handleOpenForm = () => {
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
  showButtons.value = false;
  selectedTask.value = null;
};

const handleUpdateIsActive = (task) => {
  router.post(route('task.changeIsActive', task));
};

const formatDate = (date) => {
  if (!date) return '-';
  return moment(date).format('DD MMMM YYYY');
};

const handleFilter = () => {
  const params = {};
  
  if (search.value?.trim()) {
    params.search = search.value.trim();
  }

  if (selectedProject.value) {
    params.project_id = selectedProject.value;
  }
  
  if (selectedCreator.value) {
    params.creator_id = selectedCreator.value;
  }

  if (selectedAssign.value) {
    params.assign_id = selectedAssign.value;
  }

  router.get(route('task.list'), params, {
    preserveState: true,
    preserveScroll: true,
  });
};

watch(selectedProject, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    handleFilter();
  }
});
watch(selectedCreator, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    handleFilter();
  }
});
watch(selectedAssign, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    handleFilter();
  }
});

const visibleButtons = computed(() => {
  const buttons = [];
  buttons.push({ action: 'reset', icon: Close, handler: () => router.get(route('task.list')), text: 'Reset' });

  if (['manager', 'leader', 'communicator'].includes(role.value)) {
    buttons.push({ action: 'add', icon: Plus, handler: handleOpenForm, text: 'New' });
  }

  return buttons;
});
</script>

<template>
  <Head title="Tasks" />
  
  <div class="w-full py-8">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        <div
          class="flex justify-between px-6 py-4 items-center text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-1000 ease-out"
          :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">Tasks</h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage project issues and tickets.</p>
          </div>

          <div class="flex gap-4 justify-end">
            <button
              @click="handleOpenOptions"
              class="flex items-center gap-2 px-4 py-2 bg-white/50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-700/50 text-gray-700 dark:text-gray-200 rounded-lg shadow-sm border border-white/40 dark:border-white/10 backdrop-blur-sm transition-all"
            >
              <Gear class="w-4 h-4" />
              <span class="hidden sm:inline font-medium text-sm">Options</span>
            </button>

            <button
              v-if="['manager', 'leader', 'communicator'].includes(role)" 
              @click="handleOpenForm"
              class="hidden sm:flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-md hover:shadow-primary-500/30 transition-all duration-300 transform hover:scale-105 whitespace-nowrap"
            >
              <Plus class="w-5 h-5" />
              <span class="hidden sm:inline font-bold text-sm">New Task</span>
            </button>
          </div>
        </div>
    </div>

    <div class="fixed sm:hidden right-6 bottom-6 z-50 flex flex-col-reverse items-center gap-3">
      <button
        type="button"
        @click="showButtons = !showButtons"
        class="w-14 h-14 shrink-0 inline-flex items-center justify-center rounded-full text-white bg-primary-600 shadow-xl z-40 transition-all duration-300 hover:scale-105 active:scale-95 focus:outline-none"
      >
        <Hamburger v-model="showButtons" class="w-8 h-8 pointer-events-none" />
      </button>

      <TransitionGroup tag="div" name="button-list" class="flex flex-col-reverse items-center gap-3">
        <button
          v-for="(button, index) in visibleButtons"
          v-show="showButtons"
          :key="button.action"
          @click="button.handler"
          class="w-12 h-12 inline-flex items-center justify-center border border-white/20 rounded-full text-gray-700 dark:text-white bg-white/90 dark:bg-slate-800/90 backdrop-blur-md shadow-lg transition-all active:scale-95 origin-bottom"
        >
          <component :is="button.icon" class="w-5 h-5" />
        </button>
      </TransitionGroup>
    </div>

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
        <TaskAssignForm :task="selectedTask" :pm="userData.users" :communicator="userData.communicator" :engineer="userData.programmer" :designer="userData.designer" @close="handleCloseForm" />
      </div>
    </div>

    <div
      v-if="options"
      class="w-full pt-4 sm:pt-6 transition-all duration-500 ease-out relative z-30"
      :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        <div class="relative z-20 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl p-4 rounded-xl shadow-lg border border-white/40 dark:border-white/10">
          <div class="flex flex-col xl:flex-row gap-4 items-end justify-between w-full">
            
            <div class="flex flex-col sm:flex-row gap-4 w-full xl:w-auto flex-1 items-end">
              <div class="w-full sm:w-1/2 xl:w-72">
                  <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Search</label>
                  <div class="relative w-full group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                      <Search class="h-4 w-4 text-gray-400 group-focus-within:text-primary-500 transition-colors" />
                    </div>
                    <TextInput
                      id="search"
                      type="text"
                      class="block pl-10 w-full h-[42px]"
                      v-model="search"
                      placeholder="Search issue or ticket..."
                      @keydown.enter="handleFilter"
                    />
                  </div>
              </div>

              <div class="w-full sm:w-1/2 xl:w-72">
                  <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Project</label>
                  <SelectInput
                    id="creator"
                    v-model="selectedProject"
                    :options="projects"
                    label="name"
                    valueKey="id"
                    class="block w-full h-[42px]"
                    placeholder="Select user..."
                    :dark="true"
                  />
              </div>

              <div v-if="['manager', 'leader', 'communicator'].includes(role)"  class="w-full sm:w-1/2 xl:w-72">
                  <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Created By</label>
                  <SelectInput
                    id="creator"
                    v-model="selectedCreator"
                    :options="userData.creator"
                    label="name"
                    valueKey="id"
                    class="block w-full h-[42px]"
                    placeholder="Select user..."
                    :dark="true"
                  />
              </div>

              <div v-if="['manager', 'leader', 'communicator'].includes(role)"  class="w-full sm:w-1/2 xl:w-72">
                  <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Assign To</label>
                  <SelectInput
                    id="assign"
                    v-model="selectedAssign"
                    :options="userData.users"
                    label="name"
                    valueKey="id"
                    class="block w-full h-[42px]"
                    placeholder="Select user..."
                    :dark="true"
                  />
              </div>
            </div>

            <div class="hidden sm:flex flex-wrap sm:flex-nowrap justify-end gap-3 w-full xl:w-auto">
              <button 
                @click="() => router.get(route('task.list'))" 
                class="w-full sm:w-auto flex items-center justify-center gap-2 h-[42px] px-4 py-2 text-sm font-bold rounded-lg bg-gray-100 dark:bg-slate-700/50 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-600/50 transition-all border border-gray-200 dark:border-slate-600/50 shadow-sm"
              >
                <Close class="w-4 h-4" />
                <span>Reset</span>
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div
      class="w-full py-6 sm:py-8 transition-all duration-700 ease-out delay-100 relative z-10"
      :class="{ 'opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        
        <div class="flex flex-col items-start relative">

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
                    <th v-if="['manager', 'leader', 'communicator'].includes(role)" class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">
                      Is Active
                    </th>
                    <th v-if="['leader', 'engineer', 'communicator', 'designer'].includes(role)" class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">
                      Note
                    </th>
                    <th v-if="['manager', 'leader', 'communicator'].includes(role)" class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/20 dark:divide-white/5">
                  <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-white/30 dark:hover:bg-primary-500/10 transition duration-200">
                    
                    <td class="px-4 py-3 align-middle text-sm min-w-[180px]">
                      <div class="grid grid-cols-2 gap-1.5">
                           <span v-for="(id, idx) in [...(task.pm ? [task.pm] : []), ...(task.programmer || []), ...(task.designer || []), ...(task.communicator || [])]" 
                              :key="idx"
                              class="block px-2 py-1 rounded-md bg-white/50 dark:bg-slate-800/40 border border-gray-200 dark:border-white/10 text-xs text-gray-700 dark:text-slate-300 text-center truncate"
                              :title="userData.users.find(u => u.id === id)?.name || id"
                           >
                              {{ userData.users.find(u => u.id === id)?.name || id }}
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
                    
                    <td v-if="['manager', 'leader', 'communicator'].includes(role)" class="px-4 py-3 align-middle">
                      <div class="flex justify-center">
                        <SwitchInput v-slot:default v-model="task.isActive" @update:modelValue="handleUpdateIsActive(task)" />
                      </div>
                    </td>

                    <td v-if="['leader', 'engineer', 'communicator', 'designer'].includes(role)" class="px-4 py-3 align-middle text-center whitespace-nowrap">
                      <span 
                        :class="[
                          'px-2.5 py-1 rounded-md text-xs font-semibold border inline-block',
                          getTaskRoleStatus(task) === 'Need to handle'
                            ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/50'
                            : getTaskRoleStatus(task) === 'Need to review'
                              ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50'
                              : 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-slate-800 dark:text-slate-400 dark:border-white/10'
                        ]"
                      >
                        {{ getTaskRoleStatus(task) }}
                      </span>
                    </td>

                    <td class="px-4 py-3 align-middle whitespace-nowrap">
                      <div class="flex items-center justify-center gap-3">
                        <button 
                          v-if="['manager', 'leader'].includes(role)"
                          @click="handleAssignTask(task)"
                          class="p-1.5 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition tooltip-trigger"
                          title="Assign User"
                        >
                           <UserPlus class="w-5 h-5" />
                        </button>

                        <button 
                          v-if="['manager', 'leader', 'communicator'].includes(role)"
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