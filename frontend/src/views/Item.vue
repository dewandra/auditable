<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Manajemen Item</h1>

    <div class="flex justify-between items-center mb-4">
      <fwb-button @click="openCreateModal">
        Tambah Item
      </fwb-button>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <fwb-table>
        <fwb-table-head>
          <fwb-table-head-cell>Nama Item</fwb-table-head-cell>
          <fwb-table-head-cell>Kategori</fwb-table-head-cell>
          <fwb-table-head-cell>Stok</fwb-table-head-cell>
          <fwb-table-head-cell>Status</fwb-table-head-cell>
          <fwb-table-head-cell class="text-right">Aksi</fwb-table-head-cell>
        </fwb-table-head>
        <fwb-table-body>
          <tr v-if="itemStore.loading && itemStore.items.length === 0">
            <td colspan="5" class="p-4 text-center">
              <fwb-spinner /> Memuat data...
            </td>
          </tr>
          <tr v-else-if="itemStore.items.length === 0">
            <td colspan="5" class="p-4 text-center text-gray-500">
              Belum ada data item.
            </td>
          </tr>
          <fwb-table-row v-else v-for="item in itemStore.items" :key="item.id">
            <fwb-table-cell class="font-semibold">{{ item.name }}</fwb-table-cell>
            <fwb-table-cell>{{ item.category?.name || 'N/A' }}</fwb-table-cell>
            <fwb-table-cell>{{ item.stock }}</fwb-table-cell>
            <fwb-table-cell>
              <fwb-badge :type="item.is_active ? 'green' : 'red'">
                {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
              </fwb-badge>
            </fwb-table-cell>
            <fwb-table-cell class="text-right space-x-2">
              <fwb-button @click="openHistoryModal(item)" color="blue" size="sm">History</fwb-button>
              <fwb-button @click="openEditModal(item)" color="yellow" size="sm">Edit</fwb-button>
              <fwb-button @click="confirmDelete(item)" color="red" size="sm">Hapus</fwb-button>
            </fwb-table-cell>
          </fwb-table-row>
        </fwb-table-body>
      </fwb-table>
    </div>
    
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
import { ref, onMounted, getCurrentInstance } from 'vue';
import {
  FwbButton, FwbTable, FwbTableBody, FwbTableCell, FwbTableHead,
  FwbTableHeadCell, FwbTableRow, FwbBadge, FwbSpinner
} from 'flowbite-vue';
import { useItemStore } from '@/stores/item';
import AuditHistoryModal from '@/components/AuditHistoryModal.vue';

// Inisialisasi store Pinia untuk item
const itemStore = useItemStore();
const instance = getCurrentInstance();
const { $toast, $swal } = instance.appContext.config.globalProperties;

// State untuk modal
const showHistoryModal = ref(false);
const selectedItemName = ref('');

// State untuk modal CRUD (bisa Anda kembangkan nanti)
const showItemModal = ref(false); 
const isEditMode = ref(false);
const itemToEdit = ref(null);

onMounted(() => {
  itemStore.fetchItems();
});

const closeModal = () => {
  showHistoryModal.value = false;
  selectedItemName.value = '';
  showItemModal.value = false;
  isEditMode.value = false;
  itemToEdit.value = null;
};

// Fungsi untuk membuka modal history
const openHistoryModal = async (item) => {
  selectedItemName.value = item.name;
  showHistoryModal.value = true;
  try {
    await itemStore.fetchItemHistory(item.id);
  } catch (error) {
    $toast.error("Gagal memuat riwayat item.");
    closeModal();
  }
};

// Fungsi placeholder untuk CRUD (bisa Anda lengkapi)
const openCreateModal = () => {
  // $toast.info("Fitur Tambah Item belum diimplementasikan.");
  isEditMode.value = false;
  itemToEdit.value = null;
  showItemModal.value = true;
};

const openEditModal = (item) => {
  // $toast.info(`Fitur Edit untuk item "${item.name}" belum diimplementasikan.`);
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
        $toast.error('Gagal menghapus item.');
      }
    }
  });
};
</script>