<template>
  <div>
    <header class="bg-gray-800 text-white p-4">
      <nav class="container mx-auto flex justify-between">
        <div class="flex items-center space-x-4">
          <router-link to="/" class="hover:text-gray-300">Home</router-link>
          <router-link to="/dashboard" class="hover:text-gray-300">Dashboard</router-link>
        </div>
        <div class="flex items-center space-x-4">
          <router-link to="/login" class="hover:text-gray-300">Login</router-link>
          <router-link to="/register" class="hover:text-gray-300">Register</router-link>
        </div>
      </nav>
    </header>
    <main class="container mx-auto p-4">
      <router-view />
    </main>
  </div>
</template>

<script setup>
</script>