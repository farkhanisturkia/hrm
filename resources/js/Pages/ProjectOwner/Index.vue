<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Plus from '@/Components/Icon/Plus.vue';
import Build from '@/Components/Icon/Build.vue';
import Pen from '@/Components/Icon/Pen.vue';   
import Trash from '@/Components/Icon/Trash.vue'; 
import Pagination from '@/Components/Pagination.vue';
import ProjectOwnerForm from '@/Components/Form/ProjectOwner.vue';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const openForm = ref(false);
const isEditMode = ref(false);
const selectedProjectOwner = ref(null);
const isLoaded = ref(false);
const confirmDeleteModal = ref(false);
const ownerToDelete = ref(null);

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const page = usePage();
const role = computed(() => page.props.auth.user.role);

const handleOpenForm = () => {
  isEditMode.value = false;
  selectedProjectOwner.value = null;
  openForm.value = true;
};

const handleCloseForm = () => {
  openForm.value = false;
  isEditMode.value = false;
  selectedProjectOwner.value = null;
};

const openDeleteModal = (owner) => {
  ownerToDelete.value = owner;
  confirmDeleteModal.value = true;
};

const closeDeleteModal = () => {
  ownerToDelete.value = null;
  confirmDeleteModal.value = false;
};

const handleConfirmDelete = () => {
  router.delete(route('projectOwner.destroy', ownerToDelete.value.id), {
    onSuccess: () => closeDeleteModal(),
  });
};

const handleEdit = (id) => {
  const projectOwner = props.projectOwners.data.find(c => c.id === id);
  if (projectOwner) {
    isEditMode.value = true;
    selectedProjectOwner.value = projectOwner;
    openForm.value = true;
  }
};

const props = defineProps({
  projectOwners: {}, 
  users: {}
});

const getNameUser = (id) => {
  const user = props.users.find(u => u.id === id);
  return user ? user.name : '-';
};
</script>

<template>
  <Head title="Project Owner" />
  
  <div class="w-full">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0 mt-8">
        <div
          class="flex justify-between px-6 py-4 items-center text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-1000 ease-out"
          :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">
              Project Owners
            </h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage project owner list.</p>
          </div>
          
          <div v-if="['other', 'pm', 'co'].includes(role)" class="flex justify-end">
            <button
              @click="handleOpenForm"
              class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-md hover:shadow-primary-500/30 transition-all duration-300 transform hover:scale-105"
            >
              <Plus class="w-5 h-5" />
              <span class="hidden sm:inline font-bold text-sm">New Owner</span>
            </button>
          </div>
        </div>
    </div>

    <button
      v-if="['other', 'pm', 'co'].includes(role)"
      @click="handleOpenForm"
      class="fixed sm:hidden right-6 bottom-6 border border-white/20 rounded-full p-4 text-white bg-primary-600 shadow-xl z-40 transition-all duration-500 ease-out hover:scale-110 active:scale-95"
      :class="{ 'translate-y-0 opacity-100 scale-100': isLoaded, 'translate-y-12 opacity-0 scale-75': !isLoaded }"
    >
      <Plus />
    </button>

    <div v-if="openForm" class="fixed inset-0 z-50 px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div
        class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-300"
      >
        <ProjectOwnerForm :projectOwners="selectedProjectOwner" :isEditMode="isEditMode" @close="handleCloseForm" />
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
                <Build class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">Project Owner List</span>
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
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Creator</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Updater</th>
                  <th v-if="['other', 'pm', 'co'].includes(role)" class="p-5 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/20 dark:divide-white/5">
                <tr v-for="owner in projectOwners.data" :key="owner.id" class="hover:bg-white/30 dark:hover:bg-primary-500/10 transition duration-200">
                  <td class="p-5 align-middle text-sm text-gray-500 dark:text-slate-400">
                    #{{ owner.id }}
                  </td>
                  <td class="p-5 align-middle">
                    <a
                      :href="route('project.list', { project_owner_id: owner.id })"
                      class="font-bold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 hover:underline decoration-primary-300 underline-offset-2 transition"
                    >
                      {{ owner.name }}
                    </a>
                  </td>
                  <td class="p-5 align-middle">
                     <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-transparent dark:border-white/5">
                             {{ getNameUser(owner.creator).charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ getNameUser(owner.creator) }}</span>
                     </div>
                  </td>
                  <td class="p-5 align-middle">
                     <div class="flex items-center gap-2">
                         <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/5">
                             {{ getNameUser(owner.updater).charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ getNameUser(owner.updater) }}</span>
                     </div>
                  </td>
                  <td v-if="['other', 'pm', 'co'].includes(role)" class="p-5 align-middle">
                    <div class="flex gap-3 justify-center items-center">
                      <button
                        @click.prevent="handleEdit(owner.id)"
                        class="p-1.5 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition tooltip-trigger" title="Edit"
                      >
                        <Pen class="w-5 h-5" />
                      </button>
                      <button
                        @click.prevent="openDeleteModal(owner)"
                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition tooltip-trigger" title="Delete"
                      >
                        <Trash class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6 flex justify-end w-full">
             <Pagination :links="projectOwners.links" />
          </div>

        </div>

      </div>
    </div>

    <DeleteConfirmationModal 
      :show="confirmDeleteModal"
      title="Delete Project Owner"
      :message="`Are you sure want to delete project owner ${ownerToDelete?.name}`"
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