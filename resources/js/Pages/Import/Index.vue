<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// 1. Layout Persistent
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Cloud from '@/Components/Icon/Cloud.vue'; 
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const form = useForm({
  file: null,
});

const fileInput = ref(null);
const isDragging = ref(false);
const isLoaded = ref(false);

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const submitForm = () => {
  if (!form.file) {
    alert('Please select a file first.');
    return;
  }
  form.post(route('import.store'));
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  form.file = file || null;
  updateFileLabel(file);
};

const handleDrop = (e) => {
  e.preventDefault();
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  if (file) {
    form.file = file;
    updateFileLabel(file);
    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.value.files = dt.files;
  }
};

const handleDragOver = (e) => {
  e.preventDefault();
  isDragging.value = true;
};

const handleDragLeave = () => {
  isDragging.value = false;
};

const updateFileLabel = (file) => {
  const label = document.getElementById('file-label');
  if (file) {
    label.textContent = file.name;
  } else {
    label.textContent = 'Choose a file or drag it here';
  }
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const downloadTemplate = () => {
  window.location.href = route('import.template');
};
</script>

<template>
  <Head title="Import Data" />
  
  <div class="w-full">
    
    <div
      class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 transition-all duration-700 ease-out"
      :class="{ 'opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="w-full max-w-lg"> 
        <div class="flex flex-col items-start">
            
            <div class="relative z-10 -mb-[1px]">
                <div class="w-fit px-6 h-12 bg-white/40 dark:bg-slate-900/60 backdrop-blur-xl border-t border-l border-r border-white/40 dark:border-white/20 rounded-t-lg shadow-sm relative flex items-center gap-3">
                    <Cloud class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                    <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">Import Data</span>
                    <div class="absolute -bottom-[1px] left-0 right-0 h-[2px] bg-white/40 dark:bg-slate-900/80 z-20"></div>
                </div>
            </div>

            <div class="w-full bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-2xl rounded-b-lg rounded-tr-lg relative z-0 p-8 space-y-8">
            
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-slate-100 drop-shadow-sm">
                    Upload Your File
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
                    Supports bulk data import for Projects, Tasks, Users, etc.
                    </p>
                </div>

                <div
                    @drop="handleDrop"
                    @dragover="handleDragOver"
                    @dragleave="handleDragLeave"
                    @click="triggerFileInput"
                    :class="[
                    'relative border-2 border-dashed rounded-lg p-10 text-center cursor-pointer transition-all duration-300 group',
                    isDragging
                        ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/30 scale-[1.02]'
                        : 'border-gray-300 dark:border-white/10 hover:border-primary-400 dark:hover:border-primary-500 bg-white/30 dark:bg-slate-900/40'
                    ]"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        @change="handleFileChange"
                        class="hidden"
                        accept=".xlsx,.xls,.csv"
                    />
                    
                    <div class="flex justify-center mb-4">
                        <div class="p-4 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-500 dark:text-primary-400 group-hover:text-primary-600 dark:group-hover:text-primary-300 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                             <Cloud class="w-10 h-10" />
                        </div>
                    </div>

                    <p
                    id="file-label"
                    class="mt-2 text-sm font-medium text-gray-700 dark:text-slate-200 group-hover:text-primary-600 dark:group-hover:text-primary-300 transition-colors"
                    >
                    Click or drag file here to upload
                    </p>
                    <p class="text-sm text-gray-400 dark:text-slate-500 mt-2">
                    Compatible files: XLSX, XLS, CSV (Max 10MB)
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button
                        @click.prevent="downloadTemplate"
                        type="button"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 dark:border-white/10 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-slate-200 bg-white/50 dark:bg-slate-900/40 hover:bg-white dark:hover:bg-slate-900/60 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all backdrop-blur-sm"
                    >
                        <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2-8H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2z" />
                        </svg>
                        Get Template
                    </button>
                    
                    <button
                        @click.prevent="submitForm"
                        type="button"
                        :disabled="!form.file"
                        :class="[
                            'flex-1 inline-flex justify-center items-center px-4 py-2.5 rounded-lg shadow-lg text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all transform active:scale-95',
                            form.file
                            ? 'bg-primary-600 hover:bg-primary-700 hover:shadow-primary-500/30'
                            : 'bg-gray-400 dark:bg-slate-900/60 dark:text-slate-500 cursor-not-allowed opacity-70'
                        ]"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                        Import Data
                    </button>
                </div>

            </div>
        </div>

      </div>
    </div>
  </div>
</template>