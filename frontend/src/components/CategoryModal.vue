<template>
  <fwb-modal v-if="props.show" @close="closeModal" persistent>
    <template #header>
      <h3 class="text-xl font-semibold">
        {{ isEditMode ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
      </h3>
    </template>
    <template #body>
      <form @submit.prevent="submitForm" id="categoryForm" class="space-y-4">
        <fwb-input v-model="form.name" label="Nama Kategori" required />
        <fwb-textarea v-model="form.description" label="Deskripsi" />
        <fwb-toggle v-model="form.is_active" label="Status Aktif" />
      </form>
    </template>
    <template #footer>
      <div class="flex justify-end">
        <fwb-button @click="closeModal" color="alternative">Batal</fwb-button>
        <fwb-button type="submit" form="categoryForm" color="green" class="ml-2" :loading="isSubmitting">
          {{ isEditMode ? 'Simpan Perubahan' : 'Simpan' }}
        </fwb-button>
      </div>
    </template>
  </fwb-modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { FwbModal, FwbButton, FwbInput, FwbTextarea, FwbToggle } from 'flowbite-vue';

const props = defineProps({
  show: Boolean,
  category: Object,
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
  name: '',
  description: '',
  is_active: true,
});

const isSubmitting = ref(false);
const isEditMode = computed(() => !!props.category);

watch(() => props.category, (newCategory) => {
  if (newCategory) {
    form.value.name = newCategory.name;
    form.value.description = newCategory.description;
    form.value.is_active = newCategory.is_active;
  }
});

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    is_active: true,
  };
};

const closeModal = () => {
  resetForm();
  emit('close');
};

const submitForm = async () => {
  isSubmitting.value = true;
  try {
    await emit('submit', { ...form.value });
  } finally {
    isSubmitting.value = false;
  }
};
</script>