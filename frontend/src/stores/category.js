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
      // Asumsi data kategori ada di dalam `response.data.data`
      categories.value = response.data.data || [];
    } catch (error) {
      console.error("Gagal mengambil data kategori:", error);
      categories.value = []; // Pastikan tetap array jika error
    } finally {
      loading.value = false;
    }
  }

  // --- FUNGSI HISTORY YANG DIPERBARUI & LEBIH CERDAS ---
async function fetchCategoryHistory(categoryId) {
    loading.value = true;
    history.value = []; // Selalu kosongkan state sebelum fetch baru
    try {
      const response = await apiClient.get(`/categories/${categoryId}/history`);
      
      // KODE PERBAIKAN:
      // Pesan error mengkonfirmasi bahwa data yang benar ada di dalam `response.data.data`.
      // Kita langsung ambil array tersebut.
      history.value = response.data.data;

    } catch (error) {
      console.error("Gagal mengambil data history:", error);
      history.value = []; // Pastikan tetap array jika terjadi error
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

  return { 
    categories, 
    history, 
    loading, 
    fetchCategories, 
    fetchCategoryHistory, 
    createCategory, 
    updateCategory, 
    deleteCategory 
  };
});