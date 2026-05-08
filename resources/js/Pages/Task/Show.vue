<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Pen from '@/Components/Icon/Pen.vue';
import Trash from '@/Components/Icon/Trash.vue';
import Close from '@/Components/Icon/Close.vue';
import Hamburger from '@/Components/Icon/Hamburger.vue';
import User from '@/Components/Icon/User.vue';
import UserPlus from '@/Components/Icon/UserPlus.vue';
import Chat from '@/Components/Icon/Chat.vue';
import TaskCreateEditForm from '@/Components/Form/TaskCreateEdit.vue';
import TaskAssignForm from '@/Components/Form/TaskAssign.vue';
import TaskPrForm from '@/Components/Form/TaskPr.vue';
import TaskCommentForm from '@/Components/Form/TaskComment.vue';
import TaskReplyForm from '@/Components/Form/TaskReply.vue';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';
import CloseTaskConfirmationModal from '@/Components/CloseTaskConfirmationModal.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import moment from 'moment';

const showButtons = ref(false);
const openCreateEditForm = ref(false);
const openAssignForm = ref(false);
const openPrForm = ref(false);
const openCommentForm = ref(false);
const openReplyForm = ref(false);
const isEditMode = ref(false);
const selectedTask = ref(null);
const selectedComment = ref(null);
const isLoaded = ref(false);
const confirmDeleteModal = ref(false)
const taskToDelete = ref(null)
const confirmCloseModal = ref(false)

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const page = usePage();
const id = computed(() => page.props.auth.user.id);
const role = computed(() => page.props.auth.user.role);

// --- Handle Back ---
const handleBack = () => {
  router.get(route('task.list'));
};

const openDeleteModal = (task) => {
  taskToDelete.value = task;
  confirmDeleteModal.value = true;
}

const closeDeleteModal = () => {
  taskToDelete.value = null;
  confirmDeleteModal.value = false
}

const handleConfirmDelete = () => {
  router.delete(route('task.destroy', taskToDelete.value.id), {
    onSuccess: () => closeDeleteModal(),
  });
};

const handleEdit = () => {
  isEditMode.value = true;
  selectedTask.value = props.task;
  openCreateEditForm.value = true;
};

const handleAssign = () => {
  selectedTask.value = props.task;
  openAssignForm.value = true;
};

const handlePr = () => {
  selectedTask.value = props.task;
  openPrForm.value = true;
};

const handleComment = () => {
  selectedTask.value = props.task;
  openCommentForm.value = true;
};

const handleReply = (id) => {
  const comment = props.prs.find(p => p.id === id);
  if (comment) {
    openReplyForm.value = true;
    selectedComment.value = comment;
  }
};

const markReviewDone = (taskId, reviewerId) => {
  if (!confirm('Mark review as completed?')) return;
  router.put(route('task.review.complete', { id: taskId, reviewerId }));
};

const handleClose = () => {
  confirmCloseModal.value = true;
};

const closeCloseModal = () => {
  confirmCloseModal.value = false;
};

const handleConfirmClose = () => {
  router.put(route('task.close', props.task.id), {
    onSuccess: () => closeCloseModal(),
  });
};

const handleCloseForm = () => {
  openCreateEditForm.value = false;
  openAssignForm.value = false;
  openPrForm.value = false;
  openCommentForm.value = false;
  openReplyForm.value = false;
  isEditMode.value = false;
  selectedTask.value = null;
  selectedComment.value = null;
};

const props = defineProps({
  task: {},
  users: {},
  project: {},
  projects: {},
  communicator: {},
  programmer: {},
  designer: {},
  totalTimeUsed: '',
  prs: {},
});

const formatDate = (date, withTime = false) => {
  if (!date) return '-';
  let formatString = 'DD MMMM YYYY';
  if (withTime) formatString = 'DD MMM YYYY, HH:mm';
  return moment(date).format(formatString);
};

const formatJsonList = (jsonData) => {
  if (!jsonData) return '-';
  if (Array.isArray(jsonData)) {
    return jsonData.map(id => getNameUser(id) || id).join(', ');
  }
  return jsonData;
};

const getNameUser = (id) => {
  return id ? props.users.find(u => u.id === id)?.name : '-';
};

// --- CHART LOGIC ---
const logtimeSegments = computed(() => {
  const logs = props.task.logtimes || [];
  if (!logs.length) return [];

  const grouped = {};
  let totalCalc = 0;

  logs.forEach(log => {
    const uid = log.user_id;
    const time = Number(log.time_used);
    if (!grouped[uid]) {
      grouped[uid] = { 
        id: uid,
        name: getNameUser(uid) || 'Unknown', 
        time: 0 
      };
    }
    grouped[uid].time += time;
    totalCalc += time;
  });

  const colors = ['#2876bc', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', '#3b82f6', '#06b6d4', '#f43f5e'];
  const circumference = 2 * Math.PI * 40; 
  let currentOffset = 0;

  return Object.values(grouped)
    .sort((a, b) => b.time - a.time)
    .map((item, index) => {
      const segmentLength = (item.time / totalCalc) * circumference;
      
      const data = {
        ...item,
        color: colors[index % colors.length],
        strokeDasharray: `${segmentLength} ${circumference}`,
        strokeDashoffset: -currentOffset,
        percentage: Math.round((item.time / totalCalc) * 100)
      };
      
      currentOffset += segmentLength;
      return data;
    });
});

const visibleButtons = computed(() => {
  const buttons = [];
  if (['other', 'pm'].includes(role.value)) {
    buttons.push({ action: 'assign', icon: User, handler: handleAssign, text: 'Assign' });
  }
  if (
    (['pm', 'pg', 'ds'].includes(role.value) && [props.task?.programmer, props.task?.designer].some(arr => arr?.includes(id.value))) ||
    ['other'].includes(role.value)
  ) {
    buttons.push({ action: 'review', icon: UserPlus, handler: handlePr, text: 'Review' });
  }
  if (
    props.task?.pl === id.value ||
    ['other'].includes(role.value) ||
    [props.task?.communicator, props.task?.programmer, props.task?.designer, props.task?.reviewer].some(arr => arr?.includes(id.value))
  ) {
    buttons.push({ action: 'comment', icon: Chat, handler: handleComment, text: 'Comment' });
  }
  if (['other', 'pm', 'co'].includes(role.value)) {
    buttons.push({ action: 'edit', icon: Pen, handler: handleEdit, text: 'Edit' });
  }
  if (['other', 'pm', 'co'].includes(role.value)) {
    buttons.push({ action: 'delete', icon: Trash, handler: () => openDeleteModal(props.task), text: 'Delete' });
  }
  if (
    (['pm', 'pg', 'ds'].includes(role.value) && [props.task?.programmer, props.task?.designer].some(arr => arr?.includes(id.value))) ||
    ['other'].includes(role.value)
  ) {
    buttons.push({ action: 'close', icon: Close, handler: handleClose, text: 'Close Task' });
  }
  return buttons;
});
</script>

<template>
  <Head title="Task Detail" />
  
  <div class="w-full">
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0 mt-8">
        <div
          class="flex flex-col md:flex-row justify-between px-6 py-4 items-start md:items-center gap-4 text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-700 ease-out"
          :class="{ 'opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div class="flex items-center gap-4">
            <button 
              @click="handleBack"
              class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-600 dark:text-gray-300 border border-white/40 dark:border-gray-600/30 transition-all shadow-sm group"
              title="Back"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:-translate-x-1 transition-transform">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
              </svg>
            </button>
              
            <div>
                <h2 class="font-bold text-xl leading-tight text-gray-900 dark:text-slate-100 drop-shadow-sm">
                {{ task.issue }}
                </h2>
                <div class="flex items-center gap-2 mt-1 text-sm font-medium text-gray-500 dark:text-slate-400">
                  <span>{{ project.name }}</span>
                </div>
            </div>
          </div>

          <div class="flex flex-wrap gap-2 w-full md:w-auto justify-end">
            <button
              v-for="button in visibleButtons"
              :key="button.action"
              @click="button.handler"
              class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg bg-white/50 dark:bg-slate-800/40 hover:bg-white dark:hover:bg-slate-700/50 text-gray-700 dark:text-gray-200 border border-white/40 dark:border-white/10 shadow-sm backdrop-blur-sm transition-all hover:scale-105 active:scale-95"
            >
              <component :is="button.icon" class="w-4 h-4" />
              <span class="hidden lg:inline">{{ button.text }}</span>
            </button>
          </div>
        </div>
    </div>

    <div v-if="['other', 'co'].includes(role)" class="fixed sm:hidden right-6 bottom-6 z-50">
      <div
        class="shrink-0 inline-flex items-center justify-center p-3 rounded-full text-white bg-primary-600 shadow-xl z-40 transition-all duration-500 hover:scale-110 active:scale-95"
        @click="showButtons = !showButtons"
      >
        <Hamburger v-model="showButtons" class="w-6 h-6" />
      </div>
      <TransitionGroup tag="div" name="button-list">
        <button
          v-for="(button, index) in visibleButtons"
          v-show="showButtons"
          :key="button.action"
          @click="button.handler"
          class="fixed right-6 border border-white/20 rounded-full p-3 text-gray-700 dark:text-white bg-white/90 dark:bg-slate-800/90 backdrop-blur-md shadow-lg"
          :style="{ bottom: `${80 + (index) * 60}px` }"
        >
          <component :is="button.icon" class="w-5 h-5" />
        </button>
      </TransitionGroup>
    </div>

    <div v-if="openCreateEditForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-5xl w-full p-6 relative animate-in fade-in zoom-in duration-300 overflow-y-auto max-h-[90vh]">
        <TaskCreateEditForm :task="selectedTask" :projects="projects" :projectId="task.project_id" :isEditMode="isEditMode" @close="handleCloseForm" />
      </div>
    </div>

    <div v-if="openAssignForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-200">
        <TaskAssignForm :task="selectedTask" :pl="users" :co="communicator" :pg="programmer" :ds="designer" @close="handleCloseForm" />
      </div>
    </div>

    <div v-if="openPrForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-200">
        <TaskPrForm :task="selectedTask" :pg="programmer" @close="handleCloseForm" />
      </div>
    </div>

    <div v-if="openCommentForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-200">
        <TaskCommentForm :task="selectedTask" @close="handleCloseForm" />
      </div>
    </div>

    <div v-if="openReplyForm" class="fixed inset-0 z-[100] px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-200">
        <TaskReplyForm :comment="selectedComment" @close="handleCloseForm" />
      </div>
    </div>

    <div
      class="py-8 transition-all duration-1000 ease-out delay-100"
      :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        
        <div class="bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-2xl rounded-lg overflow-hidden flex flex-col">

          <div class="p-8 border-b border-white/20 dark:border-white/5">
            <h3 class="text-lg font-bold text-gray-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <span class="w-1 h-6 bg-primary-500 rounded-full"></span>
                Overview
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-4">
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Project Owner</span>
                    <span class="col-span-2 text-sm font-medium text-gray-800 dark:text-slate-200">{{ project.project_owner?.name || '-' }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Project</span>
                    <span class="col-span-2 text-sm font-medium text-gray-800 dark:text-slate-200">{{ project.name || '-' }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Creator</span>
                    <span class="col-span-2 text-sm font-medium text-gray-800 dark:text-slate-200">{{ getNameUser(task.creator) }}</span>
                </div>
              </div>
              <div class="space-y-4">
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Start Date</span>
                    <span class="col-span-2 text-sm font-medium text-gray-800 dark:text-slate-200">{{ formatDate(task.start_date) }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Due Date</span>
                    <span class="col-span-2 text-sm font-medium text-red-500 dark:text-red-400">{{ formatDate(task.due_date) }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">End Date</span>
                    <span class="col-span-2 text-sm font-medium text-gray-800 dark:text-slate-200">{{ formatDate(task.end_date) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-8 border-b border-white/20 dark:border-white/5 bg-white/20 dark:bg-primary-500/5">
            <h3 class="text-lg font-bold text-gray-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <span class="w-1 h-6 bg-emerald-500 rounded-full"></span>
                Team Members
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
              <div class="flex flex-col gap-1">
                  <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Project Leader</span>
                  <span class="text-sm font-semibold text-gray-800 dark:text-slate-200">{{ getNameUser(task.pl) }}</span>
              </div>
              <div class="flex flex-col gap-1">
                  <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Communicator</span>
                  <span class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ formatJsonList(task.communicator) }}</span>
              </div>
              <div class="flex flex-col gap-1">
                  <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Programmer</span>
                  <span class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ formatJsonList(task.programmer) }}</span>
              </div>
              <div class="flex flex-col gap-1">
                  <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Designer</span>
                  <span class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ formatJsonList(task.designer) }}</span>
              </div>
              <div class="flex flex-col gap-1">
                  <span class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Reviewer</span>
                  <span class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ formatJsonList(task.reviewer) }}</span>
              </div>
            </div>
          </div>

          <div class="p-8 border-b border-white/20 dark:border-white/5">
            <h3 class="text-lg font-bold text-gray-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <span class="w-1 h-6 bg-amber-500 rounded-full"></span>
                Task Details
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide block mb-2">Description</label>
                        <div class="p-4 rounded-lg bg-white/50 dark:bg-slate-800/40 border border-white/40 dark:border-white/10 text-gray-700 dark:text-slate-300 text-sm whitespace-pre-wrap leading-relaxed shadow-inner max-h-[300px] overflow-y-auto custom-scrollbar">
                            {{ task.description || 'No description provided.' }}
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Ticket Link</label>
                            <a :href="'//' + task.ticket_link" target="_blank" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline break-all flex items-center gap-1">
                                {{ task.ticket_link }} ↗
                            </a>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Related Links</label>
                            <div v-if="task.related_links?.length > 0" class="space-y-1">
                                <div v-for="link in task.related_links" :key="link">
                                    <a :href="'//' + link" target="_blank" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline break-all flex items-center gap-1">
                                        {{ link }} ↗
                                    </a>
                                </div>
                            </div>
                            <span v-else class="text-sm text-gray-400 italic">-</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide block mb-2">Time Allocation</label>
                    <div v-if="logtimeSegments.length > 0" class="p-5 rounded-lg bg-white/50 dark:bg-slate-800/40 border border-white/40 dark:border-white/10 shadow-sm flex flex-col items-center">
                        <div class="relative w-40 h-40 mb-6">
                           <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                              <circle cx="50" cy="50" r="40" fill="transparent" stroke-width="12" class="stroke-gray-200/50 dark:stroke-slate-700/50" />
                              <circle 
                                v-for="seg in logtimeSegments" 
                                :key="seg.id"
                                cx="50" cy="50" r="40" 
                                fill="transparent" 
                                stroke-width="12" 
                                :stroke="seg.color"
                                :stroke-dasharray="seg.strokeDasharray"
                                :stroke-dashoffset="seg.strokeDashoffset"
                                class="transition-all duration-700 ease-out hover:stroke-width-[14]"
                              />
                           </svg>
                           <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                              <span class="text-2xl font-bold text-gray-800 dark:text-slate-100">{{ totalTimeUsed }}</span>
                              <span class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest">Hours</span>
                           </div>
                        </div>
                        <div class="w-full space-y-2 max-h-[150px] overflow-y-auto custom-scrollbar pr-2">
                            <div v-for="seg in logtimeSegments" :key="seg.id" class="flex justify-between items-center text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: seg.color }"></span>
                                    <span class="text-gray-600 dark:text-slate-300 font-medium truncate max-w-[100px]" :title="seg.name">{{ seg.name }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <span class="text-gray-400">{{ seg.percentage }}%</span>
                                    <span class="font-bold text-gray-700 dark:text-slate-200">{{ seg.time }}h</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-6 rounded-lg bg-gray-50/50 dark:bg-slate-800/40 border border-dashed border-gray-300 dark:border-slate-700 text-center">
                        <p class="text-sm text-gray-400">No time logged yet.</p>
                    </div>
                </div>
            </div>
          </div>

          <div class="p-8 bg-gray-50/30 dark:bg-black/20">
            <h3 class="text-lg font-bold text-gray-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <span class="w-1 h-6 bg-purple-500 rounded-full"></span>
                Discussion & PRs
            </h3>
            
            <div v-if="prs.length > 0" class="space-y-6">
                <div v-for="pr in prs" :key="pr.id" class="relative pl-6 border-l-2 border-primary-200 dark:border-primary-900/50">
                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-primary-500 ring-4 ring-white dark:ring-slate-900"></div>
                    
                    <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-md border border-white/50 dark:border-white/10 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-gray-900 dark:text-slate-100">{{ getNameUser(pr.from) }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">Commented</span>
                            </div>
                            <span class="text-sm text-gray-400">{{ formatDate(pr.created_at, true) }}</span>
                        </div>

                        <div class="text-sm text-gray-700 dark:text-slate-300 whitespace-pre-wrap mb-4">
                            {{ pr.comment }}
                        </div>

                        <div v-if="pr.pr_links && pr.pr_links.length > 0" class="mb-4 flex flex-wrap gap-2">
                           <a v-for="link in pr.pr_links" :key="link" :href="'//' + link" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 text-sm font-medium border border-primary-100 dark:border-primary-800 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                {{ link }}
                           </a>
                        </div>

                        <div v-if="pr.replies && pr.replies.length > 0" class="mt-4 space-y-3 pl-4 border-l border-gray-200 dark:border-slate-700">
                            <div v-for="r in pr.replies" :key="r.id" class="bg-gray-50/80 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-100 dark:border-white/5">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-semibold text-sm text-gray-800 dark:text-slate-200">{{ r.user ? r.user.name : getNameUser(r.from) }}</span>
                                    <span class="text-[10px] text-gray-400">{{ formatDate(r.created_at, true) }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-slate-400 whitespace-pre-wrap">{{ r.comment }}</p>
                                <div
                                  v-if="r.pr_links && r.pr_links.length > 0"
                                  v-for="links in r.pr_links"
                                  class="text-[#50B2D8]"
                                >
                                  <a 
                                    :href="'//' + links"
                                    target="_blank"
                                    class="flex flex-cols"
                                  >
                                    <span class="flex items-center gap-1 hover:underline">
                                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                      </svg>
                                      {{ links }}
                                    </span>
                                  </a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex justify-end">
                           <button @click="handleReply(pr.id)" class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 hover:underline">
                             Reply
                           </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-10">
                <div class="inline-flex p-4 rounded-full bg-gray-100 dark:bg-slate-800 mb-3">
                    <Chat class="w-6 h-6 text-gray-400" />
                </div>
                <p class="text-sm text-gray-500 dark:text-slate-400">No comments or PRs yet.</p>
            </div>
          </div>

        </div>
      </div>
    </div>

    <DeleteConfirmationModal
      :show="confirmDeleteModal"
      title="Delete Task"
      :message="`Are you sure want to delete task ${taskToDelete?.issue}`"
      @close="closeDeleteModal"
      @confirm="handleConfirmDelete"
    />

    <CloseTaskConfirmationModal
      :show="confirmCloseModal"
      title="Close Task"
      :message="`Are you sure you want to close task '${task.issue}'?`"
      @close="closeCloseModal"
      @confirm="handleConfirmClose"
    />
  </div>
</template>

<style>
/* Animasi tombol floating (mobile) */
.button-list-enter-active,
.button-list-leave-active {
  transition: all 0.3s ease;
}
.button-list-enter-from,
.button-list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Scrollbar halus untuk Dark Mode & Light Mode */
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
  display: block;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5); /* Abu-abu untuk Light Mode */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.8);
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1); /* Putih transparan untuk Dark Mode */
}
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.2);
}
</style>