<script setup>
defineProps({
  modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <button
    class="shrink-0 inline-flex items-center justify-center p-2 rounded-md dark:text-gray-300 hover:text-gray-500 hover:bg-white/10 dark:hover:bg-slate-800 focus:outline-none focus:bg-gray-10 focus:text-gray-500 dark:focus:text-white transition duration-150 ease-in-out"
    @click="emit('update:modelValue', !modelValue)"
  >
    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
      <path
        :class="{ 'hidden': modelValue, 'inline-flex': !modelValue }"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M4 6h16M4 12h16M4 18h16"
      />
      <path
        :class="{ 'hidden': !modelValue, 'inline-flex': modelValue }"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M6 18L18 6M6 6l12 12"
      />
    </svg>
  </button>
</template>