<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Manajemen Kategori</h1>

    <div class="flex justify-between items-center mb-4">
      <div class="flex space-x-2">
        <fwb-button @click="openCreateModal">
          Tambah Kategori
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
                <td colspan="4" class="p-4 text-center"><fwb-spinner /> Memuat data...</td>
            </tr>
            <tr v-else-if="categoryStore.categories.length === 0">
                <td colspan="4" class="p-4 text-center text-gray-500">Belum ada data kategori.</td>
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
                    <fwb-button @click="openHistoryModal(category)" color="dark" size="sm">History</fwb-button>
                    <fwb-button @click="openEditModal(category)" color="yellow" size="sm">Edit</fwb-button>
                    <fwb-button @click="confirmDelete(category)" color="red" size="sm">Hapus</fwb-button>
                </fwb-table-cell>
            </fwb-table-row>
        </fwb-table-body>
      </fwb-table>
    </div>

    <CategoryModal :show="showCategoryModal" :is-edit="isEditMode" :category="categoryToEdit" @close="closeModal" @submit="handleSubmit" />
    <AuditHistoryModal :show="showHistoryModal" :audits="categoryStore.history" :loading="categoryStore.loading" :item-name="selectedCategoryName" @close="closeModal" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { FwbButton, FwbTable, FwbTableBody, FwbTableCell, FwbTableHead, FwbTableHeadCell, FwbTableRow, FwbBadge, FwbSpinner } from 'flowbite-vue';
import { useCategoryStore } from '@/stores/category';
import CategoryModal from '@/components/CategoryModal.vue';
import AuditHistoryModal from '@/components/AuditHistoryModal.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const $toast = useToast();
const $swal = Swal;

const categoryStore = useCategoryStore();
const showCategoryModal = ref(false);
const showHistoryModal = ref(false);
const isEditMode = ref(false);
const categoryToEdit = ref(null);
const selectedCategoryName = ref('');
const importInput = ref(null);

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

// ## PERBAIKAN 1: Notifikasi SweetAlert untuk Tambah & Edit
const handleSubmit = async (formData) => {
  const isUpdating = isEditMode.value;
  const successMessage = isUpdating ? 'Kategori berhasil diperbarui!' : 'Kategori berhasil ditambahkan!';
  
  try {
    if (isUpdating) {
      await categoryStore.updateCategory(categoryToEdit.value.id, formData);
    } else {
      await categoryStore.createCategory(formData);
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

// ## PERBAIKAN 2: Notifikasi SweetAlert untuk Hapus
const confirmDelete = (category) => {
  $swal.fire({
    title: 'Anda yakin?',
    text: `Anda akan menghapus kategori "${category.name}". Aksi ini tidak dapat dibatalkan.`,
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
        // Tampilkan notifikasi sukses dari SweetAlert
        $swal.fire('Dihapus!', 'Kategori berhasil dihapus.', 'success');
      } catch (error) {
        $toast.error(error.response?.data?.message || 'Gagal menghapus kategori.');
      }
    }
  });
};

const openHistoryModal = async (category) => {
  selectedCategoryName.value = category.name;
  showHistoryModal.value = true;
  try {
    await categoryStore.fetchCategoryHistory(category.id);
  } catch (error) {
    $toast.error("Gagal memuat riwayat audit.");
    closeModal();
  }
};

// ## PERBAIKAN 3: Fungsi Ekspor dengan SweetAlert Loading
const handleExport = async () => {
  $swal.fire({
    title: 'Ekspor Data Kategori',
    text: 'Anda akan mengekspor semua data kategori ke dalam file Excel. Lanjutkan?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Lanjutkan!',
    cancelButtonText: 'Batal',
  }).then(async (result) => {
    if (result.isConfirmed) {
      $swal.fire({
        title: 'Mempersiapkan Ekspor...',
        html: 'Mohon tunggu, file sedang dibuat di server. ⏳',
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      try {
        const response = await categoryStore.exportCategories();
        const downloadUrl = response.download_url;

        setTimeout(() => {
          Swal.close();
          const link = document.createElement('a');
          link.href = downloadUrl;
          link.setAttribute('download', 'categories-export.xlsx');
          link.style.display = 'none';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);

          $swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Unduhan file ekspor telah dimulai.',
            timer: 2000,
            showConfirmButton: false
          });
        }, 5000); // Waktu tunggu 5 detik (sesuaikan jika perlu)

      } catch (error) {
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

// ## PERBAIKAN 4: Fungsi Impor dengan Alur yang Benar
const handleFileImport = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  $swal.fire({
    title: 'Impor Data Kategori',
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
        const response = await categoryStore.importCategories(formData);
        
        $toast.dismiss(toastId);
        $swal.fire({
            icon: 'success',
            title: 'Impor Berhasil Dimulai!',
            text: response.message || 'File Anda telah diterima dan akan diproses di background.',
            timer: 5000,
            showConfirmButton: false,
        });
        
        setTimeout(() => {
          categoryStore.fetchCategories();
          $toast.info('Memperbarui daftar kategori...');
        }, 5000); // Refresh data setelah 5 detik

      } catch (error) {
        $toast.dismiss(toastId);
        const errorMessage = error.response?.data?.message || 'Gagal mengunggah file.';
        $toast.error(errorMessage, { timeout: 8000 });
      } finally {
        importInput.value.value = '';
      }
    } else {
      importInput.value.value = '';
    }
  });
};
</script>