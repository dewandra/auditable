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
      // Perbaikan: Gunakan POST untuk update jika backend Anda menggunakan route resource partial
      // atau tetap PUT jika menggunakan metode standar. Sesuaikan jika perlu.
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
      const response = await apiClient.post('/categories/export');
      return response.data; // Kembalikan seluruh data respons
    } catch (error) {
      console.error("Gagal memulai ekspor kategori:", error);
      throw error;
    }
  }

  async function importCategories(formData) {
    try {
      // Kembalikan respons agar pesan sukses bisa ditampilkan
      const response = await apiClient.post('/categories/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      return response.data; // PENTING: Kembalikan respons
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
    exportCategories,
    importCategories,
  };
});