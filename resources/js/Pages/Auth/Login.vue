<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({ canResetPassword: Boolean, status: String });

const isLoaded = ref(false);
const showPassword = ref(false); // State untuk icon mata

onMounted(() => {
  setTimeout(() => isLoaded.value = true, 100);
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout :isLoaded="isLoaded">
    <Head title="Log in" />

    <form @submit.prevent="submit" class="space-y-6">

      <div
        class="transition-all duration-700 ease-out"
        :class="{ 'scale-100 opacity-100': isLoaded, 'scale-95 opacity-0': !isLoaded }"
        style="transition-delay: 300ms;"
      >
        <InputLabel for="email" value="Email" />
        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full transition-colors duration-300"
          :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.email }"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div
        class="transition-all duration-700 ease-out"
        :class="{ 'scale-100 opacity-100': isLoaded, 'scale-95 opacity-0': !isLoaded }"
        style="transition-delay: 500ms;"
      >
        <InputLabel for="password" value="Password" />
        <div class="relative mt-1">
          <TextInput
            id="password"
            :type="showPassword ? 'text' : 'password'"
            class="block w-full pr-10 transition-colors duration-300"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.password || form.errors.email }"
            v-model="form.password"
            required
            autocomplete="current-password"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none"
          >
            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </button>
        </div>
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div
        class="transition-all duration-600 ease-out"
        :class="{ 'scale-100 opacity-100': isLoaded, 'scale-90 opacity-0': !isLoaded }"
        style="transition-delay: 700ms;"
      >
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-100">Remember me</span>
        </label>
      </div>

      <div
        class="flex flex-col sm:flex-row items-center justify-end gap-4 transition-all duration-700 ease-out"
        :class="{ 'scale-100 opacity-100': isLoaded, 'scale-95 opacity-0': !isLoaded }"
        style="transition-delay: 900ms;"
      >
        <PrimaryButton
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          class="w-full justify-center sm:w-auto"
        >
          Log in
        </PrimaryButton>
      </div>

    </form>
  </GuestLayout>
</template>