<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Manajemen Item</h1>

    <div class="flex justify-between items-center mb-4">
      <fwb-button @click="openCreateModal">Tambah Item</fwb-button>
      <div class="w-1/4">
        <fwb-select
          v-model="selectedCategory"
          :options="categoryFilterOptions"
          label="Filter berdasarkan Kategori"
          @change="applyFilter"
        />
      </div>
    </div>

    <fwb-table>
      <fwb-table-head>
        <fwb-table-head-cell>Nama Item</fwb-table-head-cell>
        <fwb-table-head-cell>Kategori</fwb-table-head-cell>
        <fwb-table-head-cell>Spesifikasi</fwb-table-head-cell>
        <fwb-table-head-cell>Stok</fwb-table-head-cell>
        <fwb-table-head-cell>Lampiran</fwb-table-head-cell>
        <fwb-table-head-cell>Status</fwb-table-head-cell>
        <fwb-table-head-cell>
          <span class="sr-only">Actions</span>
        </fwb-table-head-cell>
      </fwb-table-head>
      <fwb-table-body>
        <fwb-table-row v-for="item in itemStore.items" :key="item.id">
          <fwb-table-cell>{{ item.name }}</fwb-table-cell>
          <fwb-table-cell>{{ item.category?.name || '-' }}</fwb-table-cell>
          <fwb-table-cell>
            <ul v-if="getSpecs(item.specs) && Object.keys(getSpecs(item.specs)).length" class="text-xs">
              <li v-for="(value, key) in getSpecs(item.specs)" :key="key">
                <span class="font-semibold">{{ key }}:</span> {{ value }}
              </li>
            </ul>
            <span v-else>-</span>
          </fwb-table-cell>
          <fwb-table-cell>{{ item.quantity }}</fwb-table-cell>
          <fwb-table-cell>
            <a v-if="item.file_url" :href="item.file_url" target="_blank" class="text-blue-600 hover:underline">
              Lihat PDF
            </a>
            <span v-else>-</span>
          </fwb-table-cell>
          <fwb-table-cell>
            <fwb-badge :type="item.is_active ? 'green' : 'red'">
              {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
            </fwb-badge>
          </fwb-table-cell>
          <fwb-table-cell class="space-x-2">
            <fwb-button @click="openEditModal(item)" color="yellow" size="sm">Edit</fwb-button>
            <fwb-button @click="confirmDelete(item)" color="red" size="sm">Hapus</fwb-button>
          </fwb-table-cell>
        </fwb-table-row>
        <fwb-table-row v-if="!itemStore.items.length && !itemStore.loading">
            <fwb-table-cell :colspan="7" class="text-center">
                Tidak ada data item ditemukan.
            </fwb-table-cell>
        </fwb-table-row>
      </fwb-table-body>
    </fwb-table>

    <ItemModal
      :show="showItemModal"
      :item="itemToEdit"
      @close="closeModal"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
// Bagian script tidak perlu diubah dari versi sebelumnya yang sudah benar.
import { ref, onMounted, computed, getCurrentInstance } from 'vue';
import {
  FwbButton, FwbTable, FwbTableBody, FwbTableCell, FwbTableHead,
  FwbTableHeadCell, FwbTableRow, FwbBadge, FwbSelect
} from 'flowbite-vue';
import { useItemStore } from '@/stores/item';
import { useCategoryStore } from '@/stores/category';
import ItemModal from '@/components/ItemModal.vue';

const instance = getCurrentInstance();
const itemStore = useItemStore();
const categoryStore = useCategoryStore();

const showItemModal = ref(false);
const itemToEdit = ref(null);
const selectedCategory = ref('');

const getSpecs = (specs) => {
  if (!specs) return null;
  if (typeof specs === 'object') return specs;
  try {
    return JSON.parse(specs);
  } catch (e) {
    return null;
  }
};

const categoryFilterOptions = computed(() => {
    const options = categoryStore.categories.map(cat => ({ value: cat.id, name: cat.name }));
    options.unshift({ value: '', name: 'Semua Kategori' });
    return options;
});

onMounted(() => {
  itemStore.fetchItems();
  categoryStore.fetchCategories();
});

const applyFilter = () => {
    itemStore.fetchItems({ category_id: selectedCategory.value });
};

const closeModal = () => {
  showItemModal.value = false;
  itemToEdit.value = null;
};

const openCreateModal = () => {
  itemToEdit.value = null;
  showItemModal.value = true;
};

const openEditModal = (item) => {
  itemToEdit.value = { ...item };
  showItemModal.value = true;
};

const handleSubmit = async (formData) => {
  try {
    const isEdit = !!itemToEdit.value;
    if (isEdit) {
      await itemStore.updateItem(itemToEdit.value.id, formData);
    } else {
      await itemStore.createItem(formData);
    }
    closeModal();
    await itemStore.fetchItems({ category_id: selectedCategory.value });
    instance.proxy.$swal({
      toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
      icon: 'success', title: isEdit ? 'Data berhasil diperbarui!' : 'Data berhasil disimpan!',
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
        text: 'Terjadi kesalahan saat menyimpan data.',
      });
    }
  }
};

const confirmDelete = (item) => {
  instance.proxy.$swal({
    title: 'Apakah Anda yakin?', text: `Anda akan menghapus item "${item.name}".`,
    icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal',
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await itemStore.deleteItem(item.id);
        instance.proxy.$swal('Dihapus!', 'Item berhasil dihapus.', 'success');
      } catch (error) {
        instance.proxy.$swal('Gagal!', error.message || 'Gagal menghapus item.', 'error');
      }
    }
  });
};
</script>