import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';
import { useItemStore } from './item';

export const useTransactionStore = defineStore('transaction', () => {
  const transactions = ref([]);
  const loading = ref(false);
  const itemStore = useItemStore();

  async function fetchTransactions() {
    loading.value = true;
    try {
      const response = await apiClient.get('/item-transactions');
      transactions.value = response.data.data;
    } catch (error) {
      console.error("Gagal mengambil data transaksi:", error);
    } finally {
      loading.value = false;
    }
  }
  
async function createTransaction(transactionData) {
    try {
      // PENTING: Jangan set header manual. Biarkan browser yang melakukannya.
      const response = await apiClient.post('/item-transactions', transactionData);
      
      transactions.value.unshift(response.data.data);
      await itemStore.fetchItems();
    } catch (error) {
      console.error("Gagal membuat transaksi:", error);
      throw error;
    }
  }


  return { transactions, loading, fetchTransactions, createTransaction };
});