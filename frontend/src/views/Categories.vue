<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Manajemen Kategori</h1>

    <div class="mb-4">
      <fwb-button @click="openCreateModal">Tambah Kategori</fwb-button>
    </div>

    <fwb-table>
      <fwb-table-head>
        <fwb-table-head-cell>Nama Kategori</fwb-table-head-cell>
        <fwb-table-head-cell>Deskripsi</fwb-table-head-cell>
        <fwb-table-head-cell>Status</fwb-table-head-cell>
        <fwb-table-head-cell>
          <span class="sr-only">Actions</span>
        </fwb-table-head-cell>
      </fwb-table-head>
      <fwb-table-body>
        <fwb-table-row v-for="category in categoryStore.categories" :key="category.id">
          <fwb-table-cell>{{ category.name }}</fwb-table-cell>
          <fwb-table-cell>{{ category.description || '-' }}</fwb-table-cell>
          <fwb-table-cell>
            <fwb-badge :type="category.is_active ? 'green' : 'red'">
              {{ category.is_active ? 'Aktif' : 'Non-Aktif' }}
            </fwb-badge>
          </fwb-table-cell>
          <fwb-table-cell class="space-x-2">
            <fwb-button @click="openEditModal(category)" color="yellow" size="sm">Edit</fwb-button>
            <fwb-button @click="confirmDelete(category)" color="red" size="sm">Hapus</fwb-button>
          </fwb-table-cell>
        </fwb-table-row>
      </fwb-table-body>
    </fwb-table>

    <CategoryModal
      :show="showCategoryModal"
      :category="categoryToEdit"
      @close="closeModal"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import {
  FwbButton,
  FwbTable,
  FwbTableBody,
  FwbTableCell,
  FwbTableHead,
  FwbTableHeadCell,
  FwbTableRow,
  FwbBadge,
} from 'flowbite-vue';
import { useCategoryStore } from '@/stores/category';
import CategoryModal from '@/components/CategoryModal.vue';

const instance = getCurrentInstance();
const categoryStore = useCategoryStore();

const showCategoryModal = ref(false);
const categoryToEdit = ref(null);

onMounted(() => {
  categoryStore.fetchCategories();
});

const closeModal = () => {
  showCategoryModal.value = false;
  categoryToEdit.value = null;
};

const openCreateModal = () => {
  categoryToEdit.value = null;
  showCategoryModal.value = true;
};

const openEditModal = (category) => {
  categoryToEdit.value = { ...category };
  showCategoryModal.value = true;
};

const handleSubmit = async (formData) => {
  try {
    const isEdit = !!categoryToEdit.value;
    if (isEdit) {
      await categoryStore.updateCategory(categoryToEdit.value.id, formData);
    } else {
      await categoryStore.createCategory(formData);
    }
    closeModal();
    instance.proxy.$swal({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      icon: 'success',
      title: isEdit ? 'Data berhasil diperbarui!' : 'Data berhasil disimpan!',
    });
  } catch (error) {
    instance.proxy.$swal({
      icon: 'error',
      title: 'Oops...',
      text: error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.',
    });
  }
};

const confirmDelete = (category) => {
  instance.proxy.$swal({
    title: 'Apakah Anda yakin?',
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
        instance.proxy.$swal('Dihapus!', 'Kategori berhasil dihapus.', 'success');
      } catch (error) {
        instance.proxy.$swal('Gagal!', error.message || 'Gagal menghapus kategori.', 'error');
      }
    }
  });
};
</script>