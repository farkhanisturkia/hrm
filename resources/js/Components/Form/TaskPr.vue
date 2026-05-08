<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Close from '../Icon/Close.vue';
import Plus from '../Icon/Plus.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';

const emit = defineEmits(['close']);

const props = defineProps({
    task: {},
    pg: {},
});

const availableReviewers = computed(() => {
    const assignProgrammerId = props.task?.programmer || [];

    if (Array.isArray(props.pg)) {
        return props.pg.filter(user => !assignProgrammerId.includes(user.id));
    }
    return [];
});

const initialReviewers = Array.isArray(props.task?.reviewer)
    ? props.task.reviewer
    : [''];

const form = useForm({
    reviewer: initialReviewers,
    _method: 'PUT',
});

const submitForm = () => {
    form.reviewer = form.reviewer.filter(value => value !== '');

    form.post(route('task.prTask', props.task.id), {
        onFinish: () => emit('close'),
    });
};

const addReviewer = () => {
    form.reviewer.push('');
};

const removeReviewer = (index) => {
    if (form.reviewer.length > 1) {
        form.reviewer.splice(index, 1);
    } else {
        form.reviewer = []
    }
};

const cancel = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Assign Task's Reviewer</h3>
        <form @submit.prevent="submitForm" class="flex flex-col">
            
            <div class="max-h-[60vh] overflow-y-auto px-1 space-y-4 custom-scrollbar">
                <div>
                    <InputLabel value="Reviewers" />
                    <div v-for="(link, index) in form.reviewer" :key="form.reviewer[index]" class="mt-1 flex items-center gap-2">
                        <SelectInput
                            :id="'reviewer_' + index"
                            v-model="form.reviewer[index]"
                            :options="availableReviewers"
                            :array="form.reviewer"
                            label="name"
                            valueKey="id"
                            class="mt-1 block w-full"
                            placeholder="Search or select a Reviewer..."
                            :dark="true"
                        />
                        <button
                            type="button"
                            @click="removeReviewer(index)"
                            class="p-2.5 text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 border border-gray-300 dark:border-white/10 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors bg-white/50 dark:bg-slate-800/50"
                            title="Remove"
                        >
                            <Close class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.reviewer" />
                    
                    <button
                        type="button"
                        @click="addReviewer"
                        class="mt-2 px-3 py-1.5 text-sm font-bold text-white bg-green-600 rounded-md hover:bg-green-700 shadow-sm transition-colors flex items-center gap-1"
                    >
                        <Plus class="w-3 h-3" /> Add Reviewer
                    </button>
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