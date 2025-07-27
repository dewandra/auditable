// src/stores/role.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useRoleStore = defineStore('role', () => {
  const roles = ref([]);

  // Mengambil semua role dari API
  async function fetchRoles() {
    try {
      const response = await apiClient.get('/roles');
      roles.value = response.data;
    } catch (error) {
      console.error("Gagal mengambil data roles:", error);
    }
  }

  // Menambah role baru
  async function createRole(roleData) {
    try {
      const response = await apiClient.post('/roles', roleData);
      // Tambahkan role baru ke daftar tanpa fetch ulang
      roles.value.push(response.data.role);
    } catch (error) {
      console.error("Gagal membuat role:", error);
      throw error;
    }
  }

  // Mengupdate role
  async function updateRole(roleId, roleData) {
    try {
      const response = await apiClient.put(`/roles/${roleId}`, roleData);
      // Cari dan update role di dalam state
      const index = roles.value.findIndex(r => r.id === roleId);
      if (index !== -1) {
        roles.value[index] = response.data.role;
      }
    } catch (error) {
      console.error("Gagal mengupdate role:", error);
      throw error;
    }
  }

  // Menghapus role
  async function deleteRole(roleId) {
    try {
      // Hapus default role 'Administrator' dan 'User' tidak bisa dihapus
      const roleToDelete = roles.value.find(r => r.id === roleId);
      if (roleToDelete && (roleToDelete.name === 'Administrator' || roleToDelete.name === 'User')) {
          throw new Error('Default role tidak dapat dihapus.');
      }

      await apiClient.delete(`/roles/${roleId}`);
      // Hapus role dari state
      roles.value = roles.value.filter(r => r.id !== roleId);
    } catch (error) {
      console.error("Gagal menghapus role:", error);
      throw error;
    }
  }

  return { roles, fetchRoles, createRole, updateRole, deleteRole };
});