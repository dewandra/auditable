<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Manajemen Transaksi</h1>

    <div class="flex justify-between items-center mb-4">
      <div class="flex space-x-2">
        <fwb-button @click="openCreateModal">
          Tambah Transaksi
        </fwb-button>
        <fwb-button @click="handleExport" color="green">
          Ekspor Excel
        </fwb-button>
      </div>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <fwb-table>
        <fwb-table-head>
          <fwb-table-head-cell>Item</fwb-table-head-cell>
          <fwb-table-head-cell>Tipe</fwb-table-head-cell>
          <fwb-table-head-cell>Jumlah</fwb-table-head-cell>
          <fwb-table-head-cell>Tanggal</fwb-table-head-cell>
          <fwb-table-head-cell class="text-right">Aksi</fwb-table-head-cell>
        </fwb-table-head>
        <fwb-table-body>
          <tr v-if="transactionStore.loading && transactionStore.transactions.length === 0">
            <td colspan="5" class="p-4 text-center">
              <fwb-spinner /> Memuat data...
            </td>
          </tr>
          <tr v-else-if="transactionStore.transactions.length === 0">
            <td colspan="5" class="p-4 text-center text-gray-500">
              Belum ada data transaksi.
            </td>
          </tr>
          <fwb-table-row v-else v-for="transaction in transactionStore.transactions" :key="transaction.id">
            <fwb-table-cell class="font-semibold">{{ transaction.item?.name || 'N/A' }}</fwb-table-cell>
            <fwb-table-cell>
                <fwb-badge :type="transaction.type === 'in' ? 'green' : 'red'">
                    {{ transaction.type === 'in' ? 'Masuk' : 'Keluar' }}
                </fwb-badge>
            </fwb-table-cell>
            <fwb-table-cell>{{ transaction.quantity }}</fwb-table-cell>
            
            <fwb-table-cell>{{ transaction.transaction_date }}</fwb-table-cell>
            
            <fwb-table-cell class="text-right space-x-2">
              <fwb-button @click="openHistoryModal(transaction)" color="dark" size="sm">History</fwb-button>
            </fwb-table-cell>
          </fwb-table-row>
        </fwb-table-body>
      </fwb-table>
    </div>

    <TransactionModal
      :show="showTransactionModal"
      :items="transactionStore.itemOptions"
      @close="closeModal"
      @submit="handleSubmit"
    />

    <AuditHistoryModal
      :show="showHistoryModal"
      :audits="transactionStore.history"
      :loading="transactionStore.loading"
      :item-name="`Transaksi #${selectedTransactionId}`"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {
  FwbButton, FwbTable, FwbTableBody, FwbTableCell, FwbTableHead,
  FwbTableHeadCell, FwbTableRow, FwbBadge, FwbSpinner
} from 'flowbite-vue';
import { useTransactionStore } from '@/stores/transaction';
import TransactionModal from '@/components/TransactionModal.vue';
import AuditHistoryModal from '@/components/AuditHistoryModal.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const $toast = useToast();
const $swal = Swal;

// Definisikan fungsi formatDate langsung di sini untuk menghindari error
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

const transactionStore = useTransactionStore();
const showTransactionModal = ref(false);
const showHistoryModal = ref(false);
const selectedTransactionId = ref('');

onMounted(() => {
  transactionStore.fetchTransactions();
  transactionStore.fetchItemOptions();
});

const closeModal = () => {
  showTransactionModal.value = false;
  showHistoryModal.value = false;
  selectedTransactionId.value = '';
};

const openCreateModal = () => {
  showTransactionModal.value = true;
};

const handleSubmit = async (formData) => {
    try {
        await transactionStore.createTransaction(formData);
        $toast.success('Transaksi berhasil ditambahkan!');
        closeModal();
    } catch (error) {
        $toast.error(error.response?.data?.message || 'Gagal menambahkan transaksi.');
    }
};

const openHistoryModal = async (transaction) => {
  selectedTransactionId.value = transaction.id.substring(0, 8);
  showHistoryModal.value = true;
  await transactionStore.fetchTransactionHistory(transaction.id);
};

const handleExport = async () => {
  // 1. Konfirmasi awal kepada pengguna
  $swal.fire({
    title: 'Ekspor Data Transaksi',
    text: 'Anda akan mengekspor semua data transaksi ke dalam file Excel. Lanjutkan?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Lanjutkan!',
    cancelButtonText: 'Batal',
  }).then(async (result) => {
    if (result.isConfirmed) {
      // 2. Tampilkan SweetAlert "Loading" yang tidak bisa ditutup
      $swal.fire({
        title: 'Mempersiapkan Ekspor...',
        html: 'Mohon tunggu, file sedang dibuat di server. ⏳',
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading(); // Menampilkan ikon loading
        },
      });

      try {
        // 3. Panggil API untuk memulai proses di backend
        const response = await transactionStore.exportTransactions();
        const downloadUrl = response.download_url;

        // 4. Tunggu beberapa detik agar server selesai membuat file (PENTING!)
        setTimeout(() => {
          // 5. Tutup SweetAlert loading
          Swal.close();

          // 6. Mulai unduhan file
          const link = document.createElement('a');
          link.href = downloadUrl;
          link.setAttribute('download', 'transactions-export.xlsx');
          link.style.display = 'none';

          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);

          // 7. Tampilkan notifikasi sukses singkat
          $swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Unduhan file ekspor telah dimulai.',
            timer: 2000,
            showConfirmButton: false
          });

        }, 7000); // Waktu tunggu 7 detik (sesuaikan jika perlu)

      } catch (error) {
        // Jika terjadi error, tutup loading dan tampilkan pesan error
        Swal.close();
        const errorMessage = error.response?.data?.message || "Gagal mengekspor data.";
        $swal.fire({
            icon: 'error',
            title: 'Oops... Terjadi Kesalahan',
            text: errorMessage,
        });
        console.error('Export Error:', error);
      }
    }
  });
};
</script>