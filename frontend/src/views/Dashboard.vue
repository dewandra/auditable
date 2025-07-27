<template>
  <div class="p-4 sm:p-6 md:p-8">
    <div class="mb-8">
      <h1 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white">
        Selamat Datang Kembali, {{ authStore.user?.name }}!
      </h1>
      <p class="text-lg text-gray-500 dark:text-gray-400 mt-1">
        Anda login sebagai <span class="font-semibold text-gray-600 dark:text-gray-300">{{ authStore.user?.roles[0]?.name || 'User' }}</span>.
      </p>
    </div>

    <div>
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">Akses Cepat</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <router-link to="/roles" class="block hover:scale-105 transition-transform duration-200">
          <fwb-card class="h-full">
            <div class="p-2 text-center">
              <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Kelola Role
              </h5>
              <p class="font-normal text-gray-700 dark:text-gray-400">
                Atur peran dan hak akses untuk setiap pengguna di sistem.
              </p>
            </div>
          </fwb-card>
        </router-link>

        <router-link to="/categories" class="block hover:scale-105 transition-transform duration-200">
          <fwb-card class="h-full">
            <div class="p-2 text-center">
              <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Kelola Kategori
              </h5>
              <p class="font-normal text-gray-700 dark:text-gray-400">
                Tambah, edit, atau hapus kategori.
              </p>
            </div>
          </fwb-card>
        </router-link>

        <router-link v-if="isAdmin" to="/users" class="block hover:scale-105 transition-transform duration-200">
          <fwb-card class="h-full">
            <div class="p-2 text-center">
              <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Kelola User
              </h5>
              <p class="font-normal text-gray-700 dark:text-gray-400">
                Tambah, edit, atau hapus akun pengguna. (Khusus Admin)
              </p>
            </div>
          </fwb-card>
        </router-link>

        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center h-full min-h-40">
          <p class="text-gray-400 dark:text-gray-500">Fitur selanjutnya di sini</p>
        </div>
        
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { FwbCard } from 'flowbite-vue'

const authStore = useAuthStore();

const isAdmin = computed(() =>
  authStore.user?.roles?.some(role => role.name === 'Administrator')
);
</script>