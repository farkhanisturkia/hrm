<script setup>
import { ref, watch } from 'vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: "Delete Confirmation"
    },
    message: {
        type: String,
        default: "Are You Sure Want To Delete This Item?"
    },
    processing: Boolean,
});

const emit = defineEmits(['close', 'confirm']);

// State lokal untuk trigger loading seketika saat tombol diklik
const isLoading = ref(false);

const handleConfirm = () => {
    isLoading.value = true;
    emit('confirm');
};

const handleClose = () => {
    emit('close');
};

// Reset state loading kembali ke false ketika modal ditutup
watch(() => props.show, (newVal) => {
    if (!newVal) {
        isLoading.value = false;
    }
});
</script>

<template>
    <Modal :show="show" @close="handleClose" max-width="sm">
        <div class="p-6 bg-white/50 dark:bg-slate-800 text-black dark:text-white">
            <div class="text-center">
                <h1 class="text-lg font-bold text-black dark:text-white mb-2">
                    {{ title }}
                </h1>

                <p class="text-sm text-black dark:text-white mb-6">
                    {{ message }}
                </p>
            </div>

            <div class="flex justify-center gap-4">
                <button
                    @click="handleClose"
                    :disabled="processing || isLoading"
                    class="px-4 py-2 bg-white/40 dark:bg-gray-800 text-white rounded-lg hover:opacity-30 border border-white transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Cancel
                </button>
                <button
                    @click="handleConfirm"
                    :disabled="processing || isLoading"
                    class="flex items-center justify-center min-w-[130px] px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg 
                        v-if="processing || isLoading" 
                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
                        xmlns="http://www.w3.org/2000/svg" 
                        fill="none" 
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ processing || isLoading ? 'Deleting...' : 'Delete Now' }}
                </button>
            </div>
        </div>
    </Modal>
</template>