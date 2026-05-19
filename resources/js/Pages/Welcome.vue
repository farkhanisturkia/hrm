<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
  canLogin: Boolean,
  laravelVersion: String,
  phpVersion: String,
  status: String,
  canResetPassword: Boolean,
});

const isLoaded = ref(false);
const showPassword = ref(false);
const showDemoModal = ref(false);

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

onMounted(() => {
  setTimeout(() => isLoaded.value = true, 100);
});
</script>

<template>
  <Head title="Project & HR Management" />
  
  <div class="min-h-screen w-full flex flex-col items-center justify-center p-4 lg:p-8 relative overflow-hidden">
    
    <img src="/background/welcome-light.jpg" class="absolute inset-0 w-full h-full object-cover opacity-30 pointer-events-none dark:hidden" />
    <img src="/background/BG-TM.jpg" class="absolute inset-0 w-full h-full object-cover pointer-events-none hidden dark:block">

    <div 
      class="relative z-10 w-full max-w-6xl bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden grid grid-cols-1 lg:grid-cols-3 transition-all duration-700 mb-8 shadow-white-md"
      :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
    >
      
      <div class="relative lg:col-span-2 p-8 lg:p-16 flex flex-col justify-between items-start text-left min-h-[500px] overflow-hidden">
        <div class="relative z-10 w-full flex flex-col h-full justify-between">
          <div class="">
              <img src="/icon_kndi.svg" alt="Icon" class="w-32 h-32 object-contain object-left">
          </div>

          <div>
            <h1 class="text-4xl md:text-6xl font-extrabold text-[#2876BC] tracking-tight leading-tight mb-4">
              Task Manager
            </h1>
            <p class="text-xl font-medium text-[#2876BC] mb-6">
                Micro Systems Root
            </p>
            <p class="max-w-md text-base text-black dark:text-white leading-relaxed mb-10">
                Kelola project, tugas, waktu kerja, skill karyawan, dan impor data secara efisien dalam satu platform terintegrasi.
            </p>
            <div class="flex flex-row gap-4">
              <Link
                v-if="$page.props.attendance_enabled"
                href="/recognize"
                class="inline-block px-8 py-3 bg-[#D12025] hover:bg-[#b01b1f] text-white text-sm font-extrabold rounded-full transition-all shadow-lg shadow-black/20"
              >
                PRESENSI
              </Link>
              
              <button
                @click="showDemoModal = true"
                type="button"
                class="inline-block px-8 py-3 bg-white dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 border-2 border-[#D12025] text-black dark:text-white text-sm font-extrabold rounded-full transition-all shadow-lg shadow-black/20 cursor-pointer"
              >
                DEMO
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-1 p-8 lg:p-12 bg-white dark:bg-[#132440] flex flex-col justify-center border-t lg:border-t-0 lg:border-l border-slate-100 dark:border-slate-800">
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

    <!-- MODAL DEMO (Dikontrol dengan Vue State) -->
    <div 
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm transition-opacity duration-300"
      :class="{ 'opacity-100 pointer-events-auto': showDemoModal, 'opacity-0 pointer-events-none': !showDemoModal }"
      @click.self="showDemoModal = false"
    >
      <div 
        class="bg-zinc-900 border border-white/10 max-w-lg w-full mx-5 p-6 rounded-3xl shadow-2xl transform transition-transform duration-300"
        :class="{ 'scale-100': showDemoModal, 'scale-95': !showDemoModal }"
      >
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="w-8 h-8 bg-[#D12025] rounded-xl text-sm flex items-center justify-center text-white font-bold">i</span>
                Informasi Akses Demo
            </h3>
        </div>
        
        <div class="space-y-5 text-sm text-zinc-400">
            <p>Terima kasih telah tertarik dengan platform <strong class="text-white">TASK MANAGER</strong>. Anda dapat mencoba akses sistem ini menggunakan kredensial berikut untuk akun Human Resources:</p>
            
            <div class="bg-zinc-950 border border-white/5 p-4 rounded-2xl space-y-3 font-mono text-xs">
                <div>
                    <span class="text-[#D12025]">Email :</span> 
                    <span class="text-white select-all">admin@example.com</span>
                </div>
                <div>
                    <span class="text-[#D12025]">Password :</span> 
                    <span class="text-white select-all">password</span>
                </div>
            </div>

            <p>Bila ada kesulitan dalam pengoperasian dapat menghubungi kami <a href="https://msroot.my.id" target="_BLANK" class="text-[#2876BC] font-semibold underline transition-colors">disini</a></p>

            <div class="text-xs text-zinc-500 mt-2">
                * Harap gunakan kredensial ini hanya untuk keperluan demonstrasi atau pengujian sistem.
            </div>
        </div>
        
        <div class="mt-8 flex justify-end">
            <button 
              @click="showDemoModal = false" 
              class="px-5 py-3 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl font-medium transition-all cursor-pointer"
            >
                Tutup
            </button>
        </div>
      </div>
    </div>

    <footer class="relative z-20 w-full text-sm text-dark dark:text-white text-center">
      <p>© {{ new Date().getFullYear() }} MSRoot.ID</p>
    </footer>
  </div>
</template>