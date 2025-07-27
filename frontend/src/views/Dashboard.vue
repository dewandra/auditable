<template>
  <div>
    <h1>Dashboard</h1>
    <p>Selamat datang! Anda berhasil login.</p>
    <button @click="logout">Logout</button>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const logout = () => {
  localStorage.removeItem('token'); 
  router.push({ name: 'Login' }); 
};
</script>