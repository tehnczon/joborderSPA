<template>
  <div class="update-container">
    <v-card class="update-card" dark>
      <v-card-title class="red-accent">Update Job Order</v-card-title>
      <v-divider></v-divider>
      <v-card-text v-if="jobOrder" class="content">
        <p class="job-order">Job Order: <strong style="color: orangered;">{{ jobOrder.job_order_number }}</strong></p>

        <v-select
          v-model="jobOrder.status"
          label="Status"
          :items="['Pending', 'In Progress', 'Completed', 'Unrepairable/pullout', 'Completed/claimed']"
          outlined
          class="status-select"
          color="red darken-2"
        ></v-select>

        <v-btn block color="red darken-2" class="save-btn" @click="updateJobOrder">
          Save Changes
        </v-btn>
      </v-card-text>

      <v-card-text v-else class="loading-text">
        <v-progress-circular indeterminate color="red darken-2"></v-progress-circular>
        <p>Loading job order details...</p>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();
const jobOrder = ref(null);

const fetchJobOrder = async () => {
  const jobOrderId = route.query.id;
  if (!jobOrderId) return;

  try {
    const response = await axios.get(`http://localhost:8000/api/job-orders/${jobOrderId}`);
    jobOrder.value = response.data;
  } catch (error) {
    console.error("Error fetching job order:", error);
  }
};

const updateJobOrder = async () => {
  if (!jobOrder.value) return;

  try {
    await axios.put(`http://localhost:8000/api/job-orders/${route.query.id}`, {
      status: jobOrder.value.status,
    });
    alert("Job Order updated successfully!");
    router.push("/");
  } catch (error) {
    console.error("Error updating job order:", error);
  }
};

onMounted(fetchJobOrder);
</script>

<style scoped>
.update-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #121212;
}

.update-card {
  width: 400px;
  background-color: #1e1e1e;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(255, 0, 0, 0.3);
  padding: 20px;
}

.red-accent {
  color: #ff5252;
  font-size: 1.5rem;
  text-align: center;
}

.content {
  text-align: center;
}

.job-order {
  font-size: 1.2rem;
  margin-bottom: 16px;
  color: #ffffff;
}

.status-select {
  margin-bottom: 20px;
}

.save-btn {
  font-weight: bold;
}

.loading-text {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #ff5252;
  font-size: 1.2rem;
}
</style>
