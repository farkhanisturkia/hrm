<template>
  <label class="flex items-center justify-center cursor-pointer tooltip-trigger">
    <div class="relative">
        <input type="checkbox" class="sr-only" v-model="isChecked">
        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-colors duration-300" :class="isChecked ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-slate-600'"></div>
        <div class="absolute w-4 h-4 bg-white rounded-full left-1 top-1 peer-checked:translate-x-5 transition-transform duration-300" :class="{'transform translate-x-5': isChecked}"></div>
    </div>
  </label>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [Boolean, Number],
  },
});

const emit = defineEmits(['update:modelValue']);

const isChecked = computed({
  get() {
    return !! props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value ? 1 : 0);
  },
});
</script>