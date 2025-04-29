<template>
  <div class="login-container">
    <div class="login-card">
      <h2 class="login-title">Login</h2>
      <v-form @submit.prevent="handleLogin">
  <v-text-field
    v-model="email"
    label="Email"
    variant="outlined"
    density="comfortable"
    type="email"
    required
  ></v-text-field>

  <v-text-field
    v-model="password"
    label="Password"
    variant="outlined"
    density="comfortable"
    :type="showPassword ? 'text' : 'password'"
    required
    append-inner-icon="mdi-eye"
    @click:append-inner="togglePasswordVisibility"
  ></v-text-field>

  <v-btn block color="blue" class="mt-4" type="submit">
    Login
  </v-btn>
</v-form>

    </div>
  </div>
</template>

<script>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { authService } from "@/api/auth";


export default {
  setup() {
    const email = ref("");
    const password = ref("");
    const showPassword = ref(false);
    const router = useRouter();

    const togglePasswordVisibility = () => {
      showPassword.value = !showPassword.value;
    };

    const handleLogin = async () => {
      try {
        const response = await authService.login({
          email: email.value,
          password: password.value,
        });

        console.log("Login successful:", response.data);
        localStorage.setItem("auth", JSON.stringify(response.data)); // Save login state

        router.push("/"); // Redirect after login
      } catch (error) {
        console.error("Login failed:", error.response?.data || error.message);
      }
    };

    return { email, password, showPassword, togglePasswordVisibility, handleLogin };
  },
};
</script>

<style scoped>
/* Beautify login page */
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #121212;
}

.login-card {
  width: 400px;
  padding: 20px;
  background-color: #1e1e1e;
  color: white;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.login-title {
  text-align: center;
  margin-bottom: 20px;
  color: white;
}

.v-text-field {
  margin-bottom: 20px;
}
</style>
