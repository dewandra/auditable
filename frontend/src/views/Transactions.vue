<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Riwayat Transaksi Item</h1>

    <div class="mb-4">
      <fwb-button @click="openCreateModal">Catat Transaksi Baru</fwb-button>
    </div>

    <fwb-table>
      <fwb-table-head>
        <fwb-table-head-cell>Tanggal</fwb-table-head-cell>
        <fwb-table-head-cell>Nama Item</fwb-table-head-cell>
        <fwb-table-head-cell>User</fwb-table-head-cell>
        <fwb-table-head-cell>Tipe</fwb-table-head-cell>
        <fwb-table-head-cell>Kuantitas</fwb-table-head-cell>
        <fwb-table-head-cell>Catatan</fwb-table-head-cell>
        <fwb-table-head-cell>Lampiran</fwb-table-head-cell>
      </fwb-table-head>
      <fwb-table-body>
        <fwb-table-row v-for="tx in transactionStore.transactions" :key="tx.id">
          <fwb-table-cell>{{ formatDateTime(tx.created_at) }}</fwb-table-cell>
          <fwb-table-cell>{{ tx.item?.name || 'Item Dihapus' }}</fwb-table-cell>
          <fwb-table-cell>{{ tx.user?.name || 'User Dihapus' }}</fwb-table-cell>
          <fwb-table-cell>
            <fwb-badge :type="tx.type === 'in' ? 'blue' : 'yellow'">
              {{ tx.type === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
            </fwb-badge>
          </fwb-table-cell>
          <fwb-table-cell>{{ tx.quantity }}</fwb-table-cell>
          <fwb-table-cell>{{ tx.notes || '-' }}</fwb-table-cell>
          <fwb-table-cell>
            <a v-if="tx.attachment_url" :href="tx.attachment_url" target="_blank" class="text-blue-600 hover:underline">
              Lihat PDF
            </a>
            <span v-else>-</span>
          </fwb-table-cell>
        </fwb-table-row>
        <fwb-table-row v-if="transactionStore.transactions.length === 0 && !transactionStore.loading">
            <fwb-table-cell :colspan="7" class="text-center">
                Belum ada riwayat transaksi.
            </fwb-table-cell>
        </fwb-table-row>
      </fwb-table-body>
    </fwb-table>

    <TransactionModal
      :show="showTransactionModal"
      @close="closeModal"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import {
  FwbButton, FwbTable, FwbTableBody, FwbTableCell, FwbTableHead,
  FwbTableHeadCell, FwbTableRow, FwbBadge,
} from 'flowbite-vue';
import { useTransactionStore } from '@/stores/transaction';
import TransactionModal from '@/components/TransactionModal.vue';

const instance = getCurrentInstance();
const transactionStore = useTransactionStore();
const showTransactionModal = ref(false);

onMounted(() => {
  transactionStore.fetchTransactions();
});

const formatDateTime = (value) => {
    if (!value) return '-';
    const date = new Date(value);
    if (isNaN(date.getTime())) return 'Format tanggal salah';
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
    return new Intl.DateTimeFormat('id-ID', options).format(date);
}

const closeModal = () => {
  showTransactionModal.value = false;
};

const openCreateModal = () => {
  showTransactionModal.value = true;
};

const handleSubmit = async (formData) => {
  try {
    await transactionStore.createTransaction(formData);
    closeModal();
    await transactionStore.fetchTransactions();
    instance.proxy.$swal({
      toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
      icon: 'success', title: 'Transaksi berhasil dicatat!',
    });
  } catch (error) {
    if (error.response && error.response.status === 422) {
      const validationErrors = error.response.data.errors;
      const errorMessages = Object.values(validationErrors)
        .map(messages => `<li>${messages.join('</li><li>')}</li>`)
        .join('');
      instance.proxy.$swal({
        icon: 'error',
        title: 'Validasi Gagal',
        html: `<ul class="text-left list-disc list-inside">${errorMessages}</ul>`,
      });
    } else {
      instance.proxy.$swal({
        icon: 'error',
        title: 'Oops...',
        text: error.response?.data?.message || 'Terjadi kesalahan saat mencatat transaksi.',
      });
    }
  }
};
</script>