<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

const emit = defineEmits(['close']);
const page = usePage();

const queryParams = computed(() => {
    const url = new URL(page.url, window.location.origin);
    return Object.fromEntries(url.searchParams);
});

const props = defineProps({
    project: Object,
    projectOwners: Array,
    projectOwnerId: [String, Number],
    isEditMode: Boolean,
});

const form = useForm({
    name: props.project?.name || '',
    project_owner_id: props.project?.project_owner_id || Number(props.projectOwnerId) || '',
});

watch(
    () => props.project,
    (newVal) => {
        form.name = newVal?.name || '';
        form.project_owner_id = newVal?.project_owner_id || Number(props.projectOwnerId) || '';
        form.clearErrors();
    }
);

const submitForm = () => {
    const params = {
        project_owner_id: queryParams.value.project_owner_id || queryParams.value.projectOwners_id
    };

    if (props.isEditMode) {
        form.put(route('project.update', { id: props.project.id, ...params }), {
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    } else {
        form.post(route('project.store', params), {
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
};

const cancel = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Project' : 'Add New Project' }}
        </h3>
        
        <form @submit.prevent="submitForm" class="space-y-4">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm dark:border-white/10 dark:text-white"
                    v-model="form.name"
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="project_owner_id" value="Project owner" />
                <SelectInput
                    id="project_owner_id"
                    v-model="form.project_owner_id"
                    :options="projectOwners"
                    label="name"
                    valueKey="id"
                    class="mt-1 block w-full"
                    placeholder="Search or select a project owner..."
                    :dark="true"
                />
                <InputError class="mt-2" :message="form.errors.project_owner_id" />
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