<template>
  <div>
    <v-btn color="primary" @click="printJobOrder">
      <v-icon left>mdi-printer</v-icon> Print Job Order
    </v-btn>

    <div id="printable-container">
      <!-- Job Order Copy -->
      <div class="job-order">
        <header>
          <h1><img src="@/assets/rjalogo.png" class="logo">RJA LAPTOP REPAIR & SERVICES</h1>
          <div class="center">
            <p>Door 3, Tionko Arcade Bldg., Bajada, Brgy. 19-B Poblacion District, Davao City</p>
            <p><strong>RANDY C. ABALOS</strong> | Non-VAT Reg. TIN: 949-140-202-000</p>
            <p>Cell No.: 0946 931 1650 | Ruby: 0907 813 2844</p>
            <p><i class="mdi mdi-facebook"></i>: Rja Laptop Repair & Services</p>
          </div>
          <h3>JOB ORDER <span style="font-family: 'Times New Roman', Times, serif;">No.</span>  <span class="job-number" style="color: red; font-family: 'Times New Roman', Times, serif;">{{ jobOrder.job_order_number || 'N/A' }}</span></h3>
        </header>

        <div class="customer-info">
          <p><strong>Name:</strong> {{ jobOrder.customer_name || 'N/A' }}</p>
          <p><strong>Address:</strong> {{ jobOrder.customer_address || 'N/A' }}</p>
          <p><strong>Contact No.:</strong> {{ jobOrder.contact_number || 'N/A' }}</p>
        </div>

        <div class="job-details">
          <p style="font-size: 12.5px;"><strong>Date:</strong> {{ jobOrder.created_at || 'N/A' }}</p>
        </div>

        <table>
          <thead>
            <tr>
              <th>LAPTOP</th>
              <th>DETAILS</th>
              <th>PROBLEM</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ jobOrder.laptop_model || 'N/A' }}</td>
              <td>
                <ul style="font-size: 12.5px;">
                  {{
    [
      jobOrder.ram.length ? `RAM: ${jobOrder.ram.map(ram => `${ram.capacity}GB - ${ram.type}`).join(', ')}` : '',
      jobOrder.ssd.length ? `SSD: ${jobOrder.ssd.map(ssd => `${ssd.capacity}GB - ${ssd.type}`).join(', ')}` : '',
      jobOrder.hdd ? `HDD: ${jobOrder.hdd}GB` : '',
      jobOrder.has_battery ? 'Battery' : '',
      jobOrder.has_wifi_card ? 'WiFiCard' : ''
    ].filter(Boolean).join(' / ')
  }}
                  <li v-if="jobOrder.others">Others: {{ jobOrder.others }}</li>
                  <li v-if="jobOrder.without">Without: {{ jobOrder.without }}</li>
                </ul>
              </td>
              <td>{{ jobOrder.problem}}</td>
            </tr>
          </tbody>
        </table>

        <h3>Terms & Conditions</h3>
        <ul style="font-size: 12.5px;">
          <li>RJA Laptop Repair Services shall not be liable for the damage of items due to calamities.</li>
          <li>RJA Laptop Repair Services has the right to use part substitution for repair confirmation.</li>
          <li>Unclaimed repairs after 30 days will incur a ₱10 per day storage fee.</li>
          <li>The loss of job order should be reported immediately.</li>
        </ul>

        <div class="signatures">
          <p>
            <span class="left">Signature Over Printed Name: ___________________</span>
            <span class="right" style="color: transparent;">___________________</span>
            <span class="right">Received By: ___________________</span>
          </p>
        </div>
      </div>

      <hr class="divider" />





      <!-- Job Order Copy -->
      <div class="job-order">
        <header>
          <h1><img src="@/assets/rjalogo.png" class="logo">RJA LAPTOP REPAIR & SERVICES</h1>
          <div class="center">
            <p>Door 3, Tionko Arcade Bldg., Bajada, Brgy. 19-B Poblacion District, Davao City</p>
            <p><strong>RANDY C. ABALOS</strong> | Non-VAT Reg. TIN: 949-140-202-000</p>
          </div>
          <h3>JOB ORDER No.  <span style="font-family: 'Times New Roman', Times, serif;">No.</span>  <span class="job-number" style="color: red; font-family: 'Times New Roman', Times, serif;">{{ jobOrder.job_order_number || 'N/A' }}</span></h3>
        </header>

        <div class="customer-info">
          <p><strong>Name:</strong> {{ jobOrder.customer_name || 'N/A' }}</p>
          <p><strong>Address:</strong> {{ jobOrder.customer_address || 'N/A' }}</p>
          <p><strong>Contact No.:</strong> {{ jobOrder.contact_number || 'N/A' }}</p>
        </div>

        <div class="job-details">
          <p style="font-size: 12.5px;"><strong>Date:</strong> {{ jobOrder.created_at || 'N/A' }}</p>
        </div>

        <table>
          <thead>
            <tr>
              <th>LAPTOP</th>
              <th>DETAILS</th>
              <th>PROBLEM</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ jobOrder.laptop_model || 'N/A' }}</td>
              <td>
                <ul style="font-size: 12.5px;">
                  {{
    [
      jobOrder.ram.length ? `RAM: ${jobOrder.ram.map(ram => `${ram.capacity}GB - ${ram.type}`).join(', ')}` : '',
      jobOrder.ssd.length ? `SSD: ${jobOrder.ssd.map(ssd => `${ssd.capacity}GB - ${ssd.type}`).join(', ')}` : '',
      jobOrder.hdd ? `HDD: ${jobOrder.hdd}GB` : '',
      jobOrder.has_battery ? 'Battery' : '',
      jobOrder.has_wifi_card ? 'WiFiCard' : ''
    ].filter(Boolean).join(' / ')
  }}
                  <li v-if="jobOrder.others">Others: {{ jobOrder.others }}</li>
                  <li v-if="jobOrder.without">Without: {{ jobOrder.without }}</li>
                </ul>
              </td>
              <td>{{ jobOrder.problem}}</td>
            </tr>
          </tbody>
        </table>

        <h3>Terms & Conditions</h3>
        <ul style="font-size: 12.5px;">
          <li>RJA Laptop Repair Services shall not be liable for the damage of items due to calamities.</li>
          <li>RJA Laptop Repair Services has the right to use part substitution for repair confirmation.</li>
          <li>Unclaimed repairs after 30 days will incur a ₱10 per day storage fee.</li>
          <li>The loss of job order should be reported immediately.</li>
        </ul>

        <div class="signatures">
          <p>
            <span class="left">Signature Over Printed Name: ___________________</span>
            <span class="right" style="color: transparent;">___________________</span>
            <span class="right">Received By: ___________________</span>
          </p>
        </div>
      </div>

      <hr class="divider" />

    </div>
    </div>


</template>

<script setup>
import { useRoute } from "vue-router";
import { ref, onMounted } from "vue";

const route = useRoute();
const jobOrder = ref({
  ram: [],
  ssd: [],
});

// ✅ Safe JSON Parsing Function
const safeParse = (data) => {
  try {
    return data ? JSON.parse(data) : [];
  } catch (error) {
    console.error("JSON Parse Error:", error);
    return [];
  }
};

onMounted(() => {
  jobOrder.value = { ...route.query }; // Spread to avoid Vue reactivity issues

  // ✅ Ensure `ram` and `ssd` are parsed safely
  jobOrder.value.ram = safeParse(jobOrder.value.ram);
  jobOrder.value.ssd = safeParse(jobOrder.value.ssd);

  jobOrder.value.has_battery = jobOrder.value.has_battery === "true";
  jobOrder.value.has_wifi_card = jobOrder.value.has_wifi_card === "true";

    jobOrder.value.created_at = formatDate(jobOrder.value.created_at);
});



// ✅ Print Function
const printJobOrder = () => {
  const printContent = document.getElementById("printable-container").innerHTML;
  const originalContent = document.body.innerHTML;

  document.body.innerHTML = printContent;
  window.print();
  document.body.innerHTML = originalContent;
  window.location.reload(); // Reload page to restore original content
};

// ✅ Function to Format Date
const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  return new Intl.DateTimeFormat("en-US", { year: "numeric", month: "long", day: "numeric" }).format(date);
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printable-container, #printable-container * {
    visibility: visible;
  }
  #printable-container {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 100%;
    height: 42%;
    color: black;
  }
  button {
    display: none;
  }
}

.job-order {
  width: 100%;
  padding: 20px;
  border: 1px solid black;
}

h1, h3, .center {
  text-align: center;
}
h3 {
  font-size: 13px;
}

.center {
  font-size: 12.5px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  border: 1px solid black;
  padding: 5px;
}

.signatures {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
  width: 100%;
  font-size: 12.5px;
}

.divider {
  border: 1px solid rgb(0, 0, 0);
  margin: 1px 0;
}
.customer-info {
  font-size: 12.5px;
}

.left {
  text-align: left;
}

.right {
  text-align: right;
}
.logo {
  width: 75px;
  height: 45px;
  margin-bottom: -0.6%;
}
</style>
