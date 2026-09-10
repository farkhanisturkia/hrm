<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Plus from '@/Components/Icon/Plus.vue';
import Hamburger from '@/Components/Icon/Hamburger.vue';
import Close from '@/Components/Icon/Close.vue';
import Gear from '@/Components/Icon/Gear.vue';
import Search from '@/Components/Icon/Search.vue';
import Folder from '@/Components/Icon/Folder.vue'; 
import Pen from '@/Components/Icon/Pen.vue';
import SwitchInput from '@/Components/SwitchInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import ProjectForm from '@/Components/Form/Project.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';

const page = usePage();
const role = computed(() => page.props.auth.user.role);
const queryParams = computed(() => {
  const url = new URL(page.url, window.location.origin);
  return Object.fromEntries(url.searchParams);
});

const showButtons = ref(false);
const openForm = ref(false);
const isEditMode = ref(false);
const selectedProject = ref(null);
const isLoaded = ref(false);
const search = ref(queryParams.value.search ?? '');
const selectedProjectOwner = ref(
  queryParams.value.project_owner_id ? Number(queryParams.value.project_owner_id) : ''
);

const isDesktop = typeof window !== 'undefined' ? window.innerWidth >= 640 : false;
const options = ref(isDesktop);

const handleOpenOptions = () => {
  options.value = !options.value;
};

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const handleOpenForm = () => {
  isEditMode.value = false;
  selectedProject.value = null;
  openForm.value = true;
};

const handleCloseForm = () => {
  openForm.value = false;
  isEditMode.value = false;
  showButtons.value = false;
  selectedProject.value = null;
};

const handleUpdateIsActive = (project) => {
  router.post(route('project.changeIsActive', project));
};

const handleEdit = (id) => {
  const project = props.projects.data.find(p => p.id === id);
  if (project) {
    isEditMode.value = true;
    selectedProject.value = project;
    openForm.value = true;
  }
};

const props = defineProps({
  projects: {}, 
  projectOwners: {},
  users: {}
});

const getNameUser = (id) => {
  const user = props.users.find(u => u.id === id);
  return user ? user.name : '-';
};

const handleFilter = () => {
  const params = {};
  
  if (search.value?.trim()) {
    params.search = search.value.trim();
  }
  
  if (selectedProjectOwner.value) {
    params.project_owner_id = selectedProjectOwner.value;
  }

  router.get(route('project.list'), params, {
    preserveState: true,
    preserveScroll: true,
  });
};

watch(selectedProjectOwner, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    handleFilter();
  }
});

const visibleButtons = computed(() => {
  const buttons = [];
  buttons.push({ action: 'reset', icon: Close, handler: () => router.get(route('project.list')), text: 'Reset' });

  if (['manager', 'leader', 'communicator'].includes(role.value)) {
    buttons.push({ action: 'add', icon: Plus, handler: handleOpenForm, text: 'New' });
  }

  return buttons;
});
</script>

<template>
  <Head title="Projects" />
  
  <div class="w-full py-8">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        <div
          class="flex justify-between px-6 py-4 items-center text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-1000 ease-out"
          :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">Projects</h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage all projects and assignments.</p>
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
              class="hidden sm:flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-md hover:shadow-primary-500/30 transition-all duration-300 transform hover:scale-105"
            >
              <Plus class="w-5 h-5" />
              <span class="hidden sm:inline font-bold text-sm">New Project</span>
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

    <div v-if="openForm" class="fixed inset-0 z-50 px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div
        class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-300"
      >
        <ProjectForm
          :project="selectedProject"
          :projectOwners="projectOwners"
          :projectOwnerId="queryParams.project_owner_id"
          :isEditMode="isEditMode"
          @close="handleCloseForm"
        />
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
                    placeholder="Search name..."
                    @keydown.enter="handleFilter"
                  />
                </div>
              </div>

              <div class="w-full sm:w-1/2 xl:w-72">
                  <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Project owner</label>
                  <SelectInput
                    id="projectOwners"
                    v-model="selectedProjectOwner"
                    :options="projectOwners"
                    label="name"
                    valueKey="id"
                    class="block w-full h-[42px]"
                    placeholder="Select project owner..."
                    :dark="true"
                  />
              </div>
            </div>

            <div class="hidden sm:flex flex-wrap sm:flex-nowrap justify-end gap-3 w-full xl:w-auto">
              <button 
                @click="() => router.get(route('project.list'))"
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

    <div class="w-full py-8">
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        
        <div 
          class="flex flex-col items-start transition-all duration-700 ease-out delay-100"
          :class="{ 'opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
        >

          <div class="relative z-10 -mb-[1px]">
             <div class="w-fit px-6 h-12 bg-white/40 dark:bg-slate-900/60 backdrop-blur-xl border-t border-l border-r border-white/40 dark:border-white/20 rounded-t-lg shadow-sm relative flex items-center gap-3">
                <Folder class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">Projects Data</span>
                <div class="absolute -bottom-[1px] left-0 right-0 h-[2px] bg-white/40 dark:bg-slate-900/80 z-20"></div>
             </div>
          </div>

          <div
            class="w-full overflow-x-auto bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-xl rounded-b-lg rounded-tr-lg relative z-0"
          >
            <table class="w-full text-left dark:text-slate-200 table-auto border-collapse">
              <thead>
                <tr class="bg-white/50 dark:bg-slate-900/80 backdrop-blur-md border-b border-white/20 dark:border-white/10">
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">ID</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Name</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Project Owner</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Creator</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Updater</th>
                  <th v-if="['manager', 'leader', 'communicator'].includes(role)" class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider whitespace-nowrap">
                    Is Active
                  </th>
                  <th v-if="['manager', 'leader', 'communicator'].includes(role)" class="p-5 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/20 dark:divide-white/5">
                <tr v-for="project in projects.data" :key="project.id" class="hover:bg-white/30 dark:hover:bg-primary-500/10 transition duration-200">
                  <td class="p-5 align-middle text-sm text-gray-500 dark:text-slate-400">
                    #{{ project.id }}
                  </td>
                  <td class="p-5 align-middle">
                    <a
                      :href="route('task.list', { project_id: project.id })"
                      class="font-bold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 hover:underline decoration-primary-300 underline-offset-2 transition flex items-center gap-2"
                    >
                      <Folder class="w-5 h-5 opacity-50" />
                      {{ project.name }}
                    </a>
                  </td>
                  <td class="p-5 align-middle">
                    <span v-if="project.project_owner" class="px-2.5 py-1 rounded-md bg-slate-100/50 dark:bg-slate-800/40 text-slate-600 dark:text-slate-300 text-xs font-bold border border-slate-200/50 dark:border-white/10">
                        {{ project.project_owner.name }}
                    </span>
                    <span v-else class="text-gray-400 text-xs italic">-</span>
                  </td>
                  <td class="p-5 align-middle">
                     <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-transparent dark:border-white/5">
                             {{ getNameUser(project.creator).charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ getNameUser(project.creator) }}</span>
                     </div>
                  </td>
                  <td class="p-5 align-middle">
                     <div class="flex items-center gap-2">
                         <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/5">
                             {{ getNameUser(project.updater).charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ getNameUser(project.updater) }}</span>
                     </div>
                  </td>
                  <td v-if="['manager', 'leader', 'communicator'].includes(role)" class="px-4 py-3 align-middle">
                    <div class="flex justify-center">
                      <SwitchInput v-slot:default v-model="project.isActive" @update:modelValue="handleUpdateIsActive(project)" />
                    </div>
                  </td>
                  <td v-if="['manager', 'leader', 'communicator'].includes(role)" class="p-5 align-middle">
                    <div class="flex gap-3 justify-center items-center">
                      <button
                        @click.prevent="handleEdit(project.id)"
                        class="p-1.5 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition tooltip-trigger" title="Edit"
                      >
                        <Pen class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="projects.data.length === 0">
                    <td colspan="6" class="p-8 text-center text-gray-400 dark:text-gray-500 italic">No projects found.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6 flex justify-end w-full">
             <Pagination :links="projects.links" />
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