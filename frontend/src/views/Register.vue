<template>
  <div class="flex justify-center items-center mt-10">
    <div class="w-full max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
      <form class="space-y-4" @submit.prevent="handleRegister">
        <h3 class="text-xl font-medium text-gray-900 dark:text-white">Create a new account</h3>
        
        <fwb-input
          v-model="name"
          label="Your Name"
          placeholder="John Doe"
          type="text"
          required
        />

        <fwb-input
          v-model="email"
          label="Your Email"
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

        <fwb-input
          v-model="password_confirmation"
          label="Confirm Password"
          placeholder="••••••••"
          type="password"
          required
        />
        
        <fwb-button type="submit" class="w-full">
          Create Account
        </fwb-button>
        
        <div class="text-sm font-medium text-gray-500 dark:text-gray-300">
          Already have an account? <router-link to="/login" class="text-blue-700 hover:underline dark:text-blue-500">Login</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { FwbInput, FwbButton } from 'flowbite-vue';

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');

const handleRegister = () => {
  // Logika API akan ditambahkan di sini pada tahap berikutnya
  console.log('Registering with:', name.value, email.value);
};
</script>