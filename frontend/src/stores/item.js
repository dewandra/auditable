// frontend/src/stores/item.js

import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useItemStore = defineStore('item', () => {
  const items = ref([]);
  const history = ref([]);
  const loading = ref(false);

  async function fetchItems() {
    loading.value = true;
    try {
      const response = await apiClient.get('/items');
      // PERBAIKAN KUNCI:
      // Kita pastikan untuk selalu mengakses `response.data.data`
      // dan jika itu tidak ada, kita berikan array kosong untuk mencegah error.
      items.value = response.data.data || [];
    } catch (error) {
      console.error("Gagal mengambil data item:", error);
      items.value = []; // Pastikan state tetap array kosong jika API error
    } finally {
      loading.value = false;
    }
  }
  
  async function fetchItemHistory(itemId) {
    loading.value = true;
    history.value = [];
    try {
      const response = await apiClient.get(`/items/${itemId}/history`);
      history.value = response.data.data;
    } catch (error)
    {
      console.error("Gagal mengambil data history item:", error);
      history.value = [];
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function createItem(itemData) {
    try {
      const response = await apiClient.post('/items', itemData);
      items.value.unshift(response.data.data);
    } catch (error) {
      console.error("Gagal membuat item:", error);
      throw error;
    }
  }

  async function updateItem(itemId, itemData) {
    try {
      const response = await apiClient.put(`/items/${itemId}`, itemData);
      const index = items.value.findIndex(i => i.id === itemId);
      if (index !== -1) {
        items.value[index] = response.data.data;
      }
    } catch (error) {
      console.error("Gagal mengupdate item:", error);
      throw error;
    }
  }

  async function deleteItem(itemId) {
    try {
      await apiClient.delete(`/items/${itemId}`);
      items.value = items.value.filter(i => i.id !== itemId);
    } catch (error) {
      console.error("Gagal menghapus item:", error);
      throw error;
    }
  }

  return {
    items,
    history,
    loading,
    fetchItems,
    fetchItemHistory,
    createItem,
    updateItem,
    deleteItem,
  };
});