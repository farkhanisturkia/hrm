<script setup>
import { ref, computed, onMounted } from 'vue';

const model = defineModel({
    type: [String, Number],
    required: true,
});

const props = defineProps({
    options: {
        type: Array,
        required: true,
        default: () => [],
    },
    label: {
        type: String,
        default: 'name',
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    placeholder: {
        type: String,
        default: 'Search or select a project owner...',
    },
    array: {
        type: Array,
        default: () => [],
    },
    dark: {
        type: Boolean,
        default: false
    },
    required: {
        type: Boolean,
        default: false
    }
});

const search = ref('');
const isOpen = ref(false);
const input = ref(null);

const filteredOptions = computed(() => {
    const query = search.value.toLowerCase().trim();
    if (!query) return props.options;
    return props.options.filter(option =>
        option[props.label].toLowerCase().includes(query)
    );
});

const selectOption = (option) => {
    const selectedValue = option[props.valueKey];
    if (props.array.includes(selectedValue)) {
        alert(`The option "${option[props.label]}" is already selected.`);
        return;
    }
    model.value = option[props.valueKey];
    search.value = option[props.label];
    isOpen.value = false;
};

const handleFocus = () => {
    search.value = '';
    isOpen.value = true;
};

const handleBlur = () => {
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};

onMounted(() => {
    if (model.value) {
        const selectedOption = props.options.find(o => o[props.valueKey] === model.value);
        
        if (selectedOption) {
            search.value = selectedOption[props.label];
        }
    }
    
    if (input.value && input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="relative">
        <!-- Input -->
        <input
            ref="input"
            type="text"
            :class="{' dark:border-gray-500 dark:text-gray-100': dark}"
            class="rounded-md border-gray-500 shadow-sm w-full text-gray-800  dark:text-white dark:bg-slate-900"
            v-model="search"
            @focus="handleFocus"
            @blur="handleBlur"
            :placeholder="placeholder"
            autocomplete="off"
            :required="required"
        />

        <!-- Dropdown -->
        <div
            v-if="isOpen && filteredOptions.length"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
        >
            <div
                v-for="option in filteredOptions"
                :key="option[valueKey]"
                @click="selectOption(option)"
                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-slate-500/30 cursor-pointer"
            >
                {{ option[label] }}
            </div>
        </div>
        <div
            v-if="isOpen && !filteredOptions.length"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg p-4 text-sm text-gray-500 dark:text-gray-400"
        >
            No results found
        </div>
    </div>
</template>