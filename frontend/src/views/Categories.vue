<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Manajemen Kategori</h1>

    <div class="flex justify-between items-center mb-4">
      <fwb-button @click="openCreateModal">
        Tambah Kategori
      </fwb-button>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <fwb-table>
        <fwb-table-head>
          <fwb-table-head-cell>Nama Kategori</fwb-table-head-cell>
          <fwb-table-head-cell>Deskripsi</fwb-table-head-cell>
          <fwb-table-head-cell>Status</fwb-table-head-cell>
          <fwb-table-head-cell class="text-right">Aksi</fwb-table-head-cell>
        </fwb-table-head>
        <fwb-table-body>
          <tr v-if="categoryStore.loading && categoryStore.categories.length === 0">
            <td colspan="4" class="p-4 text-center">
              <fwb-spinner /> Memuat data...
            </td>
          </tr>
          <tr v-else-if="categoryStore.categories.length === 0">
            <td colspan="4" class="p-4 text-center text-gray-500">
              Belum ada data kategori.
            </td>
          </tr>
          <fwb-table-row v-else v-for="category in categoryStore.categories" :key="category.id">
            <fwb-table-cell class="font-semibold">{{ category.name }}</fwb-table-cell>
            <fwb-table-cell>{{ category.description || '-' }}</fwb-table-cell>
            <fwb-table-cell>
              <fwb-badge :type="category.is_active ? 'green' : 'red'">
                {{ category.is_active ? 'Aktif' : 'Non-Aktif' }}
              </fwb-badge>
            </fwb-table-cell>
            <fwb-table-cell class="text-right space-x-2">
              <fwb-button @click="openHistoryModal(category)" color="blue" size="sm">History</fwb-button>
              <fwb-button @click="openEditModal(category)" color="yellow" size="sm">Edit</fwb-button>
              <fwb-button @click="confirmDelete(category)" color="red" size="sm">Hapus</fwb-button>
            </fwb-table-cell>
          </fwb-table-row>
        </fwb-table-body>
      </fwb-table>
    </div>

    <CategoryModal
      :show="showCategoryModal"
      :is-edit="isEditMode"
      :category="categoryToEdit"
      @close="closeModal"
      @submit="handleSubmit"
    />

    <AuditHistoryModal
      :show="showHistoryModal"
      :audits="categoryStore.history"
      :loading="categoryStore.loading"
      :item-name="selectedCategoryName"
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
import { useCategoryStore } from '@/stores/category';
import CategoryModal from '@/components/CategoryModal.vue';
import AuditHistoryModal from '@/components/AuditHistoryModal.vue';

const categoryStore = useCategoryStore();
const instance = getCurrentInstance();
const { $toast, $swal } = instance.appContext.config.globalProperties;

const showCategoryModal = ref(false);
const showHistoryModal = ref(false);
const isEditMode = ref(false);
const categoryToEdit = ref(null);
const selectedCategoryName = ref('');

onMounted(() => {
  categoryStore.fetchCategories();
});

const closeModal = () => {
  showCategoryModal.value = false;
  showHistoryModal.value = false;
  categoryToEdit.value = null;
  selectedCategoryName.value = '';
  isEditMode.value = false;
};

const openHistoryModal = async (category) => {
  selectedCategoryName.value = category.name;
  showHistoryModal.value = true;
  try {
    await categoryStore.fetchCategoryHistory(category.id);
  } catch (error) {
    $toast.error("Gagal memuat riwayat.");
    closeModal();
  }
};

const openCreateModal = () => {
  isEditMode.value = false;
  categoryToEdit.value = null;
  showCategoryModal.value = true;
};

const openEditModal = (category) => {
  isEditMode.value = true;
  categoryToEdit.value = { ...category };
  showCategoryModal.value = true;
};

const handleSubmit = async (formData) => {
  try {
    if (isEditMode.value) {
      await categoryStore.updateCategory(categoryToEdit.value.id, formData);
      $toast.success('Kategori berhasil diperbarui!');
    } else {
      await categoryStore.createCategory(formData);
      $toast.success('Kategori berhasil ditambahkan!');
    }
    closeModal();
  } catch (error) {
    $toast.error('Terjadi kesalahan.');
  }
};

const confirmDelete = (category) => {
  $swal.fire({
    title: 'Anda yakin?',
    text: `Anda akan menghapus kategori "${category.name}".`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await categoryStore.deleteCategory(category.id);
        $toast.success('Kategori berhasil dihapus.');
      } catch (error) {
        $toast.error('Gagal menghapus kategori.');
      }
    }
  });
};
</script>