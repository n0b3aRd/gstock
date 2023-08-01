<script setup>
import Sidebar from "@/Components/Sidebar.vue";
import TopBar from "@/Components/TopBar.vue";
import { useToast } from "vue-toastification";
import {usePage} from "@inertiajs/inertia-vue3";
import { onBeforeUnmount } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const page = usePage()
const toast = useToast();

const menu = [
  { name: "dashboard", label: "Dashboard", href: "/dashboard", icon: "bxs-dashboard" },
  { name: "inventory", label: "Inventory", href: "/inventory", icon: "bx bxs-package" },
  { name: "grn", label: "GRN", href: "/grn", icon: "bx bxs-cart-add" },
  { name: "shop", label: "Shop", href: "/shop", icon: "bx bxs-store" },
  { name: "tnote", label: "TNote", href: "/tnote", icon: "bx bx-transfer" },
  { name: "sales", label: "Sales", href: "/sales", icon: "bx bx-money-withdraw" },
  { name: "suppliers", label: "Suppliers", href: "/suppliers", icon: "bx bxs-user-pin" },
];

const removeFinishedEventListener = Inertia.on('finish', () => {
  if (page.props.value.flash.message) {
    if (page.props.value.flash.message.status === 'success') {
      toast.success(page.props.value.flash.message.message, {
        timeout: 2000
      })
    } else {
      toast.error(page.props.value.flash.message.message)
    }
  }
})

onBeforeUnmount(() => removeFinishedEventListener())

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
