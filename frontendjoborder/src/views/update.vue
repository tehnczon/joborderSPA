<template>
    <v-responsive>
      <v-app theme="dark">
        <!-- Centered Container -->
        <div class="form-container">
          <form @submit.prevent="submit">
            <!-- Job Order Number -->
            <v-text-field
            v-model="jobOrderNumber.value.value"
            :counter="10"
            :error-messages="jobOrderNumber.errorMessage.value"
            label="Job Order Number"
            type="number" 
          ></v-text-field>
    
            
    
            <!-- Status Select -->
            <v-select
              v-model="select.value.value"
              :error-messages="select.errorMessage.value"
              :items="items"
              label="Status"
            ></v-select>

            <!-- Upload Photos Button -->
            <v-btn @click="handleUpload" color="primary">
              Upload Photos
            </v-btn>
    
            <!-- Confirm Checkbox -->
            <v-checkbox
              v-model="checkbox.value.value"
              :error-messages="checkbox.errorMessage.value"
              label="Confirm"
              type="checkbox"
              value="1"
            ></v-checkbox>
    
            <v-btn
              class="me-4"
              type="submit"
            >
              Submit
            </v-btn>
    
            <v-btn @click="handleReset">
              Clear
            </v-btn>
          </form>
        </div>
      </v-app>
    </v-responsive>
  </template>
  
  <script setup>
    import { ref } from 'vue'
    import { useField, useForm } from 'vee-validate'
  
    const { handleSubmit, handleReset } = useForm({
      validationSchema: {
        jobOrderNumber(value) {
          if (value?.length >= 2) return true
          return 'Job Order Number needs to be at least 2 characters.'
        },
        select(value) {
          if (value) return true
          return 'Select an item.'
        },
        checkbox(value) {
          if (value === '1') return true
          return 'Must be checked.'
        },
      },
    })
  
    const jobOrderNumber = useField('jobOrderNumber')
    const select = useField('select')
    const checkbox = useField('checkbox')
  
    const items = ref([
      'Repaired',
      'Unrepaired',
      'Pullout/Repaired',
      'Pullout/Unrepaired',
      'Pending',
    ])
  
    const submit = handleSubmit(values => {
      alert(JSON.stringify(values, null, 2))
    })
  
    const handleUpload = () => {
      // Implement upload functionality here
      alert("Photos upload functionality goes here.");
    }
  </script>
  
  <style scoped>
  /* Center the form container */
  .form-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh; /* Ensures it takes up at least the full height of the viewport */
    padding: 20px;
  }
  
  form {
    width: 100%;
    max-width: 500px; /* Adjust the max-width as needed */
    background-color: #222121;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  </style>
  