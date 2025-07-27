// frontend/src/stores/transaction.js

import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useTransactionStore = defineStore('transaction', () => {
  // --- STATE ---
  // Menyimpan daftar semua transaksi yang akan ditampilkan di tabel
  const transactions = ref([]);
  // Menyimpan riwayat audit untuk satu transaksi yang dipilih
  const history = ref([]);
  // Mengelola status loading untuk menampilkan spinner
  const loading = ref(false);

  /**
   * Mengambil semua data transaksi dari server.
   */
  async function fetchTransactions() {
    loading.value = true;
    try {
      const response = await apiClient.get('/item-transactions');
      // Mengisi state 'transactions' dengan data dari API
      transactions.value = response.data.data || [];
    } catch (error) {
      console.error("Gagal mengambil data transaksi:", error);
      transactions.value = []; // Kosongkan jika terjadi error
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Mengambil riwayat audit untuk satu transaksi spesifik berdasarkan ID.
   * @param {string} transactionId - ID dari transaksi yang akan dicek riwayatnya.
   */
  async function fetchTransactionHistory(transactionId) {
    loading.value = true;
    history.value = []; // Kosongkan riwayat lama sebelum mengambil yang baru
    try {
      const response = await apiClient.get(`/item-transactions/${transactionId}/history`);
      // Mengisi state 'history' dengan data riwayat dari API
      history.value = response.data.data;
    } catch (error)
    {
      console.error("Gagal mengambil data history transaksi:", error);
      history.value = []; // Kosongkan jika terjadi error
      throw error; // Lemparkan error agar bisa ditangani di komponen
    } finally {
      loading.value = false;
    }
  }

  // Mengembalikan semua state dan action agar bisa digunakan di komponen
  return {
    transactions,
    history,
    loading,
    fetchTransactions,
    fetchTransactionHistory,
  };
});