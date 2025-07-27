<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Riwayat Transaksi Barang</h1>

    <div class="flex justify-between items-center mb-4">
      <fwb-button @click="openCreateModal">
        Buat Transaksi Baru
      </fwb-button>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <fwb-table>
        <fwb-table-head>
          <fwb-table-head-cell>Tanggal</fwb-table-head-cell>
          <fwb-table-head-cell>Item</fwb-table-head-cell>
          <fwb-table-head-cell>Tipe Transaksi</fwb-table-head-cell>
          <fwb-table-head-cell>Jumlah</fwb-table-head-cell>
          <fwb-table-head-cell class="text-right">Aksi</fwb-table-head-cell>
        </fwb-table-head>
        <fwb-table-body>
          <tr v-if="transactionStore.loading && transactionStore.transactions.length === 0">
            <td colspan="5" class="p-4 text-center">
              <fwb-spinner size="8" class="mr-2" /> Memuat data transaksi...
            </td>
          </tr>
          <tr v-else-if="transactionStore.transactions.length === 0">
            <td colspan="5" class="p-4 text-center text-gray-500">
              Belum ada data transaksi yang tercatat.
            </td>
          </tr>
          <fwb-table-row v-else v-for="trx in transactionStore.transactions" :key="trx.id">
            <fwb-table-cell>{{ trx.transaction_date }}</fwb-table-cell>
            <fwb-table-cell class="font-semibold">{{ trx.item?.name }}</fwb-table-cell>
            <fwb-table-cell>
              <fwb-badge :type="trx.type === 'in' ? 'green' : 'red'">
                {{ trx.type === 'in' ? 'Barang Masuk' : 'Barang Keluar' }}
              </fwb-badge>
            </fwb-table-cell>
            <fwb-table-cell>{{ trx.quantity }}</fwb-table-cell>
            <fwb-table-cell class="text-right space-x-2">
              <fwb-button @click="openHistoryModal(trx)" color="blue" size="sm">
                History
              </fwb-button>
            </fwb-table-cell>
          </fwb-table-row>
        </fwb-table-body>
      </fwb-table>
    </div>

    <AuditHistoryModal
      :show="showHistoryModal"
      :audits="transactionStore.history"
      :loading="transactionStore.loading"
      :item-name="`Transaksi: ${selectedTransactionName}`"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import {
  FwbButton, FwbTable, FwbTableBody, FwbTableCell, FwbTableHead,
  FwbTableHeadCell, FwbTableRow, FwbBadge, FwbSpinner
} from 'flowbite-vue';
// Mengimpor store dan komponen modal yang relevan
import { useTransactionStore } from '@/stores/transaction';
import AuditHistoryModal from '@/components/AuditHistoryModal.vue';

// Inisialisasi store Pinia untuk transaksi
const transactionStore = useTransactionStore();
const instance = getCurrentInstance();
const { $toast } = instance.appContext.config.globalProperties;

// State lokal untuk mengontrol tampilan modal
const showHistoryModal = ref(false);
const selectedTransactionName = ref('');

// Mengambil data transaksi saat komponen pertama kali dimuat
onMounted(() => {
  transactionStore.fetchTransactions();
});

// Fungsi untuk menutup semua modal
const closeModal = () => {
  showHistoryModal.value = false;
  selectedTransactionName.value = '';
};

/**
 * Membuka modal riwayat dan mengambil data audit untuk transaksi yang dipilih.
 * @param {object} transaction - Objek data transaksi dari baris tabel.
 */
const openHistoryModal = async (transaction) => {
  // Menyiapkan judul untuk modal
  selectedTransactionName.value = `${transaction.item_name} (${transaction.quantity} pcs)`;
  showHistoryModal.value = true;
  try {
    // Memanggil action dari store untuk mengambil data riwayat
    await transactionStore.fetchTransactionHistory(transaction.id);
  } catch (error) {
    $toast.error("Gagal memuat riwayat transaksi.");
    closeModal(); // Tutup modal jika gagal
  }
};

// Fungsi placeholder untuk tombol "Buat Transaksi Baru"
const openCreateModal = () => {
  $toast.info("Fitur untuk membuat transaksi baru belum diimplementasikan.");
};
</script>