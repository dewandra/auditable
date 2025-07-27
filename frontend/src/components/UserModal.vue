<template>
  <fwb-modal v-if="props.show" @close="closeModal" persistent>
    <template #header>
      <h3 class="text-xl font-semibold">
        {{ isEditMode ? 'Edit User' : 'Tambah User Baru' }}
      </h3>
    </template>
    <template #body>
      <form @submit.prevent="submitForm" id="userForm" class="space-y-4">
        <fwb-input v-model="form.name" label="Nama Lengkap" required />
        <fwb-input v-model="form.email" label="Email" type="email" required />
        <fwb-select v-model="form.role" :options="roleOptions" label="Pilih Role" required />

        <div v-if="!isEditMode" class="space-y-4">
          <fwb-input
            v-model="form.password"
            label="Password"
            placeholder="••••••••"
            type="password"
            required
          />
          <fwb-input
            v-model="form.password_confirmation"
            label="Konfirmasi Password"
            placeholder="••••••••"
            type="password"
            required
          />
        </div>
        </form>
    </template>
    <template #footer>
      <div class="flex justify-end">
        <fwb-button @click="closeModal" color="alternative">Batal</fwb-button>
        <fwb-button type="submit" form="userForm" color="green" class="ml-2">
          {{ isEditMode ? 'Simpan Perubahan' : 'Simpan' }}
        </fwb-button>
      </div>
    </template>
  </fwb-modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { FwbModal, FwbButton, FwbInput, FwbSelect } from 'flowbite-vue';

// Props dari parent component
const props = defineProps({
  show: Boolean,
  user: Object, // Data user untuk mode edit
  roles: Array, // Daftar semua role untuk dropdown
});

const emit = defineEmits(['close', 'submit']);

// State untuk form
const form = ref({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
});

const isEditMode = computed(() => !!props.user);

// Ubah format roles agar sesuai dengan FwbSelect
const roleOptions = computed(() =>
  props.roles.map(role => ({ value: role.name, name: role.name }))
);

// Watcher untuk mengisi form saat mode edit aktif
watch(() => props.user, (newUser) => {
  if (newUser) {
    form.value.name = newUser.name;
    form.value.email = newUser.email;
    form.value.role = newUser.roles[0]?.name || ''; // Ambil role pertama
  }
});

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
  };
};

const closeModal = () => {
  resetForm();
  emit('close');
};

const submitForm = () => {
  // Hanya kirim field password jika diisi
  const payload = { ...form.value };
  if (!payload.password) {
    delete payload.password;
    delete payload.password_confirmation;
  }
  emit('submit', payload);
};
</script>