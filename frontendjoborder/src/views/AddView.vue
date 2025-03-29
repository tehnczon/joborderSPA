  <template>
    <v-container>
      <v-card class="pa-4">
        <v-card-title>Create Job Order</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="submitForm">
            <!-- Customer Type -->
            <v-select v-model="state.customer_type" :items="['customer', 'technician-customer']" label="Customer Type" required></v-select>

            <!-- Name -->
            <v-text-field v-model="state.customer_name" label="Customer Name" required :error-messages="v$.customer_name.$errors.map(e => e.$message)" @blur="v$.customer_name.$touch"></v-text-field>

            <!-- Contact Number -->
            <v-text-field v-model="state.contact_number" label="Contact Number" type="tel" required :error-messages="v$.contact_number.$errors.map(e => e.$message)" @blur="v$.contact_number.$touch"></v-text-field>

            <!-- customer_address-->
            <v-text-field v-model="state.customer_address" label="Address" required></v-text-field>

            <!-- Laptop Model -->
            <v-text-field v-model="state.laptop_model" label="Laptop Model" required :error-messages="v$.laptop_model.$errors.map(e => e.$message)" @blur="v$.laptop_model.$touch"></v-text-field>

            <!-- RAM Section -->
            <v-card class="pa-2 mb-3">
              <v-card-title>RAM</v-card-title>
              <v-row v-for="(ram, index) in state.ram" :key="index">
                <v-col cols="5">
                  <v-text-field v-model="ram.capacity" label="RAM Capacity (GB)" type="number" required></v-text-field>
                </v-col>
                <v-col cols="5">
                  <v-select v-model="ram.type" :items="['DDR3', 'DDR4', 'DDR5']" label="RAM Type" required></v-select>
                </v-col>
                <v-col cols="2">
                  <v-btn color="red" @click="removeRam(index)">X</v-btn>
                </v-col>
              </v-row>
              <v-btn color="green" @click="addRam">+ Add RAM</v-btn>
            </v-card>

            <!-- SSD Section -->
            <v-card class="pa-2 mb-3">
              <v-card-title>SSD</v-card-title>
              <v-row v-for="(ssd, index) in state.ssd" :key="index">
                <v-col cols="5">
                  <v-text-field v-model="ssd.capacity" label="SSD Capacity (GB)" type="number" required></v-text-field>
                </v-col>
                <v-col cols="5">
                  <v-select v-model="ssd.type" :items="['SATA', 'M.2', 'NVMe']" label="SSD Type" required></v-select>
                </v-col>
                <v-col cols="2">
                  <v-btn color="red" @click="removeSsd(index)">X</v-btn>
                </v-col>
              </v-row>
              <v-btn color="green" @click="addSsd">+ Add SSD</v-btn>
            </v-card>

            <!-- HDD -->
            <v-text-field v-model="state.hdd" label="HDD Capacity (GB)" type="number"></v-text-field>

            <!-- Battery & WiFi Card Checkboxes -->
            <v-checkbox v-model="state.has_battery" label="Has Battery"></v-checkbox>
            <v-checkbox v-model="state.has_wifi_card" label="Has WiFi Card"></v-checkbox>

            <!-- Others & Without -->
            <v-text-field v-model="state.others" label="Others"></v-text-field>
            <v-text-field v-model="state.without" label="Without"></v-text-field>

            <!-- Problem-->
            <v-text-field v-model="state.problem" label="Problem" required></v-text-field>

            <!-- Agreement Checkbox -->
            <v-checkbox v-model="state.agree" label="I agree to the terms" required></v-checkbox>

            <!-- Submit & Clear Buttons -->
            <v-btn class="me-2" color="primary" @click="submitForm">Submit</v-btn>
            <v-btn color="secondary" @click="clearForm">Clear</v-btn>
          </v-form>
        </v-card-text>
      </v-card>
    </v-container>
  </template>

  <script setup>
  import { reactive } from "vue";
  import { useVuelidate } from "@vuelidate/core";
  import { required, numeric, helpers } from "@vuelidate/validators";
  import axios from "axios";

  const state = reactive({
    customer_type: "customer", // FIXED field name
    customer_name: "",
    contact_number: "",
    customer_address: "",
    laptop_model: "",
    ram: [], // Will be converted to JSON
    ssd: [], // Will be converted to JSON
    hdd: "",
    has_battery: false,
    has_wifi_card: false, // FIXED field name
    others: "",
    without: "",
    problem: "",
    agree: false,
  });

  // Validation rules
  const rules = {
    customer_name: { required: helpers.withMessage("Customer Name is required", required) },
    contact_number: { required: helpers.withMessage("Contact Number is required", required), numeric },
    customer_address: { required: helpers.withMessage("Address is required", required) },
    laptop_model: { required: helpers.withMessage("Laptop Model is required", required) },
    ram: {
      $each: {
        capacity: { required, numeric },
        type: { required },
      },
    },
    ssd: {
      $each: {
        capacity: { required, numeric },
        type: { required },
      },
    },
    hdd: { numeric },
    agree: { required: helpers.withMessage("You must agree to continue", required) },
  };

  const v$ = useVuelidate(rules, state);

  // RAM Functions
  const addRam = () => state.ram.push({ capacity: "", type: "DDR4" });
  const removeRam = (index) => state.ram.splice(index, 1);

  // SSD Functions
  const addSsd = () => state.ssd.push({ capacity: "", type: "SATA" });
  const removeSsd = (index) => state.ssd.splice(index, 1);

  const submitForm = async () => {
    const isValid = await v$.value.$validate();
    if (!isValid) return;

    // Prepare request data (no JSON.stringify)
    const requestData = { ...state };

    // Debug: Log data before sending
    console.log("Submitting Job Order:", requestData);


    try {
      const response = await axios.post("http://localhost:8000/api/job-orders", requestData, {
        headers: { "Content-Type": "application/json" },
      });
      alert(response.data.message || "Job Order Created!");
      clearForm();
    } catch (error) {
      console.error("Error submitting job order:", error);
      if (error.response) {
        console.error("Validation Errors:", error.response.data.errors);
        alert(JSON.stringify(error.response.data.errors, null, 2));
      }
    }
  };

  const clearForm = () => {
    v$.value.$reset();
    Object.assign(state, {
      customer_type: "customer",
      customer_name: "",
      contact_number: "",
      customer_address: "",
      laptop_model: "",
      ram: [],
      ssd: [],
      hdd: "",
      has_battery: false,
      has_wifi_card: false,
      others: "",
      without: "",
      problem: "",
      agree: false,
    });
  };
  </script>
