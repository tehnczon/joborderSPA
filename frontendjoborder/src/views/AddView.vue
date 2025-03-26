<!-- src/views/add.vue -->
<template>
  <div class="form-container">
    <!-- Left-aligned Form (CostumerForm.vue) -->
    <div class="form-left">
      <costumerForm />
    </div>

    <!-- Right-aligned Form (TechForm.vue) -->
    <div class="form-right">
      <techForm />
    </div>
  </div>
</template>

<script setup>
import costumerForm from '../components/costumerForm.vue'; // Import CostumerForm
import techForm from '../components/techForm.vue'; // Import TechForm


</script>

<style scoped>
.form-container {
  display: flex;
  justify-content: space-between;
  gap: 2rem;
  padding: 2rem;
}

.form-left,
.form-right {
  width: 48%; /* Adjust width for both forms */
}

form {
  background-color: #363636;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Optional: Add responsiveness for mobile screens */
@media (max-width: 768px) {
  .form-container {
    flex-direction: column;
    align-items: center;
  }

  .form-left,
  .form-right {
    width: 100%;
    margin-bottom: 2rem;
  }
}
</style>





