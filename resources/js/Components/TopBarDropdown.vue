<script setup>
import { ref, onMounted, watch, getCurrentInstance as instance } from "vue";
import { Link } from "@inertiajs/inertia-vue3";

const { proxy } = instance();

const open = ref(false);

function togglePagesMenu() {
  open.value = !open.value;
}
</script>

<template>
  <li class="relative">
    <div @click="togglePagesMenu">
      <slot name="trigger" />
    </div>
    <template v-if="open">
      <slot name="content" />
    </template>
  </li>
</template>
