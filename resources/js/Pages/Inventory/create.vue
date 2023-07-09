<script setup>
import {getCurrentInstance as instance} from "vue";
import {Head, Link, useForm} from "@inertiajs/inertia-vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";

const {proxy} = instance();

proxy.$appState.parentSelection = null;
proxy.$appState.elementName = "inventory";

defineProps(['categories'])

const form = useForm({
  code: null,
  name: null,
  category_id: null,
  qty: null,
  reorder_point: null,
  price: null,
})

function submit() {
  form.post('/inventory')
}

function handleInput(e) {
  form.clearErrors(e.target.name)
}

</script>

<template>
  <Head title="Inventory-Create"/>

  <DashboardLayout>
    <div class="container grid px-6 mx-auto">
      <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Inventory</h2>
      <div class="flex justify-end px-4 py-0">
        <div>
          <Link :href="route('inventory')"
              class="px-4 py-2 ml-2 text-sm font-medium leading-5 text-gray-700 transition-colors duration-150 bg-gray-100 border border-transparent rounded-lg active:bg-gray-100 hover:bg-gray-100 focus:outline-none focus:shadow-outline-gray dark:bg-gray-700 dark:text-gray-100">
            Back to List
          </Link>
        </div>
      </div>

      <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Add New Product</h4>
      <form @submit.prevent="submit">
        <div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
          <label class="block text-sm mt-2">
            <span class="text-gray-700 dark:text-gray-400">Code</span>
            <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                   placeholder="Code"
                   v-model="form.code"
                   name="code"
                   @input="handleInput"
            >
            <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.code">{{ form.errors.code }}</span>
          </label>

          <label class="block text-sm mt-4">
            <span class="text-gray-700 dark:text-gray-400">Name</span>
            <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                   placeholder="Name"
                   v-model="form.name"
                   name="name"
                   @input="handleInput"
            >
            <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.name">{{ form.errors.name }}</span>
          </label>

          <label class="block mt-4 text-sm mt-4">
                <span class="text-gray-700 dark:text-gray-400">
                  Category
                </span>
            <select class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray"
                    v-model="form.category_id"
                    name="category_id"
            >
              <option disabled value="null">Select Category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </label>

          <label class="block text-sm mt-4">
            <span class="text-gray-700 dark:text-gray-400">Quantity</span>
            <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                   placeholder="Quantity"
                   v-model="form.qty"
                   name="qty"
                   @input="handleInput"
            >
            <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.qty">{{ form.errors.qty }}</span>
          </label>

          <label class="block text-sm mt-4">
            <span class="text-gray-700 dark:text-gray-400">Reorder Point</span>
            <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                   placeholder="Reorder Point"
                   v-model="form.reorder_point"
                   name="reorder_point"
                   @input="handleInput"
            >
            <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.reorder_point">{{ form.errors.reorder_point }}</span>
          </label>

          <label class="block text-sm mt-4 mb-4">
            <span class="text-gray-700 dark:text-gray-400">Price</span>
            <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                   placeholder="Price"
                   v-model="form.price"
                   name="price"
                   @input="handleInput"
            >
            <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.price">{{ form.errors.price }}</span>
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
