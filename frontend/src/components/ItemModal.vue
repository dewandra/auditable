<template>
  <fwb-modal v-if="props.show" @close="closeModal" persistent size="lg">
    <template #header>
      <h3 class="text-xl font-semibold">
        {{ isEditMode ? 'Edit Item' : 'Tambah Item Baru' }}
      </h3>
    </template>
    <template #body>
      <form @submit.prevent="submitForm" id="itemForm" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <fwb-input v-model="form.name" label="Nama Item" required />
            <fwb-select v-model="form.category_id" :options="categoryOptions" label="Kategori" required />
            <fwb-textarea v-model="form.description" label="Deskripsi" :rows="4"/>
            <fwb-input v-model.number="form.quantity" type="number" label="Stok Awal" required />
            
            <div>
                <label for="item-file-upload" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                  Lampiran (PDF, 100-500 KB)
                </label>
                <input 
                  id="item-file-upload"
                  ref="fileInput" 
                  type="file" 
                  class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                  accept=".pdf"
                >
            </div>
            <a v-if="isEditMode && currentAttachmentUrl" :href="currentAttachmentUrl" target="_blank" class="text-sm text-blue-600 hover:underline">
              Lihat Lampiran Saat Ini
            </a>
            <fwb-toggle v-model="form.is_active" label="Status Aktif" class="mt-4" />
          </div>

          <div class="space-y-3">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Spesifikasi Item</label>
            <div v-for="(spec, index) in form.specs" :key="index" class="flex items-center gap-2">
              <fwb-input v-model="spec.key" placeholder="Properti (cth: Warna)" class="w-1/2"/>
              <fwb-input v-model="spec.value" placeholder="Nilai (cth: Merah)" class="w-1/2"/>
              <fwb-button @click="removeSpec(index)" color="red" square>
                <font-awesome-icon icon="trash" />
              </fwb-button>
            </div>
            <fwb-button @click="addSpec" type="button" color="light" class="w-full">
              <font-awesome-icon icon="plus" class="mr-2" />
              Tambah Spesifikasi
            </fwb-button>
          </div>
        </div>
      </form>
    </template>
    <template #footer>
      <div class="flex justify-end">
        <fwb-button @click="closeModal" color="alternative">Batal</fwb-button>
        <fwb-button type="submit" form="itemForm" color="green" class="ml-2" :loading="isSubmitting">
          {{ isEditMode ? 'Simpan Perubahan' : 'Simpan' }}
        </fwb-button>
      </div>
    </template>
  </fwb-modal>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { FwbModal, FwbButton, FwbInput, FwbTextarea, FwbToggle, FwbSelect } from 'flowbite-vue';
import { useCategoryStore } from '@/stores/category';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faTrash, faPlus } from '@fortawesome/free-solid-svg-icons';

library.add(faTrash, faPlus);

const props = defineProps({
  show: Boolean,
  item: Object,
});

const emit = defineEmits(['close', 'submit']);
const categoryStore = useCategoryStore();

const fileInput = ref(null); // Ref untuk input file

const initialFormState = {
  name: '',
  description: '',
  quantity: 0,
  category_id: '',
  is_active: true,
  specs: [],
};

const form = ref({ ...initialFormState });
const isSubmitting = ref(false);
const isEditMode = computed(() => !!props.item);
const currentAttachmentUrl = ref(null);

const categoryOptions = computed(() =>
  categoryStore.categories.map(cat => ({ value: cat.id, name: cat.name }))
);

onMounted(async () => {
  if (categoryStore.categories.length === 0) {
    await categoryStore.fetchCategories();
  }
});

const addSpec = () => {
  form.value.specs.push({ key: '', value: '' });
};

const removeSpec = (index) => {
  form.value.specs.splice(index, 1);
};

watch(() => props.item, (newItem) => {
  if (newItem) {
    form.value.name = newItem.name;
    form.value.description = newItem.description;
    form.value.quantity = newItem.quantity;
    form.value.category_id = newItem.category_id;
    form.value.is_active = newItem.is_active;
    currentAttachmentUrl.value = newItem.file_url;
    
    let specsObject = {};
    if (newItem.specs) {
      try {
        specsObject = (typeof newItem.specs === 'string') ? JSON.parse(newItem.specs) : newItem.specs;
      } catch (e) {
        specsObject = {};
      }
    }
    form.value.specs = Object.entries(specsObject).map(([key, value]) => ({ key, value }));

  } else {
    resetForm();
  }
});

const resetForm = () => {
  form.value = { ...initialFormState, specs: [] };
  currentAttachmentUrl.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const closeModal = () => {
  resetForm();
  emit('close');
};

const submitForm = async () => {
  isSubmitting.value = true;
  const formData = new FormData();
  
  const specsObject = form.value.specs.reduce((obj, item) => {
    if (item.key) {
      obj[item.key] = item.value;
    }
    return obj;
  }, {});

  formData.append('name', form.value.name);
  formData.append('category_id', form.value.category_id);
  formData.append('description', form.value.description || '');
  formData.append('quantity', form.value.quantity);
  formData.append('specs', JSON.stringify(specsObject));
  formData.append('is_active', form.value.is_active ? 1 : 0);

  // Ambil file langsung dari ref
  if (fileInput.value && fileInput.value.files[0]) {
    formData.append('file', fileInput.value.files[0]);
  }

  if (isEditMode.value) {
    formData.append('_method', 'PUT');
  }

  try {
    await emit('submit', formData);
  } finally {
    isSubmitting.value = false;
  }
};
</script>