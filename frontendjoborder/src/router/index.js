import { createRouter, createWebHistory } from "vue-router";
import ListsView from "@/views/ListsView.vue";
import AddView from "@/views/AddView.vue";
import UpdateView from "@/views/update.vue";
import LoginView from "@/components/my-login.vue";
import axios from "@/api/axios"; // Use your axios instance

const routes = [
  { path: "/", component: ListsView, meta: { requiresAuth: true } },
  { path: "/add", component: AddView, meta: { requiresAuth: true } },
  { path: "/update", component: UpdateView, meta: { requiresAuth: true } },
  { path: "/login", component: LoginView },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Function to check authentication using Laravel Sanctum
const isAuthenticated = async () => {
  try {
    const response = await axios.get("/api/user");
    return response.status === 200;
  } catch {
    return false;
  }
};

// Navigation Guard
router.beforeEach(async (to, from, next) => {
  const authRequired = to.matched.some((record) => record.meta.requiresAuth);
  const userAuthenticated = await isAuthenticated();

  if (authRequired && !userAuthenticated) {
    next("/login"); // Redirect to login if not authenticated
  } else {
    next();
  }
});

export default router;
