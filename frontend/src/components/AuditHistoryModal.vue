<template>
  <fwb-modal v-if="show" @close="$emit('close')" size="4xl">
    <template #header>
      <h3 class="text-xl font-semibold">
        Riwayat Perubahan: {{ itemName }}
      </h3>
    </template>
    <template #body>
      <div v-if="loading" class="text-center">Memuat data...</div>
      <div v-else-if="audits.length === 0" class="text-center text-gray-500">
        Tidak ada riwayat perubahan ditemukan.
      </div>
      <div v-else class="overflow-x-auto">
        <fwb-table>
          <fwb-table-head>
            <fwb-table-head-cell>Tanggal</fwb-table-head-cell>
            <fwb-table-head-cell>Aksi</fwb-table-head-cell>
            <fwb-table-head-cell>Pengguna</fwb-table-head-cell>
            <fwb-table-head-cell>Nilai Lama</fwb-table-head-cell>
            <fwb-table-head-cell>Nilai Baru</fwb-table-head-cell>
          </fwb-table-head>
          <fwb-table-body>
            <fwb-table-row v-for="audit in audits" :key="audit.id">
              <fwb-table-cell>{{ audit.date }}</fwb-table-cell>
              <fwb-table-cell>
                <fwb-badge :type="getEventBadge(audit.event)">{{ audit.event }}</fwb-badge>
              </fwb-table-cell>
              <fwb-table-cell>{{ audit.user }}</fwb-table-cell>
              <fwb-table-cell class="text-xs">
                <pre>{{ formatValues(audit.old_values) }}</pre>
              </fwb-table-cell>
              <fwb-table-cell class="text-xs">
                <pre>{{ formatValues(audit.new_values) }}</pre>
              </fwb-table-cell>
            </fwb-table-row>
          </fwb-table-body>
        </fwb-table>
      </div>
    </template>
    <template #footer>
      <div class="flex justify-end">
        <fwb-button @click="$emit('close')" color="alternative">Tutup</fwb-button>
      </div>
    </template>
  </fwb-modal>
</template>

<script setup>
import {
  FwbModal,
  FwbButton,
  FwbTable,
  FwbTableBody,
  FwbTableCell,
  FwbTableHead,
  FwbTableHeadCell,
  FwbTableRow,
  FwbBadge,
} from 'flowbite-vue';

defineProps({
  show: Boolean,
  audits: {
    type: Array,
    default: () => []
  },
  loading: Boolean,
  itemName: String,
});

defineEmits(['close']);

const getEventBadge = (event) => {
  switch (event) {
    case 'created': return 'green';
    case 'updated': return 'yellow';
    case 'deleted': return 'red';
    default: return 'default';
  }
};

const formatValues = (values) => {
  if (!values || Object.keys(values).length === 0) return 'N/A';
  return JSON.stringify(values, null, 2);
};
</script>

<style scoped>
pre {
  white-space: pre-wrap;
  word-break: break-all;
  background-color: #f7fafc;
  padding: 8px;
  border-radius: 4px;
}
.dark pre {
    background-color: #2d3748;
}
</style>