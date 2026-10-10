<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';

const emit = defineEmits(['close']);

const props = defineProps({
    task: {},
    pm: {},
    communicator: {},
    engineer: {},
    designer: {},
});

const parseArray = (data) => {
    if (!data) return [];
    if (Array.isArray(data)) {
        return data.map(item => (typeof item === 'object' && item !== null) ? item.id : item);
    }
    return [typeof data === 'object' ? data.id : data];
};

const form = useForm({
    pm: props.task?.pm?.id || props.task?.pm || '',
    communicator: parseArray(props.task?.communicator),
    engineer: parseArray(props.task?.engineer),
    designer: parseArray(props.task?.designer),
    _method: 'PUT',
});

const isAllCommunicatorsSelected = computed({
    get() {
        if (!props.communicator || props.communicator.length === 0) return false;
        return props.communicator.every(user => form.communicator.includes(user.id));
    },
    set(value) {
        if (value) {
            form.communicator = props.communicator.map(user => user.id);
        } else {
            form.communicator = [];
        }
    }
});

const isAllEngineersSelected = computed({
    get() {
        if (!props.engineer || props.engineer.length === 0) return false;
        return props.engineer.every(user => form.engineer.includes(user.id));
    },
    set(value) {
        if (value) {
            form.engineer = props.engineer.map(user => user.id);
        } else {
            form.engineer = [];
        }
    }
});

// Computed Property untuk Select All Designers
const isAllDesignersSelected = computed({
    get() {
        if (!props.designer || props.designer.length === 0) return false;
        return props.designer.every(user => form.designer.includes(user.id));
    },
    set(value) {
        if (value) {
            form.designer = props.designer.map(user => user.id);
        } else {
            form.designer = [];
        }
    }
});

const submitForm = () => {
    form.post(route('task.assignTask', props.task.id), {
        onSuccess: () => emit('close'),
    });
};

const cancel = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Assign Task</h3>        
        <form @submit.prevent="submitForm" class="flex flex-col">
            <div class="max-h-[60vh] overflow-y-auto px-1 space-y-5 custom-scrollbar">

                <div>
                    <InputLabel for="type" value="Project Manager" />
                    <SelectInput
                        id="type"
                        v-model="form.pm"
                        :options="pm"
                        label="name"
                        valueKey="id"
                        class="mt-1 block w-full"
                        placeholder="Search or select a project manager..."
                        :dark="true"
                    />
                    <InputError class="mt-2" :message="form.errors.pm" />
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <InputLabel value="Communicators" />
                        <span class="text-xs font-semibold text-primary-500">
                            {{ form.communicator.length }} selected
                        </span>
                    </div>
                    
                    <div class="p-3 bg-white/50 dark:bg-slate-800/50 border border-gray-300 dark:border-white/10 rounded-lg max-h-48 overflow-y-auto custom-scrollbar">
                        <template v-if="communicator && communicator.length">
                            <!-- Checkbox Select All -->
                            <div class="pb-2 mb-2 border-b border-gray-200 dark:border-slate-700/60">
                                <label class="flex items-center gap-2 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700/50 cursor-pointer text-sm font-semibold text-gray-800 dark:text-slate-100">
                                    <input 
                                        type="checkbox" 
                                        v-model="isAllCommunicatorsSelected"
                                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:border-slate-700 shrink-0"
                                    />
                                    <span class="select-none">All</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <label 
                                    v-for="user in communicator" 
                                    :key="'comm-' + user.id" 
                                    class="flex items-center gap-2 p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700/50 cursor-pointer transition text-sm text-gray-700 dark:text-slate-200 truncate"
                                >
                                    <input 
                                        type="checkbox" 
                                        :value="user.id" 
                                        v-model="form.communicator"
                                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:border-slate-700 shrink-0"
                                    />
                                    <span class="select-none truncate" :title="user.name">{{ user.name }}</span>
                                </label>
                            </div>
                        </template>
                        <p v-else class="text-xs italic text-gray-400 dark:text-slate-500 text-center py-1">
                            No communicators available.
                        </p>
                    </div>
                    <InputError class="mt-2" :message="form.errors.communicator" />
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <InputLabel value="Engineers" />
                        <span class="text-xs font-semibold text-primary-500">
                            {{ form.engineer.length }} selected
                        </span>
                    </div>
                    
                    <div class="p-3 bg-white/50 dark:bg-slate-800/50 border border-gray-300 dark:border-white/10 rounded-lg max-h-48 overflow-y-auto custom-scrollbar">
                        <template v-if="engineer && engineer.length">
                            <div class="pb-2 mb-2 border-b border-gray-200 dark:border-slate-700/60">
                                <label class="flex items-center gap-2 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700/50 cursor-pointer text-sm font-semibold text-gray-800 dark:text-slate-100">
                                    <input 
                                        type="checkbox" 
                                        v-model="isAllEngineersSelected"
                                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:border-slate-700 shrink-0"
                                    />
                                    <span class="select-none">All</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <label 
                                    v-for="user in engineer" 
                                    :key="'eng-' + user.id" 
                                    class="flex items-center gap-2 p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700/50 cursor-pointer transition text-sm text-gray-700 dark:text-slate-200 truncate"
                                >
                                    <input 
                                        type="checkbox" 
                                        :value="user.id" 
                                        v-model="form.engineer"
                                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:border-slate-700 shrink-0"
                                    />
                                    <span class="select-none truncate" :title="user.name">{{ user.name }}</span>
                                </label>
                            </div>
                        </template>
                        <p v-else class="text-xs italic text-gray-400 dark:text-slate-500 text-center py-1">
                            No engineers available.
                        </p>
                    </div>
                    <InputError class="mt-2" :message="form.errors.engineer" />
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <InputLabel value="Designers" />
                        <span class="text-xs font-semibold text-primary-500">
                            {{ form.designer.length }} selected
                        </span>
                    </div>
                    
                    <div class="p-3 bg-white/50 dark:bg-slate-800/50 border border-gray-300 dark:border-white/10 rounded-lg max-h-48 overflow-y-auto custom-scrollbar">
                        <template v-if="designer && designer.length">
                            <div class="pb-2 mb-2 border-b border-gray-200 dark:border-slate-700/60">
                                <label class="flex items-center gap-2 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700/50 cursor-pointer text-sm font-semibold text-gray-800 dark:text-slate-100">
                                    <input 
                                        type="checkbox" 
                                        v-model="isAllDesignersSelected"
                                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:border-slate-700 shrink-0"
                                    />
                                    <span class="select-none">All</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <label 
                                    v-for="user in designer" 
                                    :key="'des-' + user.id" 
                                    class="flex items-center gap-2 p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700/50 cursor-pointer transition text-sm text-gray-700 dark:text-slate-200 truncate"
                                >
                                    <input 
                                        type="checkbox" 
                                        :value="user.id" 
                                        v-model="form.designer"
                                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:border-slate-700 shrink-0"
                                    />
                                    <span class="select-none truncate" :title="user.name">{{ user.name }}</span>
                                </label>
                            </div>
                        </template>
                        <p v-else class="text-xs italic text-gray-400 dark:text-slate-500 text-center py-1">
                            No designers available.
                        </p>
                    </div>
                    <InputError class="mt-2" :message="form.errors.designer" />
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-slate-700 mt-4">
                <button
                    type="button"
                    @click="cancel"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-slate-700 rounded-md hover:bg-gray-300 dark:hover:bg-slate-600 disabled:opacity-50 transition-colors"
                >
                    Cancel
                </button>
                
                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="{ 'opacity-25 cursor-not-allowed': form.processing }"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 transition-all duration-200 shadow-lg shadow-primary-500/30"
                >
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}
</style>