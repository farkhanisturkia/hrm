<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default { layout: AuthenticatedLayout };
</script>

<script setup>
import Plus from '@/Components/Icon/Plus.vue';
import Close from '@/Components/Icon/Close.vue';
import Gear from '@/Components/Icon/Gear.vue';
import Download from '@/Components/Icon/Download.vue';
import Hamburger from '@/Components/Icon/Hamburger.vue';
import Clock from '@/Components/Icon/Clock.vue';
import Pagination from '@/Components/Pagination.vue';
import LogtimeForm from '@/Components/Form/Logtime.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import moment from 'moment';
import Datepicker from '@vuepic/vue-datepicker';
import Download2 from '@/Components/Icon/Download2.vue';
import Modal from '@/Components/Modal.vue';
import Trash from '@/Components/Icon/Trash.vue';
import Detail from '@/Components/Icon/Detail.vue';
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue';

const showButtons = ref(false);
const openForm = ref(false);
const isLoaded = ref(false);
const selectedLogtime = ref(null);
const showDetailLogtime = ref(false);
const confirmDeleteModal = ref(false);
const itemToDelete = ref(null);

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 100);
});

const page = usePage();
const role = computed(() => page.props.auth.user.role);

const queryParams = computed(() => {
  const url = new URL(page.url, window.location.origin);
  return Object.fromEntries(url.searchParams);
});

const options = ref(['other', 'co'].includes(role.value) ? true : false);
const id = ref(Number(queryParams.value.user_id) || null);

const dates = ref(
  queryParams.value.from && queryParams.value.to
    ? [
        moment(queryParams.value.from, 'DD-MM-YYYY').toDate(),
        moment(queryParams.value.to, 'DD-MM-YYYY').toDate(),
      ]
    : []
);

const handleOpenOptions = () => {
  options.value = !options.value;
};

const handleOpenForm = () => {
  openForm.value = true;
};

const handleCloseForm = () => {
  openForm.value = false;
};

const openDeleteModal = (id) => {
  itemToDelete.value = id;
  confirmDeleteModal.value = true;
}

const closeDeleteModal = () => {
  itemToDelete.value = null;
  confirmDeleteModal.value = false;
}

const handleConfirmDelete = () => {
  router.delete(route('logtime.destroy', itemToDelete.value), {
    onSuccess: () => closeDeleteModal(),
  })
};

const props = defineProps({
  logtimes: Object,
  tasks: {},
  users: {}
});

const groupedLogtimes = computed(() => {
  const grouped = {};
  const list = props.logtimes && props.logtimes.data ? props.logtimes.data : [];

  list.forEach(item => {
    const date = formatDate(item.date);
    if (!grouped[date]) {
      grouped[date] = { items: [], totalTime: 0 };
    }
    grouped[date].items.push(item);
    grouped[date].totalTime += parseFloat(item.time_used || 0);
  });
  return grouped;
});

const formatDate = (date) => {
  return moment(date).format('DD MMMM YYYY');
};

watch(id, (newValue) => {
  const params = { user_id: newValue };
  if (dates.value.length === 2) {
    params.from = moment(dates.value[0]).format('DD-MM-YYYY');
    params.to = moment(dates.value[1]).format('DD-MM-YYYY');
  }
  router.get(route('logtime.list', params));
});

const handleDateChange = (newDate) => {
  const params = {};
  if (id.value) params.user_id = id.value;
  if (newDate && newDate.length === 2) {
    params.from = moment(newDate[0]).format('DD-MM-YYYY');
    params.to = moment(newDate[1]).format('DD-MM-YYYY');
  }
  router.get(route('logtime.list', params));
};

const exportLogtime = (summary) => {
  const params = {};
  if (id.value) params.user_id = id.value;
  if (dates.value.length === 2) {
    params.from = moment(dates.value[0]).format('DD-MM-YYYY');
    params.to = moment(dates.value[1]).format('DD-MM-YYYY');
  }
  params.summary = summary;
  window.open(route('logtime.export', params), '_blank');
};

const visibleButtons = computed(() => {
  return [
    { action: 'add', icon: Plus, handler: handleOpenForm, text: 'New' },
    { action: 'export', icon: Download, handler: () => exportLogtime(false), text: 'Export' },
    { action: 'exportSummary', icon: Download2, handler: () => exportLogtime(true), text: 'ExportSummary' },
    { action: 'reset', icon: Close, handler: () => router.get(route('logtime.list')), text: 'Reset' }
  ];
});

const openDetailLogtime = (item) => {
  selectedLogtime.value = item;
  showDetailLogtime.value = true;
}

const closeDetailLogtime = () => {
  showDetailLogtime.value = false;
  selectedLogtime.value = null;
}
</script>

<template>
  <Head title="Logtimes" />
  
  <div class="w-full py-8"> 
    
    <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        <div
          class="flex flex-col md:flex-row justify-between px-6 py-4 items-start md:items-center gap-4 text-gray-800 dark:text-gray-200 
                 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 
                 shadow-lg rounded-lg transition-all duration-1000 ease-out"
          :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-8 opacity-0': !isLoaded }"
        >
          <div>
            <h2 class="font-bold text-xl leading-tight text-gray-800 dark:text-slate-100 drop-shadow-sm">Logtimes</h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Track and manage work hours.</p>
          </div>
          
          <div class="flex flex-wrap gap-3 justify-end w-full md:w-auto">
            <button
              v-if="['other', 'co'].includes(role)"
              @click="handleOpenOptions"
              class="flex items-center gap-2 px-4 py-2 bg-white/50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-700/50 text-gray-700 dark:text-gray-200 rounded-lg shadow-sm border border-white/40 dark:border-white/10 backdrop-blur-sm transition-all"
            >
              <Gear class="w-4 h-4" />
              <span class="hidden sm:inline font-medium text-sm">Options</span>
            </button>
            <button
              @click="handleOpenForm"
              class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-md hover:shadow-primary-500/30 transition-all duration-300 transform hover:scale-105"
            >
              <Plus class="w-5 h-5" />
              <span class="hidden sm:inline font-bold text-sm">Log Time</span>
              <span class="sm:hidden font-bold text-sm">New</span>
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

    <div v-if="openForm" class="fixed inset-0 z-50 px-4 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
      <div class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-2xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl max-w-lg w-full p-6 relative animate-in fade-in zoom-in duration-300">
        <LogtimeForm :tasks="tasks" @close="handleCloseForm" />
      </div>
    </div>

    <div
      v-if="options"
      class="w-full pt-4 sm:pt-6 transition-all duration-500 ease-out relative z-30"
      :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        <div class="relative z-20 bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl p-4 rounded-xl shadow-lg border border-white/40 dark:border-white/10">
          <div class="flex flex-col xl:flex-row gap-4 items-end justify-between w-full">
            
            <div class="flex flex-col sm:flex-row gap-4 w-full xl:w-auto flex-1">
              <div class="w-full sm:w-1/2 xl:w-72">
                 <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Date Range</label>
                 <Datepicker
                  v-model="dates"
                  range
                  placeholder="Select Range Dates"
                  :enable-time-picker="false"
                  @update:model-value="handleDateChange"
                  input-class-name="glass-datepicker"
                  :clearable="true"
                />
              </div>
              <div class="w-full sm:w-1/2 xl:w-72">
                  <label class="text-[10px] font-bold text-gray-500 dark:text-slate-400 mb-1 block uppercase tracking-wider">Filter User</label>
                  <SelectInput
                    id="user"
                    v-model="id"
                    :options="users"
                    label="name"
                    valueKey="id"
                    class="block w-full h-[42px]"
                    placeholder="Select user..."
                    :dark="true"
                  />
              </div>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap justify-end gap-3 w-full xl:w-auto">
              <button 
                @click="exportLogtime(false)" 
                class="w-full sm:w-auto flex items-center justify-center gap-2 h-[42px] px-4 py-2 text-sm font-bold rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition-all shadow-md"
              >
                <Download class="w-4 h-4" />
                <span class="whitespace-nowrap">Export</span>
              </button>
              
              <button 
                @click="exportLogtime(true)" 
                class="w-full sm:w-auto flex items-center justify-center gap-2 h-[42px] px-4 py-2 text-sm font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-md"
              >
                <Download2 class="w-4 h-4" />
                <span class="whitespace-nowrap">Summary</span>
              </button>
              
              <button 
                @click="() => router.get(route('logtime.list'))" 
                class="w-full sm:w-auto flex items-center justify-center gap-2 h-[42px] px-4 py-2 text-sm font-bold rounded-lg bg-gray-100 dark:bg-slate-700/50 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-600/50 transition-all border border-gray-200 dark:border-slate-600/50 shadow-sm"
              >
                <Close class="w-4 h-4" />
                <span>Reset</span>
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div
      class="w-full py-6 sm:py-8 transition-all duration-700 ease-out delay-100 relative z-10"
      :class="{ 'opacity-100': isLoaded, 'translate-y-12 opacity-0': !isLoaded }"
    >
      <div class="mx-auto max-w-[100rem] sm:px-6 lg:px-0">
        
        <div class="flex flex-col items-start relative">
            
            <div class="relative z-10 -mb-[1px]">
                <div class="w-fit px-6 h-12 bg-white/40 dark:bg-slate-900/60 backdrop-blur-xl border-t border-l border-r border-white/40 dark:border-white/20 rounded-t-lg shadow-sm relative flex items-center gap-3">
                    <Clock class="w-5 h-5 text-primary-600 dark:text-primary-400 drop-shadow-sm" />
                    <span class="font-bold text-gray-800 dark:text-slate-100 text-sm tracking-wide shadow-black drop-shadow-sm">Time Logs</span>
                    <div class="absolute -bottom-[1px] left-0 right-0 h-[2px] bg-white/40 dark:bg-slate-900/80 z-20"></div>
                </div>
            </div>

            <div
            class="w-full overflow-x-auto bg-white/40 dark:bg-slate-900/40 backdrop-blur-xl border border-white/40 dark:border-white/20 shadow-xl rounded-b-lg rounded-tr-lg relative z-0 flex flex-col"
            >
            <table class="w-full min-w-[40rem] text-left dark:text-slate-200 table-auto border-collapse">
                <thead class="bg-white/50 dark:bg-slate-900/80 backdrop-blur-md border-b border-white/20 dark:border-white/10">
                <tr>
                    <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Date</th>
                    <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Issue</th>
                    <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Ticket</th>
                    <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Description</th>
                    <th class="p-5 font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Time used</th>
                    <th v-if="['other', 'co'].includes(role)" class="p-5 text-center font-semibold text-gray-600 dark:text-slate-400 text-sm uppercase tracking-wider">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-white/20 dark:divide-white/5">
                <template v-for="(group, date) in groupedLogtimes" :key="date">
                    <tr class="bg-primary-50/50 dark:bg-primary-500/10 backdrop-blur-sm border-t border-white/20 dark:border-white/10">
                      <td class="py-2 px-5 font-bold text-primary-700 dark:text-primary-400" colspan="3">{{ date }}</td>
                      <td></td>
                      <td class="py-2 px-5">
                          <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-white/50 dark:bg-slate-900/60 font-bold text-gray-800 dark:text-gray-200 border border-white/40 dark:border-white/10 shadow-sm">{{ group.totalTime }} h</span>
                      </td>
                      <td v-if="['other', 'co'].includes(role)" class="py-2 px-5"></td>
                    </tr>
                    <tr v-for="value in group.items" :key="value.id" class="border-t border-white/20 dark:border-white/5 hover:bg-white/30 dark:hover:bg-white/5 transition duration-200">
                    <td class="p-5 align-middle text-sm text-gray-500 dark:text-slate-400">{{ formatDate(value.date) }}</td>
                    <td class="p-5 align-middle">
                        <a :href="route('task.show', value.task.id)" class="text-sm font-bold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 hover:underline decoration-primary-300 underline-offset-2 transition">
                        {{ value.task.issue }}
                        </a>
                    </td>
                    <td class="p-5 align-middle">
                        <a :href="'//' + value.task.ticket_link" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline truncate block max-w-[200px]">
                        {{ value.task.ticket_link }}
                        </a>
                    </td>
                    <td class="p-5 align-middle max-w-[200px]">
                      <div class="truncate text-sm text-gray-600 dark:text-slate-400" :title="value.description">
                        {{ value.description || '-' }}
                      </div>
                    </td>
                    <td class="p-5 align-middle text-sm text-gray-700 dark:text-slate-200 font-medium">{{ parseFloat(value.time_used) }} h</td>
                    <td v-if="['other', 'co'].includes(role)" class="p-5 align-middle">
                      <div class="flex justify-center gap-4">
                        <button
                          @click="openDetailLogtime(value)"
                          class="p-1.5 rounded-lg text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition tooltip-trigger" title="Detail"
                        >
                          <Detail class="w-5 h-5" />
                        </button>
                        <button 
                          @click.prevent="openDeleteModal(value.id)" 
                          class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition tooltip-trigger" title="Delete"
                        >
                          <Trash class="w-5 h-5" />
                        </button>
                      </div>
                    </td>
                    </tr>
                </template>
                <tr v-if="!logtimes?.data?.length">
                    <td colspan="6" class="p-12 text-center text-gray-400 dark:text-gray-500 italic">No logtimes found.</td>
                </tr>
                </tbody>
            </table>
            </div>
            
            <div class="mt-6 flex justify-end w-full">
                <Pagination v-if="logtimes?.links" :links="logtimes.links" />
            </div>

        </div>

      </div>
    </div>

  <Modal :show="showDetailLogtime" @close="closeDetailLogtime" max-width="2xl">
    <div class="p-8 bg-white/90 dark:bg-slate-900/95 backdrop-blur-xl border border-white/50 dark:border-white/10 rounded-lg shadow-2xl relative"> 
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 drop-shadow-sm">
                Logtime Details
            </h2>
        </div>

        <div class="space-y-4">
            <div class="border-b border-gray-200/50 dark:border-white/10 pb-3">
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1 tracking-wider">Date</label>
                <p class="text-gray-800 dark:text-slate-200 font-medium">{{ formatDate(selectedLogtime?.date) }}</p>
            </div>
            
            <div class="border-b border-gray-200/50 dark:border-white/10 pb-3">
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1 tracking-wider">Ticket Link</label>
                <a :href="'//' + selectedLogtime?.task.ticket_link" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline break-all font-medium">
                    {{ selectedLogtime?.task.ticket_link }}
                </a>
            </div>
            
            <div class="border-b border-gray-200/50 dark:border-white/10 pb-3">
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1 tracking-wider">Time Used</label>
                <p class="text-gray-800 dark:text-slate-200 font-medium">{{ selectedLogtime?.time_used }} h</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1 tracking-wider">Description</label>
                <div class="p-4 rounded-lg bg-white/50 dark:bg-slate-800/40 border border-white/40 dark:border-white/10 text-gray-700 dark:text-slate-300 text-sm whitespace-pre-wrap leading-relaxed shadow-inner max-h-[250px] overflow-y-auto custom-scrollbar">
                    {{ selectedLogtime?.description || 'No description provided.' }}
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button 
                @click="closeDetailLogtime"
                class="px-4 py-2 bg-gray-100/50 dark:bg-slate-700/50 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-200/50 dark:hover:bg-slate-600/50 border border-gray-200/50 dark:border-slate-600/50 backdrop-blur-sm transition shadow-sm font-bold text-sm"
            >
                Close
            </button>
        </div>
    </div>
  </Modal>

  <DeleteConfirmationModal 
    :show="confirmDeleteModal"
    title="Delete Log Time"
    message="Are you sure want to delete this logtime?"
    @close="closeDeleteModal"
    @confirm="handleConfirmDelete"
  />
  </div> 
</template>

<style scoped>
:deep(.dp__input) {
  background-color: #ffffff;
  backdrop-filter: blur(8px);
  border-color: rgba(255, 255, 255, 0.5);
  border-radius: 0.5rem;
  height: 42px;
  font-size: 0.875rem;
  font-weight: 500;
}

.custom-scrollbar {
  scrollbar-gutter: stable;
  scrollbar-width: thin;
}
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
  display: block;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5); /* Abu-abu di Light Mode */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.8);
}

/* Dark Mode scrollbar */
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1);
}
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.2);
}
</style>