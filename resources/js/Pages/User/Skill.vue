<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// 1. Layout sudah didaftarkan di sini (Persistent)
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Plus from '@/Components/Icon/Plus.vue';
import Gear from '@/Components/Icon/Gear.vue';
import Download from '@/Components/Icon/Download.vue';
import Hamburger from '@/Components/Icon/Hamburger.vue';
import Book from '@/Components/Icon/Book.vue'; 
import Trash from '@/Components/Icon/Trash.vue'; 
import SkillForm from '@/Components/Form/Skill.vue';
import SelectInput from '@/Components/SelectInput.vue';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import moment from 'moment';
import Close from '@/Components/Icon/Close.vue';

const showButtons = ref(false);
const openForm = ref(false);
const isLoaded = ref(false);
const confirmDeleteModal = ref(false)
const skillToDelete = ref(null)

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

const options = ref(['other', 'co'].includes(role.value) ? true : false);
const id = ref(Number(queryParams.value.user_id) || null);

const handleOpenOptions = () => {
  options.value = !options.value;
};

const handleOpenForm = () => {
  openForm.value = true;
};

const handleCloseForm = () => {
  openForm.value = false;
};

const openDeleteModal = (skill) => {
  skillToDelete.value = skill;
  confirmDeleteModal.value = true;
}

const closeDeleteModal = () => {
  skillToDelete.value = null;
  confirmDeleteModal.value = false;
}

const handleConfirmDelete = (id) => {
  router.delete(route('skill.destroy', skillToDelete.value.id), {
    onSuccess: () => closeDeleteModal(),
  });
};

const props = defineProps({
  skills: {}, 
  users: {}
});

const formatDate = (date) => {
  return moment(date).format('DD MMMM YYYY');
};

const exportSkill = () => {
  const params = {};
  if (id.value) params.user_id = id.value;
  window.open(route('skill.export', params), '_blank');
};

const visibleButtons = computed(() => {
  return [
    { action: 'add', icon: Plus, handler: handleOpenForm, text: 'New' },
    { action: 'export', icon: Download, handler: () => exportSkill(), text: 'Export' },
    { action: 'reset', icon: Close, handler: () => router.get(route('skill.list')), text: 'Reset' }
  ];
});

watch(id, (newValue) => {
  const params = { user_id: newValue };
  router.get(route('skill.list', params));
});
</script>

<template>
  <Head title="Skills" />
  
  <div class="w-full py-8">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        <div
          class="flex justify-between px-6 py-4 items-center text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-1000 ease-out"
          :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">
              Skills Matrix
            </h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage user technical skills.</p>
          </div>
          
          <div class="flex gap-4 justify-end">
            <button
              v-if="['other', 'co'].includes(role)"
              @click="handleOpenOptions"
              class="flex items-center gap-2 px-4 py-2 bg-white/50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-700/50 text-gray-700 dark:text-gray-200 rounded-lg shadow-sm border border-white/40 dark:border-white/10 backdrop-blur-sm transition-all"
            >
              <Gear class="w-4 h-4" />
              <span class="hidden sm:inline font-medium text-sm">Options</span>
            </button>
            <button
              @click="handleOpenForm"
              class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-md hover:shadow-primary-500/30 transition-all duration-300 transform hover:scale-105"
            >
              <Plus class="w-5 h-5" />
              <span class="hidden sm:inline font-bold text-sm">Add Skill</span>
            </button>
          </div>
        </div>
    </div>

    <div v-if="['other', 'co'].includes(role)" class="fixed sm:hidden right-6 bottom-6 z-50">
      <div
        class="shrink-0 inline-flex items-center justify-center p-3 rounded-full text-white bg-primary-600 shadow-xl z-40 transition-all duration-500 hover:scale-110 active:scale-95"
        @click="showButtons = !showButtons"
      >
        <Hamburger v-model="showButtons" class="w-6 h-6" />
      </div>
      <TransitionGroup tag="div" name="button-list">
        <button
          v-for="(button, index) in visibleButtons"
          v-show="showButtons"
          :key="button.action"
          @click="button.handler"
          class="fixed right-6 border border-white/20 rounded-full p-3 text-gray-700 dark:text-white bg-white/90 dark:bg-slate-800/90 backdrop-blur-md shadow-lg"
          :style="{ bottom: `${80 + (index) * 60}px` }"
        >
          <component :is="button.icon" class="w-5 h-5" />
        </button>
      </TransitionGroup>
    </div>
    <button v-else
      @click="handleOpenForm"
      class="fixed sm:hidden right-6 bottom-6 border border-white/20 rounded-full p-4 text-white bg-primary-600 shadow-xl z-40 transition-all duration-500 ease-out hover:scale-110 active:scale-95"
      :class="{ 'translate-y-0 opacity-100 scale-100': isLoaded, 'translate-y-12 opacity-0 scale-75': !isLoaded }"
    >
      <Plus />
    </button>

    <div v-if="openForm" class="fixed inset-0 z-50 px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-300">
        <SkillForm @close="handleCloseForm" />
      </div>
    </div>

    <div
      v-if="options"
      class="w-full pt-4 sm:pt-6 transition-all duration-500 ease-out relative z-30"
      :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        <div class="relative z-20 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl p-4 rounded-xl shadow-lg border border-white/40 dark:border-white/10">
          <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
            
            <div class="flex flex-1 flex-col md:flex-row gap-4 w-full">
              <div class="w-full md:w-72">
                <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Filter User</label>
                <SelectInput
                  id="user"
                  v-model="id"
                  :options="users"
                  label="name"
                  valueKey="id"
                  class="block w-full h-[42px]"
                  placeholder="Select user..."
                  :dark="true"
                />
              </div>
            </div>

            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto items-end">
              <button
                @click="exportSkill"
                class="w-full md:w-auto flex items-center justify-center gap-2 h-11 px-4 py-2 text-sm font-bold rounded-lg bg-primary-600 hover:bg-primary-500 text-white transition-all shadow-md"
              >
                <Download class="w-4 h-4" />
                <span>Export</span>
              </button>
              <button
                @click="() => router.get(route('skill.list'))"
                class="w-full md:w-auto flex items-center justify-center gap-2 h-11 px-4 py-2 text-sm font-bold rounded-lg bg-gray-100 dark:bg-slate-700/50 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-600/50 transition-all border border-gray-200 dark:border-slate-600/50 shadow-sm"
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
                    <Book class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                    <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">Skills List</span>
                    <div class="absolute -bottom-[1px] left-0 right-0 h-[2px] bg-white/40 dark:bg-slate-900/80 z-20"></div>
                </div>
            </div>

            <div class="w-full overflow-x-auto bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-xl rounded-b-lg rounded-tr-lg relative z-0">
            <table class="w-full text-left dark:text-slate-200 table-auto border-collapse">
                <thead class="sticky top-0 z-20 bg-white/50 dark:bg-slate-900/80 backdrop-blur-md border-b border-white/20 dark:border-white/10">
                <tr>
                    <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">
                      Skill Name
                    </th>
                    <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">
                      Date Added
                    </th>
                    <th class="p-5 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">
                      Action
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-white/20 dark:divide-white/5">
                <tr v-for="value in skills" :key="value.id" class="hover:bg-white/30 dark:hover:bg-primary-500/10 transition duration-200">
                    <td class="p-5 align-middle">
                      <div class="flex items-center gap-2">
                          <span class="px-2.5 py-1 rounded-full bg-primary-100/50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-sm font-bold border border-primary-200/50 dark:border-primary-700/30">
                             {{ value.skill }}
                          </span>
                      </div>
                    </td>
                    <td class="p-5 align-middle text-sm text-gray-500 dark:text-slate-400 font-mono">
                        {{ formatDate(value.created_at) }}
                    </td>
                    <td class="p-5 align-middle text-center">
                        <button
                        @click.prevent="openDeleteModal(value)"
                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition tooltip-trigger inline-flex"
                        title="Delete"
                        >
                          <Trash class="w-5 h-5" />
                        </button>
                    </td>
                </tr>
                <tr v-if="skills.length === 0">
                    <td colspan="3" class="p-8 text-center text-gray-400 dark:text-gray-500 italic">No skills added yet.</td>
                </tr>
                </tbody>
            </table>
            </div>
        </div>

      </div>
    </div>

    <DeleteConfirmationModal
      :show="confirmDeleteModal"
      title="Delete Skill"
      :message="`Are you sure want to delete ${skillToDelete?.skill} skill`"
      @close="closeDeleteModal"
      @confirm="handleConfirmDelete"
    />
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
  background-color: rgba(156, 163, 175, 0.5); 
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