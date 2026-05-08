<script setup>
import { useForm } from '@inertiajs/vue3';
import Close from '../Icon/Close.vue';
import Plus from '../Icon/Plus.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';

const emit = defineEmits(['close']);

const props = defineProps({
    task: {},
    pl: {},
    co: {},
    pg: {},
    ds: {},
});

const initialComunicators = Array.isArray(props.task?.communicator)
    ? props.task.communicator
    : [''];

const initialProgrammers = Array.isArray(props.task?.programmer)
    ? props.task.programmer
    : [''];

const initialDesigners = Array.isArray(props.task?.designer)
    ? props.task.designer
    : [''];

const form = useForm({
    pl: props.task?.pl || '',
    communicator: initialComunicators,
    programmer: initialProgrammers,
    designer: initialDesigners,
    _method: 'PUT',
});

const submitForm = () => {
    form.communicator = form.communicator.filter(value => value !== '');
    form.programmer = form.programmer.filter(value => value !== '');
    form.designer = form.designer.filter(value => value !== '');

    form.post(route('task.assignTask', props.task.id), {
        onFinish: () => emit('close'),
    });
};

const addCommunicator = () => {
    form.communicator.push('');
};

const removeCommunicator = (index) => {
    if (form.communicator.length > 1) {
        form.communicator.splice(index, 1);
    } else {
        form.communicator = []
    }
};

const addProgrammer = () => {
    form.programmer.push('');
};

const removeProgrammer = (index) => {
    if (form.programmer.length > 1) {
        form.programmer.splice(index, 1);
    } else {
        form.programmer = []
    }
};

const addDesigner = () => {
    form.designer.push('');
};

const removeDesigner = (index) => {
    if (form.designer.length > 1) {
        form.designer.splice(index, 1);
    } else {
        form.designer = []
    }
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
            
            <div class="max-h-[60vh] overflow-y-auto px-1 space-y-4 custom-scrollbar">
                <div>
                    <InputLabel for="type" value="Project Leader" />
                    <SelectInput
                        id="type"
                        v-model="form.pl"
                        :options="pl"
                        label="name"
                        valueKey="id"
                        class="mt-1 block w-full"
                        placeholder="Search or select a project leader..."
                        :dark="true"
                    />
                    <InputError class="mt-2" :message="form.errors.pl" />
                </div>

                <div>
                    <InputLabel value="Communicators" />
                    <div v-for="(link, index) in form.communicator" :key="'co-' + index" class="mt-1 flex items-center gap-2">
                        <SelectInput
                            :id="'co_link_' + index"
                            v-model="form.communicator[index]"
                            :options="co"
                            :array="form.communicator"
                            label="name"
                            valueKey="id"
                            class="mt-1 block w-full"
                            placeholder="Search or select a Communicator..."
                            :dark="true"
                        />
                        <button
                            type="button"
                            @click="removeCommunicator(index)"
                            class="p-2.5 text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 border border-gray-300 dark:border-white/10 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors bg-white/50 dark:bg-slate-800/50"
                            title="Remove"
                        >
                            <Close class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.communicator" />
                    <button
                        type="button"
                        @click="addCommunicator"
                        class="mt-2 px-3 py-1.5 text-sm font-bold text-white bg-green-600 rounded-md hover:bg-green-700 shadow-sm transition-colors flex items-center gap-1"
                    >
                        <Plus class="w-3 h-3" /> Add Communicator
                    </button>
                </div>

                <div>
                    <InputLabel value="Programmers" />
                    <div v-for="(link, index) in form.programmer" :key="'pg-' + index" class="mt-1 flex items-center gap-2">
                        <SelectInput
                            :id="'pg_link_' + index"
                            v-model="form.programmer[index]"
                            :options="pg"
                            :array="form.programmer"
                            label="name"
                            valueKey="id"
                            class="mt-1 block w-full"
                            placeholder="Search or select a Programmer..."
                            :dark="true"
                        />
                        <button
                            type="button"
                            @click="removeProgrammer(index)"
                            class="p-2.5 text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 border border-gray-300 dark:border-white/10 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors bg-white/50 dark:bg-slate-800/50"
                            title="Remove"
                        >
                            <Close class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.programmer" />
                    <button
                        type="button"
                        @click="addProgrammer"
                        class="mt-2 px-3 py-1.5 text-sm font-bold text-white bg-green-600 rounded-md hover:bg-green-700 shadow-sm transition-colors flex items-center gap-1"
                    >
                        <Plus class="w-3 h-3" /> Add Programmer
                    </button>
                </div>

                <div>
                    <InputLabel value="Designer" />
                    <div v-for="(link, index) in form.designer" :key="'ds-' + index" class="mt-1 flex items-center gap-2">
                        <SelectInput
                            :id="'ds_link_' + index"
                            v-model="form.designer[index]"
                            :options="ds"
                            :array="form.designer"
                            label="name"
                            valueKey="id"
                            class="mt-1 block w-full"
                            placeholder="Search or select a Designer..."
                            :dark="true"
                        />
                        <button
                            type="button"
                            @click="removeDesigner(index)"
                            class="p-2.5 text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 border border-gray-300 dark:border-white/10 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors bg-white/50 dark:bg-slate-800/50"
                            title="Remove"
                        >
                            <Close class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.designer" />
                    <button
                        type="button"
                        @click="addDesigner"
                        class="mt-2 px-3 py-1.5 text-sm font-bold text-white bg-green-600 rounded-md hover:bg-green-700 shadow-sm transition-colors flex items-center gap-1"
                    >
                        <Plus class="w-3 h-3" /> Add Designer
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