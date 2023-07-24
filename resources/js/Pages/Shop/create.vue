<script setup>
import { onMounted, getCurrentInstance as instance, computed } from 'vue'
import {Head, Link, useForm, usePage} from "@inertiajs/inertia-vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";

const {proxy} = instance();

proxy.$appState.parentSelection = null;
proxy.$appState.elementName = "shop";

const props = defineProps(['products', 'item'])

let hasId = computed(() => {
  return Object.hasOwn(route().params, 'shop')
})

const form = useForm({
  id: null,
  product_id: null,
  qty: null,
})

function submit() {
  if (hasId.value) {
    form.put('/shop/'+form.id)
  } else {
    form.post('/shop')
  }
}

function handleInput(e) {
  form.clearErrors(e.target.name)
}

function setFieldValues(data) {
  form.id = data.id
  form.product_id = data.product_id
  form.qty = data.qty
}

onMounted(() => {
  if (hasId.value) setFieldValues(props.item)
})

</script>

<template>
  <Head title="Inventory-Create"/>

  <DashboardLayout>
    <div class="container grid px-6 mx-auto">
      <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Shop Inventory</h2>
      <div class="flex justify-end px-4 py-0">
        <div>
          <SecondaryLink :href="route('shop.index')">Back to List</SecondaryLink>
        </div>
      </div>

      <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300" v-html="hasId ? 'Update Shop Inventory' : 'Add New Shop Inventory'"></h4>
      <form @submit.prevent="submit">
        <div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
          <label class="block text-sm">
            <select
                class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray"
                name="category_id"
                required
                v-model="form.product_id"
            >
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
            <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.product_id">{{ form.errors.product_id }}</span>
          </label>
          <label class="block text-sm mt-4">
            <span class="text-gray-700 dark:text-gray-400">qty</span>
            <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                   placeholder="Quantity"
                   v-model="form.qty"
                   name="qty"
                   type="number"
                   @input="handleInput"
            >
            <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.qty">{{ form.errors.qty }}</span>
          </label>
        </div>
        <div class="flex mb-4">
          <button
              class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"
              type="submit"
          >
              Save
          </button>
          <button
              class="px-4 py-2 ml-2 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 bg-gray-100 border border-transparent rounded-lg active:bg-gray-100 hover:bg-gray-100 focus:outline-none focus:shadow-outline-gray dark:bg-gray-700 dark:text-gray-100"
            type="reset">
            Clear
          </button>
        </div>
      </form>
    </div>
  </DashboardLayout>
</template>
