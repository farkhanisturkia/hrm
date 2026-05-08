<script setup>
import { useForm } from '@inertiajs/vue3';
import Close from '../Icon/Close.vue';
import Plus from '../Icon/Plus.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const emit = defineEmits(['close']);

const props = defineProps({
    comment: {},
});

const form = useForm({
    pr_links: [''],
    comment: ''
});

const submitForm = () => {
    const pr_links = form.pr_links.filter(value => value !== '');

    form.transform((data) => {
        data.pr_links = pr_links;
        return data;
    }).post(route('task.replyTask', props.comment.id), {
        onFinish: () => emit('close'),
    });
};

const addLink = () => {
    form.pr_links.push('');
};

const removeLink = (index) => {
    if (form.pr_links.length > 1) {
        form.pr_links.splice(index, 1);
    } else {
        form.pr_links = []
    }
};

const cancel = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reply Comment</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
            <div>
                <InputLabel value="Links" />
                <div v-for="(link, index) in form.pr_links" :key="index" class="mt-1 flex items-center gap-2">
                    <TextInput
                        :id="'pr_link_' + index"
                        type="text"
                        class="block w-full bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm dark:border-white/10 dark:text-white"
                        v-model="form.pr_links[index]"
                        :placeholder="'Link ' + (index + 1)"
                    />
                    <button
                        type="button"
                        @click="removeLink(index)"
                        class="p-2.5 text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 border border-gray-300 dark:border-white/10 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors bg-white/50 dark:bg-slate-800/50"
                        title="Remove"
                    >
                        <Close class="w-4 h-4" />
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.pr_links" />
                <button
                    type="button"
                    @click="addLink"
                    class="mt-2 px-3 py-1.5 text-sm font-bold text-white bg-green-600 rounded-md hover:bg-green-700 shadow-sm transition-colors flex items-center gap-1"
                >
                    <Plus class="w-3 h-3" /> Add Link
                </button>
            </div>

            <div>
                <InputLabel for="comment" value="Comment" />
                <textarea
                    id="comment"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-white/10 bg-white/50 dark:bg-slate-900/50 dark:text-white focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 shadow-sm backdrop-blur-sm"
                    v-model="form.comment"
                    autocomplete="comment"
                    rows="4"
                />
                <InputError class="mt-2" :message="form.errors.comment" />
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