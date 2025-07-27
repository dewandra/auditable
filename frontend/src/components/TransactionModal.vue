<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
import { useItemStore } from '../stores/item';

const props = defineProps({
  show: Boolean,
  transaction: Object,
});

const emit = defineEmits(['close', 'submit']);

const itemStore = useItemStore();
const transactionData = ref({});

// Meng-copy data props ke state lokal saat modal dibuka
watch(() => props.show, (newVal) => {
  if (newVal) {
    transactionData.value = { ...props.transaction };
    if (!itemStore.items.length) {
      itemStore.fetchItems();
    }
  }
}, { immediate: true });

const submitForm = () => {
  emit('submit', transactionData.value);
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <div class="mt-3 text-center">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Buat Transaksi Baru</h3>
        <div class="mt-2 px-7 py-3">
          <form @submit.prevent="submitForm" class="text-left">
            <div class="mb-4">
              <label for="item_id" class="block text-sm font-medium text-gray-700">Item</label>
              <select id="item_id" v-model="transactionData.item_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option disabled value="">Pilih item</option>
                <option v-for="item in itemStore.items" :key="item.id" :value="item.id">
                  {{ item.name }}
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label for="type" class="block text-sm font-medium text-gray-700">Tipe</label>
              <select id="type" v-model="transactionData.type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="in">Masuk</option>
                <option value="out">Keluar</option>
              </select>
            </div>

            <div class="mb-4">
              <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah</label>
              <input type="number" id="quantity" v-model="transactionData.quantity" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
              <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
              <textarea id="notes" v-model="transactionData.notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
            </div>
            
            <div class="items-center px-4 py-3 space-y-2">
              <button type="submit" class="w-full px-4 py-2 bg-indigo-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none">
                Simpan
              </button>
              <button type="button" @click="$emit('close')" class="w-full px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none">
                Batal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>