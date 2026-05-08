<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { TransitionRoot } from '@headlessui/vue'

const page = usePage()

const message = ref('')
const type = ref('success')
const show = ref(false)
const importErrors = ref([])
let timeout = null

const checkFlash = () => {
  const flash = page.props.flash || {}

  importErrors.value = []
  message.value = ''
  type.value = 'success'

  if (flash.error) {
    message.value = flash.error
    type.value = 'error'

    if (flash.import_errors && Array.isArray(flash.import_errors)) {
      importErrors.value = flash.import_errors
    }
  } else if (flash.warning) {
    message.value = flash.warning
    type.value = 'warning'
  } else if (flash.info) {
    message.value = flash.info
    type.value = 'info'
  } else if (flash.success) {
    message.value = flash.success
    type.value = 'success'
  } else {
    return
  }

  show.value = true

  if (timeout) clearTimeout(timeout)
  timeout = setTimeout(() => {
    show.value = false
  }, 6000)
}

onMounted(checkFlash)
watch(() => page.props.flash, checkFlash, { deep: true })
</script>

<template>
  <TransitionRoot
    :show="show"
    enter="transition ease-out duration-300 transform"
    enterFrom="translate-y-8 opacity-0"
    enterTo="translate-y-0 opacity-100"
    leave="transition ease-in duration-200 transform"
    leaveFrom="translate-y-0 opacity-100"
    leaveTo="translate-y-8 opacity-0"
    class="fixed bottom-6 right-6 z-[9999] pointer-events-none"
  >
    <div
      class="max-w-sm w-full bg-white dark:bg-gray-800 border-l-4 shadow-2xl rounded-r-lg px-5 py-4 flex items-center gap-4 pointer-events-auto"
      :class="{
        'border-green-500': type === 'success',
        'border-red-500': type === 'error',
        'border-amber-500': type === 'warning',
        'border-blue-500': type === 'info',
      }"
    >
      <div class="flex items-start gap-4">
        <!-- Success Icon -->
        <svg
          v-if="type === 'success'"
          class="w-6 h-6 text-green-500 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
  
        <!-- Error Icon -->
        <svg
          v-else-if="type === 'error'"
          class="w-6 h-6 text-red-500 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
  
        <!-- Warning Icon -->
        <svg
          v-else-if="type === 'warning'"
          class="w-6 h-6 text-amber-500 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
  
        <!-- Info Icon -->
        <svg
          v-else-if="type === 'info'"
          class="w-6 h-6 text-blue-500 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
  
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ message }}
          </p>
          <div v-if="importErrors.length > 0" class="mt-3 text-sm text-gray-600 dark:text-gray-400 max-h-48 overflow-y-auto">
            <p class="font-semibold mb-1">Detail kesalahan:</p>
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(error, index) in importErrors" :key="index">
                {{ error }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </TransitionRoot>
</template>