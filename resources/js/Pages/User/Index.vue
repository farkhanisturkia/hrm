<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Trash from '@/Components/Icon/Trash.vue';
import { data } from 'autoprefixer';
import Pen from '@/Components/Icon/Pen.vue';

// 1. Layout Persistent
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Plus from '@/Components/Icon/Plus.vue';
import User from '@/Components/Icon/User.vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue'; 
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
  users: {}
});

const openForm = ref(false);
const isEditMode = ref(false);
const isLoaded = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const confirmDeleteModal = ref(false)
const userToDelete = ref(null)

const form = useForm({
  id: null,
  name: '',
  email: '',
  role: 'pg',
  is_wfa_allowed: false,
  password: '',
  password_confirmation: '',
  face_photo: '',
});

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const isPasswordMismatch = computed(() => {
  return form.password && form.password_confirmation && form.password !== form.password_confirmation;
});

const handleOpenForm = () => {
  isEditMode.value = false;
  form.reset(); 
  form.clearErrors();
  showPassword.value = false;
  showConfirmPassword.value = false;
  openForm.value = true;
};

const handleCloseForm = () => {
  openForm.value = false;
  isEditMode.value = false;
  form.reset();
  form.clearErrors();
  document.getElementById('face_photo').value = '';
};

const handleEdit = (id) => {
  const user = props.users.data.find(u => u.id === id);
  if (user) {
    isEditMode.value = true;
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.is_wfa_allowed = Boolean(user.is_wfa_allowed);
    form.password = ''; 
    form.password_confirmation = '';
    
    showPassword.value = false;
    showConfirmPassword.value = false;
    openForm.value = true;
  }
};

const openDeleteModal = (user) => {
  userToDelete.value = user;
  confirmDeleteModal.value = true;
};

const closeDeleteModal = () => {
  userToDelete.value = null;
  confirmDeleteModal.value = false;
};

const handleConfirmDelete = () => {
  router.delete(route('user.destroy', userToDelete.value.id), {
    onSuccess: () => closeDeleteModal(),
  })
};

const toggleWfa = (user) => {
  router.patch(route('user.toggleWfa', user.id), {
    is_wfa_allowed: !user.is_wfa_allowed
  }, {
    preserveScroll: true
  });
};

const handleSubmit = () => {
  if (isPasswordMismatch.value) return;

  const url = isEditMode.value
              ? route('user.update', form.id)
              : route('user.store')

  form.transform((data) => ({
    ...data,
    _method: isEditMode.value ? 'put' : 'post'
  })).post(url, {
    preserveScroll: true,
    onSuccess: () => handleCloseForm()
  });
};

const handleFileChange = (e) => {
  form.face_photo = e.target.files[0]
};

const formatRole = (role) => {
  const roles = {
    'pm': 'Project Manager',
    'pg': 'Programmer',
    'co': 'Communicator',
    'ds': 'Designer',
    'other': 'Other'
  };
  return roles[role] || role;
};
</script>

<template>
  <Head title="Users" />
  
  <div class="w-full">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0 mt-8">
        <div
          class="flex justify-between px-6 py-4 items-center text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-1000 ease-out"
          :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">Users Management</h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage system access and roles.</p>
          </div>
          <div class="flex justify-end">
            <button
              @click="handleOpenForm"
              class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-md hover:shadow-primary-500/30 transition-all duration-300 transform hover:scale-105"
            >
              <Plus class="w-5 h-5" />
              <span class="hidden sm:inline font-bold text-sm">Add New User</span>
            </button>
          </div>
        </div>
    </div>

    <button
      @click="handleOpenForm"
      class="fixed sm:hidden right-6 bottom-6 border border-white/20 rounded-full p-4 text-white bg-primary-600 shadow-xl z-40 transition-all duration-500 ease-out hover:scale-110 active:scale-95"
      :class="{ 'translate-y-0 opacity-100 scale-100': isLoaded, 'translate-y-12 opacity-0 scale-75': !isLoaded }"
    >
      <Plus />
    </button>

    <div v-if="openForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-8 relative animate-in fade-in zoom-in duration-300">
        
        <div class="flex justify-between items-center mb-6">
          <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-slate-100">
                {{ isEditMode ? 'Edit User' : 'Create New User' }}
              </h2>
              <p class="text-sm text-gray-500 dark:text-slate-400">Fill in the details below.</p>
          </div>
          <button @click="handleCloseForm" class="p-2 bg-gray-100 dark:bg-slate-800/80 rounded-full text-gray-400 hover:text-red-500 transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <InputLabel for="name" value="Full Name" />
            <TextInput id="name" type="text" class="mt-1 block w-full bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm dark:border-white/10 dark:text-white" v-model="form.name" required autofocus placeholder="John Doe" />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <div>
            <InputLabel for="email" value="Email Address" />
            <TextInput id="email" type="email" class="mt-1 block w-full bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm dark:border-white/10 dark:text-white" v-model="form.email" required placeholder="name@company.com" />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div>
            <InputLabel for="role" value="Role" />
            <div class="relative">
                <select id="role" v-model="form.role" class="mt-1 block w-full border-gray-300 dark:border-white/10 bg-white/50 dark:bg-slate-800/50 dark:text-slate-200 focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-sm backdrop-blur-sm transition cursor-pointer py-2.5">
                  <option value="pm">Project Manager</option>
                  <option value="pg">Programmer</option>
                  <option value="ds">Designer</option>
                  <option value="co">Communicator</option>
                  <option value="other">Other</option>
                </select>
            </div>
          </div>

          <div class="mt-4">
            <InputLabel for="face_photo" value="Foto Data Wajah" />

            <input
              type="file"
              id="face_photo"
              class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-4 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2876BC] file:text-white hover:file:bg-indigo-100"
              @input="form.face_photo = $event.target.files[0]"
              @change="handleFileChange"
              accept="image/*"
            />

            <InputError :message="form.errors.face_photo" class="mt-2" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
              <InputLabel for="password" :value="isEditMode ? 'Password (Optional)' : 'Password'" />
              <div class="relative mt-1">
                <TextInput id="password" :type="showPassword ? 'text' : 'password'" class="block w-full pr-10 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm dark:border-white/10 dark:text-white" v-model="form.password" :required="!isEditMode" placeholder="••••••••" />
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-primary-500 transition">
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                </button>
              </div>
              <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="relative">
              <InputLabel for="password_confirmation" value="Confirm Password" />
              <div class="relative mt-1">
                <TextInput id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" class="block w-full pr-10 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm dark:border-white/10 dark:text-white" v-model="form.password_confirmation" :required="!isEditMode && form.password" placeholder="••••••••" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-primary-500 transition">
                  <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                </button>
              </div>
              <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>
          </div>
          <p v-if="isPasswordMismatch" class="text-sm text-red-500 font-bold dark:bg-red-500/10 p-2 rounded-lg animate-pulse">Passwords do not match!</p>

          <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100 dark:border-white/10">
            <SecondaryButton @click="handleCloseForm" :disabled="form.processing">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing" class="shadow-lg shadow-primary-500/30">
               {{ form.processing ? 'Saving...' : (isEditMode ? 'Update User' : 'Create User') }}
            </PrimaryButton>
          </div>
        </form>
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
                <User class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">All Users Data</span>
                <div class="absolute -bottom-[1px] left-0 right-0 h-[2px] bg-white/40 dark:bg-slate-900/80 z-20"></div>
             </div>
          </div>
          
          <div
            class="w-full overflow-x-auto bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-xl rounded-b-lg rounded-tr-lg relative z-0"
          >
            <table class="w-full text-left dark:text-slate-200 table-auto border-collapse">
              <thead>
                <tr class="bg-white/50 dark:bg-slate-900/80 backdrop-blur-md border-b border-white/20 dark:border-white/10">
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">No</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Name</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Email</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Role</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Photo</th>
                  <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider text-center">WFA</th>
                  <th class="p-5 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/20 dark:divide-white/5">
                <tr v-for="(user, idx) in users.data" :key="user.id" class="hover:bg-white/30 dark:hover:bg-white/5 transition duration-200">
                  <td class="p-5 align-middle text-sm text-gray-500 dark:text-slate-400">{{ (users.current_page - 1) * users.per_page + idx + 1 }}</td>
                  
                  <td class="p-5 align-middle">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-100/50 dark:bg-slate-800 flex items-center justify-center text-primary-600 dark:text-primary-300 font-bold text-xs border border-primary-100/50 dark:border-slate-700">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="font-bold text-gray-800 dark:text-slate-200">{{ user.name }}</span>
                      </div>
                  </td>
                  <td class="p-5 align-middle text-sm text-gray-600 dark:text-slate-400 font-mono">{{ user.email }}</td>
                  <td class="p-5 align-middle">
                     <span :class="{
                        'bg-purple-100/50 text-purple-700 border-purple-200/50 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800/30': user.role === 'pm',
                        'bg-primary-100/50 text-primary-700 border-primary-200/50 dark:bg-primary-900/30 dark:text-primary-400 dark:border-primary-800/30': user.role === 'pg',
                        'bg-pink-100/50 text-pink-700 border-pink-200/50 dark:bg-pink-900/30 dark:text-pink-400 dark:border-pink-800/30': user.role === 'ds',
                        'bg-orange-100/50 text-orange-700 border-orange-200/50 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/30': user.role === 'co',
                        'bg-gray-100/50 text-gray-700 border-gray-200/50 dark:bg-slate-800/30 dark:text-slate-300 dark:border-slate-700/30': user.role === 'other',
                     }" class="px-3 py-1 rounded-full text-xs font-bold border backdrop-blur-sm shadow-sm">
                        {{ formatRole(user.role) }}
                     </span>
                  </td>
                  <td 
                    class="p-5 align-middle text-sm italic"
                    :class="[
                      user.face_embedding
                        ? 'text-slate-500'
                        : 'text-slate-800 font-bold dark:text-slate-300'
                    ]"
                  >
                    <span>
                      {{ user.face_embedding ? 'Available' : 'Not Available' }}
                    </span>
                  </td>
                  <td class="p-5 align-middle text-center">
                    <label class="flex items-center justify-center cursor-pointer tooltip-trigger" title="Toggle Akses WFA">
                      <div class="relative">
                          <input type="checkbox" class="sr-only" :checked="user.is_wfa_allowed" @change="toggleWfa(user)">
                          <div class="block w-10 h-5 rounded-full transition-colors duration-300 shadow-inner" :class="user.is_wfa_allowed ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-slate-600'"></div>
                          <div class="dot absolute left-1 top-1 bg-white w-3 h-3 rounded-full transition-transform duration-300 shadow-sm" :class="{'transform translate-x-5': user.is_wfa_allowed}"></div>
                      </div>
                    </label>
                  </td>
                  <td class="p-5 align-middle">
                    <div class="flex gap-3 justify-center items-center text-sm">
                      <button 
                        @click="handleEdit(user.id)" 
                        class="p-1.5 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition tooltip-trigger" title="Edit"
                      >
                          <Pen class="w-5 h-5" />
                      </button>
                      <button 
                        @click.prevent="openDeleteModal(user)" 
                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition tooltip-trigger" 
                        title="Delete"
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
             <Pagination :links="users.links" />
          </div>

        </div>

      </div>
    </div>
    <DeleteConfirmationModal
      :show="confirmDeleteModal"
      title="Delete User"
      :message="`Are you sure you want to delete user ${userToDelete?.name}?`"
      @close="closeDeleteModal"
      @confirm="handleConfirmDelete"
    />
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
  display: block;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.8);
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1);
}
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.2);
}
</style>