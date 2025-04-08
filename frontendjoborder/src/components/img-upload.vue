<template>
  <v-container>
    <v-card class="pa-4">
      <v-card-title>Upload Images for Job Order</v-card-title>
      <!-- Image upload section -->
      <v-file-input
        v-model="files"
        label="Upload Images"
        accept="image/*"
        multiple
        outlined
        required
        @change="uploadImages"
      ></v-file-input>

      <!-- Display uploaded images -->
      <v-row>
        <v-col v-for="(image, index) in images" :key="index" cols="4" class="pa-2">
          <v-img :src="image.url" alt="Uploaded Image" aspect-ratio="1" contain />
        </v-col>
      </v-row>

      <v-btn
        class="mt-4"
        color="primary"
        @click="uploadImages"
      >
        Submit
      </v-btn>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";

// Configure Axios to include cookies in requests
axios.defaults.withCredentials = true;

// State for uploaded files and images
const files = ref([]);
const images = ref([]);
const route = useRoute();

const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB
const ALLOWED_FILE_TYPES = ["image/jpeg", "image/png", "image/gif"];

// Fetch the images for the job order on page load
const fetchImages = async () => {
  const jobOrderId = route.query.id; // Get the job order ID from the URL query parameters
  try {
    const response = await axios.get(`http://localhost:8000/api/job-orders/${jobOrderId}/images`);
    images.value = response.data; // Assuming the API returns the image URLs
  } catch (error) {
    if (error.response?.status === 404) {
      console.warn("No images found for this job order.");
      images.value = []; // Clear the images array if no images are found
    } else {
      console.error("Error fetching images:", error);
      alert("An error occurred while fetching images. Please try again later.");
    }
  }
};

// Upload the images to the server
const uploadImages = async () => {
  if (files.value.length === 0) {
    alert("Please select at least one image to upload.");
    return;
  }

  // Client-side validation for file size and type
  for (const file of files.value) {
    if (!ALLOWED_FILE_TYPES.includes(file.type)) {
      alert(`Invalid file type: ${file.name}. Only JPEG, PNG, and GIF are allowed.`);
      return;
    }
    if (file.size > MAX_FILE_SIZE) {
      alert(`File too large: ${file.name}. Maximum size is 2 MB.`);
      return;
    }
  }

  const formData = new FormData();
  files.value.forEach((file) => formData.append("images[]", file)); // Ensure the key matches Laravel's expected input

  // Debugging: Log FormData content
  for (let pair of formData.entries()) {
    console.log(`${pair[0]}:`, pair[1]);
  }

  try {
    const jobOrderId = route.query.id;
    const response = await axios.post(
      `http://localhost:8000/api/job-orders/${jobOrderId}/upload-images`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );
    console.log("Images uploaded successfully:", response.data);
    fetchImages(); // Refresh the list of images after upload
  } catch (error) {
    if (error.response?.data?.errors) {
      console.error("Validation errors:", error.response.data.errors);
      alert(`Error: ${Object.values(error.response.data.errors).flat().join(", ")}`);
    } else {
      console.error("Error uploading images:", error.response?.data || error.message);
      alert("An unexpected error occurred while uploading images.");
    }
  }
};

// Fetch images on component mount
onMounted(() => {
  fetchImages();
});
</script>
