<template>
  <v-responsive>
    <v-app theme="dark">
      <v-app-bar class="px-3 d-flex align-center">
        <!-- Logo -->
        <img src="./assets/rjalogo.png" class="logo" />

        <!-- Search Bar with Suggestions -->
        <v-menu
          v-model="showSuggestions"
          activator=".search-bar"
          offset-y
          transition="scale-transition"
          close-on-content-click
        >
          <template #activator="{ props }">
            <v-text-field
              v-model="search"
              variant="solo"
              density="compact"
              placeholder="Search joborder"
              prepend-inner-icon="mdi-magnify"
              class="search-bar"
              hide-details
              rounded
              bg-color="gray"
              @focus="showSuggestions = true"
              @blur="hideWithDelay"
              v-bind="props"
            ></v-text-field>
          </template>

          <v-list v-if="filteredSuggestions.length">
            <v-list-item
              v-for="(item, index) in filteredSuggestions"
              :key="index"
              @click="selectItem(item)"
            >
              <v-list-item-title>{{ item }}</v-list-item-title>
            </v-list-item>
          </v-list>
          <v-list v-else>
            <v-list-item>
              <v-list-item-title>No results found</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>

        <!-- Navigation Buttons -->
        <v-tooltip text="Lists" location="bottom">
          <template #activator="{ props }">
            <v-btn
              icon
              class="ml-15"
              v-bind="props"
              :color="$route.path === '/' ? 'blue' : 'red'"
              to="/"
            >
              <v-icon>mdi-format-list-bulleted</v-icon>
            </v-btn>
          </template>
        </v-tooltip>

        <v-tooltip text="Add" location="bottom">
          <template #activator="{ props }">
            <v-btn
              icon
              class="ml-15"
              v-bind="props"
              :color="$route.path === '/add' ? 'blue' : 'red'"
              to="/add"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </template>
        </v-tooltip>

        <v-tooltip text="Update" location="bottom">
          <template #activator="{ props }">
            <v-btn
              icon
              class="ml-15"
              v-bind="props"
              :color="$route.path === '/update' ? 'blue' : 'red'"
              to="/update"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
          </template>
        </v-tooltip>

        <v-spacer></v-spacer>

        <!-- Logout/Login Button -->
        <v-btn v-if="isAuthenticated" @click="handlelogout" color="red" icon class="ml-auto">
          <v-icon>mdi-logout</v-icon>
        </v-btn>
        <v-btn v-else to="/login" color="red" icon class="ml-auto">
          <v-icon>mdi-login</v-icon>
        </v-btn>
      </v-app-bar>

      <v-main>
        <router-view></router-view>
      </v-main>
    </v-app>
  </v-responsive>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios' // Your Axios instance

const router = useRouter()
const search = ref('')
const showSuggestions = ref(false)
const suggestions = ref([
  'Job Order 1001',
  'Job Order 1002',
  'Job Order 1003',
  'Customer Request 1',
  'Customer Request 2',
])

const filteredSuggestions = computed(() =>
  search.value
    ? suggestions.value.filter((s) => s.toLowerCase().includes(search.value.toLowerCase()))
    : [],
)

function selectItem(item) {
  search.value = item
  showSuggestions.value = false
}

function hideWithDelay() {
  setTimeout(() => {
    showSuggestions.value = false
  }, 200)
}

// ✅ Authentication State
const isAuthenticated = ref(false)

// ✅ Check if User is Authenticated
const checkAuth = async () => {
  try {
    await axios.get('/api/user') // Call Laravel's authentication check
    isAuthenticated.value = true
  } catch {
    isAuthenticated.value = false
  }
}

// ✅ Logout Function
const handlelogout = async () => {
  try {
    await axios.post('/logout') // Call Laravel API to log out
    isAuthenticated.value = false
    router.push('/login') // Redirect to login page
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

// ✅ Run Authentication Check on Mount
onMounted(() => {
  checkAuth()
})
</script>

<style scoped>
/* Logo styling */
.logo {
  width: 75px;
  height: 45px;
  margin-right: 10px;
  margin-bottom: 0.2%;
}

/* Facebook-style search bar */
.search-bar {
  max-width: 250px;
  background-color: #000000 !important;
  color: white !important;
  border-radius: 30px;
}

/* Adjust input text color */
.search-bar ::v-deep(input) {
  color: rgb(255, 255, 255) !important;
}

/* Red Search Icon */
.search-bar ::v-deep(.v-icon) {
  color: red !important;
}

/* Active button effect */
.active-btn {
  background-color: rgba(255, 255, 255, 0.2) !important; /* Light background for active state */
  color: white !important;
}
</style>
