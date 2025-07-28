<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Manajemen Item</h1>

    <div class="flex justify-between items-center mb-4">
      <!-- Grup Tombol Utama -->
      <div class="flex space-x-2">
        <fwb-button @click="openCreateModal">
          Tambah Item
        </fwb-button>
        <fwb-button @click="handleExport" color="green">
          Ekspor Excel
        </fwb-button>
        <fwb-button @click="triggerImport" color="blue">
          Impor Excel
        </fwb-button>
        <input
          type="file"
          ref="importInput"
          @change="handleFileImport"
          class="hidden"
          accept=".xlsx, .xls"
        />
      </div>
    </div>

    <!-- Tabel Data -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <fwb-table>
        <fwb-table-head>
          <fwb-table-head-cell>Nama Item</fwb-table-head-cell>
          <fwb-table-head-cell>Kategori</fwb-table-head-cell>
          <fwb-table-head-cell>Stok</fwb-table-head-cell>
          <fwb-table-head-cell class="text-right">Aksi</fwb-table-head-cell>
        </fwb-table-head>
        <fwb-table-body>
          <tr v-if="itemStore.loading && itemStore.items.length === 0">
            <td colspan="4" class="p-4 text-center">
              <fwb-spinner /> Memuat data...
            </td>
          </tr>
          <tr v-else-if="itemStore.items.length === 0">
            <td colspan="4" class="p-4 text-center text-gray-500">
              Belum ada data item.
            </td>
          </tr>
          <fwb-table-row v-else v-for="item in itemStore.items" :key="item.id">
            <fwb-table-cell class="font-semibold">{{ item.name }}</fwb-table-cell>
            <fwb-table-cell>{{ item.category?.name || 'N/A' }}</fwb-table-cell>
            <fwb-table-cell>{{ item.quantity }}</fwb-table-cell>
            <fwb-table-cell class="text-right space-x-2">
              <fwb-button @click="openHistoryModal(item)" color="dark" size="sm">History</fwb-button>
              <fwb-button @click="openEditModal(item)" color="yellow" size="sm">Edit</fwb-button>
              <fwb-button @click="confirmDelete(item)" color="red" size="sm">Hapus</fwb-button>
            </fwb-table-cell>
          </fwb-table-row>
        </fwb-table-body>
      </fwb-table>
    </div>

    <!-- Modal untuk Create/Update -->
    <ItemModal
      :show="showItemModal"
      :is-edit="isEditMode"
      :item="itemToEdit"
      :categories="itemStore.categoryOptions"
      @close="closeModal"
      @submit="handleSubmit"
    />

    <!-- Modal untuk History Audit -->
    <AuditHistoryModal
      :show="showHistoryModal"
      :audits="itemStore.history"
      :loading="itemStore.loading"
      :item-name="selectedItemName"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {
  FwbButton, FwbTable, FwbTableBody, FwbTableCell, FwbTableHead,
  FwbTableHeadCell, FwbTableRow, FwbSpinner
} from 'flowbite-vue';
import { useItemStore } from '@/stores/item';
import ItemModal from '@/components/ItemModal.vue';
import AuditHistoryModal from '@/components/AuditHistoryModal.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const $toast = useToast();
const $swal = Swal;

const itemStore = useItemStore();
const showItemModal = ref(false);
const showHistoryModal = ref(false);
const isEditMode = ref(false);
const itemToEdit = ref(null);
const selectedItemName = ref('');
const importInput = ref(null);

onMounted(() => {
  itemStore.fetchItems();
  itemStore.fetchCategoryOptions();
});

const closeModal = () => {
  showItemModal.value = false;
  showHistoryModal.value = false;
  itemToEdit.value = null;
  selectedItemName.value = '';
  isEditMode.value = false;
};

const handleSubmit = async (formData) => {
    const isUpdating = isEditMode.value;
    const successMessage = isUpdating ? 'Item berhasil diperbarui!' : 'Item berhasil ditambahkan!';
    try {
        if (isUpdating) {
            await itemStore.updateItem(itemToEdit.value.id, formData);
        } else {
            await itemStore.createItem(formData);
        }
        
        // Ganti toast dengan SweetAlert
        $swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: successMessage,
        });

        closeModal();
    } catch (error) {
        // Notifikasi error tetap menggunakan toast agar tidak terlalu mengganggu
        $toast.error(error.response?.data?.message || 'Terjadi kesalahan.');
    }
};

const openCreateModal = () => {
  isEditMode.value = false;
  itemToEdit.value = null;
  showItemModal.value = true;
};

const openEditModal = (item) => {
  isEditMode.value = true;
  itemToEdit.value = { ...item };
  showItemModal.value = true;
};

const confirmDelete = (item) => {
  $swal.fire({
    title: 'Anda yakin?',
    text: `Anda akan menghapus item "${item.name}".`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await itemStore.deleteItem(item.id);
        // Tampilkan notifikasi sukses dari SweetAlert
        $swal.fire(
          'Dihapus!',
          'Item berhasil dihapus.',
          'success'
        );
      } catch (error) {
        $toast.error(error.response?.data?.message || 'Gagal menghapus item.');
      }
    }
  });
};

const openHistoryModal = async (item) => {
  selectedItemName.value = item.name;
  showHistoryModal.value = true;
  await itemStore.fetchItemHistory(item.id);
};

// ========================================================================
// FUNGSI EKSPOR & IMPOR ITEMS
// ========================================================================

const handleExport = async () => {
  const fieldsToExport = ['id', 'name', 'category', 'quantity', 'is_active', 'created_at'];

  // 1. Konfirmasi awal kepada pengguna
  $swal.fire({
    title: 'Ekspor Data Item',
    text: 'Anda akan mengekspor data item ke dalam file Excel. Lanjutkan?',
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
        allowOutsideClick: false, // Mencegah klik di luar modal
        allowEscapeKey: false,    // Mencegah menutup dengan tombol Esc
        didOpen: () => {
          Swal.showLoading(); // Menampilkan ikon loading
        },
      });

      try {
        // 3. Panggil API untuk memulai proses di backend
        const response = await itemStore.exportItems({
          fields: fieldsToExport,
        });

        const downloadUrl = response.download_url;

        // 4. Tunggu 7 detik agar server selesai membuat file
        setTimeout(() => {
          // 5. Tutup SweetAlert loading
          Swal.close();

          // 6. Mulai unduhan file
          const link = document.createElement('a');
          link.href = downloadUrl;
          link.setAttribute('download', 'items-export.xlsx');
          link.style.display = 'none';

          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);

          // 7. (Opsional) Tampilkan notifikasi sukses singkat
          $swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Unduhan file ekspor telah dimulai.',
            timer: 2000, // Hilang setelah 2 detik
            showConfirmButton: false
          });

        }, 7000); // Waktu tunggu 7 detik

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

const triggerImport = () => {
  importInput.value.click();
};

const handleFileImport = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  $swal.fire({
    title: 'Impor Data Item',
    html: `Anda akan mengimpor file: <br><b>${file.name}</b><br><br>Pastikan format file sudah benar. Lanjutkan?`,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Ya, Impor!',
    cancelButtonText: 'Batal',
  }).then(async (result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('file', file);
      
      const toastId = $toast.info('Mengunggah file, mohon tunggu...', { timeout: false });
      try {
                const response = await itemStore.importItems(formData);
        
        // Hentikan toast loading dan tampilkan SweetAlert sukses
        $toast.dismiss(toastId);
        $swal.fire({
            icon: 'success',
            title: 'Impor Berhasil Dimulai!',
            text: response.message || 'File Anda telah diterima dan akan diproses di background. Data akan diperbarui secara otomatis.',
            timer: 5000,
            showConfirmButton: false,
        });
        
        // Tetap jalankan refresh data setelah beberapa saat
        setTimeout(() => {
          itemStore.fetchItems();
          $toast.info('Memperbarui daftar item...');
        }, 5000); 

      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Gagal mengunggah file.';
        $toast.update(toastId, {
          content: errorMessage,
          options: { type: 'error', timeout: 8000 }
        });
      } finally {
        importInput.value.value = '';
      }
    } else {
      importInput.value.value = '';
    }
  });
};
</script>
