<template>
  <form>
    <!-- Name Field -->
    <v-text-field
      v-model="state.name"
      :counter="30"
      :error-messages="v$.name.$errors.map(e => e.$message)"
      label="Name"
      required
      @blur="v$.name.$touch"
      @input="v$.name.$touch"
    ></v-text-field>

    <!-- Contact Number Field (Replaces Email) -->
    <v-text-field
      v-model="state.contactNumber"
      :error-messages="v$.contactNumber.$errors.map(e => e.$message)"
      label="Contact Number"
      required
      type="tel"
      @blur="v$.contactNumber.$touch"
      @input="v$.contactNumber.$touch"
    ></v-text-field>

    <!-- Address Field-->
    <v-text-field
      v-model="state.message"
      :error-messages="v$.message.$errors.map(e => e.$message)"
      label="Address"
      required
      type="text"
      @blur="v$.message.$touch"
      @input="v$.message.$touch"
    ></v-text-field>

    <!-- Laptop Model Field-->
    <v-text-field
      v-model="state.laptopModel"
      :error-messages="v$.laptopModel.$errors.map(e => e.$message)"
      label="Laptop Model"
      required
      type="text"
      @blur="v$.laptopModel.$touch"
      @input="v$.laptopModel.$touch"
    ></v-text-field>

    <!-- Checkbox Fields -->
    <v-row>
      <v-col cols="auto">
        <v-checkbox label="Battery"></v-checkbox>
      </v-col>
      <v-col cols="auto">
        <v-checkbox label="WiFi crd"></v-checkbox>
      </v-col>
      <v-col xs12 sm6 md3>
        <v-text-field
          label="RAM/GB"
          placeholder="Placeholder"
          solo
        ></v-text-field>
      </v-col>
      <v-col xs12 sm6 md3>
        <v-text-field
          label="SSD/GB"
          placeholder="Placeholder"
          solo
        ></v-text-field>
      </v-col>
      <v-col xs12 sm6 md3>
        <v-text-field
          label="HDD/GB"
          placeholder="Placeholder"
          solo
        ></v-text-field>
      </v-col>
    </v-row>

    <!-- "Others" and "Without" Fields -->
    <v-text-field
      v-model="state.others"
      :error-messages="v$.others.$errors.map(e => e.$message)"
      label="Others"
      required
      type="text"
      @blur="v$.others.$touch"
      @input="v$.others.$touch"
    ></v-text-field>

    <v-text-field
      v-model="state.without"
      :error-messages="v$.without.$errors.map(e => e.$message)"
      label="Without"
      required
      type="text"
      @blur="v$.without.$touch"
      @input="v$.without.$touch"
    ></v-text-field>

    <!-- Checkbox Field -->
    <v-checkbox
      v-model="state.checkbox"
      :error-messages="v$.checkbox.$errors.map(e => e.$message)"
      label="Do you agree?"
      required
      @blur="v$.checkbox.$touch"
      @change="v$.checkbox.$touch"
    ></v-checkbox>

    <!-- Submit Button -->
    <v-btn
      class="me-4"
      @click="v$.$validate"
    >
      Submit
    </v-btn>

    <!-- Clear Button -->
    <v-btn @click="clear">
      Clear
    </v-btn>
  </form>
</template>

<script setup>
import { reactive } from 'vue'
import { useVuelidate } from '@vuelidate/core'
import { required, helpers } from '@vuelidate/validators'

const initialState = {
  name: '',
  contactNumber: '',
  message: '',  // Address field
  laptopModel: '',  // Laptop Model field
  others: '',  // "Others" field
  without: '',  // "Without" field
  checkbox: null,
}

const state = reactive({
  ...initialState,
})

const rules = {
  name: { required },
  contactNumber: { required, contactNumber: helpers.regex(/^\+?\d{10,15}$/, 'Invalid contact number') },
  message: { required },  // Address field
  laptopModel: { required },  // Laptop Model field
  others: { required },  // "Others" field
  without: { required },  // "Without" field
  checkbox: { required },
}

const v$ = useVuelidate(rules, state)

function clear() {
  v$.value.$reset()

  for (const [key, value] of Object.entries(initialState)) {
    state[key] = value
  }
}
</script>

<style scoped>
form {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
