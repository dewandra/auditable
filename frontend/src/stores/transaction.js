import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useTransactionStore = defineStore('transaction', () => {
  const transactions = ref([]);
  const history = ref([]);
  const loading = ref(false);
  const itemOptions = ref([]);

  async function fetchTransactions() {
    loading.value = true;
    try {
      const response = await apiClient.get('/item-transactions');
      transactions.value = response.data.data || [];
    } catch (error) {
      console.error("Gagal mengambil data transaksi:", error);
      transactions.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function fetchItemOptions() {
    try {
      const response = await apiClient.get('/items'); 
      itemOptions.value = response.data.data.map(item => ({ id: item.id, name: item.name }));
    } catch (error) {
      console.error("Gagal mengambil opsi item:", error);
    }
  }

  async function fetchTransactionHistory(transactionId) {
    loading.value = true;
    history.value = [];
    try {
      const response = await apiClient.get(`/item-transactions/${transactionId}/history`);
      history.value = response.data.data;
    } catch (error) {
      console.error("Gagal mengambil data history transaksi:", error);
      history.value = [];
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function createTransaction(transactionData) {
    try {
      const response = await apiClient.post('/item-transactions', transactionData);
      transactions.value.unshift(response.data.data);
    } catch (error) {
      console.error("Gagal membuat transaksi:", error);
      throw error;
    }
  }

  // ======================================================
  // FUNGSI BARU UNTUK EKSPOR TRANSAKSI
  // ======================================================
  async function exportTransactions() {
    try {
      const response = await apiClient.post('/item-transactions/export');
      return response.data;
    } catch (error) {
      console.error("Gagal mengekspor transaksi:", error);
      throw error;
    }
  }

  return {
    transactions,
    history,
    loading,
    itemOptions,
    fetchTransactions,
    fetchItemOptions,
    fetchTransactionHistory,
    createTransaction,
    exportTransactions, // <-- Ditambahkan
  };
});