import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api'; // Pastikan path ini benar
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
  
  // Fungsi ini sekarang sangat sederhana
  // Hanya mengirim objek JavaScript (JSON)
  async function createTransaction(transactionData) {
    loading.value = true;
    try {
      const response = await apiClient.post('/item-transactions', transactionData);
      
      // Refresh data
      transactions.value.unshift(response.data.data);
      await itemStore.fetchItems();
    } catch (error) {
      console.error("Gagal membuat transaksi:", error);
      // Lemparkan error agar bisa ditangani di komponen
      throw error; 
    } finally {
      loading.value = false;
    }
  }

  return { 
    transactions, 
    loading, 
    fetchTransactions, 
    createTransaction 
  };
});