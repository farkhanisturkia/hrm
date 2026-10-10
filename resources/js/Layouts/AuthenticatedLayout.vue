<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'; 
import { Head, router, usePage, useForm, Link } from '@inertiajs/vue3';
import Menus from "@/Components/Menus.vue";
import Hamburger from '@/Components/Icon/Hamburger.vue';
import Logout from '@/Components/Icon/Logout.vue';
import Pen from '@/Components/Icon/Pen.vue';
import Toast from '@/Components/Toast.vue';
import Modal from '@/Components/Modal.vue'; 
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';

// Props
defineProps({
    title: String,
});

// State & Hooks
const page = usePage();
const user = computed(() => page.props.auth?.user);

// --- Logic Notification, Audio & Polling ---
const showNotifications = ref(false);
const notifications = computed(() => page.props.notifications || []); 

const unreadCount = computed(() => {
    if (page.props.unreadNotificationCount !== undefined) {
        return page.props.unreadNotificationCount;
    }
    return notifications.value.filter(n => !n.read_at).length;
});

const previousUnreadCount = ref(unreadCount.value);

const toggleNotifications = () => {
    if (!showNotifications.value) {
        showProfilePanel.value = false;
    }
    showNotifications.value = !showNotifications.value;
};

// Audio Notifikasi
const playNotificationSound = () => {
    try {
        const audio = new Audio('/backsounds/notification.mp3');
        audio.play().catch(e => console.log('Autoplay audio diblokir atau file belum ada:', e));
    } catch (e) {
        console.error('Audio play error:', e);
    }
};

watch(unreadCount, (newVal, oldVal) => {
    if (newVal > oldVal) {
        playNotificationSound();
    }
});

// Polling interval
let pollInterval = null;

// Logic Switcher Theme
const currentTheme = ref('system');
const isDropdownOpen = ref(false);

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

const closeThemeDropdown = (e) => {
    if (!e.target.closest('#theme-dropdown-container')) {
        isDropdownOpen.value = false;
    }
};

let mediaQueryListener = null;
let mediaQueryList = null;

onMounted(() => {
    const savedTheme = localStorage.getItem('theme') || 'system';
    selectTheme(savedTheme);

    mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQueryListener = (e) => {
        if (currentTheme.value === 'system') {
            if (e.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    };
    mediaQueryList.addEventListener('change', mediaQueryListener);
    window.addEventListener('click', closeThemeDropdown);

    pollInterval = setInterval(() => {
        router.reload({
            only: ['notifications', 'unreadNotificationCount'],
            preserveScroll: true,
            preserveState: true,
            onBefore: () => {
                page.props.flash = {};
            },
            onSuccess: (pageProps) => {
                const currentUnread = pageProps.props.unreadNotificationCount ?? 
                    (pageProps.props.notifications ? pageProps.props.notifications.filter(n => !n.read_at).length : 0);

                if (currentUnread > previousUnreadCount.value) {
                    playNotificationSound();
                }

                previousUnreadCount.value = currentUnread;
            }
        });
    }, 10000);
});

onUnmounted(() => {
    document.body.style.overflow = '';
    if (pollInterval) {
        clearInterval(pollInterval);
    }
    removeRouterListener();

    window.removeEventListener('click', closeThemeDropdown);
    if (mediaQueryList && mediaQueryListener) {
        mediaQueryList.removeEventListener('change', mediaQueryListener);
    }
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    } catch (e) {
        return dateString;
    }
};

const markAsRead = (notification) => {
    const targetUrl = notification.data?.url || notification.url || '#';

    showNotifications.value = false;

    const navigateToTarget = () => {
        if (targetUrl !== '#') {
            const cleanPath = targetUrl.replace(/^https?:\/\/[^\/]+/, '');
            router.visit(cleanPath);
        }
    };

    if (!notification.read_at) {
        router.post(route('notifications.markAsRead', notification.id), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => navigateToTarget(),
            onError: () => navigateToTarget()
        });
    } else {
        navigateToTarget();
    }
};

const markAllAsRead = () => {
    router.post(route('notifications.markAllAsRead'), {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

// --- Logic Sidebar Kanan (Profile) ---
const showProfilePanel = ref(false);

const toggleProfilePanel = () => {
    if (!showProfilePanel.value) {
        showNotifications.value = false;
    }
    showProfilePanel.value = !showProfilePanel.value;
};

// Logic Edit Nama & Avatar
const isEditingName = ref(false);
const nameForm = useForm({ name: '', email: '', avatar: '' });

const startEditingName = () => {
    nameForm.name = user.value?.name;
    nameForm.email = user.value?.email;
    nameForm.avatar = user.value?.avatar || '/avatars/1.png';
    isEditingName.value = true;
};

const saveName = () => {
    nameForm.patch(route('profile.update'), {
        preserveScroll: true, 
        preserveState: true, 
        onSuccess: () => { isEditingName.value = false; }
    });
};

const cancelEditingName = () => {
    isEditingName.value = false;
    nameForm.reset();
};

const showAvatarModal = ref(false);
const avatarForm = useForm({ name: '', email: '', avatar: '' });

const avatarAssets = [
    '/avatars/1.png', '/avatars/2.png', '/avatars/3.png', '/avatars/4.png',
    '/avatars/5.png', '/avatars/6.png', '/avatars/7.png', '/avatars/8.png',
    '/avatars/9.png', '/avatars/10.png', '/avatars/11.png', '/avatars/12.png',
];

const selectedAvatarTemp = ref('');

const openAvatarModal = () => {
    selectedAvatarTemp.value = user.value?.avatar || '/avatars/1.png';
    showAvatarModal.value = true;
};
const selectAvatar = (assetPath) => { selectedAvatarTemp.value = assetPath; };
const saveAvatar = () => {
    avatarForm.name = user.value?.name;
    avatarForm.email = user.value?.email;
    avatarForm.avatar = selectedAvatarTemp.value;
    avatarForm.patch(route('profile.update'), {
        preserveScroll: true, 
        preserveState: true,
        onSuccess: () => { showAvatarModal.value = false; }
    });
};

// --- Logic Ganti Password ---
const showPasswordModal = ref(false);
const openPasswordModal = () => { showPasswordModal.value = true; };
const closePasswordModal = () => { showPasswordModal.value = false; };

// --- Logic Pilih Skill ---
const showSkillModal = ref(false);
const selectedSkills = ref([]);
const skillForm = useForm({ displayed_skills: [] });

const displayedSkills = computed(() => {
    if (!user.value?.skills || user.value.skills.length === 0) return [];
    const selected = user.value.skills.filter(s => s.is_displayed);
    if (selected.length === 0) {
        return user.value.skills.slice(0, 5);
    }
    return selected.slice(0, 5);
});

const openSkillModal = () => {
    if (!user.value?.skills) return;
    const currentSelected = user.value.skills.filter(s => s.is_displayed).map(s => s.id);
    if (currentSelected.length === 0) {
        selectedSkills.value = user.value.skills.slice(0, 5).map(s => s.id);
    } else {
        selectedSkills.value = currentSelected;
    }
    showSkillModal.value = true;
};

const saveSkills = () => {
    skillForm.displayed_skills = selectedSkills.value;
    skillForm.patch(route('profile.skills.update'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => { showSkillModal.value = false; }
    });
};

// --- Logic Sidebar Kiri (Navigation) ---
const getInitialSidebarState = () => {
    if (typeof window !== 'undefined') {
        const savedState = localStorage.getItem('sidebarOpen');
        if (savedState !== null) return savedState === 'true';
        return window.innerWidth >= 768;
    }
    return true;
};

const removeRouterListener = router.on('start', () => {
    if (typeof window !== 'undefined' && window.innerWidth < 768) {
        openMenus.value = false;
        showProfilePanel.value = false;
        showNotifications.value = false;
    }
});

const openMenus = ref(getInitialSidebarState());
watch(openMenus, (newValue) => { 
    if (typeof window !== 'undefined' && window.innerWidth >= 768) {
        localStorage.setItem('sidebarOpen', newValue); 
    }
});

watch(openMenus, (isOpen) => { if (isOpen) showProfilePanel.value = false; });

watch(showProfilePanel, (isOpen) => {
    if (isOpen) {
        openMenus.value = false;
        document.body.style.overflow = 'hidden';
    } else {
        if (window.innerWidth >= 1024) openMenus.value = true;
        document.body.style.overflow = '';
    }
});

const logout = () => { router.post(route('logout')); };
const getInitials = (name) => {
    if (!name) return 'U';
    const names = name.split(' ');
    let initials = names[0].substring(0, 1).toUpperCase();
    if (names.length > 1) initials += names[names.length - 1].substring(0, 1).toUpperCase();
    return initials;
};
</script>

<template>
    <div class="min-h-screen bg-[url('/background/BGlight-TM.jpg')] bg-cover bg-center bg-fixed bg-no-repeat dark:bg-[url('/background/BG-TM.jpg')] dark:bg-cover dark:bg-center dark:bg-fixed dark:bg-no-repeat font-sans text-slate-600 dark:text-slate-300 relative selection:bg-primary-500 selection:text-white transition-colors duration-500">
        <Head :title="title" />

        <!-- Mobile Nav -->
        <nav class="md:hidden fixed top-0 left-0 w-full bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-white/10 z-50 shadow-sm">
            <div class="flex justify-between items-center px-4 h-16">
                <div class="flex items-center gap-3">
                    <button @click="openMenus = !openMenus" class="p-2 text-gray-600 dark:text-gray-300 rounded-none hover:bg-white/20 focus:outline-none transition">
                        <Hamburger :show="openMenus" />
                    </button>
                    <img src="/icon_kndi.svg" alt="Logo" class="w-8 h-8 drop-shadow-md" />
                    <span class="font-bold text-lg text-blue-800 dark:text-white tracking-tight">KNDI</span>
                </div>
                <div class="flex gap-3">
                    <!-- Notification Bell Dropdown -->
                    <div class="relative">
                        <button 
                            @click="toggleNotifications"
                            type="button"
                            class="relative p-2.5 rounded-none text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-300"
                            title="Notifications"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>

                            <span 
                                v-if="unreadCount > 0" 
                                class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900 animate-pulse"
                            >
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Dropdown Menu Notifications -->
                        <div 
                            v-if="showNotifications" 
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-gray-200 dark:border-white/10 shadow-2xl rounded-none z-50 overflow-hidden"
                        >
                            <div class="p-3 border-b border-gray-100 dark:border-white/10 flex justify-between items-center bg-gray-50 dark:bg-slate-800/50">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700 dark:text-slate-200">Notifications</h3>
                                <button 
                                    v-if="unreadCount > 0" 
                                    @click="markAllAsRead"
                                    type="button"
                                    class="text-[10px] font-semibold text-primary-600 dark:text-primary-400 hover:underline"
                                >
                                    Mark All as Read
                                </button>
                            </div>

                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5 no-scrollbar">
                                <template v-if="notifications.length > 0">
                                    <div 
                                        v-for="item in notifications" 
                                        :key="item.id"
                                        @click="markAsRead(item)"
                                        class="block p-3 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition cursor-pointer text-left relative"
                                        :class="{
                                            'bg-primary-50/30 dark:bg-primary-900/10': !item.read_at,
                                            'opacity-75': item.read_at
                                        }"
                                    >
                                        <!-- Titik Indikator Belum Dibaca -->
                                        <span 
                                            v-if="!item.read_at" 
                                            class="absolute top-3 right-3 w-2 h-2 rounded-full bg-primary-500"
                                        ></span>

                                        <!-- Fleksibel membaca atribut langsung atau dari data JSON -->
                                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 pr-4">
                                            {{ item.data?.title || item.title || 'Notifikasi Baru' }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">
                                            {{ item.data?.message || item.data?.body || item.message || '-' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 mt-1 block">
                                            {{ formatDate(item.created_at) }}
                                        </span>
                                    </div>
                                </template>
                                <div v-else class="p-6 text-center text-xs text-slate-400 italic">
                                    No notifications yet.
                                </div>
                            </div>
                        </div>
                    </div>

                    <button @click="toggleProfilePanel" class="relative">
                        <div v-if="user?.avatar" class="w-8 h-8 rounded-none overflow-hidden">
                            <img :src="user.avatar" alt="Avatar" class="w-full h-full object-contain">
                        </div>
                        <div v-else class="w-8 h-8 rounded-none bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center text-primary-600 dark:text-primary-300 font-bold text-sm shadow-inner">
                            {{ getInitials(user?.name) }}
                        </div>
                    </button>
                </div>
            </div>

            <Transition name="slide-fade">
                <div 
                    v-show="openMenus" 
                    class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-t border-white/20 dark:border-white/10 shadow-xl overflow-y-auto max-h-[80vh]"
                >
                    <div class="flex flex-col p-4 space-y-4">
                        <Menus :sidebarOpen="true" />
                    </div>
                </div>
            </Transition>
        </nav>

        <!-- Desktop Sidebar -->
        <nav 
            class="hidden md:flex fixed top-4 left-4 bottom-4 z-50 transition-all duration-500 shadow-2xl flex-col rounded-none border border-white/20 dark:border-white/10" 
            :class="[
                openMenus ? 'w-72' : 'w-24',
                showProfilePanel ? '-translate-x-[200%]' : 'translate-x-0',
                'bg-white/70 dark:bg-slate-900/90 backdrop-blur-xl'
            ]"
        >
            <div 
                class="h-24 flex items-center shrink-0 text-white transition-all duration-300 relative z-20"
                :class="openMenus ? 'justify-between pl-8 pr-6' : 'justify-center px-0'" 
            >
                <div v-if="openMenus" class="flex items-center gap-3 overflow-hidden whitespace-nowrap animate-fade-in">
                    <img src="/icon_kndi.svg" alt="Logo" class="w-12 h-12 shrink-0" />
                    <span class="text-blue-900 dark:text-white font-bold text-2xl tracking-wide">KNDI</span>
                </div>
                
                <button 
                    @click="openMenus = !openMenus" 
                    class="text-blue-900 dark:text-gray-300 hover:text-white transition focus:outline-none p-2 rounded-none"
                >
                    <Hamburger :show="openMenus" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto overflow-x-visible py-6 no-scrollbar relative z-10">
                <Menus :sidebarOpen="openMenus" />
            </div>
        </nav>

        <!-- Main Wrapper -->
        <div 
            class="flex-1 flex flex-col min-h-screen transition-all duration-500 ease-out pt-16 md:pt-0" 
            :class="{
                'md:pl-[20rem] md:pr-4': openMenus && !showProfilePanel, 
                'md:pl-[8rem] md:pr-4': !openMenus && !showProfilePanel,
                'md:pl-6 md:pr-[22rem]': showProfilePanel
            }"
        >
            <!-- Header -->
            <header class="hidden md:flex h-24 items-center justify-between px-4 sticky top-0 z-40 transition-colors duration-300
                bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-white/5 shadow-sm">
                <div>
                    <h1 class="text-3xl font-bold text-[#0d1b3e] dark:text-slate-100 tracking-tight">
                        Hello, {{ user?.name ? user.name.split(' ')[0] : 'User' }}!
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Have a productive day.</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notification Bell Dropdown -->
                    <div class="relative">
                        <button 
                            @click="toggleNotifications"
                            type="button"
                            class="relative p-2.5 rounded-none bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-white/10"
                            title="Notifications"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>

                            <span 
                                v-if="unreadCount > 0" 
                                class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900 animate-pulse"
                            >
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Dropdown Menu Notifications -->
                        <div 
                            v-if="showNotifications" 
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-gray-200 dark:border-white/10 shadow-2xl rounded-none z-50 overflow-hidden"
                        >
                            <div class="p-3 border-b border-gray-100 dark:border-white/10 flex justify-between items-center bg-gray-50 dark:bg-slate-800/50">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700 dark:text-slate-200">Notifications</h3>
                                <button 
                                    v-if="unreadCount > 0" 
                                    @click="markAllAsRead"
                                    type="button"
                                    class="text-[10px] font-semibold text-primary-600 dark:text-primary-400 hover:underline"
                                >
                                    Mark All as Read
                                </button>
                            </div>

                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5 no-scrollbar">
                                <template v-if="notifications.length > 0">
                                    <div 
                                        v-for="item in notifications" 
                                        :key="item.id"
                                        @click="markAsRead(item)"
                                        class="block p-3 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition cursor-pointer text-left relative"
                                        :class="{
                                            'bg-primary-50/30 dark:bg-primary-900/10': !item.read_at,
                                            'opacity-75': item.read_at
                                        }"
                                    >
                                        <!-- Titik Indikator Belum Dibaca -->
                                        <span 
                                            v-if="!item.read_at" 
                                            class="absolute top-3 right-3 w-2 h-2 rounded-full bg-primary-500"
                                        ></span>

                                        <!-- Fleksibel membaca atribut langsung atau dari data JSON -->
                                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 pr-4">
                                            {{ item.data?.title || item.title || 'Notifikasi Baru' }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">
                                            {{ item.data?.message || item.data?.body || item.message || '-' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 mt-1 block">
                                            {{ formatDate(item.created_at) }}
                                        </span>
                                    </div>
                                </template>
                                <div v-else class="p-6 text-center text-xs text-slate-400 italic">
                                    No notifications yet.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Trigger Button -->
                    <button 
                        @click="toggleProfilePanel"
                        class="flex items-center gap-3 p-1.5 pr-5 rounded-none bg-white dark:bg-slate-800 shadow-sm hover:shadow-lg transition-all duration-300 group border border-gray-200 dark:border-white/10"
                    >
                        <div v-if="user?.avatar" class="w-10 h-10 rounded-none overflow-hidden">
                             <img :src="user.avatar" alt="User Avatar" class="w-full h-full object-contain">
                        </div>
                        <div v-else class="w-10 h-10 rounded-none bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center text-primary-600 dark:text-primary-300 font-bold text-sm">
                            {{ getInitials(user?.name) }}
                        </div>

                        <div class="text-left hidden lg:block">
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-primary-600 transition">{{ user?.name }}</p>
                        </div>
                    </button>
                </div>
            </header>

            <main class="flex-1 px-4 pb-8">
                <slot />
            </main>
        </div>

        <!-- Right Profile Panel -->
        <aside 
            class="fixed top-0 right-0 h-screen w-80 shadow-2xl z-[70] transform transition-transform duration-500 overflow-y-auto border-l border-white/20 dark:border-white/10
            bg-white dark:bg-slate-900 rounded-none"
            :class="showProfilePanel ? 'translate-x-0' : 'translate-x-full'"
        >
            <div class="sticky top-0 right-0 flex items-center justify-between p-4 z-[80] bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-white/5">
                <!-- Theme Dropdown (Minimalist Icon Only) -->
                <div id="theme-dropdown-container" class="relative">
                    <button 
                        type="button"
                        @click.stop="isDropdownOpen = !isDropdownOpen"
                        class="flex items-center gap-1.5 p-2 rounded-md bg-gray-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-700 dark:text-slate-200 shadow-sm hover:bg-gray-100 dark:hover:bg-slate-700 transition-all"
                        :title="`Theme: ${currentTheme}`"
                    >
                        <svg v-if="currentTheme === 'light'" class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <svg v-else-if="currentTheme === 'dark'" class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        <svg v-else class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>

                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <!-- Dropdown Menu -->
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
                            class="absolute left-0 mt-2 w-32 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden py-1 z-50"
                        >
                            <button 
                                type="button" 
                                @click="selectTheme('light')"
                                class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/60"
                                :class="currentTheme === 'light' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-50 dark:bg-slate-700/40' : 'text-slate-700 dark:text-slate-300'"
                            >
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <span>Light</span>
                            </button>

                            <button 
                                type="button" 
                                @click="selectTheme('dark')"
                                class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/60"
                                :class="currentTheme === 'dark' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-50 dark:bg-slate-700/40' : 'text-slate-700 dark:text-slate-300'"
                            >
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                                <span>Dark</span>
                            </button>

                            <button 
                                type="button" 
                                @click="selectTheme('system')"
                                class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/60"
                                :class="currentTheme === 'system' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-50 dark:bg-slate-700/40' : 'text-slate-700 dark:text-slate-300'"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <span>System</span>
                            </button>
                        </div>
                    </Transition>
                </div>

                <button 
                    @click="showProfilePanel = false" 
                    class="flex items-center justify-center w-10 h-10 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-none transition-all duration-200"
                    title="Close profile"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 pb-6 flex flex-col h-full relative">
                <h2 class="text-lg font-bold text-[#0d1b3e] dark:text-white mb-8 mt-2 uppercase tracking-widest opacity-50">User Profile</h2>

                <div class="flex flex-col items-center mb-8 w-full">
                    <div class="relative group cursor-pointer mb-6" @click="openAvatarModal">
                        <div class="w-24 h-24 flex items-center justify-center text-3xl font-bold text-primary-600 relative overflow-hidden">
                            <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-contain" alt="Profile">
                            <span v-else>{{ getInitials(user?.name) }}</span>
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="text-white font-bold text-xs">CHANGE</span>
                            </div>
                        </div>
                    </div>

                    <div class="w-full text-center">
                        <div v-if="!isEditingName" class="flex items-center justify-center gap-2 group">
                            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ user?.name }}</h2>
                            <button @click="startEditingName" class="p-1 text-slate-400 group-hover:text-primary-500 transition">
                                <Pen class="w-3.5 h-3.5" />
                            </button>
                        </div>
                        <div v-else class="flex items-center justify-center gap-2 animate-fade-in">
                            <TextInput 
                                v-model="nameForm.name" 
                                class="py-1 px-2 text-center text-sm font-bold w-40 bg-gray-50 dark:bg-slate-800 border-gray-200 rounded-none" 
                                :disabled="nameForm.processing"
                                @keyup.enter="saveName" 
                            />
                            <button 
                                @click="saveName" 
                                :disabled="nameForm.processing"
                                class="p-1.5 bg-primary-600 text-white rounded-none hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center min-w-[28px]"
                            >
                                <svg v-if="nameForm.processing" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-else>✓</span>
                            </button>
                            <button 
                                @click="cancelEditingName" 
                                :disabled="nameForm.processing"
                                class="p-1.5 bg-gray-200 text-gray-600 rounded-none hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                            >✕</button>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-1">{{ user?.email }}</p>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="bg-gray-50 dark:bg-slate-800/50 rounded-none p-5 mb-6 border border-gray-100 dark:border-white/5">
                        <div class="flex justify-between items-center mb-3 group">
                            <h3 class="font-bold text-xs uppercase tracking-widest text-slate-400">Skills</h3>
                            <button @click="openSkillModal" class="p-1 text-slate-400 hover:text-primary-500 transition" title="Pilih Skill untuk Ditampilkan">
                                <Pen class="w-3.5 h-3.5" />
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                             <template v-if="displayedSkills && displayedSkills.length">
                                <span v-for="(skillItem, index) in displayedSkills" :key="index" class="px-2.5 py-1 rounded-none text-[10px] font-bold bg-white dark:bg-primary-500/20 text-primary-600 dark:text-primary-300 border border-primary-100 dark:border-primary-500/30">
                                    {{ skillItem.skill }}
                                </span>
                            </template>
                            <span v-else class="text-xs text-slate-400 italic">No skills selected.</span>
                        </div>
                    </div>

                    <div class="mt-auto pt-6 border-t border-gray-100 dark:border-white/10 space-y-2">
                        <button 
                            @click="openPasswordModal"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 bg-gray-50 dark:bg-slate-800/50 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 rounded-none transition-all border border-transparent hover:border-primary-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <span>Change Password</span>
                        </button>

                        <button 
                            @click="logout" 
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold text-red-500 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 hover:text-red-600 rounded-none transition-all border border-transparent hover:border-red-100"
                        >
                            <Logout class="w-4 h-4" />
                            <span>Sign Out</span>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Modals -->
        <Modal :show="showAvatarModal" @close="showAvatarModal = false" max-width="2xl">
            <div class="p-8 bg-white dark:bg-slate-800 rounded-lg"> 
                <h2 class="text-xl font-bold text-gray-900 dark:text-slate-100 mb-8 text-center md:text-left">Select Profile Avatar</h2>
                
                <div class="flex flex-col md:flex-row gap-10">
                    <div class="flex flex-col items-center md:items-start space-y-4">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Preview</span>
                        <div class="w-48 h-48 bg-slate-50 dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden rounded-xl shadow-inner">
                            <img 
                                :src="selectedAvatarTemp" 
                                alt="Avatar Preview" 
                                class="w-full h-full object-contain p-2" 
                            />
                        </div>
                    </div>

                    <div class="flex-1">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4 block">Available Assets</span>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-4 max-h-[320px] overflow-y-auto pr-2 no-scrollbar p-1"> 
                            <button 
                                v-for="(asset, index) in avatarAssets" 
                                :key="index" 
                                @click="selectAvatar(asset)" 
                                class="relative aspect-square flex items-center justify-center transition-all duration-300 rounded-xl group"
                                :class="[
                                    selectedAvatarTemp === asset 
                                        ? 'bg-white dark:bg-slate-700 border-2 border-primary-500 shadow-lg scale-95 ring-2 ring-primary-500/20' 
                                        : 'bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700/60 shadow-sm hover:shadow-md hover:border-primary-400 dark:hover:border-primary-500 hover:-translate-y-1'
                                ]"
                            >
                                <img :src="asset" class="w-full h-full object-contain p-2" />
                                
                                <div v-if="selectedAvatarTemp === asset" class="absolute -top-2 -right-2 bg-primary-500 text-white rounded-full p-1 shadow-lg border-2 border-white dark:border-slate-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-10 pt-6 border-t border-gray-100 dark:border-white/10">
                    <SecondaryButton @click="showAvatarModal = false" class="rounded-lg px-6" :disabled="avatarForm.processing">Cancel</SecondaryButton>
                    <PrimaryButton 
                        @click="saveAvatar" 
                        :disabled="avatarForm.processing" 
                        class="rounded-lg px-8 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="avatarForm.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ avatarForm.processing ? 'Updating...' : 'Update Avatar' }}</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showSkillModal" @close="showSkillModal = false" max-width="md">
            <div class="p-6 bg-white dark:bg-slate-900 rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-slate-100">Display Skills (Max 5)</h2>
                    <button @click="showSkillModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-1 max-h-60 overflow-y-auto pr-2 no-scrollbar mb-4">
                    <div v-if="!user?.skills || user.skills.length === 0" class="text-sm text-slate-500 italic py-4">
                        You haven't added any skills yet.
                    </div>
                    <label 
                        v-else
                        v-for="skill in user.skills" 
                        :key="skill.id" 
                        class="flex items-center space-x-3 cursor-pointer p-3 border border-transparent hover:bg-gray-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
                        :class="{'opacity-50 cursor-not-allowed': selectedSkills.length >= 5 && !selectedSkills.includes(skill.id)}"
                    >
                        <input 
                            type="checkbox" 
                            :value="skill.id" 
                            v-model="selectedSkills"
                            :disabled="selectedSkills.length >= 5 && !selectedSkills.includes(skill.id)"
                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:bg-slate-700 dark:border-slate-600"
                        >
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ skill.skill }}</span>
                    </label>
                </div>
                
                <div class="text-xs text-slate-500 mb-6">
                    Selected: {{ selectedSkills.length }} / 5
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
                    <SecondaryButton @click="showSkillModal = false" class="rounded-lg px-4" :disabled="skillForm.processing">Cancel</SecondaryButton>
                    <PrimaryButton 
                        @click="saveSkills" 
                        :disabled="skillForm.processing" 
                        class="rounded-lg px-6 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="skillForm.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ skillForm.processing ? 'Saving...' : 'Save Skills' }}</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modal Change Password -->
        <Modal :show="showPasswordModal" @close="closePasswordModal" max-width="lg">
            <div class="p-6 bg-white dark:bg-slate-800 rounded-lg">
                <UpdatePasswordForm @close="closePasswordModal" />
            </div>
        </Modal>

        <!-- Toast bawaan dipertahankan -->
        <Toast />
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 80vh;
    opacity: 1;
    overflow: hidden;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    max-height: 0;
    opacity: 0;
    transform: translateY(-8px);
}
</style>