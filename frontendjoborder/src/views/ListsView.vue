<template>
  <div>
    <v-card>
      <v-card-title>Job Orders</v-card-title>

      <v-card-text>
        <v-data-table :headers="headers" :items="jobOrders" item-value="id" :loading="isLoading">
          <template v-slot:item="{ item }">
            <tr>
              <td>{{ item.job_order_number }}</td>
              <td>{{ item.customer_name }}</td>
              <td>{{ item.contact_number }}</td>
              <td>{{ item.laptop_model }}</td>
              <td>{{ item.status }}</td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td>
                <v-btn icon="mdi-eye" @click="viewJobOrder(item)"></v-btn>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Job Order Details Dialog -->
    <v-dialog v-model="showDialog" max-width="600px">
      <v-card>
        <v-card-title>Job Order Details</v-card-title>
        <v-card-text>
          <p><strong>Job Order #:</strong> {{ selectedJobOrder?.job_order_number }}</p>
          <p><strong>Customer Name:</strong> {{ selectedJobOrder?.customer_name }}</p>
          <p><strong>Contact:</strong> {{ selectedJobOrder?.contact_number }}</p>
          <p><strong>Address:</strong> {{ selectedJobOrder?.customer_address }}</p>
          <p><strong>Laptop Model:</strong> {{ selectedJobOrder?.laptop_model }}</p>
          <p><strong>Status:</strong> {{ selectedJobOrder?.status }}</p>
          <p><strong>Created At:</strong> {{ formatDate(selectedJobOrder?.created_at) }}</p>

          <!-- RAM Details -->
          <div v-if="selectedJobOrder?.ram">
  <strong>RAM:</strong>
  <ul>
    <li v-for="(ram, index) in JSON.parse(selectedJobOrder.ram)" :key="index">
      {{ ram.capacity ? `${ram.capacity}GB - ${ram.type}` : "Unknown RAM" }}
    </li>
  </ul>
</div>

          <!-- SSD Details -->
          <div v-if="selectedJobOrder?.ram">
  <strong>SSD:</strong>
  <ul>
    <li v-for="(ssd, index) in JSON.parse(selectedJobOrder.ssd)" :key="index">
      {{ ssd.capacity ? `${ssd.capacity}GB - ${ssd.type}` : "Unknown RAM" }}
    </li>
  </ul>
</div>

          <p><strong>HDD:</strong> {{ selectedJobOrder?.hdd || "None" }}</p>
          <p><strong>Battery:</strong> {{ selectedJobOrder?.has_battery ? "Yes" : "No" }}</p>
          <p><strong>WiFi Card:</strong> {{ selectedJobOrder?.has_wifi_card ? "Yes" : "No" }}</p>
          <p><strong>Others:</strong> {{ selectedJobOrder?.others || "None" }}</p>
          <p><strong>Without:</strong> {{ selectedJobOrder?.without || "None" }}</p>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="showDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const jobOrders = ref([]);
const isLoading = ref(false);
const showDialog = ref(false);
const selectedJobOrder = ref(null);

const headers = [
  { title: "Job Order", key: "job_order_number" },
  { title: "Customer Name", key: "customer_name" },
  { title: "Contact", key: "contact_number" },
  { title: "Laptop Model", key: "laptop_model" },
  { title: "Status", key: "status" },
  { title: "Created At", key: "created_at" },
  { title: "Actions", key: "actions", sortable: false },
];

const fetchJobOrders = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get("http://localhost:8000/api/job-orders");
    jobOrders.value = response.data;
  } catch (error) {
    console.error("Error fetching job orders:", error);
  }
  isLoading.value = false;
};

const viewJobOrder = (jobOrder) => {
  selectedJobOrder.value = jobOrder;
  showDialog.value = true;
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

onMounted(fetchJobOrders);
</script>
