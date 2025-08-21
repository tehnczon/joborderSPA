<template>
  <div class="dashboard-container">
    <v-container fluid>
      <!-- Header -->
      <div class="dashboard-header">
        <h1 class="dashboard-title">
          <v-icon size="35" class="mr-3">mdi-view-dashboard</v-icon>
          Dashboard
        </h1>
        <p class="dashboard-subtitle">Welcome back! Here's your business overview</p>
      </div>

      <!-- Date Range Selector -->
      <v-card class="date-selector-card" elevation="4">
        <v-card-text>
          <v-row align="center">
            <v-col cols="12" md="3">
              <v-select
                v-model="dateRange"
                :items="dateRangeOptions"
                label="Time Period"
                variant="outlined"
                density="compact"
                @update:model-value="fetchStatistics"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="startDate"
                label="Start Date"
                type="date"
                variant="outlined"
                density="compact"
                v-if="dateRange === 'custom'"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="endDate"
                label="End Date"
                type="date"
                variant="outlined"
                density="compact"
                v-if="dateRange === 'custom'"
              />
            </v-col>
            <v-spacer />
            <v-col cols="auto">
              <v-btn
                color="primary"
                variant="elevated"
                @click="fetchStatistics"
                :loading="isLoading"
              >
                <v-icon start>mdi-refresh</v-icon>
                Refresh
              </v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Statistics Cards -->
      <v-row class="stats-row">
        <v-col
          v-for="(stat, index) in statsCards"
          :key="stat.title"
          cols="12"
          sm="6"
          md="3"
        >
          <v-card
            class="stat-card"
            :class="`stat-card-${stat.color}`"
            elevation="6"
            :style="{ animationDelay: `${index * 0.1}s` }"
          >
            <v-card-text>
              <div class="stat-icon-container">
                <v-icon :color="stat.color" size="40">{{ stat.icon }}</v-icon>
              </div>
              <div class="stat-content">
                <p class="stat-title">{{ stat.title }}</p>
                <transition name="number-transition" mode="out-in">
                  <p class="stat-value" :key="stat.value">
                    {{ stat.prefix }}{{ animatedValue(stat.value) }}{{ stat.suffix }}
                  </p>
                </transition>
                <div class="stat-change" :class="stat.changeType">
                  <v-icon size="16">
                    {{ stat.changeType === 'increase' ? 'mdi-trending-up' : 'mdi-trending-down' }}
                  </v-icon>
                  {{ stat.change }}% from last period
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row -->
      <v-row>
        <!-- Revenue Chart -->
        <v-col cols="12" lg="8">
          <v-card class="chart-card" elevation="4">
            <v-card-title>
              <v-icon class="mr-2">mdi-chart-line</v-icon>
              Revenue Overview
            </v-card-title>
            <v-card-text>
              <canvas ref="revenueChart" height="100"></canvas>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Status Distribution -->
        <v-col cols="12" lg="4">
          <v-card class="chart-card" elevation="4">
            <v-card-title>
              <v-icon class="mr-2">mdi-chart-donut</v-icon>
              Job Status Distribution
            </v-card-title>
            <v-card-text>
              <canvas ref="statusChart"></canvas>
              <div class="legend-container">
                <div v-for="status in statusDistribution" :key="status.label" class="legend-item">
                  <span class="legend-color" :style="{ background: status.color }"></span>
                  <span class="legend-label">{{ status.label }}</span>
                  <span class="legend-value">{{ status.value }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Recent Activity & Top Customers -->
      <v-row>
        <!-- Recent Activity -->
        <v-col cols="12" lg="6">
          <v-card class="activity-card" elevation="4">
            <v-card-title>
              <v-icon class="mr-2">mdi-history</v-icon>
              Recent Activity
              <v-spacer />
              <v-btn size="small" variant="text" to="/">View All</v-btn>
            </v-card-title>
            <v-card-text>
              <v-timeline density="compact" side="end">
                <v-timeline-item
                  v-for="(activity, index) in recentActivities"
                  :key="index"
                  :dot-color="activity.color"
                  size="small"
                >
                  <template v-slot:opposite>
                    <span class="timeline-time">{{ activity.time }}</span>
                  </template>
                  <div class="activity-content">
                    <p class="activity-title">{{ activity.title }}</p>
                    <p class="activity-description">{{ activity.description }}</p>
                  </div>
                </v-timeline-item>
              </v-timeline>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Top Customers -->
        <v-col cols="12" lg="6">
          <v-card class="customers-card" elevation="4">
            <v-card-title>
              <v-icon class="mr-2">mdi-account-group</v-icon>
              Top Customers
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="(customer, index) in topCustomers"
                  :key="index"
                  class="customer-item"
                >
                  <template v-slot:prepend>
                    <v-avatar :color="getAvatarColor(index)" size="40">
                      <span class="text-white">{{ getInitials(customer.name) }}</span>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ customer.name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ customer.orders }} orders • ₱{{ customer.revenue.toLocaleString() }}
                  </v-list-item-subtitle>
                  <template v-slot:append>
                    <v-chip size="small" :color="customer.status === 'active' ? 'success' : 'grey'">
                      {{ customer.status }}
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Performance Metrics -->
      <v-row>
        <v-col cols="12">
          <v-card class="metrics-card" elevation="4">
            <v-card-title>
              <v-icon class="mr-2">mdi-speedometer</v-icon>
              Performance Metrics
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col v-for="metric in performanceMetrics" :key="metric.label" cols="12" md="3">
                  <div class="metric-item">
                    <div class="metric-header">
                      <v-icon :color="metric.color" size="24">{{ metric.icon }}</v-icon>
                      <span class="metric-label">{{ metric.label }}</span>
                    </div>
                    <div class="metric-value">{{ metric.value }}</div>
                    <v-progress-linear
                      :model-value="metric.progress"
                      :color="metric.color"
                      height="6"
                      rounded
                      class="mt-2"
                    />
                    <div class="metric-target">Target: {{ metric.target }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

const API_DOMAIN = import.meta.env.VITE_API_DOMAIN;

// State
const isLoading = ref(false);
const dateRange = ref('month');
const startDate = ref('');
const endDate = ref('');

// Chart refs
const revenueChart = ref(null);
const statusChart = ref(null);
let revenueChartInstance = null;
let statusChartInstance = null;

// Date range options
const dateRangeOptions = [
  { title: 'Today', value: 'today' },
  { title: 'This Week', value: 'week' },
  { title: 'This Month', value: 'month' },
  { title: 'This Quarter', value: 'quarter' },
  { title: 'This Year', value: 'year' },
  { title: 'Custom Range', value: 'custom' }
];

// Statistics Cards
const statsCards = ref([
  {
    title: 'Total Revenue',
    value: 0,
    prefix: '₱',
    suffix: '',
    icon: 'mdi-cash-multiple',
    color: 'success',
    change: 12.5,
    changeType: 'increase'
  },
  {
    title: 'Active Jobs',
    value: 0,
    prefix: '',
    suffix: '',
    icon: 'mdi-progress-wrench',
    color: 'info',
    change: 8.3,
    changeType: 'increase'
  },
  {
    title: 'Completed Jobs',
    value: 0,
    prefix: '',
    suffix: '',
    icon: 'mdi-check-circle',
    color: 'primary',
    change: 15.7,
    changeType: 'increase'
  },
  {
    title: 'Avg. Repair Time',
    value: 0,
    prefix: '',
    suffix: ' days',
    icon: 'mdi-clock-fast',
    color: 'warning',
    change: 5.2,
    changeType: 'decrease'
  }
]);

// Status Distribution
const statusDistribution = ref([
  { label: 'Pending', value: 25, color: '#FFA726' },
  { label: 'In Progress', value: 35, color: '#42A5F5' },
  { label: 'Completed', value: 30, color: '#66BB6A' },
  { label: 'Unrepairable', value: 10, color: '#EF5350' }
]);

// Recent Activities
const recentActivities = ref([
  {
    time: '2 min ago',
    title: 'New Job Order #T2411001',
    description: 'Dell Inspiron 15 - Screen replacement',
    color: 'primary'
  },
  {
    time: '15 min ago',
    title: 'Status Updated',
    description: 'Job #2411023 marked as completed',
    color: 'success'
  },
  {
    time: '1 hour ago',
    title: 'Payment Received',
    description: '₱3,500 for Job #2411019',
    color: 'green'
  },
  {
    time: '3 hours ago',
    title: 'Part Ordered',
    description: 'RAM module for Job #2411021',
    color: 'warning'
  }
]);

// Top Customers
const topCustomers = ref([
  { name: 'John Dela Cruz', orders: 12, revenue: 45000, status: 'active' },
  { name: 'Maria Santos', orders: 8, revenue: 32000, status: 'active' },
  { name: 'Robert Garcia', orders: 6, revenue: 28000, status: 'inactive' },
  { name: 'Ana Reyes', orders: 5, revenue: 21000, status: 'active' }
]);

// Performance Metrics
const performanceMetrics = ref([
  {
    label: 'Customer Satisfaction',
    value: '4.8/5.0',
    progress: 96,
    target: '4.5/5.0',
    icon: 'mdi-emoticon-happy',
    color: 'success'
  },
  {
    label: 'First Response Time',
    value: '2.5 hrs',
    progress: 75,
    target: '3 hrs',
    icon: 'mdi-clock-fast',
    color: 'info'
  },
  {
    label: 'Resolution Rate',
    value: '92%',
    progress: 92,
    target: '90%',
    icon: 'mdi-check-all',
    color: 'primary'
  },
  {
    label: 'Return Rate',
    value: '3%',
    progress: 97,
    target: '<5%',
    icon: 'mdi-replay',
    color: 'warning'
  }
]);

// Methods
const fetchStatistics = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(`${API_DOMAIN}/api/statistics`);
    const data = response.data;
    
    // Update stats cards
    statsCards.value[0].value = data.revenue_this_month || 0;
    statsCards.value[1].value = data.in_progress || 0;
    statsCards.value[2].value = data.completed || 0;
    
    // Update charts
    await nextTick();
    updateCharts();
  } catch (error) {
    console.error('Error fetching statistics:', error);
  } finally {
    isLoading.value = false;
  }
};

const updateCharts = () => {
  // Revenue Chart
  if (revenueChart.value) {
    const ctx = revenueChart.value.getContext('2d');
    
    if (revenueChartInstance) {
      revenueChartInstance.destroy();
    }
    
    revenueChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue',
          data: [30000, 35000, 32000, 40000, 45000, 52000],
          borderColor: '#667eea',
          backgroundColor: 'rgba(102, 126, 234, 0.1)',
          tension: 0.4,
          fill: true
        }, {
          label: 'Expenses',
          data: [15000, 18000, 16000, 20000, 22000, 25000],
          borderColor: '#764ba2',
          backgroundColor: 'rgba(118, 75, 162, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => '₱' + value.toLocaleString()
            }
          }
        }
      }
    });
  }
  
  // Status Chart
  if (statusChart.value) {
    const ctx = statusChart.value.getContext('2d');
    
    if (statusChartInstance) {
      statusChartInstance.destroy();
    }
    
    statusChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: statusDistribution.value.map(s => s.label),
        datasets: [{
          data: statusDistribution.value.map(s => s.value),
          backgroundColor: statusDistribution.value.map(s => s.color),
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  }
};

// Helper functions
const animatedValue = (value) => {
  // In a real app, you'd animate this
  return typeof value === 'number' ? value.toLocaleString() : value;
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const getAvatarColor = (index) => {
  const colors = ['primary', 'success', 'warning', 'error', 'info'];
  return colors[index % colors.length];
};

// Lifecycle
onMounted(() => {
  fetchStatistics();
});
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
}

/* Header */
.dashboard-header {
  color: white;
  margin-bottom: 30px;
  animation: slideInDown 0.5s ease-out;
}

.dashboard-title {
  font-size: 2.5rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.dashboard-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* Date Selector */
.date-selector-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  margin-bottom: 30px;
  animation: slideInUp 0.5s ease-out;
}

/* Stats Cards */
.stats-row {
  margin-bottom: 30px;
}

.stat-card {
  border-radius: 16px;
  background: white;
  transition: all 0.3s ease;
  animation: slideInUp 0.5s ease-out;
  overflow: visible;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon-container {
  position: absolute;
  top: 20px;
  right: 20px;
  opacity: 0.2;
}

.stat-content {
  position: relative;
}

.stat-title {
  font-size: 0.9rem;
  color: #666;
  margin: 0 0 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.stat-change {
  font-size: 0.85rem;
  margin-top: 10px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.stat-change.increase {
  color: #4caf50;
}

.stat-change.decrease {
  color: #f44336;
}

/* Chart Cards */
.chart-card {
  border-radius: 16px;
  background: white;
  margin-bottom: 20px;
  animation: slideInUp 0.6s ease-out;
}

/* Legend */
.legend-container {
  margin-top: 20px;
}

.legend-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin-right: 10px;
}

.legend-label {
  flex: 1;
  font-size: 0.9rem;
  color: #666;
}

.legend-value {
  font-weight: 600;
}

/* Activity Card */
.activity-card, .customers-card, .metrics-card {
  border-radius: 16px;
  background: white;
  animation: slideInUp 0.7s ease-out;
}

.timeline-time {
  font-size: 0.85rem;
  color: #999;
}

.activity-content {
  padding: 10px;
  background: #f5f5f5;
  border-radius: 8px;
}

.activity-title {
  font-weight: 600;
  margin: 0 0 5px;
}

.activity-description {
  font-size: 0.9rem;
  color: #666;
  margin: 0;
}

/* Customer Item */
.customer-item {
  transition: all 0.3s ease;
  border-radius: 8px;
  margin-bottom: 10px;
}

.customer-item:hover {
  background: #f5f5f5;
}

/* Metrics */
.metric-item {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.metric-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.metric-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.metric-label {
  font-size: 0.9rem;
  color: #666;
}

.metric-value {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.metric-target {
  font-size: 0.85rem;
  color: #999;
  margin-top: 10px;
}

/* Animations */
@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

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

/* Number transition */
.number-transition-enter-active,
.number-transition-leave-active {
  transition: all 0.5s ease;
}

.number-transition-enter-from {
  opacity: 0;
  transform: scale(0.8);
}

.number-transition-leave-to {
  opacity: 0;
  transform: scale(1.2);
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-container {
    padding: 10px;
  }
  
  .dashboard-title {
    font-size: 1.8rem;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
}
</style>