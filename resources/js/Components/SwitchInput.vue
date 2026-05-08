<template>
  <label class="relative inline-flex items-center cursor-pointer">
    <input
      type="checkbox"
      v-model="isChecked"
      class="sr-only peer"
    />
    <div
      class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-colors duration-300"
    ></div>
    <span
      class="absolute w-4 h-4 bg-white rounded-full left-1 top-1 peer-checked:translate-x-5 transition-transform duration-300"
    ></span>
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