<script setup>
import { getCurrentInstance as instance } from "vue";
import { Link } from "@inertiajs/inertia-vue3";
import SidebarDropdown from "./SidebarDropdown.vue";

defineProps({
  menu: {
    type: Array,
    required: true,
  },
});

const { proxy } = instance();

function closeSideMenu() {
  if (proxy.$appState.isSideMenuOpen) proxy.$appState.isSideMenuOpen = false;
}
</script>

<template>
  <aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0">
    <div class="py-4 text-gray-500 dark:text-gray-400">
      <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#"> Gstock </a>
      <ul class="mt-6">
        <template v-for="(item, index) in menu" :key="index">
          <template v-if="item.hasOwnProperty('items') && item.items.length > 0">
            <SidebarDropdown :name="item.name" :label="item.label" :icon="item.icon" :items="item.items" />
          </template>
          <template v-else>
            <li class="relative px-6 py-3">
              <span
                v-if="item.name == $appState.elementName"
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              <Link
                :href="item.href"
                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                :class="{ 'text-gray-800 dark:text-gray-100': item.name == $appState.elementName }"
              >
                <i class="bx text-xl" :class="item.icon" style="color: currentColor"></i>
                <span class="ml-4"> {{ item.label }}</span>
              </Link>
            </li>
          </template>
        </template>
      </ul>
    </div>
  </aside>
  <!-- Mobile sidebar -->
  <!-- Backdrop -->
  <transition
    enter-active-class="transition ease-in-out duration-150"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in-out duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="$appState.isSideMenuOpen"
      class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
      @click="closeSideMenu"
    ></div>
  </transition>
  <transition
    enter-active-class="transition ease-in-out duration-150"
    enter-from-class="opacity-0 transform -translate-x-20"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in-out duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0 transform -translate-x-20"
  >
    <aside
      class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
      v-if="$appState.isSideMenuOpen"
      @keydown.esc="closeSideMenu"
    >
      <div class="py-4 text-gray-500 dark:text-gray-400">
        <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#"> Windmill </a>
        <ul class="mt-6">
          <template v-for="(item, index) in menu" :key="index">
            <template v-if="item.hasOwnProperty('items') && item.items.length > 0">
              <SidebarDropdown :name="item.name" :label="item.label" :items="item.items" />
            </template>
            <template v-else>
              <li class="relative px-6 py-3">
                <span
                  v-if="item.name == $appState.elementName"
                  class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                  aria-hidden="true"
                ></span>
                <Link
                  :href="item.href"
                  class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  :class="{ 'text-gray-800 dark:text-gray-100': item.name == $appState.elementName }"
                >
                  <i class="bx text-xl" :class="item.icon" style="color: currentColor"></i>
                  <span class="ml-4">{{ item.label }}</span>
                </Link>
              </li>
            </template>
          </template>
        </ul>
      </div>
    </aside>
  </transition>
</template>
