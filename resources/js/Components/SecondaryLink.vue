<script setup>
import { Link } from "@inertiajs/inertia-vue3";

const props = defineProps(["href"]);

</script>

<template>
  <Link :href="href" class="px-4 py-2 ml-2 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 bg-gray-100 border border-transparent rounded-lg active:bg-gray-100 hover:bg-gray-100 focus:outline-none focus:shadow-outline-gray dark:bg-gray-700 dark:text-gray-100">
    <slot />
  </Link>
</template>
