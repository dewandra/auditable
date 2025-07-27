// src/stores/auth.js
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '../api';
import { useRouter } from 'vue-router';

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter();
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);

  if (token.value) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  const isAuthenticated = computed(() => !!token.value);

async function login(credentials) {
    try {
      const response = await apiClient.post('/auth/login', credentials);
      
      // Ambil token dan data user langsung dari respons login
      const newToken = response.data.access_token;
      const loggedInUser = response.data.user; // <-- Kita ambil data user dari sini

      if (!newToken || !loggedInUser) {
        throw new Error("Respons login tidak valid dari server.");
      }

      // Simpan token ke localStorage dan state
      localStorage.setItem('token', newToken);
      token.value = newToken;
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;

      // Langsung simpan data user ke state, tidak perlu fetch lagi
      user.value = loggedInUser;

      // Arahkan ke dashboard
      router.push({ name: 'Dashboard' });

    } catch (error) {
      console.error("Login gagal:", error);
      throw error;
    }
  }

  async function fetchUser() {
    if (!token.value) return;
    try {
        const response = await apiClient.get('/auth/user-profile');
      user.value = response.data;
      console.log("Data: ", user.value);
    } catch (error) {
      console.error("Failed to fetch user:", error);
      await logout();
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await apiClient.post('/auth/logout'); // ✅ /auth/logout
      }
    } catch (error) {
      console.error("Logout API call failed:", error);
    } finally {
      user.value = null;
      token.value = null;
      localStorage.removeItem('token');
      delete apiClient.defaults.headers.common['Authorization'];
      router.push({ name: 'Login' });
    }
  }

  return { user, token, isAuthenticated, login, logout, fetchUser };
});
