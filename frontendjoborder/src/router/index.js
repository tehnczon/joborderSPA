import { createRouter, createWebHistory } from "vue-router";
import ListsView from "@/views/ListsView.vue";
import AddView from "@/views/AddView.vue";
import UpdateView from "@/views/update-component.vue";
import LoginView from "@/components/my-login.vue";
import Print from "@/views/PrintView.vue";
import ImageView from "@/components/img-upload.vue";
import axios from "@/api/axios"; // Import global axios instance
import { ref } from "vue";

const isAuthenticated = ref(false); // 🔹 Reactive authentication state

// ✅ Function to check if the user is authenticated
const checkAuth = async () => {
  try {
    const response = await axios.get("/api/user"); // Check Laravel Sanctum authentication
    isAuthenticated.value = response.status === 200;
  } catch {
    isAuthenticated.value = false;
  }
};

// ✅ Function to log out the user
const logout = async () => {
  try {
    await axios.post("/logout"); // Laravel Sanctum logout
    isAuthenticated.value = false; // Clear auth state
  } catch (error) {
    console.error("Logout error:", error);
  }
};

// ✅ Define routes
const routes = [
  { path: "/", component: ListsView, meta: { requiresAuth: true } },
  { path: "/add", component: AddView, meta: { requiresAuth: true } },
  { path: "/update", component: UpdateView, meta: { requiresAuth: true } },
  { path: "/login", component: LoginView },
  { path: "/print", component: Print },
  { path: "/image", component: ImageView, meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// ✅ Navigation Guard to protect routes
router.beforeEach(async (to, from, next) => {
  if (!isAuthenticated.value) {
    await checkAuth(); // Only check auth if state is not set
  }

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    next("/login"); // Redirect to login if not authenticated
  } else if (to.path === "/login" && isAuthenticated.value) {
    next("/"); // Redirect logged-in users away from login page
  } else {
    next();
  }
});

// ✅ Export authentication state & logout function for use in components
export { isAuthenticated, logout };
export default router;
