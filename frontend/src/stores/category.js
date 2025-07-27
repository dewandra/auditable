// frontend/src/stores/category.js

import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useCategoryStore = defineStore('category', () => {
  const categories = ref([]);
  const history = ref([]);
  const loading = ref(false);

  async function fetchCategories() {
    loading.value = true;
    try {
      const response = await apiClient.get('/categories');
      categories.value = response.data.data || [];
    } catch (error) {
      console.error("Gagal mengambil data kategori:", error);
      categories.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function fetchCategoryHistory(categoryId) {
    loading.value = true;
    history.value = [];
    try {
      const response = await apiClient.get(`/categories/${categoryId}/history`);
      history.value = response.data.data;
    } catch (error) {
      console.error("Gagal mengambil data history:", error);
      history.value = [];
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function createCategory(categoryData) {
    try {
      const response = await apiClient.post('/categories', categoryData);
      categories.value.unshift(response.data.data);
    } catch (error) {
      console.error("Gagal membuat kategori:", error);
      throw error;
    }
  }

  async function updateCategory(categoryId, categoryData) {
    try {
      const response = await apiClient.put(`/categories/${categoryId}`, categoryData);
      const index = categories.value.findIndex(c => c.id === categoryId);
      if (index !== -1) {
        categories.value[index] = response.data.data;
      }
    } catch (error) {
      console.error("Gagal mengupdate kategori:", error);
      throw error;
    }
  }

  async function deleteCategory(categoryId) {
    try {
      await apiClient.delete(`/categories/${categoryId}`);
      categories.value = categories.value.filter(c => c.id !== categoryId);
    } catch (error) {
      console.error("Gagal menghapus kategori:", error);
      throw error;
    }
  }

  async function exportCategories() { 
    try {
      // Kirim request POST tanpa body, atau dengan body kosong {}
      const response = await apiClient.post('/categories/export', {});
      return response.data.download_url;
    } catch (error) {
      console.error("Gagal memulai ekspor:", error);
      throw error;
    }
  }

  // --- ACTION BARU UNTUK IMPOR ---
  async function importCategories(formData) {
    try {
      await apiClient.post('/categories/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    } catch (error) {
      console.error("Gagal mengimpor file:", error);
      throw error;
    }
  }

  return {
    categories,
    history,
    loading,
    fetchCategories,
    fetchCategoryHistory,
    createCategory,
    updateCategory,
    deleteCategory,
    exportCategories, // <-- Daftarkan action baru
    importCategories, // <-- Daftarkan action baru
  };
});