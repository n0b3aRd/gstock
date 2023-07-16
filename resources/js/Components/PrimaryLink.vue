<script setup>
import { Link } from "@inertiajs/inertia-vue3";

const props = defineProps(["href"]);

</script>

<template>
  <Link :href="href" class="px-4 py-2 ml-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
    <slot />
  </Link>
</template>
