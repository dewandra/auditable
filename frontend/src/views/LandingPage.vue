<template>
  <div class="bg-gray-50 dark:bg-gray-900">
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
      <div class="w-full text-center p-6 space-y-4 md:space-y-6 sm:p-8">
        <div class="text-6xl mb-4">
          📦
        </div>

        <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900 md:text-5xl dark:text-white">
          Manajemen Inventaris yang Modern
        </h1>

        <p class="text-lg font-light text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
          Lacak stok, kelola pengguna, dan pantau riwayat data dengan mudah. Semua dalam satu platform yang aman, andal, dan siap membantu bisnis Anda berkembang.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-4 pt-4">
          <router-link to="/login">
            <fwb-button size="xl" class="w-full sm:w-auto">
              Masuk ke Akun Anda
            </fwb-button>
          </router-link>
          
          </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { FwbButton } from 'flowbite-vue';
</script>

<style scoped>
/* Menyesuaikan tinggi agar pas di layar, bisa disesuaikan */
.md\:h-screen {
  height: calc(100vh - 100px); /* Sesuaikan 100px dengan tinggi header Anda */
}
</style>