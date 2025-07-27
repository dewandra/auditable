// frontend/src/stores/item.js

import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useItemStore = defineStore('item', () => {
  const items = ref([]);
  const loading = ref(false);

  // ... fungsi fetchItems() tidak berubah ...
  async function fetchItems(filters = {}) {
    loading.value = true;
    try {
      const response = await apiClient.get('/items', { params: filters });
      items.value = response.data.data;
    } catch (error) {
      console.error("Gagal mengambil data item:", error);
    } finally {
      loading.value = false;
    }
  }

  // --- PERUBAHAN DI SINI ---
  async function createItem(itemData) {
    try {
      const response = await apiClient.post('/items', itemData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      items.value.unshift(response.data.data);
    } catch (error) {
      console.error("Gagal membuat item:", error);
      // Lempar error agar bisa ditangkap oleh komponen Vue
      throw error;
    }
  }

  // --- PERUBAHAN DI SINI ---
  async function updateItem(itemId, itemData) {
    try {
      const response = await apiClient.post(`/items/${itemId}`, itemData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      const index = items.value.findIndex(i => i.id === itemId);
      if (index !== -1) {
        items.value[index] = response.data.data;
      }
    } catch (error) {
      console.error("Gagal mengupdate item:", error);
      // Lempar error agar bisa ditangkap oleh komponen Vue
      throw error;
    }
  }

  // ... fungsi deleteItem() tidak berubah ...
  async function deleteItem(itemId) {
    try {
      await apiClient.delete(`/items/${itemId}`);
      items.value = items.value.filter(i => i.id !== itemId);
    } catch (error) {
      console.error("Gagal menghapus item:", error);
      throw error;
    }
  }

  return { items, loading, fetchItems, createItem, updateItem, deleteItem };
});