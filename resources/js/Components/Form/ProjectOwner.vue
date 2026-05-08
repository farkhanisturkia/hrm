<script setup>
import { useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

const emit = defineEmits(['close']);

const props = defineProps({
    projectOwners: {}, // Disesuaikan dengan prop dari parent
    isEditMode: Boolean,
});

const form = useForm({
    name: props.projectOwners?.name || '',
    _method: props.isEditMode ? 'PUT' : undefined,
});

const submitForm = () => {
    const routeName = props.isEditMode ? 'projectOwner.update' : 'projectOwner.store';
    const routeParams = props.isEditMode ? props.projectOwners.id : undefined;

    form.post(route(routeName, routeParams), {
        onFinish: () => emit('close'),
    });
};

const cancel = () => {
    form.reset('name');
    emit('close');
};
</script>

<template>
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Project Owner' : 'Add New Project Owner' }}
        </h3>
        
        <form @submit.prevent="submitForm" class="space-y-4">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm dark:border-white/10 dark:text-white"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
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