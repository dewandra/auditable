<template>
  <fwb-modal v-if="props.show" @close="closeModal" persistent>
    <template #header>
      <h3 class="text-xl font-semibold">Catat Transaksi Baru</h3>
    </template>
    <template #body>
      <form @submit.prevent="submitForm" id="transactionForm" class="space-y-4">
        <fwb-select v-model="form.type" :options="transactionTypes" label="Tipe Transaksi" required />
        <fwb-select v-model="form.item_id" :options="itemOptions" label="Pilih Item" required />
        <fwb-input v-model.number="form.quantity" type="number" label="Kuantitas" :min="1" required />
        <div v-if="selectedItem" class="text-sm text-gray-600 p-3 bg-gray-50 rounded-lg">
          <p><strong>Stok Saat Ini:</strong> {{ selectedItem.quantity }}</p>
          <p v-if="form.type === 'out' && form.quantity > selectedItem.quantity" class="text-red-500 font-bold">
            Kuantitas melebihi stok yang tersedia!
          </p>
        </div>
        <fwb-textarea v-model="form.notes" label="Catatan (Opsional)" :rows="3" />

        <div>
          <label for="attachment-upload" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Lampiran (Opsional, PDF, 100-500 KB)
          </label>
          <input 
            id="attachment-upload"
            ref="fileInput" 
            type="file" 
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
            accept=".pdf"
          >
        </div>
      </form>
    </template>
    <template #footer>
      <div class="flex justify-end">
        <fwb-button @click="closeModal" color="alternative">Batal</fwb-button>
        <fwb-button type="submit" form="transactionForm" color="green" class="ml-2" :loading="isSubmitting" :disabled="isSubmitDisabled">
          Simpan Transaksi
        </fwb-button>
      </div>
    </template>
  </fwb-modal>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { FwbModal, FwbButton, FwbInput, FwbSelect, FwbTextarea } from 'flowbite-vue';
import { useItemStore } from '@/stores/item';

const props = defineProps({ show: Boolean });
const emit = defineEmits(['close', 'submit']);
const itemStore = useItemStore();

// Ref untuk menampung elemen input file
const fileInput = ref(null);

const form = ref({
  item_id: '',
  type: 'in',
  quantity: 1,
  notes: '',
});

const isSubmitting = ref(false);

const transactionTypes = [
  { value: 'in', name: 'Stok Masuk' },
  { value: 'out', name: 'Stok Keluar (Penjualan)' },
];

const itemOptions = computed(() =>
  itemStore.items
    .filter(item => item.is_active)
    .map(item => ({ value: item.id, name: `${item.name} (Stok: ${item.quantity})` }))
);

const selectedItem = computed(() => {
  if (!form.value.item_id) return null;
  return itemStore.items.find(i => i.id === form.value.item_id);
});

const isSubmitDisabled = computed(() => {
  if (form.value.type === 'out' && selectedItem.value) {
    return form.value.quantity > selectedItem.value.quantity;
  }
  return false;
});

onMounted(async () => {
  if (itemStore.items.length === 0) {
    await itemStore.fetchItems();
  }
});

const resetForm = () => {
  form.value = { item_id: '', type: 'in', quantity: 1, notes: '' };
  // Reset juga input file agar kosong saat modal dibuka lagi
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const closeModal = () => {
  resetForm();
  emit('close');
};

const submitForm = async () => {
  if (isSubmitDisabled.value) return;
  isSubmitting.value = true;
  
  const formData = new FormData();
  formData.append('item_id', form.value.item_id);
  formData.append('type', form.value.type);
  formData.append('quantity', form.value.quantity);
  formData.append('notes', form.value.notes || '');

  // Ambil file langsung dari ref, ini cara paling aman
  if (fileInput.value && fileInput.value.files[0]) {
    formData.append('attachment', fileInput.value.files[0]);
  }

  try {
    await emit('submit', formData);
  } finally {
    isSubmitting.value = false;
  }
};
</script>