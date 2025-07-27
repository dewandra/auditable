<template>
  <fwb-modal v-if="props.show" @close="closeModal">
    <template #header>
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
        {{ isEditMode ? 'Edit Role' : 'Tambah Role Baru' }}
      </h3>
    </template>
    <template #body>
      <form @submit.prevent="submitForm" id="roleForm" class="space-y-6">
        <div>
          <fwb-input
            v-model="form.name"
            label="Nama Role"
            placeholder="Contoh: Supervisor"
            required
          />
        </div>
        </form>
    </template>
    <template #footer>
      <div class="flex justify-end">
        <fwb-button @click="closeModal" color="alternative">
          Batal
        </fwb-button>
        <fwb-button type="submit" form="roleForm" color="green" class="ml-2">
          {{ isEditMode ? 'Simpan Perubahan' : 'Simpan' }}
        </fwb-button>
      </div>
    </template>
  </fwb-modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { FwbModal, FwbButton, FwbInput } from 'flowbite-vue';

const props = defineProps({
  show: Boolean,
  role: Object, // Menerima data role untuk mode edit
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
  name: '',
});

// Cek apakah ini mode edit atau tambah
const isEditMode = computed(() => !!props.role);

// Gunakan 'watch' untuk mengisi form saat mode edit aktif
watch(() => props.role, (newRole) => {
  if (newRole) {
    form.value.name = newRole.name;
  }
});

const closeModal = () => {
  // Reset form saat modal ditutup
  form.value.name = '';
  emit('close');
};

const submitForm = () => {
  emit('submit', form.value);
  closeModal();
};
</script>