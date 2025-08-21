<template>
  <v-container>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <h2>Job Orders</h2>
        <v-btn color="primary" @click="$router.push('/add')">
          <v-icon left>mdi-plus</v-icon>
          New Order
        </v-btn>
      </v-card-title>

      <v-card-text>
        <!-- Search Bar -->
        <v-text-field
          v-model="search"
          label="Search"
          prepend-inner-icon="mdi-magnify"
          clearable
          class="mb-4"
        />

        <!-- Data Table -->
        <v-data-table
          :headers="headers"
          :items="filteredJobOrders"
          :loading="isLoading"
          item-value="id"
        >
          <!-- Status Column -->
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              size="small"
            >
              {{ item.status }}
            </v-chip>
          </template>

          <!-- Date Column -->
          <template v-slot:item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <!-- Actions Column -->
          <template v-slot:item.actions="{ item }">
            <v-btn
              icon="mdi-eye"
              size="small"
              variant="text"
              color="info"
              @click="viewJobOrder(item)"
            />
            <v-btn
              icon="mdi-pencil"
              size="small"
              variant="text"
              color="warning"
              :to="{ path: '/update', query: { id: item.id } }"
            />
            <v-btn
              icon="mdi-printer"
              size="small"
              variant="text"
              color="primary"
              :to="{ path: '/print', query: item }"
            />
            <v-btn
              icon="mdi-image"
              size="small"
              variant="text"
              color="success"
              :to="{ path: '/image', query: { id: item.id } }"
            />
            <v-btn
              icon="mdi-delete"
              size="small"
              variant="text"
              color="error"
              @click="confirmDelete(item)"
            />
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- View Details Dialog -->
    <v-dialog v-model="showDialog" max-width="700px">
      <v-card>
        <v-card-title class="bg-primary text-white">
          Job Order Details
        </v-card-title>
        
        <v-card-text class="pa-4">
          <v-row v-if="selectedJobOrder">
            <v-col cols="12" md="6">
              <p><strong>Job Order #:</strong> {{ selectedJobOrder.job_order_number }}</p>
              <p><strong>Customer:</strong> {{ selectedJobOrder.customer_name }}</p>
              <p><strong>Contact:</strong> {{ selectedJobOrder.contact_number }}</p>
              <p><strong>Address:</strong> {{ selectedJobOrder.customer_address || 'N/A' }}</p>
            </v-col>
            <v-col cols="12" md="6">
              <p><strong>Model:</strong> {{ selectedJobOrder.laptop_model }}</p>
              <p><strong>Status:</strong> 
                <v-chip :color="getStatusColor(selectedJobOrder.status)" size="small">
                  {{ selectedJobOrder.status }}
                </v-chip>
              </p>
              <p><strong>Created:</strong> {{ formatDate(selectedJobOrder.created_at) }}</p>
            </v-col>
          </v-row>

          <v-divider class="my-3" />

          <div v-if="selectedJobOrder">
            <h4 class="mb-2">Components:</h4>
            <v-chip-group>
              <v-chip v-for="(ram, index) in parseJson(selectedJobOrder.ram)" :key="'ram-'+index">
                RAM: {{ ram.capacity }}GB {{ ram.type }}
              </v-chip>
              <v-chip v-for="(ssd, index) in parseJson(selectedJobOrder.ssd)" :key="'ssd-'+index">
                SSD: {{ ssd.capacity }}GB {{ ssd.type }}
              </v-chip>
              <v-chip v-if="selectedJobOrder.hdd">
                HDD: {{ selectedJobOrder.hdd }}GB
              </v-chip>
              <v-chip v-if="selectedJobOrder.has_battery" color="success">
                Has Battery
              </v-chip>
              <v-chip v-if="selectedJobOrder.has_wifi_card" color="success">
                Has WiFi Card
              </v-chip>
            </v-chip-group>

            <div class="mt-3">
              <p><strong>Problem:</strong> {{ selectedJobOrder.problem }}</p>
              <p v-if="selectedJobOrder.others"><strong>Others:</strong> {{ selectedJobOrder.others }}</p>
              <p v-if="selectedJobOrder.without"><strong>Without:</strong> {{ selectedJobOrder.without }}</p>
            </div>

            <div v-if="selectedJobOrder.repair_price" class="mt-3">
              <v-divider class="mb-3" />
              <h4 class="mb-2">Pricing:</h4>
              <p><strong>Labor Cost:</strong> ₱{{ selectedJobOrder.labor_cost || 0 }}</p>
              <p><strong>Parts Cost:</strong> ₱{{ selectedJobOrder.parts_cost || 0 }}</p>
              <p><strong>Total Price:</strong> ₱{{ selectedJobOrder.repair_price || 0 }}</p>
              <p v-if="selectedJobOrder.repair_notes"><strong>Notes:</strong> {{ selectedJobOrder.repair_notes }}</p>
            </div>
          </div>
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn color="primary" variant="text" @click="showDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="showDeleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">Confirm Delete</v-card-title>
        <v-card-text>
          Are you sure you want to delete Job Order #{{ jobOrderToDelete?.job_order_number }}?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="grey" variant="text" @click="showDeleteDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="elevated" @click="deleteJobOrder">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="showSnackbar" :color="snackbarColor" :timeout="3000">
      {{ snackbarMessage }}
      <template v-slot:actions>
        <v-btn variant="text" @click="showSnackbar = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const API_DOMAIN = import.meta.env.VITE_API_DOMAIN;

// State
const jobOrders = ref([]);
const isLoading = ref(false);
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedJobOrder = ref(null);
const jobOrderToDelete = ref(null);
const search = ref("");
const showSnackbar = ref(false);
const snackbarMessage = ref("");
const snackbarColor = ref("success");

// Table headers
const headers = [
  { title: "Job Order", key: "job_order_number", align: "start" },
  { title: "Customer", key: "customer_name" },
  { title: "Contact", key: "contact_number" },
  { title: "Model", key: "laptop_model" },
  { title: "Status", key: "status" },
  { title: "Created", key: "created_at" },
  { title: "Actions", key: "actions", sortable: false, align: "center" },
];

// Computed
const filteredJobOrders = computed(() => {
  if (!search.value) return jobOrders.value;
  
  const searchLower = search.value.toLowerCase();
  return jobOrders.value.filter((item) => {
    return (
      item.job_order_number?.toLowerCase().includes(searchLower) ||
      item.customer_name?.toLowerCase().includes(searchLower) ||
      item.contact_number?.includes(searchLower) ||
      item.laptop_model?.toLowerCase().includes(searchLower) ||
      item.status?.toLowerCase().includes(searchLower)
    );
  });
});

// Methods
const fetchJobOrders = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(`${API_DOMAIN}/api/job-orders`);
    jobOrders.value = response.data;
  } catch (error) {
    console.error("Error fetching job orders:", error);
    showNotification("Failed to fetch job orders", "error");
  } finally {
    isLoading.value = false;
  }
};

const viewJobOrder = (jobOrder) => {
  selectedJobOrder.value = jobOrder;
  showDialog.value = true;
};

const confirmDelete = (jobOrder) => {
  jobOrderToDelete.value = jobOrder;
  showDeleteDialog.value = true;
};

const deleteJobOrder = async () => {
  if (!jobOrderToDelete.value) return;
  
  try {
    await axios.delete(`${API_DOMAIN}/api/job-orders/${jobOrderToDelete.value.id}`);
    showNotification("Job order deleted successfully", "success");
    showDeleteDialog.value = false;
    fetchJobOrders();
  } catch (error) {
    console.error("Error deleting job order:", error);
    showNotification("Failed to delete job order", "error");
  }
};

const showNotification = (message, color = "success") => {
  snackbarMessage.value = message;
  snackbarColor.value = color;
  showSnackbar.value = true;
};

const getStatusColor = (status) => {
  const colors = {
    "Pending": "warning",
    "In Progress": "info",
    "Completed": "success",
    "Completed/claimed": "success",
    "Unrepairable/pullout": "error",
  };
  return colors[status] || "grey";
};

const parseJson = (data) => {
  try {
    return typeof data === 'string' ? JSON.parse(data) : data || [];
  } catch {
    return [];
  }
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

onMounted(fetchJobOrders);
</script>

<style scoped>
.bg-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>