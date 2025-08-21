<template>
  <div class="update-container">
    <v-container fluid>
      <v-row>
        <!-- Main Update Card -->
        <v-col cols="12" lg="7">
          <v-card class="update-card" elevation="10">
            <v-card-title class="card-header">
              <div>
                <h2 class="card-title">Update Job Order</h2>
                <p class="job-order-number">
                  #{{ jobOrder?.job_order_number }}
                  <v-chip
                    size="small"
                    :color="getStatusColor(jobOrder?.status)"
                    variant="flat"
                    class="ml-2"
                  >
                    {{ jobOrder?.status }}
                  </v-chip>
                </p>
              </div>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text v-if="jobOrder" class="pa-6">
              <v-form ref="updateForm" v-model="isFormValid">
                <!-- Status Update -->
                <div class="form-section">
                  <h3 class="section-title">
                    <v-icon size="20" class="mr-2">mdi-update</v-icon>
                    Status Update
                  </h3>
                  
                  <v-select
                    v-model="jobOrder.status"
                    label="Status"
                    :items="statusOptions"
                    variant="outlined"
                    :color="getStatusColor(jobOrder.status)"
                    class="status-select"
                  >
                    <template v-slot:prepend-inner>
                      <v-icon :color="getStatusColor(jobOrder.status)">
                        {{ getStatusIcon(jobOrder.status) }}
                      </v-icon>
                    </template>
                  </v-select>

                  <v-expand-transition>
                    <v-text-field
                      v-if="requiresPullout"
                      v-model="jobOrder.pulled_out_by"
                      label="Pulled out by"
                      variant="outlined"
                      :rules="[v => !requiresPullout || !!v || 'Required for pullout status']"
                      prepend-inner-icon="mdi-account-check"
                      class="mt-4"
                    />
                  </v-expand-transition>
                </div>

                <!-- Pricing Section -->
                <div class="form-section">
                  <h3 class="section-title">
                    <v-icon size="20" class="mr-2">mdi-cash-multiple</v-icon>
                    Pricing Details
                  </h3>
                  
                  <v-row>
                    <v-col cols="12" md="4">
                      <v-text-field
                        v-model.number="jobOrder.labor_cost"
                        label="Labor Cost"
                        type="number"
                        variant="outlined"
                        prepend-inner-icon="mdi-wrench"
                        prefix="₱"
                        :rules="[v => v >= 0 || 'Must be positive']"
                        @update:model-value="calculateTotal"
                      />
                    </v-col>
                    <v-col cols="12" md="4">
                      <v-text-field
                        v-model.number="jobOrder.parts_cost"
                        label="Parts Cost"
                        type="number"
                        variant="outlined"
                        prepend-inner-icon="mdi-package-variant"
                        prefix="₱"
                        :rules="[v => v >= 0 || 'Must be positive']"
                        @update:model-value="calculateTotal"
                      />
                    </v-col>
                    <v-col cols="12" md="4">
                      <v-text-field
                        v-model.number="jobOrder.repair_price"
                        label="Total Price"
                        type="number"
                        variant="outlined"
                        prepend-inner-icon="mdi-calculator"
                        prefix="₱"
                        readonly
                        class="total-field"
                      />
                    </v-col>
                  </v-row>

                  <v-textarea
                    v-model="jobOrder.repair_notes"
                    label="Repair Notes"
                    variant="outlined"
                    rows="3"
                    prepend-inner-icon="mdi-note-text"
                    class="mt-4"
                  />
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                  <v-btn
                    variant="outlined"
                    color="grey"
                    @click="$router.push('/')"
                    size="large"
                  >
                    <v-icon start>mdi-arrow-left</v-icon>
                    Cancel
                  </v-btn>
                  <v-btn
                    color="primary"
                    variant="elevated"
                    size="large"
                    :loading="isSaving"
                    :disabled="!isFormValid"
                    @click="updateJobOrder"
                    class="save-btn"
                  >
                    <v-icon start>mdi-content-save</v-icon>
                    Save Changes
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>

            <!-- Loading State -->
            <v-card-text v-else class="text-center py-10">
              <v-progress-circular
                indeterminate
                color="primary"
                size="64"
              />
              <p class="mt-4 text-grey">Loading job order details...</p>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Repair History Card -->
        <v-col cols="12" lg="5">
          <RepairHistory
            v-if="jobOrder"
            :job-order-id="jobOrderId"
            :job-order-number="jobOrder.job_order_number"
            @history-updated="fetchJobOrder"
          />
        </v-col>
      </v-row>

      <!-- Customer Info Card -->
      <v-row class="mt-4">
        <v-col cols="12">
          <v-card class="info-card" elevation="6">
            <v-card-title>
              <v-icon class="mr-2">mdi-account-details</v-icon>
              Customer Information
            </v-card-title>
            <v-card-text>
              <v-row v-if="jobOrder">
                <v-col cols="12" md="3">
                  <div class="info-item">
                    <p class="info-label">Customer Name</p>
                    <p class="info-value">{{ jobOrder.customer_name }}</p>
                  </div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="info-item">
                    <p class="info-label">Contact Number</p>
                    <p class="info-value">{{ jobOrder.contact_number }}</p>
                  </div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="info-item">
                    <p class="info-label">Laptop Model</p>
                    <p class="info-value">{{ jobOrder.laptop_model }}</p>
                  </div>
                </v-col>
                <v-col cols="12" md="3">
                  <div class="info-item">
                    <p class="info-label">Date Created</p>
                    <p class="info-value">{{ formatDate(jobOrder.created_at) }}</p>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- Success Snackbar -->
    <v-snackbar
      v-model="showSnackbar"
      :color="snackbarColor"
      :timeout="3000"
      location="top"
      transition="slide-y-transition"
    >
      {{ snackbarMessage }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          @click="showSnackbar = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import RepairHistory from '../components/RepairHistory.vue';

const route = useRoute();
const router = useRouter();
const API_DOMAIN = import.meta.env.VITE_API_DOMAIN;

// State
const jobOrder = ref(null);
const jobOrderId = route.query.id;
const isSaving = ref(false);
const isFormValid = ref(true);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

// Status options
const statusOptions = [
  'Pending',
  'In Progress',
  'Completed',
  'Unrepairable/pullout',
  'Completed/claimed'
];

// Computed
const requiresPullout = computed(() => {
  return ['Unrepairable/pullout', 'Completed/claimed'].includes(jobOrder.value?.status);
});

// Watch for status changes
watch(() => jobOrder.value?.status, (newStatus) => {
  if (!requiresPullout.value) {
    jobOrder.value.pulled_out_by = null;
  }
});

// Fetch job order
const fetchJobOrder = async () => {
  if (!jobOrderId) {
    router.push('/');
    return;
  }

  try {
    const response = await axios.get(`${API_DOMAIN}/api/job-orders/${jobOrderId}`);
    jobOrder.value = response.data;
    
    // Initialize pricing if not set
    if (!jobOrder.value.labor_cost) jobOrder.value.labor_cost = 0;
    if (!jobOrder.value.parts_cost) jobOrder.value.parts_cost = 0;
    if (!jobOrder.value.repair_price) jobOrder.value.repair_price = 0;
    
    calculateTotal();
  } catch (error) {
    console.error("Error fetching job order:", error);
    showNotification('Failed to load job order', 'error');
    setTimeout(() => router.push('/'), 2000);
  }
};

// Calculate total price
const calculateTotal = () => {
  if (jobOrder.value) {
    const labor = parseFloat(jobOrder.value.labor_cost) || 0;
    const parts = parseFloat(jobOrder.value.parts_cost) || 0;
    jobOrder.value.repair_price = labor + parts;
  }
};

// Update job order
const updateJobOrder = async () => {
  if (!isFormValid.value) return;

  if (requiresPullout.value && !jobOrder.value.pulled_out_by) {
    showNotification('Please enter who pulled out the device', 'warning');
    return;
  }

  isSaving.value = true;
  try {
    const updateData = {
      status: jobOrder.value.status,
      pulled_out_by: jobOrder.value.pulled_out_by,
      labor_cost: jobOrder.value.labor_cost,
      parts_cost: jobOrder.value.parts_cost,
      repair_price: jobOrder.value.repair_price,
      repair_notes: jobOrder.value.repair_notes,
    };

    if (requiresPullout.value) {
      updateData.pullout_date = new Date().toISOString().split('T')[0];
    }

    await axios.put(`${API_DOMAIN}/api/job-orders/${jobOrderId}`, updateData);
    
    showNotification('Job Order updated successfully!', 'success');
    setTimeout(() => router.push('/'), 1500);
  } catch (error) {
    console.error("Error updating job order:", error);
    showNotification('Failed to update job order', 'error');
  } finally {
    isSaving.value = false;
  }
};

// Show notification
const showNotification = (message, color = 'success') => {
  snackbarMessage.value = message;
  snackbarColor.value = color;
  showSnackbar.value = true;
};

// Get status color
const getStatusColor = (status) => {
  const colors = {
    'Pending': 'warning',
    'In Progress': 'info',
    'Completed': 'success',
    'Completed/claimed': 'success',
    'Unrepairable/pullout': 'error',
  };
  return colors[status] || 'grey';
};

// Get status icon
const getStatusIcon = (status) => {
  const icons = {
    'Pending': 'mdi-clock-outline',
    'In Progress': 'mdi-progress-wrench',
    'Completed': 'mdi-check-circle',
    'Completed/claimed': 'mdi-check-all',
    'Unrepairable/pullout': 'mdi-close-circle',
  };
  return icons[status] || 'mdi-circle';
};

// Format date
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

onMounted(fetchJobOrder);
</script>

<style scoped>
.update-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px 0;
}

/* Cards */
.update-card, .info-card {
  border-radius: 20px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.98);
  animation: slideInUp 0.5s ease-out;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px;
}

.card-title {
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0;
}

.job-order-number {
  font-size: 1rem;
  opacity: 0.95;
  margin: 5px 0 0;
}

/* Form Sections */
.form-section {
  margin-bottom: 32px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.form-section:hover {
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
}

/* Status Select */
.status-select {
  transition: all 0.3s ease;
}

/* Total Field */
.total-field {
  background: linear-gradient(145deg, #f0f0f0, #ffffff);
}

.total-field :deep(.v-field) {
  font-weight: 600;
  font-size: 1.1rem;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 16px;
  justify-content: flex-end;
  margin-top: 32px;
}

.save-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-width: 150px;
  transition: all 0.3s ease;
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

/* Info Card */
.info-card {
  margin-top: 20px;
  animation: slideInUp 0.6s ease-out;
}

.info-item {
  padding: 10px;
  border-radius: 8px;
  background: #f8f9fa;
  transition: all 0.3s ease;
}

.info-item:hover {
  background: #e9ecef;
  transform: translateY(-2px);
}

.info-label {
  font-size: 0.85rem;
  color: #6c757d;
  margin: 0 0 5px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 1rem;
  font-weight: 600;
  color: #212529;
  margin: 0;
}

/* Animations */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 1280px) {
  .update-container {
    padding: 10px;
  }
  
  .form-section {
    padding: 15px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .action-buttons > * {
    width: 100%;
  }
}
</style>