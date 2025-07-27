<template>
  <div>
    <header class="bg-gray-800 text-white p-4">
      <nav class="container mx-auto flex justify-between items-center">
        
        <div class="flex items-center space-x-4">
          <router-link v-if="!authStore.isAuthenticated" to="/" class="hover:text-gray-300">Home</router-link>
          
          <router-link v-if="authStore.isAuthenticated" to="/dashboard" class="hover:text-gray-300">
            Dashboard
          </router-link>
          
          <router-link v-if="authStore.isAuthenticated" to="/roles" class="hover:text-gray-300">
            Roles
          </router-link>
          
          <router-link v-if="isAdmin" to="/users" class="hover:text-gray-300">
            Users
          </router-link>
        </div>

        <div class="flex items-center space-x-4">
          <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4">
            <span class="text-gray-300">Halo, {{ authStore.user?.name || 'Pengguna' }}</span>
            <button @click="handleLogout" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
              Logout
            </button>
          </div>

          <div v-else>
            <router-link to="/login" class="hover:text-gray-300">Login</router-link>
          </div>
        </div>

      </nav>
    </header>

    <main class="container mx-auto p-4">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { computed } from 'vue';

// Dapatkan akses ke store
const authStore = useAuthStore();

// Buat computed property untuk mengecek peran admin
const isAdmin = computed(() => 
  authStore.user?.roles?.some(role => role.name === 'Administrator')
);

// Fungsi untuk menangani logout
const handleLogout = () => {
  authStore.logout();
};
</script>