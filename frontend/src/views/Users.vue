<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">User Account Management</h1>

    <div class="mb-4">
      <fwb-button @click="openCreateModal">Tambah User Baru</fwb-button>
    </div>

    <fwb-table>
      <fwb-table-head>
        <fwb-table-head-cell>Nama</fwb-table-head-cell>
        <fwb-table-head-cell>Email</fwb-table-head-cell>
        <fwb-table-head-cell>Role</fwb-table-head-cell>
        <fwb-table-head-cell>
          <span class="sr-only">Actions</span>
        </fwb-table-head-cell>
      </fwb-table-head>
      <fwb-table-body>
        <fwb-table-row v-for="user in userStore.users" :key="user.id">
          <fwb-table-cell>{{ user.name }}</fwb-table-cell>
          <fwb-table-cell>{{ user.email }}</fwb-table-cell>
          <fwb-table-cell>
            {{ user.roles.map(role => role.name).join(', ') }}
          </fwb-table-cell>
          <fwb-table-cell class="space-x-2">
            <fwb-button @click="openEditModal(user)" color="yellow" size="sm">Edit</fwb-button>
            <fwb-button @click="confirmDelete(user)" color="red" size="sm">Hapus</fwb-button>
          </fwb-table-cell>
        </fwb-table-row>
      </fwb-table-body>
    </fwb-table>

    <UserModal
      :show="showUserModal"
      :user="userToEdit"
      :roles="roleStore.roles"
      @close="closeModal"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import { useUserStore } from '@/stores/user';
import { useRoleStore } from '@/stores/role'; // <-- Import role store
import UserModal from '@/components/UserModal.vue'; // <-- Import user modal
import {
  FwbButton,
  FwbTable,
  FwbTableBody,
  FwbTableCell,
  FwbTableHead,
  FwbTableHeadCell,
  FwbTableRow,
} from 'flowbite-vue';

const instance = getCurrentInstance();
const userStore = useUserStore();
const roleStore = useRoleStore();

// State untuk modal
const showUserModal = ref(false);
const userToEdit = ref(null);

onMounted(() => {
  userStore.fetchUsers();
  roleStore.fetchRoles(); // <-- Ambil data roles untuk dropdown
});

const closeModal = () => {
  showUserModal.value = false;
  userToEdit.value = null;
};

// --- Logika Tambah/Edit ---
const openCreateModal = () => {
  userToEdit.value = null;
  showUserModal.value = true;
};

const openEditModal = (user) => {
  userToEdit.value = user;
  showUserModal.value = true;
};

const handleSubmit = async (formData) => {
  const isEdit = !!userToEdit.value;
  try {
    if (isEdit) {
      // Backend Anda mengharapkan `roles` sebagai array nama
      const payload = { ...formData, roles: [formData.role] };
      delete payload.role; // Hapus properti 'role' tunggal
      await userStore.updateUser(userToEdit.value.id, payload);
    } else {
      // Backend Anda mengharapkan `roles` sebagai array nama
      const payload = { ...formData, roles: [formData.role] };
      delete payload.role;
      await userStore.createUser(payload);
    }
    closeModal();
    instance.proxy.$swal({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      icon: 'success',
      title: isEdit ? 'User berhasil diperbarui!' : 'User berhasil dibuat!'
    });
  } catch (error) {
    instance.proxy.$swal({
      icon: 'error',
      title: 'Oops...',
      text: error.response?.data?.message || 'Terjadi kesalahan!',
    });
  }
};

// --- Logika Hapus ---
const confirmDelete = (user) => {
  instance.proxy.$swal({
    title: 'Apakah Anda yakin?',
    text: `Anda akan menghapus user "${user.name}"!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await userStore.deleteUser(user.id);
        instance.proxy.$swal('Dihapus!', 'User berhasil dihapus.', 'success');
      } catch (error) {
        instance.proxy.$swal('Gagal!', error.response?.data?.error || 'Gagal menghapus user.', 'error');
      }
    }
  });
};
</script>