<template>
  <div class="flex justify-center items-center mt-10">
    <div class="w-full max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
      <form class="space-y-4" @submit.prevent="handleLogin">
        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Sign in to our platform</h3>
        
        <fwb-input
          v-model="email"
          label="Email"
          placeholder="name@company.com"
          type="email"
          required
        />

        <fwb-input
          v-model="password"
          label="Password"
          placeholder="••••••••"
          type="password"
          required
        />
        
        <div class="flex items-center justify-between">
          <fwb-checkbox v-model="remember" label="Remember me" />
          <a href="#" class="text-sm text-blue-700 hover:underline dark:text-blue-500">Lost Password?</a>
        </div>
        
        <fwb-button type="submit" class="w-full">
          Login to your account
        </fwb-button>
        
        <div class="text-sm font-medium text-gray-500 dark:text-gray-300">
          Not registered? <router-link to="/register" class="text-blue-700 hover:underline dark:text-blue-500">Create account</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { FwbInput, FwbButton, FwbCheckbox } from 'flowbite-vue';

const email = ref('');
const password = ref('');
const remember = ref(false);

const handleLogin = () => {
  console.log('Login attempt with:', email.value, password.value);
  // Logika API akan ditambahkan di sini pada tahap berikutnya
};
</script>