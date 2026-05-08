<script setup>
import { useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import moment from 'moment';

const emit = defineEmits(['close']);

const props = defineProps({
    tasks: {}
});

const form = useForm({
    date: moment().format('YYYY-MM-DD'),
    task_id: '',
    time_used: '',
    description: '',
});

const validateTime = () => {
    if (form.time_used < 0) {
        form.time_used = 1;
    }
};

const submitForm = () => {
    form.post(route('logtime.store'), {
        onFinish: () => {
            emit('close');
        },
    });
};

const cancel = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New Logtime</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
            <div>
                <InputLabel for="date" value="Date" />
                <input
                    type="date"
                    id="date"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white/50 dark:bg-slate-900/50 dark:text-white focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 shadow-sm backdrop-blur-sm"
                    v-model="form.date"
                    required
                    autocomplete="date"
                />
                <InputError class="mt-2" :message="form.errors.date" />
            </div>

            <div>
                <InputLabel for="type" value="Task" />
                <SelectInput
                    id="type"
                    v-model="form.task_id"
                    :options="tasks"
                    label="issue"
                    valueKey="id"
                    class="mt-1 block w-full"
                    placeholder="Search or select a task..."
                    :required="true"
                    :dark="true"
                />
                <InputError class="mt-2" :message="form.errors.task_id" />
            </div>

            <div>
                <InputLabel for="time_used" value="Time used" />
                <TextInput
                    id="time_used"
                    type="number"
                    class="mt-1 block w-full bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm dark:border-white/10 dark:text-white"
                    v-model="form.time_used"
                    required
                    autofocus
                    autocomplete="time_used"
                    min="0"
                    step="0.1"
                    @change="validateTime" 
                />
                <InputError class="mt-2" :message="form.errors.time_used" />
            </div>

            <div>
                <InputLabel for="description" value="Description" />
                <TextInput
                    id="description"
                    type="text"
                    class="mt-1 block w-full bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm dark:border-white/10 dark:text-white"
                    v-model="form.description"
                />
            </div>

            <div class="flex justify-end gap-4 pt-2">
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