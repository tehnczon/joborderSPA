<template>
  <v-app theme="dark">
    <!-- Navigation Drawer for Mobile -->
    <v-navigation-drawer
      v-model="drawer"
      temporary
      location="left"
      class="nav-drawer"
    >
      <v-list>
        <v-list-item class="drawer-header">
          <v-img src="./assets/rjalogo.png" class="drawer-logo" />
          <h3 class="drawer-title">RJA Repair Services</h3>
        </v-list-item>
        
        <v-divider />
        
        <v-list-item
          v-for="item in navigationItems"
          :key="item.path"
          :to="item.path"
          :active="$route.path === item.path"
          @click="drawer = false"
        >
          <template v-slot:prepend>
            <v-icon :color="item.color">{{ item.icon }}</v-icon>
          </template>
          <v-list-item-title>{{ item.title }}</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- App Bar -->
    <v-app-bar
      v-if="showAppBar"
      elevation="0"
      class="app-bar"
      height="70"
    >
      <!-- Mobile Menu Button -->
      <v-app-bar-nav-icon
        @click="drawer = !drawer"
        class="d-md-none"
      />

      <!-- Logo -->
      <div class="logo-container">
        <v-img src="./assets/rjalogo.png" class="logo" />
        <div class="brand-text d-none d-sm-block">
          <h2 class="brand-name">RJA</h2>
          <p class="brand-tagline">Repair Services</p>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="search-container d-none d-md-flex">
        <v-text-field
          v-model="searchQuery"
          placeholder="Search job orders..."
          variant="solo"
          density="compact"
          hide-details
          class="search-field"
          @keyup.enter="performSearch"
        >
          <template v-slot:prepend-inner>
            <v-icon size="20">mdi-magnify</v-icon>
          </template>
          <template v-slot:append-inner>
            <v-progress-circular
              v-if="isSearching"
              indeterminate
              size="20"
              width="2"
            />
          </template>
        </v-text-field>
      </div>

      <v-spacer />

      <!-- Desktop Navigation -->
      <div class="nav-buttons d-none d-md-flex">
        <v-btn
          v-for="item in navigationItems"
          :key="item.path"
          :to="item.path"
          :color="$route.path === item.path ? item.color : 'grey'"
          :variant="$route.path === item.path ? 'flat' : 'text'"
          class="nav-btn"
        >
          <v-icon :size="20" start>{{ item.icon }}</v-icon>
          {{ item.title }}
        </v-btn>
      </div>

      <!-- Notifications -->
      <v-btn icon class="mx-2">
        <v-badge
          :content="notifications"
          :value="notifications > 0"
          color="red"
          overlap
        >
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>

      <!-- User Menu -->
      <v-menu offset-y>
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props">
            <v-avatar size="35" color="primary">
              <v-icon>mdi-account</v-icon>
            </v-avatar>
          </v-btn>
        </template>
        <v-list class="user-menu">
          <v-list-item>
            <v-list-item-title>{{ userName }}</v-list-item-title>
            <v-list-item-subtitle>{{ userEmail }}</v-list-item-subtitle>
          </v-list-item>
          <v-divider />
          <v-list-item @click="showSettings = true">
            <template v-slot:prepend>
              <v-icon>mdi-cog</v-icon>
            </template>
            <v-list-item-title>Settings</v-list-item-title>
          </v-list-item>
          <v-list-item @click="handleLogout" class="logout-item">
            <template v-slot:prepend>
              <v-icon color="red">mdi-logout</v-icon>
            </template>
            <v-list-item-title>Logout</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <!-- Main Content -->
    <v-main class="main-content">
      <transition name="page-transition" mode="out-in">
        <router-view :key="$route.fullPath" />
      </transition>
    </v-main>

    <!-- Footer -->
    <v-footer
      v-if="showFooter"
      app
      class="footer"
      height="40"
    >
      <div class="footer-content">
        <span>&copy; 2024 RJA Laptop Repair & Services</span>
        <v-spacer />
        <span class="d-none d-sm-block">Davao City, Philippines</span>
      </div>
    </v-footer>

    <!-- Settings Dialog -->
    <v-dialog v-model="showSettings" max-width="500">
      <v-card>
        <v-card-title>
          <span class="text-h5">Settings</span>
        </v-card-title>
        <v-card-text>
          <v-switch
            v-model="darkMode"
            label="Dark Mode"
            color="primary"
            @update:model-value="toggleTheme"
          />
          <v-switch
            v-model="enableAnimations"
            label="Enable Animations"
            color="primary"
          />
          <v-switch
            v-model="compactMode"
            label="Compact Mode"
            color="primary"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="primary" variant="text" @click="showSettings = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Loading Overlay -->
    <v-overlay
      :model-value="isLoading"
      persistent
      class="loading-overlay"
    >
      <div class="loading-content">
        <v-progress-circular
          indeterminate
          size="64"
          width="4"
          color="white"
        />
        <p class="loading-text">Loading...</p>
      </div>
    </v-overlay>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useTheme } from 'vuetify';
import { logout, isAuthenticated } from "@/router";

const router = useRouter();
const route = useRoute();
const theme = useTheme();

// State
const drawer = ref(false);
const searchQuery = ref('');
const isSearching = ref(false);
const showSettings = ref(false);
const darkMode = ref(true);
const enableAnimations = ref(true);
const compactMode = ref(false);
const isLoading = ref(false);
const notifications = ref(3);

// User info (would come from auth/store in real app)
const userName = ref('Admin User');
const userEmail = ref('admin@rja-repair.com');

// Navigation items
const navigationItems = [
  {
    title: 'Dashboard',
    icon: 'mdi-view-dashboard',
    path: '/dashboard',
    color: 'primary'
  },
  {
    title: 'Job Orders',
    icon: 'mdi-format-list-bulleted',
    path: '/',
    color: 'blue'
  },
  {
    title: 'New Order',
    icon: 'mdi-plus-circle',
    path: '/add',
    color: 'green'
  },
  {
    title: 'Reports',
    icon: 'mdi-chart-box',
    path: '/reports',
    color: 'purple'
  }
];

// Computed
const showAppBar = computed(() => {
  return route.path !== '/login';
});

const showFooter = computed(() => {
  return route.path !== '/login';
});

// Methods
const performSearch = () => {
  if (!searchQuery.value) return;
  
  isSearching.value = true;
  // Simulate search delay
  setTimeout(() => {
    router.push({
      path: '/',
      query: { search: searchQuery.value }
    });
    isSearching.value = false;
  }, 500);
};

const toggleTheme = () => {
  theme.global.name.value = darkMode.value ? 'dark' : 'light';
};

const handleLogout = async () => {
  isLoading.value = true;
  await logout();
  isLoading.value = false;
  router.push("/login");
};

// Watch route changes for animations
watch(() => route.path, () => {
  // Add any route change animations or effects here
});

// Lifecycle
onMounted(() => {
  // Initialize theme
  theme.global.name.value = darkMode.value ? 'dark' : 'light';
  
  // Check for pending notifications
  // This would typically fetch from an API
});
</script>

<style scoped>
/* App Bar */
.app-bar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Logo */
.logo-container {
  display: flex;
  align-items: center;
  margin-left: 16px;
  animation: slideInLeft 0.5s ease-out;
}

.logo {
  width: 50px;
  height: 35px;
  margin-right: 12px;
}

.brand-text {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.brand-name {
  font-size: 1.2rem;
  font-weight: 700;
  color: white;
  margin: 0;
  line-height: 1;
}

.brand-tagline {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.8);
  margin: 0;
}

/* Search */
.search-container {
  max-width: 400px;
  width: 100%;
  margin: 0 20px;
}

.search-field {
  animation: slideInDown 0.5s ease-out;
}

.search-field :deep(.v-field) {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.search-field:hover :deep(.v-field) {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.search-field :deep(input) {
  color: white !important;
}

.search-field :deep(input::placeholder) {
  color: rgba(255, 255, 255, 0.6) !important;
}

/* Navigation Buttons */
.nav-buttons {
  gap: 8px;
  margin-right: 16px;
}

.nav-btn {
  text-transform: none;
  font-weight: 500;
  transition: all 0.3s ease;
  animation: fadeIn 0.5s ease-out;
}

.nav-btn:hover {
  transform: translateY(-2px);
}

/* Navigation Drawer */
.nav-drawer {
  background: linear-gradient(180deg, #1e1e1e 0%, #2d2d2d 100%);
}

.drawer-header {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  flex-direction: column;
  align-items: center;
}

.drawer-logo {
  width: 80px;
  height: 60px;
  margin-bottom: 10px;
}

.drawer-title {
  font-size: 1.2rem;
  text-align: center;
  margin: 0;
}

/* Main Content */
.main-content {
  background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
  min-height: calc(100vh - 110px);
}

/* User Menu */
.user-menu {
  min-width: 200px;
}

.logout-item:hover {
  background: rgba(255, 0, 0, 0.1);
}

/* Footer */
.footer {
  background: #1e1e1e !important;
  color: rgba(255, 255, 255, 0.7);
}

.footer-content {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 0 20px;
  font-size: 0.85rem;
}

/* Loading Overlay */
.loading-overlay {
  background: rgba(103, 58, 183, 0.95);
}

.loading-content {
  text-align: center;
}

.loading-text {
  color: white;
  margin-top: 16px;
  font-size: 1.1rem;
}

/* Page Transitions */
.page-transition-enter-active,
.page-transition-leave-active {
  transition: all 0.3s ease;
}

.page-transition-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.page-transition-leave-to {
  opacity: 0;
  transform: translateX(-30px);
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

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 960px) {
  .logo-container {
    margin-left: 8px;
  }
  
  .app-bar {
    height: 60px !important;
  }
}

/* Dark mode adjustments */
:deep(.v-theme--dark) .main-content {
  background: linear-gradient(135deg, #1e1e1e 0%, #2d2d2d 100%);
}
</style>