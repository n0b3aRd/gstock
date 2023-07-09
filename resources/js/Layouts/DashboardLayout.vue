<script setup>
import Sidebar from "@/Components/Sidebar.vue";
import TopBar from "@/Components/TopBar.vue";

const menu = [
  { name: "dashboard", label: "Dashboard", href: "/dashboard", icon: "bxs-dashboard" },
  { name: "inventory", label: "Inventory", href: "/inventory", icon: "bxs-bar-chart-alt-2" },
  { name: "grn", label: "GRN", href: "/grn", icon: "bx-square" },
  { name: "shop", label: "Shop", href: "/shop", icon: "bx-table" },
  { name: "tnote", label: "TNote", href: "/tnote", icon: "bx-square" },
  { name: "sales", label: "Sales", href: "/sales", icon: "bxs-bar-chart-alt-2" },
];

</script>

<template>
  <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': $appState.isSideMenuOpen }">
    <Sidebar :menu="menu" />
    <div class="flex flex-col flex-1 w-full">
      <TopBar />
      <main class="h-full overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
