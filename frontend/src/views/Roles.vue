<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Role Management</h1>

    <div class="mb-4">
      <fwb-button @click="openCreateModal">Tambah Role Baru</fwb-button>
    </div>

    <fwb-table>
      <fwb-table-head>
        <fwb-table-head-cell>Nama Role</fwb-table-head-cell>
        <fwb-table-head-cell>
          <span class="sr-only">Actions</span>
        </fwb-table-head-cell>
      </fwb-table-head>
      <fwb-table-body>
        <fwb-table-row v-for="role in roleStore.roles" :key="role.id">
          <fwb-table-cell>{{ role.name }}</fwb-table-cell>
          <fwb-table-cell class="space-x-2">
            <fwb-button @click="openEditModal(role)" color="yellow" size="sm">Edit</fwb-button>
            <fwb-button @click="confirmDelete(role)" color="red" size="sm">Hapus</fwb-button>
          </fwb-table-cell>
        </fwb-table-row>
      </fwb-table-body>
    </fwb-table>

    <RoleModal
      :show="showRoleModal"
      :role="roleToEdit"
      @close="closeModal"
      @submit="handleSubmit"
    />

    <fwb-modal v-if="showDeleteModal" @close="closeModal">
        <template #header>
            <h3 class="text-xl font-semibold">Konfirmasi Hapus</h3>
        </template>
        <template #body>
            <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                Apakah Anda yakin ingin menghapus role **"{{ roleToDelete?.name }}"**? Tindakan ini tidak dapat dibatalkan.
            </p>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <fwb-button @click="closeModal" color="alternative">
                    Batal
                </fwb-button>
                <fwb-button @click="deleteRole" color="red" class="ml-2">
                    Ya, Hapus
                </fwb-button>
            </div>
        </template>
    </fwb-modal>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import {
  FwbButton,
  FwbModal,
  FwbTable,
  FwbTableBody,
  FwbTableCell,
  FwbTableHead,
  FwbTableHeadCell,
  FwbTableRow,
} from 'flowbite-vue';
import { useRoleStore } from '@/stores/role';
import RoleModal from '@/components/RoleModal.vue';

const instance = getCurrentInstance();
const roleStore = useRoleStore();

// State untuk mengontrol modal
const showRoleModal = ref(false);
const showDeleteModal = ref(false);
const roleToEdit = ref(null);
const roleToDelete = ref(null);

onMounted(() => {
  roleStore.fetchRoles();
});

// Fungsi untuk menutup semua modal
const closeModal = () => {
  showRoleModal.value = false;
  showDeleteModal.value = false;
  roleToEdit.value = null;
  roleToDelete.value = null;
};

// --- Logika untuk Tambah/Edit ---
const openCreateModal = () => {
  roleToEdit.value = null; // Pastikan mode edit tidak aktif
  showRoleModal.value = true;
};

const openEditModal = (role) => {
  roleToEdit.value = { ...role }; // Set data untuk diedit
  showRoleModal.value = true;
};

const handleSubmit = async (formData) => {
  try {
    const isEdit = !!roleToEdit.value; // Cek apakah ini mode edit

    if (isEdit) {
      // Panggil aksi update dari store
      await roleStore.updateRole(roleToEdit.value.id, formData);
    } else {
      // Panggil aksi create dari store
      await roleStore.createRole(formData);
    }

    // Tutup modal setelah berhasil
    closeModal();

    // Tampilkan notifikasi sukses
    instance.proxy.$swal({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      icon: 'success',
      title: isEdit ? 'Data berhasil diperbarui!' : 'Data berhasil disimpan!'
    });

  } catch (error) {
    // Tampilkan notifikasi error jika gagal
    instance.proxy.$swal({
      icon: 'error',
      title: 'Oops...',
      text: error.message || 'Terjadi kesalahan saat menyimpan data.',
    });
  }
};

const confirmDelete = (role) => {
  instance.proxy.$swal({
    title: 'Apakah Anda yakin?',
    text: `Anda tidak akan bisa mengembalikan role "${role.name}"!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await roleStore.deleteRole(role.id);
        // Tampilkan notifikasi sukses
        instance.proxy.$swal(
          'Dihapus!',
          `Role "${role.name}" berhasil dihapus.`,
          'success'
        );
      } catch (error) {
        // Tampilkan notifikasi error
        instance.proxy.$swal(
          'Gagal!',
          error.message || 'Terjadi kesalahan saat menghapus role.',
          'error'
        );
      }
    }
  });
};

const deleteRole = async () => {
  if (!roleToDelete.value) return;
  try {
    await roleStore.deleteRole(roleToDelete.value.id);
  } catch(error) {
    alert(error.message || 'Gagal menghapus role');
  } finally {
    closeModal();
  }
};
</script>