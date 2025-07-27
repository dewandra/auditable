// src/stores/user.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api';

export const useUserStore = defineStore('user', () => {
  const users = ref([]);

  // GET /api/users
  async function fetchUsers() {
    const response = await apiClient.get('/users');
    users.value = response.data.users;
  }

  // POST /api/users
  async function createUser(userData) {
    await apiClient.post('/users', userData);
    await fetchUsers(); // Refresh data
  }

  // PUT /api/users/{id}
  async function updateUser(userId, userData) {
    await apiClient.put(`/users/${userId}`, userData);
    await fetchUsers(); // Refresh data
  }

  // DELETE /api/users/{id}
  async function deleteUser(userId) {
    await apiClient.delete(`/users/${userId}`);
    await fetchUsers(); // Refresh data
  }

  return { users, fetchUsers, createUser, updateUser, deleteUser };
});