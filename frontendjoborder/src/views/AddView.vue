<template>
  <div class="add-container">
    <v-container>
      <v-card class="add-card" elevation="10">
        <v-card-title class="card-header">
          <h1 class="card-title">
            <v-icon size="30" class="mr-3">mdi-clipboard-plus</v-icon>
            Create New Job Order
          </h1>
          <p class="card-subtitle">Fill in the details below to create a new repair job</p>
        </v-card-title>

        <v-stepper v-model="currentStep" class="stepper-custom" elevation="0">
          <v-stepper-header>
            <v-stepper-item
              :complete="currentStep > 1"
              :title="'Customer Info'"
              :value="1"
              color="primary"
            />
            <v-divider />
            <v-stepper-item
              :complete="currentStep > 2"
              :title="'Device Details'"
              :value="2"
              color="primary"
            />
            <v-divider />
            <v-stepper-item
              :complete="currentStep > 3"
              :title="'Components'"
              :value="3"
              color="primary"
            />
            <v-divider />
            <v-stepper-item
              :title="'Problem & Pricing'"
              :value="4"
              color="primary"
            />
          </v-stepper-header>

          <v-stepper-window>
            <!-- Step 1: Customer Information -->
            <v-stepper-window-item :value="1">
              <v-card-text class="step-content">
                <v-form ref="step1Form" v-model="step1Valid">
                  <v-row>
                    <v-col cols="12">
                      <v-radio-group
                        v-model="state.customer_type"
                        inline
                        class="customer-type-selector"
                      >
                        <template v-slot:label>
                          <span class="field-label">Customer Type</span>
                        </template>
                        <v-radio
                          label="Regular Customer"
                          value="customer"
                          color="primary"
                        />
                        <v-radio
                          label="Technician Customer"
                          value="technician-customer"
                          color="primary"
                        />
                      </v-radio-group>
                    </v-col>

                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="state.customer_name"
                        label="Customer Name"
                        :rules="[rules.required]"
                        variant="outlined"
                        prepend-inner-icon="mdi-account"
                        class="animated-field"
                        :class="{ 'field-filled': state.customer_name }"
                      />
                    </v-col>

                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="state.contact_number"
                        label="Contact Number"
                        :rules="[rules.required, rules.phone]"
                        variant="outlined"
                        prepend-inner-icon="mdi-phone"
                        class="animated-field"
                        :class="{ 'field-filled': state.contact_number }"
                      />
                    </v-col>

                    <v-col cols="12">
                      <v-text-field
                        v-model="state.customer_address"
                        label="Address"
                        variant="outlined"
                        prepend-inner-icon="mdi-map-marker"
                        class="animated-field"
                        :class="{ 'field-filled': state.customer_address }"
                      />
                    </v-col>
                  </v-row>
                </v-form>
              </v-card-text>
            </v-stepper-window-item>

            <!-- Step 2: Device Details -->
            <v-stepper-window-item :value="2">
              <v-card-text class="step-content">
                <v-form ref="step2Form" v-model="step2Valid">
                  <v-row>
                    <v-col cols="12">
                      <v-text-field
                        v-model="state.laptop_model"
                        label="Laptop Model"
                        :rules="[rules.required]"
                        variant="outlined"
                        prepend-inner-icon="mdi-laptop"
                        placeholder="e.g., Dell Inspiron 15, MacBook Pro 2020"
                        class="animated-field"
                        :class="{ 'field-filled': state.laptop_model }"
                      />
                    </v-col>
                  </v-row>
                </v-form>
              </v-card-text>
            </v-stepper-window-item>

            <!-- Step 3: Components -->
            <v-stepper-window-item :value="3">
              <v-card-text class="step-content">
                <!-- RAM Section -->
                <div class="component-section">
                  <h3 class="section-title">
                    <v-icon size="20" class="mr-2">mdi-memory</v-icon>
                    RAM Configuration
                  </h3>
                  <transition-group name="list-slide" tag="div">
                    <v-row v-for="(ram, index) in state.ram" :key="`ram-${index}`" class="component-row">
                      <v-col cols="5">
                        <v-text-field
                          v-model="ram.capacity"
                          label="Capacity (GB)"
                          type="number"
                          variant="outlined"
                          density="compact"
                        />
                      </v-col>
                      <v-col cols="5">
                        <v-select
                          v-model="ram.type"
                          :items="['DDR3', 'DDR4', 'DDR5']"
                          label="Type"
                          variant="outlined"
                          density="compact"
                        />
                      </v-col>
                      <v-col cols="2">
                        <v-btn
                          icon="mdi-delete"
                          color="error"
                          variant="tonal"
                          @click="removeRam(index)"
                          size="small"
                        />
                      </v-col>
                    </v-row>
                  </transition-group>
                  <v-btn
                    color="primary"
                    variant="tonal"
                    @click="addRam"
                    size="small"
                    class="mt-2"
                  >
                    <v-icon start>mdi-plus</v-icon>
                    Add RAM Module
                  </v-btn>
                </div>

                <!-- SSD Section -->
                <div class="component-section mt-6">
                  <h3 class="section-title">
                    <v-icon size="20" class="mr-2">mdi-harddisk</v-icon>
                    SSD Configuration
                  </h3>
                  <transition-group name="list-slide" tag="div">
                    <v-row v-for="(ssd, index) in state.ssd" :key="`ssd-${index}`" class="component-row">
                      <v-col cols="5">
                        <v-text-field
                          v-model="ssd.capacity"
                          label="Capacity (GB)"
                          type="number"
                          variant="outlined"
                          density="compact"
                        />
                      </v-col>
                      <v-col cols="5">
                        <v-select
                          v-model="ssd.type"
                          :items="['SATA', 'M.2', 'NVMe']"
                          label="Type"
                          variant="outlined"
                          density="compact"
                        />
                      </v-col>
                      <v-col cols="2">
                        <v-btn
                          icon="mdi-delete"
                          color="error"
                          variant="tonal"
                          @click="removeSsd(index)"
                          size="small"
                        />
                      </v-col>
                    </v-row>
                  </transition-group>
                  <v-btn
                    color="primary"
                    variant="tonal"
                    @click="addSsd"
                    size="small"
                    class="mt-2"
                  >
                    <v-icon start>mdi-plus</v-icon>
                    Add SSD
                  </v-btn>
                </div>

                <!-- HDD -->
                <div class="component-section mt-6">
                  <h3 class="section-title">
                    <v-icon size="20" class="mr-2">mdi-database</v-icon>
                    HDD (Optional)
                  </h3>
                  <v-text-field
                    v-model="state.hdd"
                    label="HDD Capacity (GB)"
                    type="number"
                    variant="outlined"
                    density="compact"
                  />
                </div>

                <!-- Other Components -->
                <div class="component-section mt-6">
                  <h3 class="section-title">
                    <v-icon size="20" class="mr-2">mdi-chip</v-icon>
                    Other Components
                  </h3>
                  <v-row>
                    <v-col cols="6">
                      <v-checkbox
                        v-model="state.has_battery"
                        label="Has Battery"
                        color="primary"
                        class="component-checkbox"
                      />
                    </v-col>
                    <v-col cols="6">
                      <v-checkbox
                        v-model="state.has_wifi_card"
                        label="Has WiFi Card"
                        color="primary"
                        class="component-checkbox"
                      />
                    </v-col>
                  </v-row>
                  <v-text-field
                    v-model="state.others"
                    label="Other Components"
                    variant="outlined"
                    density="compact"
                    placeholder="e.g., Webcam, Fingerprint reader"
                    class="mt-2"
                  />
                  <v-text-field
                    v-model="state.without"
                    label="Missing Components"
                    variant="outlined"
                    density="compact"
                    placeholder="e.g., Charger, Battery"
                    class="mt-2"
                  />
                </div>
              </v-card-text>
            </v-stepper-window-item>

            <!-- Step 4: Problem & Pricing -->
            <v-stepper-window-item :value="4">
              <v-card-text class="step-content">
                <v-form ref="step4Form" v-model="step4Valid">
                  <v-row>
                    <v-col cols="12">
                      <v-textarea
                        v-model="state.problem"
                        label="Problem Description"
                        :rules="[rules.required]"
                        variant="outlined"
                        rows="4"
                        prepend-inner-icon="mdi-alert-circle"
                        placeholder="Describe the issue with the laptop..."
                        class="animated-field"
                        :class="{ 'field-filled': state.problem }"
                      />
                    </v-col>

                    <!-- Initial Pricing -->
                    <v-col cols="12">
                      <h3 class="section-title">
                        <v-icon size="20" class="mr-2">mdi-cash</v-icon>
                        Initial Pricing Estimate (Optional)
                      </h3>
                    </v-col>

                    <v-col cols="12" md="4">
                      <v-text-field
                        v-model.number="state.labor_cost"
                        label="Labor Cost"
                        type="number"
                        variant="outlined"
                        prepend-inner-icon="mdi-wrench"
                        prefix="₱"
                        @update:model-value="calculateTotal"
                      />
                    </v-col>

                    <v-col cols="12" md="4">
                      <v-text-field
                        v-model.number="state.parts_cost"
                        label="Parts Cost"
                        type="number"
                        variant="outlined"
                        prepend-inner-icon="mdi-package-variant"
                        prefix="₱"
                        @update:model-value="calculateTotal"
                      />
                    </v-col>

                    <v-col cols="12" md="4">
                      <v-text-field
                        v-model.number="state.repair_price"
                        label="Total Estimate"
                        type="number"
                        variant="outlined"
                        prepend-inner-icon="mdi-calculator"
                        prefix="₱"
                        readonly
                        class="total-field"
                      />
                    </v-col>

                    <v-col cols="12">
                      <v-textarea
                        v-model="state.repair_notes"
                        label="Initial Notes (Optional)"
                        variant="outlined"
                        rows="2"
                        prepend-inner-icon="mdi-note-text"
                      />
                    </v-col>
                  </v-row>
                </v-form>
              </v-card-text>
            </v-stepper-window-item>
          </v-stepper-window>

          <!-- Navigation Buttons -->
          <v-card-actions class="stepper-actions">
            <v-btn
              v-if="currentStep > 1"
              variant="text"
              @click="currentStep--"
              size="large"
            >
              <v-icon start>mdi-arrow-left</v-icon>
              Previous
            </v-btn>
            <v-spacer />
            <v-btn
              v-if="currentStep < 4"
              color="primary"
              variant="elevated"
              @click="nextStep"
              size="large"
              :disabled="!isCurrentStepValid"
            >
              Next
              <v-icon end>mdi-arrow-right</v-icon>
            </v-btn>
            <v-btn
              v-if="currentStep === 4"
              color="success"
              variant="elevated"
              @click="submitForm"
              size="large"
              :loading="isSubmitting"
              :disabled="!isFormValid"
            >
              <v-icon start>mdi-check</v-icon>
              Create Job Order
            </v-btn>
          </v-card-actions>
        </v-stepper>
      </v-card>
    </v-container>

    <!-- Success Dialog -->
    <v-dialog v-model="showSuccessDialog" max-width="400" persistent>
      <v-card class="success-card">
        <v-card-text class="text-center py-8">
          <v-icon size="80" color="success" class="success-icon">
            mdi-check-circle
          </v-icon>
          <h2 class="mt-4">Job Order Created!</h2>
          <p class="mt-2 text-grey">Job Order #{{ createdJobOrderNumber }}</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="primary" variant="text" @click="createAnother">
            Create Another
          </v-btn>
          <v-btn color="primary" variant="elevated" @click="goToList">
            View List
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Error Snackbar -->
    <v-snackbar
      v-model="showError"
      color="error"
      :timeout="5000"
      location="top"
    >
      {{ errorMessage }}
      <template v-slot:actions>
        <v-btn variant="text" @click="showError = false">Close</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const API_DOMAIN = import.meta.env.VITE_API_DOMAIN;

// Stepper
const currentStep = ref(1);
const step1Valid = ref(false);
const step2Valid = ref(false);
const step4Valid = ref(false);

// Form state
const state = reactive({
  customer_type: "customer",
  customer_name: "",
  contact_number: "",
  customer_address: "",
  laptop_model: "",
  ram: [],
  ssd: [],
  hdd: "",
  has_battery: false,
  has_wifi_card: false,
  others: "",
  without: "",
  problem: "",
  labor_cost: 0,
  parts_cost: 0,
  repair_price: 0,
  repair_notes: "",
});

// UI state
const isSubmitting = ref(false);
const showSuccessDialog = ref(false);
const createdJobOrderNumber = ref("");
const showError = ref(false);
const errorMessage = ref("");

// Validation rules
const rules = {
  required: v => !!v || "This field is required",
  phone: v => !v || /^[\d\s\-\+\(\)]+$/.test(v) || "Invalid phone number",
};

// Computed
const isCurrentStepValid = computed(() => {
  switch (currentStep.value) {
    case 1: return step1Valid.value;
    case 2: return step2Valid.value;
    case 3: return true; // Components are optional
    case 4: return step4Valid.value;
    default: return false;
  }
});

const isFormValid = computed(() => {
  return step1Valid.value && step2Valid.value && step4Valid.value;
});

// Methods
const nextStep = () => {
  if (isCurrentStepValid.value) {
    currentStep.value++;
  }
};

const addRam = () => {
  state.ram.push({ capacity: "", type: "DDR4" });
};

const removeRam = (index) => {
  state.ram.splice(index, 1);
};

const addSsd = () => {
  state.ssd.push({ capacity: "", type: "SATA" });
};

const removeSsd = (index) => {
  state.ssd.splice(index, 1);
};

const calculateTotal = () => {
  const labor = parseFloat(state.labor_cost) || 0;
  const parts = parseFloat(state.parts_cost) || 0;
  state.repair_price = labor + parts;
};

const submitForm = async () => {
  if (!isFormValid.value) return;

  isSubmitting.value = true;
  try {
    const response = await axios.post(`${API_DOMAIN}/api/job-orders`, state, {
      headers: { "Content-Type": "application/json" },
    });

    createdJobOrderNumber.value = response.data.data.job_order_number;
    showSuccessDialog.value = true;
  } catch (error) {
    console.error("Error submitting job order:", error);
    errorMessage.value = error.response?.data?.message || "Failed to create job order";
    showError.value = true;
  } finally {
    isSubmitting.value = false;
  }
};

const createAnother = () => {
  resetForm();
  showSuccessDialog.value = false;
};

const goToList = () => {
  router.push("/");
};

const resetForm = () => {
  Object.assign(state, {
    customer_type: "customer",
    customer_name: "",
    contact_number: "",
    customer_address: "",
    laptop_model: "",
    ram: [],
    ssd: [],
    hdd: "",
    has_battery: false,
    has_wifi_card: false,
    others: "",
    without: "",
    problem: "",
    labor_cost: 0,
    parts_cost: 0,
    repair_price: 0,
    repair_notes: "",
  });
  currentStep.value = 1;
};
</script>

<style scoped>
.add-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.add-card {
  max-width: 1000px;
  margin: 0 auto;
  border-radius: 20px;
  overflow: hidden;
  animation: slideInUp 0.5s ease-out;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 30px;
}

.card-title {
  font-size: 2rem;
  font-weight: 600;
  margin: 0;
  display: flex;
  align-items: center;
}

.card-subtitle {
  font-size: 1rem;
  opacity: 0.9;
  margin: 10px 0 0;
}

/* Stepper */
.stepper-custom {
  background: transparent;
}

.step-content {
  padding: 30px;
  min-height: 400px;
}

.stepper-actions {
  padding: 20px 30px;
  background: #f5f5f5;
}

/* Form Fields */
.animated-field {
  transition: all 0.3s ease;
}

.animated-field:hover {
  transform: translateY(-2px);
}

.field-filled :deep(.v-field) {
  background: linear-gradient(145deg, #f0f9ff, #ffffff);
}

.field-label {
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
}

/* Component Sections */
.component-section {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.component-section:hover {
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
}

.component-row {
  margin-bottom: 10px;
  animation: slideInLeft 0.3s ease-out;
}

.component-checkbox {
  background: white;
  padding: 10px;
  border-radius: 8px;
}

/* Total Field */
.total-field :deep(.v-field) {
  background: linear-gradient(145deg, #e8f5e9, #ffffff);
  font-weight: 600;
}

/* Success Dialog */
.success-card {
  border-radius: 16px;
}

.success-icon {
  animation: bounceIn 0.6s ease-out;
}

/* Customer Type Selector */
.customer-type-selector {
  background: white;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
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

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes bounceIn {
  0% {
    transform: scale(0.3);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

/* List transitions */
.list-slide-enter-active,
.list-slide-leave-active {
  transition: all 0.3s ease;
}

.list-slide-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.list-slide-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Responsive */
@media (max-width: 768px) {
  .add-container {
    padding: 10px;
  }
  
  .card-header {
    padding: 20px;
  }
  
  .card-title {
    font-size: 1.5rem;
  }
  
  .step-content {
    padding: 20px;
    min-height: 300px;
  }
}
</style>