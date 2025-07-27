import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useItemStore = defineStore('item', () => {
  const items = ref([]);
  const history = ref([]);
  const loading = ref(false);
  const categoryOptions = ref([]);

  async function fetchItems() {
    loading.value = true;
    try {
      const response = await apiClient.get('/items');
      items.value = response.data.data || [];
    } catch (error) {
      console.error("Gagal mengambil data item:", error);
      items.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function fetchCategoryOptions() {
    try {
      const response = await apiClient.get('/category-options');
      categoryOptions.value = response.data;
    } catch (error) {
      console.error("Gagal mengambil opsi kategori:", error);
    }
  }

  async function fetchItemHistory(itemId) {
    loading.value = true;
    history.value = [];
    try {
      const response = await apiClient.get(`/items/${itemId}/history`);
      history.value = response.data.data;
    } catch (error) {
      console.error("Gagal mengambil data history item:", error);
      history.value = [];
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function createItem(itemData) {
    try {
      const response = await apiClient.post('/items', itemData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      items.value.unshift(response.data.data);
      // Mengembalikan data agar bisa diakses di komponen jika perlu
      return response.data;
    } catch (error) {
      console.error("Gagal membuat item:", error);
      throw error;
    }
  }

  async function updateItem(itemId, itemData) {
    try {
      const response = await apiClient.post(`/items/${itemId}`, itemData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      const index = items.value.findIndex(i => i.id === itemId);
      if (index !== -1) {
        items.value[index] = response.data.data;
      }
      return response.data;
    } catch (error) {
      console.error("Gagal mengupdate item:", error);
      throw error;
    }
  }

  async function deleteItem(itemId) {
    try {
      const response = await apiClient.delete(`/items/${itemId}`);
      items.value = items.value.filter(i => i.id !== itemId);
      return response.data;
    } catch (error) {
      console.error("Gagal menghapus item:", error);
      throw error;
    }
  }

  // ======================================================
  // FUNGSI EKSPOR & IMPOR DIPERBAIKI
  // ======================================================
  async function exportItems() {
    try {
      // PERBAIKAN: Mengirim payload dengan 'fields' sesuai yang diharapkan backend
      // untuk menyelesaikan error 422 (Unprocessable Content).
      const payload = {
        fields: ['name', 'category', 'quantity'] // Sesuai data dari Postman
      };
      const response = await apiClient.post('/items/export', payload);
      return response.data;
    } catch (error) {
      console.error("Gagal mengekspor item:", error);
      throw error;
    }
  }

  async function importItems(formData) {
    try {
      const response = await apiClient.post('/items/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      return response.data;
    } catch (error) {
      console.error("Gagal mengimpor item:", error);
      throw error;
    }
  }

  return {
    items,
    history,
    loading,
    categoryOptions,
    fetchItems,
    fetchCategoryOptions,
    fetchItemHistory,
    createItem,
    updateItem,
    deleteItem,
    exportItems,
    importItems,
  };
});