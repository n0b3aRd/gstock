<script setup>
import {computed, getCurrentInstance as instance} from "vue";
import {Head, Link, useForm, usePage} from "@inertiajs/inertia-vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import TableEditButton from "@/Components/TableEditButton.vue";
import TableDeleteButton from "@/Components/TableDeleteButton.vue";
import TableFooter from "@/Components/TableFooter.vue";

const {proxy} = instance();

proxy.$appState.parentSelection = null;
proxy.$appState.elementName = "inventory";

defineProps(['inventories', 'categories'])

function viewQty(inventory) {
  if (inventory.qty > inventory.reorder_point) {
    return `<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">${inventory.qty}</span>`
  }
  return `<span class="px-2 py-1 font-semibold leading-tight text-red-600 bg-red-100 rounded-full dark:bg-red-600 dark:text-red-100">${inventory.qty}</span>`
}

const form = useForm({
  code: route().params?.code,
  name: route().params?.name,
  category_id: route().params?.category_id,
})

function submit() {
  form.get('/inventory')
}

</script>

<template>
  <Head title="Inventory"/>

  <DashboardLayout>
    <div class="container grid px-6 mx-auto">
      <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Inventory</h2>
      <div class="flex justify-end px-4 py-0">
        <div>
          <SecondaryLink :href="route('product-category.index')">Manage Categories</SecondaryLink>
        </div>
        <div>
          <SecondaryLink :href="route('grn.create')">New GNR</SecondaryLink>
        </div>
        <div>
          <PrimaryLink :href="route('inventory.create')">New Item</PrimaryLink>
        </div>
      </div>

      <h6 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Search Data</h6>
      <div class="px-4 py-3 mb-4 bg-white rounded-lg dark:bg-gray-800">
        <form @submit.prevent="submit">
        <div class="grid gap-6 mb-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="">
            <label class="block text-sm">
              <span class="text-gray-700 dark:text-gray-400">Code</span>
              <input
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  v-model="form.code"
                  placeholder="Code">
            </label>
          </div>
          <div class="">
            <label class="block text-sm">
              <span class="text-gray-700 dark:text-gray-400">Name</span>
              <input
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  v-model="form.name"
                  placeholder="Name">
            </label>
          </div>
          <div class="">
            <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                  Category
                </span>
              <select class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                      v-model="form.category_id"
                      name="category_id"
              >
                <option value="">All Category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </label>
          </div>
          <div class="">
            <button
                class="px-4 mt-6 py-2 text-sm font-medium block w-full leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"
                type="submit"
            >
              Search
            </button>
          </div>
        </div>
        </form>
      </div>
      <!-- With actions -->
      <div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
          <table class="w-full whitespace-no-wrap">
            <thead>
            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
              <th class="px-4 py-3">Product</th>
              <th class="px-4 py-3">Category</th>
              <th class="px-4 py-3">Quantity</th>
              <th class="px-4 py-3">Reorder Point</th>
              <th class="px-4 py-3 text-center">Amount</th>
              <th class="px-4 py-3 text-center">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
            <tr v-for="inventory in inventories.data" class="text-gray-700 dark:text-gray-400">
              <td class="px-4 py-3">
                <div class="flex items-center text-sm">
                  <!-- Avatar with inset shadow -->
                  <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                    <div class="object-cover w-full h-full rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-bold"><span>{{ inventory.name.charAt(0)}}</span></div>
                    <div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true"></div>
                  </div>
                  <div>
                    <p class="font-semibold">{{ inventory.name }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ inventory.code }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">{{ inventory.category }}</td>
              <td class="px-4 py-3 text-xs" v-html="viewQty(inventory)"></td>
              <td class="px-4 py-3 text-sm">{{ inventory.reorder_point }}</td>
              <td class="px-4 py-3 text-sm sm:text-right">Rs {{ new Intl.NumberFormat('en-US').format(parseFloat(inventory.price).toFixed(2)) }}</td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center space-x-4 text-sm">
                  <TableEditButton :href="route('inventory.edit', inventory.id)"></TableEditButton>
                  <TableDeleteButton :url="route('inventory.destroy', inventory.id)"></TableDeleteButton>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <TableFooter :meta="inventories.meta"></TableFooter>
      </div>
    </div>
  </DashboardLayout>
</template>
