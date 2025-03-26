<template>
  <v-container class="main-container" style="max-width: 100%">
    <v-data-table-virtual
      :headers="headers"
      :items="virtualJobOrders"
      height="800"
      width="100%"
      item-value="jobOrderNumber"
      fixed-header
      @click:row="onRowClick"
    >
    </v-data-table-virtual>

    <!-- Dialog to show full information -->
    <v-dialog v-model="dialogVisible" max-width="1000px">
      <v-card>
        <v-card-title>
          <span class="headline">Job Order Details</span>
        </v-card-title>
        <v-card-subtitle>{{ selectedJobOrder.jobOrderNumber }}</v-card-subtitle>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <strong>Name:</strong> {{ selectedJobOrder.name }}
            </v-col>
            <v-col cols="12" md="6">
              <strong>Laptop Model:</strong> {{ selectedJobOrder.laptopModel }}
            </v-col>
            <v-col cols="12">
              <strong>Date Created:</strong> {{ selectedJobOrder.dateCreated }}
            </v-col>
            <v-col cols="12">
              <strong>Status:</strong> {{ selectedJobOrder.status }}
            </v-col>
            <v-col cols="12">
              <strong>Parts:</strong>
              <ul>
                <li v-for="(part, index) in selectedJobOrder.parts" :key="index">{{ part }}</li>
              </ul>
            </v-col>
            <v-col cols="12">
              <strong>Without:</strong>
              <ul>
                <li v-for="(item, index) in selectedJobOrder.without" :key="index">{{ item }}</li>
              </ul>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-btn text @click="dialogVisible = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const headers = [
  { title: 'Job Order Number', align: 'start', key: 'jobOrderNumber' },
  { title: 'Name', align: 'start', key: 'name' },
  { title: 'Laptop Model', align: 'start', key: 'laptopModel' },
  { title: 'Date Created', align: 'start', key: 'dateCreated' },
  { title: 'Current Status', align: 'start', key: 'status' },
  { title: 'Parts', align: 'start', key: 'parts' },
  { title: 'Without', align: 'start', key: 'without' }
]

const jobOrders = [
  { jobOrderNumber: 'JO12345', name: 'John Doe', laptopModel: 'Dell XPS 13', dateCreated: '2023-03-01', status: 'Under Repair',
    parts: ['2x16GB RAM', '256GB SSD', '500GB HDD', 'Battery', 'Charger', 'Bag'],
    without: ['Screws'] },

  { jobOrderNumber: 'JO12346', name: 'Jane Smith', laptopModel: 'MacBook Pro 16', dateCreated: '2023-03-05', status: 'Completed',
    parts: ['16GB RAM', '512GB SSD', '250GB HDD', 'Battery', 'Charger'],
    without: ['Screws', 'Power Cable'] },

  { jobOrderNumber: 'JO12347', name: 'Alice Johnson', laptopModel: 'HP Spectre x360', dateCreated: '2023-03-10', status: 'In Progress',
    parts: ['8GB RAM', '1TB SSD', '1TB HDD', 'Battery', 'Charger'],
    without: ['Screws'] },

  { jobOrderNumber: 'JO12348', name: 'Bob Lee', laptopModel: 'Lenovo ThinkPad X1', dateCreated: '2023-03-12', status: 'Under Repair',
    parts: ['16GB RAM', '256GB SSD', '500GB HDD', 'Battery', 'Charger', 'Bag'],
    without: ['Screws'] }
]

const virtualJobOrders = ref([])

const dialogVisible = ref(false) // Controls dialog visibility
const selectedJobOrder = ref({}) // Stores the selected job order for the dialog

onMounted(() => {
  fetchData()
})

function fetchData() {
  setTimeout(() => {
    virtualJobOrders.value = [...Array(500).keys()].map(i => {
      const jobOrder = { ...jobOrders[i % jobOrders.length] }
      jobOrder.jobOrderNumber = `${jobOrder.jobOrderNumber} #${i}`
      return jobOrder
    })
  }, 1000)
}

function onRowClick(item) {
  // Set the selected job order and open the dialog
  selectedJobOrder.value = item
  dialogVisible.value = true
}

</script>

<style scoped>
form {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
