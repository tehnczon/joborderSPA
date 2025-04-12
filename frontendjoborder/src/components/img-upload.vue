<template>
  <v-container>
    <!-- Loading Spinner Overlay -->
    <v-overlay :model-value="isUploading" class="d-flex justify-center align-center" persistent>
      <v-progress-circular
        indeterminate
        size="64"
        color="red darken-2"
      />
    </v-overlay>

    <v-card class="pa-4">
      <!-- Display Job Order Number -->
      <v-card-subtitle v-if="jobOrderId">Job Order #: {{ jobOrderId }}</v-card-subtitle>
      <v-card-title>Upload Images for Job Order</v-card-title>

      <!-- Image upload section -->
      <v-file-input
        v-model="files"
        label="Upload Images"
        accept="image/*"
        multiple
        outlined
        required
      ></v-file-input>



      <!-- Submit Button -->
      <v-btn
        class="mt-4"
        color="red darken-2"
        :loading="isUploading"
        :disabled="isUploading"
        @click="uploadImages"
      >
        <span v-if="!isUploading">Submit</span>
      </v-btn>

      <!-- Divider and Uploaded Images -->
      <v-divider class="my-4"></v-divider>
      <v-card-title>Uploaded Images</v-card-title>
      <v-row>
        <v-col
          v-for="(image, index) in images"
          :key="index"
          cols="4"
          class="pa-2"
        >
          <v-img
            :src="image.url"
            alt="Uploaded Image"
            aspect-ratio="1"
            contain
          />
        </v-col>
      </v-row>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import imageCompression from "browser-image-compression";

axios.defaults.withCredentials = true;

const files = ref([]);
const images = ref([]);
const isUploading = ref(false);
const route = useRoute();
const jobOrderId = route.query.id;
const ALLOWED_FILE_TYPES = ["image/jpeg", "image/png", "image/gif"];

// Fetch uploaded images
const fetchImages = async () => {
  try {
    const response = await axios.get(`http://localhost:8000/api/job-orders/${jobOrderId}/images`);

    images.value = response.data.map((path) => ({
  url: `http://localhost:8000${path}`.replace(/([^:]\/)\/+/g, "$1")
}));


  } catch (error) {
    if (error.response?.status === 404) {
      console.warn("No images found for this job order.");
      images.value = [];
    } else {
      console.error("Error fetching images:", error);
      alert("An error occurred while fetching images.");
    }
  }


};

// Upload images
const uploadImages = async () => {
  if (files.value.length === 0) {
    alert("Please select at least one image.");
    return;
  }

  isUploading.value = true;

  try {
    const formData = new FormData();

    for (const file of files.value) {
      if (!ALLOWED_FILE_TYPES.includes(file.type)) {
        alert(`Invalid file type: ${file.name}`);
        isUploading.value = false;
        return;
      }

      const compressedFile = await imageCompression(file, {
        maxSizeMB: 1,
        maxWidthOrHeight: 1024,
        useWebWorker: true,
      });

      formData.append("images[]", compressedFile);
    }

    await axios.post(
      `http://localhost:8000/api/job-orders/${jobOrderId}/upload-images`,
      formData,
      { headers: { "Content-Type": "multipart/form-data" } }
    );

    await fetchImages();
  } catch (error) {
    console.error("Upload error:", error);
    alert("Upload failed. Please try again.");
  } finally {
    isUploading.value = false;
  }
};

// Fetch on mount
onMounted(fetchImages);
</script>
