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
        $toast.success(successMessage);
        closeModal();
    } catch (error) {
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
        $toast.success('Item berhasil dihapus.');
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
  $swal.fire({
    title: 'Ekspor Data Item',
    text: 'Anda akan mengekspor semua data item ke dalam file Excel. Lanjutkan?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Lanjutkan!',
    cancelButtonText: 'Batal',
  }).then(async (result) => {
    if (result.isConfirmed) {
      const toastId = $toast.info('Mempersiapkan file ekspor...', { timeout: false });
      try {
        const response = await itemStore.exportItems();
        const downloadUrl = response.download_url;

        $toast.dismiss(toastId);

        const link = document.createElement('a');
        link.href = downloadUrl;
        link.setAttribute('download', 'items-export.xlsx');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        $swal.fire('Berhasil!', 'File ekspor telah berhasil diunduh.', 'success');

      } catch (error) {
        const errorMessage = error.response?.data?.message || "Gagal mengekspor data.";
        $toast.update(toastId, {
          content: errorMessage,
          options: { type: 'error', timeout: 5000 }
        });
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
        
        $toast.update(toastId, {
          content: response.message || 'File diterima! Proses impor berjalan di background.',
          options: { type: 'success', timeout: 8000 }
        });
        
        setTimeout(() => {
          itemStore.fetchItems();
          $toast.success('Data item diperbarui!');
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
