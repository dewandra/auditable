// frontend/src/stores/category.js

import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useCategoryStore = defineStore('category', () => {
  const categories = ref([]);
  const loading = ref(false);

  async function fetchCategories() {
    loading.value = true;
    try {
      const response = await apiClient.get('/categories');
      categories.value = response.data.data;
    } catch (error) {
      console.error("Gagal mengambil data kategori:", error);
    } finally {
      loading.value = false;
    }
  }

  async function createCategory(categoryData) {
    try {
      const response = await apiClient.post('/categories', categoryData);
      categories.value.unshift(response.data.data); // Tambah di awal array
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

  return { categories, loading, fetchCategories, createCategory, updateCategory, deleteCategory };
});