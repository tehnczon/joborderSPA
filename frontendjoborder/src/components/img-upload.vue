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
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";

// State for uploaded files and images
const files = ref([]);
const images = ref([]);
const route = useRoute();

// Fetch the images for the job order on page load
const fetchImages = async () => {
  const jobOrderId = route.query.id; // Get the job order ID from the URL query parameters
  try {
    const response = await axios.get(`http://localhost:8000/api/job-orders/${jobOrderId}/images`);
    images.value = response.data; // Assuming the API returns the image URLs
  } catch (error) {
    console.error("Error fetching images:", error);
  }
};

// Upload the images to the server
const uploadImages = async () => {
  const formData = new FormData();
  files.value.forEach((file) => formData.append("images[]", file));

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
    console.error("Error uploading images:", error);
  }
};

// Fetch images on component mount
onMounted(() => {
  fetchImages();
});
</script>
