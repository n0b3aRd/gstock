<script setup>
import {onMounted, getCurrentInstance as instance, computed, ref, watch} from 'vue'
import {Head, Link, useForm, usePage} from "@inertiajs/inertia-vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import TableDeleteButton from "@/Components/TableDeleteButton.vue";
import {useToast} from "vue-toastification";

const toast = useToast();
const {proxy} = instance();

proxy.$appState.parentSelection = null;
proxy.$appState.elementName = "tnote";

const props = defineProps(['tnote', 'tnoteItems', 'products'])

let hasId = computed(() => {
  return Object.hasOwn(route().params, 'tnote')
})

const tnoteItems = ref(props.tnoteItems)

function addRow() {
  tnoteItems.value.push({
    'id': 'N'+tnoteItems.value.length,
    'product_id': null,
    'qty': 0,
    'price': 0
  })
}

function removeTnoteItem(id) {
  if (confirm('Are you sure?')) {
    const index = tnoteItems.value.findIndex((tnoteItem) => tnoteItem.id === id);
    if (index !== -1) {
      tnoteItems.value.splice(index, 1);
      toast.success("Item removed form transfer note", {
        timeout: 2000
      });
    }
  }
}

function hasDuplicateProduct() {
  const productCounts = {};

  // Iterate through the array and update the count for each product
  for (const obj of tnoteItems.value) {
    const { product_id } = obj;
    if (productCounts[product_id]) {
      productCounts[product_id]++;
    } else {
      productCounts[product_id] = 1;
    }
  }

  // Check if any product has a count greater than 1
  return Object.values(productCounts).some(count => count > 1)
}

function getUnitPrice(tnoteItem) {
  console.log(props.products)
  const index = props.products.findIndex((product) => product.id === tnoteItem.product_id);
  tnoteItem.price = props.products.at(index).price
}

function getAvailableQty(tnoteItem) {
  const index = props.products.findIndex((product) => product.id === tnoteItem.product_id);
  if (tnoteItem.qty > props.products.at(index).qty) {
    tnoteItem.qty = props.products.at(index).qty
    toast.error('This product only have '+props.products.at(index).qty+' items.')
  }
}

const grandTotal = computed(() => {
  let tot = tnoteItems.value.reduce((sum, tnoteItem) => sum + (tnoteItem.qty * tnoteItem.price), 0);
  return new Intl.NumberFormat('en-US').format(parseFloat(tot).toFixed(2))
});

const form = useForm({
  id: null,
  code: 'TN____',
  date: null,
  tnoteItems: tnoteItems,
})

function submit() {
  //check for duplicate product ids
  if (hasDuplicateProduct()) {
    toast.error('Please merge or remove duplicate products to continue')
    return false
  }

  if (hasId.value) {
    form.put('/tnote/' + form.id)
  } else {
    form.post('/tnote')
  }
}


function setFieldValues(tnote) {
  form.id = tnote.data.id
  form.code = tnote.data.code
  form.date = tnote.data.date
}

onMounted(() => {
  if (hasId.value) setFieldValues(props.tnote)
})

</script>

<template>
  <Head title="Inventory-Create"/>

  <DashboardLayout>
    <div class="container grid px-6 mx-auto">
      <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Transfer Note</h2>
      <div class="flex justify-end px-4 py-0">
        <div>
          <SecondaryLink :href="route('tnote.index')">Back to List</SecondaryLink>
        </div>
      </div>

      <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300"
          v-html="hasId ? 'Update Transfer Note' : 'Add New Transfer Note'"></h4>
      <form @submit.prevent="submit">
        <div class="px-4 py-3 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
          <div class="grid gap-6 mb-4 lg:grid-cols-2 xl:grid-cols-3">
            <div>
              <label class="block text-sm mt-2">
                <span class="text-gray-700 dark:text-gray-400">Code</span>
                <input
                    class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    v-model="form.code"
                    readonly
                >
              </label>
            </div>
            <div>
              <label class="block text-sm mt-2">
                <span class="text-gray-700 dark:text-gray-400">Date</span>
                <input
                    class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    type="date"
                    v-model="form.date"
                >
                <span class="text-xs text-red-600 dark:text-red-400" v-if="form.errors.date">{{
                    form.errors.date
                  }}</span>
              </label>
            </div>
            <div>
              <label class="block text-sm mt-2">
                <span class="text-gray-700 dark:text-gray-400">Total</span>
                <input
                    class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    v-model="grandTotal"
                    readonly
                >
              </label>
            </div>
          </div>
          <!--          <span class="font-semibold text-gray-600 dark:text-gray-300">GRN Products</span>-->
          <table class="w-full whitespace-no-wrap mt-8">
            <thead>
            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
              <th class="px-4 py-3 text-center" width="50%">Product</th>
              <th class="px-4 py-3 text-center">Quantity</th>
              <th class="px-4 py-3 text-center">Unit Price</th>
              <th class="px-4 py-3 text-center" width="10%">Sub Total</th>
              <th class="px-4 py-3 text-center"></th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
            <tr v-for="tnoteItem in tnoteItems" :key="tnoteItem.id" class="text-gray-700 dark:text-gray-400">
              <td class="px-4 py-3 text-sm">
                <label class="block text-sm">
                  <select
                      class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray"
                      name="category_id"
                      required
                      @change="getUnitPrice(tnoteItem)"
                      v-model="tnoteItem.product_id"
                  >
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }}
                    </option>
                  </select>
                </label>
              </td>
              <td class="px-4 py-3 text-xs">
                <label class="block text-sm">
                  <input
                      class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                      type="number"
                      min="1"
                      @change="getAvailableQty(tnoteItem)"
                      required
                      v-model="tnoteItem.qty"
                  >
                </label>
              </td>
              <td class="px-4 py-3 text-sm sm:text-right">
                {{ new Intl.NumberFormat('en-US').format(parseFloat(tnoteItem.price).toFixed(2)) }}
              </td>
              <td class="px-4 py-3 text-sm sm:text-right">
                {{ new Intl.NumberFormat('en-US').format(parseFloat(tnoteItem.qty * tnoteItem.price).toFixed(2)) }}
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center items-center space-x-4 text-sm">
                  <button @click="removeTnoteItem(tnoteItem.id)"
                          class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-red-600 rounded-lg dark:text-red-400 focus:outline-none focus:shadow-outline-gray"
                          aria-label="Delete"
                  >
                    <svg
                        class="w-5 h-5"
                        aria-hidden="true"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                      <path
                          fill-rule="evenodd"
                          d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                          clip-rule="evenodd"
                      ></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            </tbody>
            <tfoot>
            <div class="my-4 ml-4">
              <button @click="addRow()"
                      class="px-8 py-2 text-sm font-medium leading-5 text-gray-600 dark:text-white transition-colors duration-150 bg-gray-100 dark:bg-gray-700 border border-gray-700 dark:border-gray-600 rounded-lg active:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:shadow-outline-purple"
                      type="button">Add New Row
              </button>
            </div>
            </tfoot>
          </table>


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
