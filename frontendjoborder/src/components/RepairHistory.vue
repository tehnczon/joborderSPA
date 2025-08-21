<template>
  <div class="repair-history-container">
    <v-card class="history-card" elevation="8">
      <v-card-title class="d-flex justify-space-between align-center">
        <div>
          <h2 class="title">Repair History</h2>
          <p class="subtitle">Job Order #{{ jobOrderNumber }}</p>
        </div>
        <v-btn
          color="primary"
          variant="elevated"
          @click="showAddDialog = true"
          class="add-history-btn"
        >
          <v-icon left>mdi-plus</v-icon>
          Add Entry
        </v-btn>
      </v-card-title>

      <v-card-text>
        <!-- Timeline View -->
        <v-timeline
          v-if="history.length > 0"
          side="end"
          class="custom-timeline"
        >
          <transition-group name="list-fade">
            <v-timeline-item
              v-for="(item, index) in history"
              :key="item.id || index"
              :dot-color="getActionColor(item.action_type)"
              size="large"
              :class="{ 'timeline-item-animate': true }"
              :style="{ animationDelay: `${index * 0.1}s` }"
            >
              <template v-slot:icon>
                <v-icon size="20">{{ getActionIcon(item.action_type) }}</v-icon>
              </template>

              <template v-slot:opposite>
                <div class="timeline-opposite">
                  <p class="date-text">{{ formatDate(item.performed_at) }}</p>
                  <p class="time-text">{{ formatTime(item.performed_at) }}</p>
                </div>
              </template>

              <v-card class="timeline-card" elevation="2">
                <v-card-title class="d-flex justify-space-between">
                  <span class="action-title">{{ getActionLabel(item.action_type) }}</span>
                  <v-chip
                    v-if="item.cost"
                    color="success"
                    size="small"
                    variant="flat"
                  >
                    ₱{{ parseFloat(item.cost).toLocaleString() }}
                  </v-chip>
                </v-card-title>
                <v-card-text>
                  <p class="description">{{ item.description }}</p>
                  <div class="meta-info">
                    <v-chip size="x-small" variant="tonal" class="mr-2">
                      <v-icon size="12" start>mdi-account</v-icon>
                      {{ item.performed_by || 'System' }}
                    </v-chip>
                  </div>
                </v-card-text>
              </v-card>
            </v-timeline-item>
          </transition-group>
        </v-timeline>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <v-icon size="80" color="grey">mdi-history</v-icon>
          <p class="empty-text">No repair history yet</p>
          <p class="empty-subtext">Add your first entry to start tracking repairs</p>
        </div>
      </v-card-text>
    </v-card>

    <!-- Add History Dialog -->
    <v-dialog
      v-model="showAddDialog"
      max-width="600px"
      transition="dialog-transition"
    >
      <v-card class="add-dialog">
        <v-card-title>
          <span class="text-h5">Add Repair History Entry</span>
        </v-card-title>
        
        <v-card-text>
          <v-form ref="historyForm" v-model="formValid">
            <v-select
              v-model="newEntry.action_type"
              :items="actionTypes"
              item-title="label"
              item-value="value"
              label="Action Type"
              :rules="[v => !!v || 'Action type is required']"
              variant="outlined"
              class="mb-4"
            >
              <template v-slot:prepend-inner>
                <v-icon :color="getActionColor(newEntry.action_type)">
                  {{ getActionIcon(newEntry.action_type) }}
                </v-icon>
              </template>
            </v-select>

            <v-textarea
              v-model="newEntry.description"
              label="Description"
              :rules="[v => !!v || 'Description is required']"
              variant="outlined"
              rows="3"
              class="mb-4"
            />

            <v-text-field
              v-model="newEntry.performed_by"
              label="Performed By"
              variant="outlined"
              prepend-inner-icon="mdi-account"
              class="mb-4"
            />

            <v-text-field
              v-model="newEntry.cost"
              label="Cost (Optional)"
              type="number"
              variant="outlined"
              prepend-inner-icon="mdi-currency-php"
              class="mb-4"
            />

            <v-text-field
              v-model="newEntry.performed_at"
              label="Date & Time"
              type="datetime-local"
              variant="outlined"
              prepend-inner-icon="mdi-calendar-clock"
            />
          </v-form>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey"
            variant="text"
            @click="closeAddDialog"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            variant="elevated"
            :disabled="!formValid"
            @click="saveEntry"
            :loading="isSaving"
          >
            Save Entry
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Success Snackbar -->
    <v-snackbar
      v-model="showSnackbar"
      :color="snackbarColor"
      :timeout="3000"
      location="top"
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
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  jobOrderId: {
    type: [String, Number],
    required: true
  },
  jobOrderNumber: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['historyUpdated']);

const API_DOMAIN = import.meta.env.VITE_API_DOMAIN;

// State
const history = ref([]);
const showAddDialog = ref(false);
const formValid = ref(false);
const isSaving = ref(false);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

// New entry form
const newEntry = ref({
  action_type: '',
  description: '',
  performed_by: '',
  cost: null,
  performed_at: new Date().toISOString().slice(0, 16)
});

// Action types
const actionTypes = [
  { value: 'diagnosis', label: 'Diagnosis' },
  { value: 'repair', label: 'Repair Work' },
  { value: 'part_replaced', label: 'Part Replaced' },
  { value: 'testing', label: 'Testing' },
  { value: 'completed', label: 'Completed' },
  { value: 'note', label: 'Note' }
];

// Fetch repair history
const fetchHistory = async () => {
  try {
    const response = await axios.get(`${API_DOMAIN}/api/job-orders/${props.jobOrderId}/repair-history`);
    history.value = response.data || [];
  } catch (error) {
    console.error('Error fetching repair history:', error);
    showNotification('Failed to load repair history', 'error');
  }
};

// Save new entry
const saveEntry = async () => {
  if (!formValid.value) return;
  
  isSaving.value = true;
  try {
    const payload = {
      ...newEntry.value,
      performed_at: newEntry.value.performed_at ? 
        new Date(newEntry.value.performed_at).toISOString() : 
        new Date().toISOString()
    };
    
    await axios.post(`${API_DOMAIN}/api/job-orders/${props.jobOrderId}/repair-history`, payload);
    
    showNotification('Repair history entry added successfully', 'success');
    closeAddDialog();
    await fetchHistory();
    emit('historyUpdated');
  } catch (error) {
    console.error('Error saving repair history:', error);
    showNotification('Failed to save repair history entry', 'error');
  } finally {
    isSaving.value = false;
  }
};

// Close dialog and reset form
const closeAddDialog = () => {
  showAddDialog.value = false;
  newEntry.value = {
    action_type: '',
    description: '',
    performed_by: '',
    cost: null,
    performed_at: new Date().toISOString().slice(0, 16)
  };
};

// Show notification
const showNotification = (message, color = 'success') => {
  snackbarMessage.value = message;
  snackbarColor.value = color;
  showSnackbar.value = true;
};

// Get action color
const getActionColor = (type) => {
  const colors = {
    'diagnosis': 'primary',
    'repair': 'info',
    'part_replaced': 'warning',
    'testing': 'purple',
    'completed': 'success',
    'note': 'grey'
  };
  return colors[type] || 'grey';
};

// Get action icon
const getActionIcon = (type) => {
  const icons = {
    'diagnosis': 'mdi-magnify',
    'repair': 'mdi-wrench',
    'part_replaced': 'mdi-package-variant',
    'testing': 'mdi-test-tube',
    'completed': 'mdi-check-circle',
    'note': 'mdi-note-text'
  };
  return icons[type] || 'mdi-circle';
};

// Get action label
const getActionLabel = (type) => {
  const action = actionTypes.find(a => a.value === type);
  return action ? action.label : type;
};

// Format date
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

// Format time
const formatTime = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(fetchHistory);
</script>

<style scoped>
.repair-history-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.history-card {
  border-radius: 16px;
  background: linear-gradient(145deg, #ffffff 0%, #f5f5f5 100%);
}

.title {
  font-size: 1.5rem;
  font-weight: 600;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  font-size: 0.9rem;
  color: #666;
  margin: 0;
}

.add-history-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: all 0.3s ease;
}

.add-history-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

/* Timeline Styles */
.custom-timeline {
  padding: 20px 0;
}

.timeline-item-animate {
  animation: slideInLeft 0.5s ease-out forwards;
  opacity: 0;
}

.timeline-opposite {
  text-align: right;
}

.date-text {
  font-weight: 600;
  color: #333;
  margin: 0;
}

.time-text {
  font-size: 0.85rem;
  color: #666;
  margin: 0;
}

.timeline-card {
  border-radius: 12px;
  transition: all 0.3s ease;
  background: white;
}

.timeline-card:hover {
  transform: translateX(5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.action-title {
  font-size: 1.1rem;
  font-weight: 600;
}

.description {
  color: #555;
  margin: 10px 0;
  line-height: 1.5;
}

.meta-info {
  margin-top: 10px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-text {
  font-size: 1.2rem;
  color: #666;
  margin: 20px 0 10px;
}

.empty-subtext {
  color: #999;
  font-size: 0.9rem;
}

/* Dialog */
.add-dialog {
  border-radius: 16px;
}

/* Animations */
@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.list-fade-enter-active,
.list-fade-leave-active {
  transition: all 0.5s ease;
}

.list-fade-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.list-fade-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Responsive */
@media (max-width: 768px) {
  .repair-history-container {
    padding: 10px;
  }
  
  .timeline-opposite {
    display: none;
  }
}
</style>