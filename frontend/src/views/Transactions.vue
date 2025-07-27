<script setup>
import { ref, onMounted } from 'vue';
import { useTransactionStore } from '../stores/transaction';
import TransactionModal from '../components/TransactionModal.vue';

const transactionStore = useTransactionStore();
const showModal = ref(false);
const newTransaction = ref({});

onMounted(() => {
  transactionStore.fetchTransactions();
});

const openCreateModal = () => {
  // Siapkan data default untuk form
  newTransaction.value = {
    item_id: '',
    type: 'in',
    quantity: 1,
    notes: ''
  };
  showModal.value = true;
};

// Fungsi ini sekarang hanya meneruskan data
const handleSubmit = async (dataFromModal) => {
  try {
    await transactionStore.createTransaction(dataFromModal);
    showModal.value = false; // Tutup modal jika sukses
  } catch (error) {
    // Tampilkan notifikasi error jika perlu
    alert('Terjadi kesalahan saat menyimpan transaksi.');
  }
};
</script>

<template>
  <div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Daftar Transaksi</h1>
      <button @click="openCreateModal" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">
        + Buat Transaksi
      </button>
    </div>
    
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
      <table class="min-w-full leading-normal">
        <thead>
          <tr>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Jumlah</th>
            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Catatan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="transactionStore.loading">
            <td colspan="5" class="text-center py-4 animate-pulse">Memuat data...</td>
          </tr>
          <tr v-for="trx in transactionStore.transactions" :key="trx.id" class="hover:bg-gray-50">
            <td class="px-5 py-4 border-b border-gray-200 text-sm">{{ trx.transaction_date }}</td>
            <td class="px-5 py-4 border-b border-gray-200 text-sm">{{ trx.item?.name }}</td>
            <td class="px-5 py-4 border-b border-gray-200 text-sm">
              <span :class="trx.type === 'in' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ trx.type === 'in' ? 'Masuk' : 'Keluar' }}
              </span>
            </td>
            <td class="px-5 py-4 border-b border-gray-200 text-sm">{{ trx.quantity }}</td>
            <td class="px-5 py-4 border-b border-gray-200 text-sm">{{ trx.notes }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <TransactionModal
      :show="showModal"
      :transaction="newTransaction"
      @close="showModal = false"
      @submit="handleSubmit"
    />
  </div>
</template>