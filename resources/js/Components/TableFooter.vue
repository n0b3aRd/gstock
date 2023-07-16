<script setup>
import { Link } from "@inertiajs/inertia-vue3";

defineProps(["meta"]);

function getClass(active) {
  if (active) {
    return "px-3 py-1 text-white transition-colors duration-150 bg-purple-600 border border-r-0 border-purple-600 rounded-md focus:outline-none focus:shadow-outline-purple";
  } else {
    return "px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple";
  }
}
</script>

<template>
  <div class="grid px-4 py-5 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
                <span class="flex items-center col-span-3">
                  Showing {{ meta.from }}-{{ meta.to }} of {{ meta.total }}
                </span>
    <span class="col-span-2"></span>
    <!-- Pagination -->
    <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
                  <nav aria-label="Table navigation">
                    <ul class="inline-flex items-center">
                      <li v-for="link in meta.links">
                        <Link :href="link.url" :class="getClass(link.active)" v-html="link.label"></Link>
                      </li>
                    </ul>
                  </nav>
                </span>
  </div>
</template>
