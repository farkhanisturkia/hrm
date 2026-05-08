<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

// Daftar 12 Avatar
const availableAvatars = [
    '/avatars/1.png',
    '/avatars/2.png',
    '/avatars/3.png',
    '/avatars/4.png',
    '/avatars/5.png',
    '/avatars/6.png',
    '/avatars/7.png', 
    '/avatars/8.png',
    '/avatars/9.png',
    '/avatars/10.png',
    '/avatars/11.png',
    '/avatars/12.png',
];

const form = useForm({
    name: user.name,
    email: user.email,
    // MODIFIKASI: Jika user.avatar null/undefined, set ke path avatar 1 secara default
    avatar: user.avatar || '/avatars/1.png',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div class="flex flex-col items-center gap-6 mb-8">
                <div class="flex flex-col items-center">
                    <InputLabel value="Selected Profile Photo" class="mb-2" />
                    <div class="relative group">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-full blur opacity-50 group-hover:opacity-100 transition duration-200"></div>
                        <img 
                            :src="form.avatar" 
                            alt="Current Avatar" 
                            class="relative w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-6 gap-3 sm:gap-4">
                    <button 
                        v-for="(avatar, index) in availableAvatars" 
                        :key="index"
                        type="button"
                        @click="form.avatar = avatar"
                        class="relative rounded-full transition-all duration-200 focus:outline-none"
                        :class="[
                            form.avatar === avatar 
                                ? 'ring-2 ring-offset-2 ring-indigo-500 scale-110' 
                                : 'opacity-70 hover:opacity-100 hover:scale-105'
                        ]"
                    >
                        <img 
                            :src="avatar" 
                            alt="Avatar Option" 
                            class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover shadow-sm"
                        />
                    </button>
                </div>
                
                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>