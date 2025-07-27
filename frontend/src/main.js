// src/main.js

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import './assets/css/main.css';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { useAuthStore } from './stores/auth'; // Import auth store

// Buat instance Pinia terlebih dahulu
const pinia = createPinia();

const app = createApp(App);

app.use(pinia); // Gunakan Pinia agar bisa diakses oleh store lain
app.use(router);
app.use(VueSweetalert2);

// --- LOGIKA BARU UNTUK MENGATASI REFRESH ---

// Fungsi untuk menginisialisasi aplikasi
async function initializeApp() {
  const authStore = useAuthStore(); // Dapatkan auth store

  try {
    // Jika ada token, coba ambil data user terlebih dahulu
    if (authStore.token) {
      await authStore.fetchUser();
    }
  } catch (error) {
    // Jika fetchUser gagal (misal token expired), token akan dihapus di dalam store
    console.error("Inisialisasi gagal, kemungkinan token expired.", error);
  } finally {
    // Setelah selesai mencoba fetchUser, baru mount aplikasi
    app.mount('#app');
  }
}

// Panggil fungsi inisialisasi
initializeApp();