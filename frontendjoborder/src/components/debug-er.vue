<template>
  <div>
    <!-- ...existing code... -->
    <h2>Job Order Images</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Job Order ID</th>
          <th>Path</th>
          <th>Created At</th>
          <th>Updated At</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(image, index) in jobOrderImages" :key="image.id">
          <td>{{ image.id }}</td>
          <td>
            <input
              type="text"
              v-model="jobOrderImages[index].job_order_id"
              @input="updateJobOrderId(index, $event.target.value)"
            />
          </td>
          <td>
            <a :href="`http://localhost/backendjoborder/${image.path}`" target="_blank">
              {{ image.path }}
            </a>
          </td>
          <td>{{ image.created_at }}</td>
          <td>{{ image.updated_at }}</td>
        </tr>
      </tbody>
    </table>
    <!-- ...existing code... -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      jobOrderImages: [] // Array to store job_order_images data
    };
  },
  methods: {
    fetchJobOrderImages() {
      fetch('http://localhost/backendjoborder/job_order_images', {
        mode: 'cors' // Ensure CORS mode is enabled
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          this.jobOrderImages = data;
        })
        .catch(error => {
          console.error('Error fetching job_order_images:', error);
        });
    },
    updateJobOrderId(index, value) {
      this.jobOrderImages[index].job_order_id = value;
      // Optionally, send the updated value to the backend
      console.log(`Updated job_order_id for index ${index}:`, value);
    }
  },
  mounted() {
    this.fetchJobOrderImages();
  }
};
</script>
