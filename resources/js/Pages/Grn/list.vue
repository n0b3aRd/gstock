<script setup>
import {getCurrentInstance as instance} from "vue";
import {Head, Link} from "@inertiajs/inertia-vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import TableEditButton from "@/Components/TableEditButton.vue";
import TableDeleteButton from "@/Components/TableDeleteButton.vue";
import TableFooter from "@/Components/TableFooter.vue";

const {proxy} = instance();

proxy.$appState.parentSelection = null;
proxy.$appState.elementName = "grn";

defineProps(['grns'])
</script>

<template>
  <Head title="Inventory"/>

  <DashboardLayout>
    <div class="container grid px-6 mx-auto">
      <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Goods Received Note</h2>
      <div class="flex justify-end px-4 py-0">
        <div>
          <PrimaryLink :href="route('grn.create')">New GNR</PrimaryLink>
        </div>
      </div>

      <h6 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Search Data</h6>
      <div class="px-4 py-3 mb-4 bg-white rounded-lg dark:bg-gray-800">
        <div class="grid gap-6 mb-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="">
            <label class="block text-sm">
              <span class="text-gray-700 dark:text-gray-400">Code</span>
              <input
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="Code">
            </label>
          </div>
          <div class="">
            <label class="block text-sm">
              <span class="text-gray-700 dark:text-gray-400">Date</span>
              <input
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="Name" type="date">
            </label>
          </div>
        </div>
      </div>
      <!-- With actions -->
      <div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
          <table class="w-full whitespace-no-wrap">
            <thead>
            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
              <th class="px-4 py-3 text-center">Code</th>
              <th class="px-4 py-3 text-center">Date</th>
              <th class="px-4 py-3 text-center">Total</th>
              <th class="px-4 py-3 text-center">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
            <tr v-for="grn in grns.data" class="text-gray-700 dark:text-gray-400">
              <td class="px-4 py-3 text-sm">#{{ grn.code }}</td>
              <td class="px-4 py-3 text-xs text-center">{{ grn.date }}</td>
              <td class="px-4 py-3 text-sm sm:text-right">{{ grn.total }}</td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center space-x-4 text-sm">
                  <TableEditButton :href="route('grn.edit', grn.id)"></TableEditButton>
                  <TableDeleteButton :url="route('grn.destroy', grn.id)"></TableDeleteButton>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <TableFooter :meta="grns.meta"></TableFooter>
      </div>
    </div>
  </DashboardLayout>
</template>
