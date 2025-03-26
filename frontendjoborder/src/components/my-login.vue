<template>
  <div>
    <input v-model="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button @click="handleLogin">Login</button>
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
    const router = useRouter();

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

    return { email, password, handleLogin };
  },
};


</script>
