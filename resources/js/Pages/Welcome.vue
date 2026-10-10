<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

defineProps({
  canLogin: Boolean,
  laravelVersion: String,
  phpVersion: String,
  status: String,
  canResetPassword: Boolean,
});

const isLoaded = ref(false);
const showPassword = ref(false);
const currentTheme = ref('system');
const isDropdownOpen = ref(false);

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password')
  })
}

// Logika Pengaturan Tema
const selectTheme = (mode) => {
  currentTheme.value = mode;
  localStorage.setItem('theme', mode);
  isDropdownOpen.value = false;

  const isSystemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

  if (mode === 'dark' || (mode === 'system' && isSystemDark)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
};

// Close dropdown saat klik di luar area
const closeDropdown = (e) => {
  if (!e.target.closest('#theme-dropdown-container')) {
    isDropdownOpen.value = false;
  }
};

onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'system';
  selectTheme(savedTheme);

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (currentTheme.value === 'system') {
      if (e.matches) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    }
  });

  window.addEventListener('click', closeDropdown);
  setTimeout(() => isLoaded.value = true, 100);
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdown);
});
</script>

<template>
  <Head title="Project & HR Management" />
  
  <div class="min-h-screen w-full flex flex-col items-center justify-center p-4 lg:p-8 relative overflow-hidden">

    <img src="/background/BGlight-TM.jpg" class="absolute inset-0 w-full h-full object-cover pointer-events-none dark:hidden" />
    <img src="/background/BG-TM.jpg" class="absolute inset-0 w-full h-full object-cover pointer-events-none hidden dark:block">

    <div id="theme-dropdown-container" class="absolute top-4 right-4 lg:top-6 lg:right-6 z-30">
      <div class="relative">
        <button 
          type="button"
          @click.stop="isDropdownOpen = !isDropdownOpen"
          class="flex items-center gap-1.5 p-2 rounded-md bg-white/70 dark:bg-slate-800/70 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 text-slate-700 dark:text-slate-200 shadow-md hover:bg-white dark:hover:bg-slate-800 transition-all"
          :title="`Theme: ${currentTheme}`"
        >
          <svg v-if="currentTheme === 'light'" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
          <svg v-else-if="currentTheme === 'dark'" class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
          <svg v-else class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>

          <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </button>

        <Transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform scale-95 opacity-0 -translate-y-1"
          enter-to-class="transform scale-100 opacity-100 translate-y-0"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="transform scale-100 opacity-100 translate-y-0"
          leave-to-class="transform scale-95 opacity-0 -translate-y-1"
        >
          <div 
            v-if="isDropdownOpen" 
            class="absolute right-0 mt-2 w-36 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden py-1 z-40"
          >
            <button 
              type="button" 
              @click="selectTheme('light')"
              class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/60"
              :class="currentTheme === 'light' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-50 dark:bg-slate-700/40' : 'text-slate-700 dark:text-slate-300'"
            >
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
              <span>Light</span>
            </button>

            <button 
              type="button" 
              @click="selectTheme('dark')"
              class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/60"
              :class="currentTheme === 'dark' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-50 dark:bg-slate-700/40' : 'text-slate-700 dark:text-slate-300'"
            >
              <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
              <span>Dark</span>
            </button>

            <button 
              type="button" 
              @click="selectTheme('system')"
              class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/60"
              :class="currentTheme === 'system' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-50 dark:bg-slate-700/40' : 'text-slate-700 dark:text-slate-300'"
            >
              <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              <span>System</span>
            </button>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Card Container -->
    <div 
      class="relative z-10 w-full max-w-6xl rounded-lg border border-white/40 dark:border-white/20 overflow-hidden grid grid-cols-1 lg:grid-cols-3 transition-all duration-700 mb-8 shadow-xl dark:shadow-white-md"
      :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
    >
      
      <!-- Left Hero Section -->
      <div class="relative lg:col-span-2 p-8 lg:p-16 flex flex-col justify-between items-start text-left min-h-[500px] overflow-hidden bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl">
        <div class="relative z-10 w-full flex flex-col h-full justify-between">
          <div>
            <img src="/icon_kndi.svg" alt="Icon" class="w-20 h-20 object-contain object-left">
          </div>

          <div>
            <h1 class="-ml-1 text-4xl md:text-6xl font-extrabold text-[#2876BC] tracking-tight leading-tight mb-4">
              Task Manager
            </h1>
            <p class="text-xl font-medium text-[#2876BC] mb-6">
                Kyodo News Digital Indonesia
            </p>
            <p class="max-w-md text-base text-black dark:text-white leading-relaxed mb-10">
                Kelola project, tugas, waktu kerja, skill karyawan, dan impor data secara efisien dalam satu platform terintegrasi.
            </p>
            <Link
              v-if="$page.props.attendance_enabled"
              href="/recognize"
              class="inline-block px-8 py-3 bg-[#D12025] hover:bg-[#b01b1f] text-white text-sm font-extrabold rounded-full transition-all shadow-lg shadow-black/20"
            >
              PRESENSI
            </Link>
          </div>
        </div>
      </div>

      <!-- Right Form Section -->
      <div class="lg:col-span-1 p-8 lg:p-12 bg-white dark:bg-[#132440] flex flex-col justify-center border-t lg:border-t-0 lg:border-l border-white/40 dark:border-white/20">
        <div class="w-full">
            <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white mb-8">AKUN</h2>
            
            <form @submit.prevent="submit" class="space-y-5">
              <div>
                <TextInput 
                  id="email" 
                  type="email" 
                  placeholder="Email"
                  class="mt-1 block w-full border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-blue-500 transition-colors duration-300" 
                  v-model="form.email" 
                  :disabled="form.processing"
                  required 
                  autofocus 
                />
                <InputError class="mt-2" :message="form.errors.email" />
              </div>

              <div>
                <div class="relative mt-1">
                  <TextInput 
                    id="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    placeholder="Password"
                    class="block w-full pr-10 border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-blue-500 transition-colors duration-300" 
                    v-model="form.password" 
                    :disabled="form.processing"
                    required 
                  />
                  <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                    <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                  </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
              </div>

              <label class="flex items-center gap-2 cursor-pointer select-none">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="text-sm text-slate-600 dark:text-slate-300">Remember me</span>
              </label>

              <button 
                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-white font-bold bg-[#2876BC] hover:bg-blue-700 transition-all shadow-md active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed" 
                :disabled="form.processing"
              >
                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ form.processing ? 'MEMPROSES...' : 'MASUK' }}</span>
              </button>
            </form>
        </div>
      </div>
    </div>

    <footer class="relative z-20 w-full text-sm text-dark dark:text-white text-center">
      <p>© {{ new Date().getFullYear() }} PT. KND Indonesia</p>
    </footer>
  </div>
</template>