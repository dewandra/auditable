<template>
  <div class="p-4 sm:p-6 md:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="mb-8">
      <h1 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white">
        Selamat Datang, {{ authStore.user?.name }}!
      </h1>
      <p class="text-lg text-gray-500 dark:text-gray-400 mt-1">
        Berikut adalah ringkasan aktivitas sistem hari ini.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
            Tren Transaksi (7 Hari Terakhir)
          </h3>
          <div class="h-80">
            <Line v-if="lineChartData.labels.length" :data="lineChartData" :options="chartOptions" />
            <div v-else class="flex items-center justify-center h-full text-gray-400">
              Data tidak cukup untuk menampilkan grafik.
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
           <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Akses Cepat</h3>
           <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <router-link to="/items" class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-blue-100 dark:hover:bg-blue-800 transition">
                  <p class="font-bold text-blue-600 dark:text-blue-400">Kelola Item</p>
              </router-link>
              <router-link to="/transactions" class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-green-100 dark:hover:bg-green-800 transition">
                  <p class="font-bold text-green-600 dark:text-green-400">Transaksi</p>
              </router-link>
              <router-link to="/categories" class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-indigo-100 dark:hover:bg-indigo-800 transition">
                  <p class="font-bold text-indigo-600 dark:text-indigo-400">Kategori</p>
              </router-link>
              <router-link v-if="isAdmin" to="/users" class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-red-100 dark:hover:bg-red-800 transition">
                  <p class="font-bold text-red-600 dark:text-red-400">Pengguna</p>
              </router-link>
              <router-link v-if="isAdmin" to="/roles" class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-yellow-100 dark:hover:bg-yellow-800 transition">
                  <p class="font-bold text-yellow-600 dark:text-yellow-400">Roles</p>
              </router-link>
           </div>
        </div>
      </div>

      <div class="lg:col-span-1 space-y-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
           <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Tipe Transaksi</h3>
           <div class="h-60">
              <Doughnut v-if="doughnutChartData.labels.length" :data="doughnutChartData" :options="chartOptions" />
           </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Aktivitas Terbaru</h3>
          <ul class="space-y-4">
            <li v-for="tx in recentTransactions" :key="tx.id" class="flex items-center space-x-3">
              <div :class="[tx.type === 'in' ? 'bg-green-500' : 'bg-red-500', 'p-2 rounded-full']">
                 <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path v-if="tx.type === 'in'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path><path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>
              </div>
              <div>
                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                      {{ tx.item?.name || 'Item Dihapus' }} <span class="font-normal">({{ tx.quantity }})</span>
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ tx.user?.name || 'User Dihapus' }} - {{ formatDate(tx.transaction_date) }}
                  </p>
              </div>
            </li>
            <li v-if="!recentTransactions.length" class="text-center text-gray-400">
                Belum ada transaksi.
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useTransactionStore } from '@/stores/transaction';

// Import Chart.js dan komponen Vue
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement } from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';

// Daftarkan komponen Chart.js
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement);

// Inisialisasi store
const authStore = useAuthStore();
const transactionStore = useTransactionStore();

const isAdmin = computed(() => authStore.user?.roles?.some(role => role.name === 'Administrator'));

// Ambil data saat komponen dimuat
onMounted(() => {
    transactionStore.fetchTransactions();
});

// Helper untuk format tanggal
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};

// --- LOGIKA UNTUK GRAFIK ---

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
};

// Data untuk Grafik Garis (Line Chart) Tren Transaksi
const lineChartData = computed(() => {
  const last7Days = {};
  for (let i = 6; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    const key = d.toISOString().slice(0, 10); // Format YYYY-MM-DD
    last7Days[key] = 0;
  }

  transactionStore.transactions.forEach(tx => {
    const txDate = tx.transaction_date.slice(0, 10);
    if (last7Days.hasOwnProperty(txDate)) {
      last7Days[txDate]++;
    }
  });

  return {
    labels: Object.keys(last7Days).map(d => formatDate(d)),
    datasets: [{
      label: 'Jumlah Transaksi',
      backgroundColor: '#3b82f6',
      borderColor: '#3b82f6',
      data: Object.values(last7Days),
      tension: 0.3,
    }]
  };
});

// Data untuk Grafik Donat (Doughnut Chart) Tipe Transaksi
const doughnutChartData = computed(() => {
    const typeCounts = transactionStore.transactions.reduce((acc, tx) => {
        acc[tx.type] = (acc[tx.type] || 0) + 1;
        return acc;
    }, { in: 0, out: 0 });

    return {
        labels: ['Barang Masuk', 'Barang Keluar'],
        datasets: [{
            backgroundColor: ['#22c55e', '#ef4444'],
            data: [typeCounts.in, typeCounts.out]
        }]
    }
});

// Data untuk Aktivitas Terbaru
const recentTransactions = computed(() => {
    // Ambil 5 transaksi terbaru
    return transactionStore.transactions.slice(0, 5);
});
</script>