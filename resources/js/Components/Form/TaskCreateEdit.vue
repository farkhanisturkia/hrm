<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue'; 
import Close from '../Icon/Close.vue';
import Plus from '../Icon/Plus.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import moment from 'moment';

const emit = defineEmits(['close']);

const props = defineProps({
    task: {},
    projects: {},
    users: {}, 
    projectId: '',
    isEditMode: Boolean,
});

const getNameUser = (id) => {
    return id ? props.users?.find(u => u.id === id)?.name : '-';
};

const formatDate = (date) => {
    if (!date) return '-';
    return moment(date).format('DD MMMM YYYY');
};

const displayedProjectOwnerName = computed(() => {
    const selectedProject = props.projects?.find(p => p.id == form.project_id);
    
    return selectedProject?.project_owner?.name || selectedProject?.projectOwner?.name || '-';
});

const initialLinks = props.isEditMode && Array.isArray(props.task?.related_links)
    ? props.task.related_links
    : [''];

const form = useForm({
    project_id: props.task?.project_id || Number(props.projectId) || '',
    issue: props.task?.issue || '',
    ticket_link: props.task?.ticket_link || '',
    related_links: initialLinks,
    description: props.task?.description || '',
    start_date: props.task?.start_date ? moment(props.task?.start_date).format('YYYY-MM-DD') : undefined,
    due_date: props.task?.due_date ? moment(props.task?.due_date).format('YYYY-MM-DD') : undefined,
    _method: props.isEditMode ? 'PUT' : undefined,
});

const submitForm = () => {
    form.related_links = form.related_links.filter(link => link.trim() !== '');
    const routeName = props.isEditMode ? 'task.update' : 'task.store';
    const routeParams = props.isEditMode ? props.task.id : undefined;

    form.post(route(routeName, routeParams), {
        onSuccess: () => emit('close'),
    });
};

const addRelatedLink = () => {
    form.related_links.push('');
};

const removeRelatedLink = (index) => {
    if (form.related_links.length > 1) {
        form.related_links.splice(index, 1);
    } else {
        form.related_links = [''];
    }
};

const cancel = () => {
    form.reset();
    emit('close');
};

const types = [
    { id: 'low', name: 'low' },
    { id: 'normal', name: 'normal' },
    { id: 'high', name: 'high' },
];
</script>

<template>
    <div class="w-full">
        <form @submit.prevent="submitForm">
            <div class="mb-8 pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    {{ isEditMode ? 'Edit Task' : 'Add New Task' }}
                </h3>
                <button 
                    type="button" 
                    @click="cancel" 
                    :disabled="form.processing"
                    class="text-gray-400 hover:text-gray-600 transition-colors disabled:opacity-50"
                >
                    <Close class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-12">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 tracking-tight">Task Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <InputLabel value="ProjectOwner" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                                <p class="text-gray-400 dark:text-gray-500 font-medium py-1 italic select-none">
                                    {{ displayedProjectOwnerName }}
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <InputLabel value="Project" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                                <SelectInput v-model="form.project_id" :options="projects" label="name" valueKey="id" class="mt-1 block w-full bg-slate-50/50 dark:bg-slate-900/50 border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium" />
                                <InputError :message="form.errors.project_id" />
                            </div>
                            <div class="mb-4">
                                <InputLabel value="Issue" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                                <TextInput v-model="form.issue" class="mt-1 block w-full text-black dark:text-white bg-white dark:bg-slate-900/50  rounded-md text-sm font-medium" />
                                <InputError :message="form.errors.issue" />
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <InputLabel value="Start Date" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                                <input type="date" v-model="form.start_date" class="mt-1 block w-full bg-white dark:bg-slate-900/50 rounded-md text-sm font-medium dark:text-white" />
                                <InputError :message="form.errors.start_date" />
                            </div>
                            <div class="mb-4">
                                <InputLabel value="Due Date" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                                <input type="date" v-model="form.due_date" class="mt-1 block w-full bg-white dark:bg-slate-900/50 rounded-md text-sm font-medium dark:text-white" />
                                <InputError :message="form.errors.due_date" />
                            </div>
                            <div v-if="isEditMode" class="mb-4 opacity-60">
                                <InputLabel value="End Date" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                                <p class="text-gray-500 py-1 text-sm">{{ formatDate(task.end_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 tracking-tight">Details</h3>
                    <div class="space-y-6">
                        <div class="mb-4">
                            <InputLabel value="Description" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                            <textarea v-model="form.description" rows="3" class="mt-1 block w-full bg-white dark:bg-slate-900/50  dark:border-gray-700 rounded-md text-sm font-medium dark:text-white resize-none shadow-inner"></textarea>
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="mb-4">
                            <InputLabel value="Ticket Link" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                            <TextInput v-model="form.ticket_link" class="mt-1 block w-full bg-white dark:bg-slate-900/50 text-primary-400 dark:border-gray-700 rounded-md text-sm font-medium underline" />
                            <InputError :message="form.errors.ticket_link" />
                        </div>
                        <div class="mb-4">
                            <InputLabel value="Related Links" class="text-sm font-medium text-gray-600 dark:text-gray-400" />
                            <div class="space-y-3 mt-2">
                                <div v-for="(link, index) in form.related_links" :key="index" class="flex items-center group">
                                    <TextInput v-model="form.related_links[index]" class="block w-full bg-white dark:bg-slate-900/50 border-gray-700 rounded-md text-sm text-primary-400 underline" />
                                    <button type="button" @click="removeRelatedLink(index)" class="ml-3 text-gray-400 hover:text-red-500 transition-colors">
                                        <Close class="w-4 h-4" />
                                    </button>
                                </div>
                                <button type="button" @click="addRelatedLink" class="inline-flex items-center text-sm font-bold text-primary-600 dark:text-primary-400 hover:underline mt-2 uppercase tracking-widest transition-all">
                                    + Add Related Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-4">
                <button 
                    type="button" 
                    @click="cancel" 
                    :disabled="form.processing"
                    class="px-6 py-2 text-sm font-bold text-gray-500 uppercase tracking-widest hover:text-gray-700 transition-colors disabled:opacity-50"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    class="px-10 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded shadow-lg transition-all uppercase tracking-widest active:scale-95"
                >
                    <span v-if="form.processing">
                        {{ isEditMode ? 'Updating...' : 'Creating...' }}
                    </span>
                    <span v-else>
                        {{ isEditMode ? 'Update Task' : 'Create Task' }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>