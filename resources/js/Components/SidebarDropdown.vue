<script setup>
import { ref, onMounted, getCurrentInstance as instance } from "vue";
import { Link } from "@inertiajs/inertia-vue3";

const { proxy } = instance();

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
  items: {
    type: Array,
    required: true,
  },
});

const isPagesMenuOpen = ref(false);

function togglePagesMenu() {
  isPagesMenuOpen.value = !isPagesMenuOpen.value;
}

if (props.name == proxy.$appState.parentSelection) isPagesMenuOpen.value = true;
onMounted(() => {});
</script>

<template>
  <li class="relative px-6 py-3">
    <button
      class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
      :class="{ 'text-gray-800 dark:text-gray-200': isPagesMenuOpen }"
      @click="togglePagesMenu"
      aria-haspopup="true"
    >
      <span class="inline-flex items-center">
        <i class="bx text-xl" :class="icon" style="color: currentColor"></i>
        <span class="ml-4">{{ label }}</span>
      </span>
      <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
        <path
          fill-rule="evenodd"
          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
          clip-rule="evenodd"
        ></path>
      </svg>
    </button>
    <transition
      enter-active-class="transition-all ease-in-out duration-300"
      enter-from-class="opacity-25 max-h-0"
      enter-to-class="opacity-100 max-h-xl"
      leave-active-class="transition-all ease-in-out duration-300"
      leave-from-class="opacity-100 max-h-xl"
      leave-to-class="opacity-0 max-h-0"
    >
      <ul
        v-if="isPagesMenuOpen"
        class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
        aria-label="submenu"
      >
        <li
          v-for="(item, index) in items"
          :key="index"
          class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          :class="{ 'text-gray-800 dark:text-gray-100': item.name == $appState.elementName }"
        >
          <Link class="w-full" :href="item.href">{{ item.label }}</Link>
        </li>
      </ul>
    </transition>
  </li>
</template>
