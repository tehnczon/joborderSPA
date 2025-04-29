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

      <!-- Modal for Zoomed Image -->
      <v-dialog v-model="isZoomed" max-width="720px">
        <v-card>
          <v-img :src="zoomedImage" alt="Zoomed Image" aspect-ratio="1" contain />
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="red darken-2" text @click="isZoomed = false">Close</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

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
            @click="zoomImage(image.url)"
            class="cursor-pointer"
          />
          <v-btn
            small
            color="red darken-2"
            class="mt-2"
            @click="deleteImage(image.url)"
          >
            Delete
          </v-btn>
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
const isZoomed = ref(false);
const zoomedImage = ref("");
const route = useRoute();
const jobOrderId = route.query.id;
const ALLOWED_FILE_TYPES = ["image/jpeg", "image/png", "image/gif"];
const API_DOMAIN = import.meta.env.VITE_API_DOMAIN;

// Fetch uploaded images
const fetchImages = async () => {
  try {
    const response = await axios.get(`${API_DOMAIN}/api/job-orders/${jobOrderId}/images`);

    images.value = response.data.map((path) => ({
      url: `${API_DOMAIN}${path}`.replace(/([^:]\/)\/+/g, "$1")
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
      `${API_DOMAIN}/api/job-orders/${jobOrderId}/upload-images`,
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

// Function to handle image click
const zoomImage = (imageUrl) => {
  zoomedImage.value = imageUrl;
  isZoomed.value = true;
};

// Delete an image
const deleteImage = async (imageUrl) => {
  if (!confirm("Are you sure you want to delete this image?")) return;

  try {
    // Remove the API domain and extract the relative path after `/storage/`
    const imagePath = imageUrl.split("/storage/")[1];
    if (!imagePath) {
      throw new Error("Invalid image URL format.");
    }

    await axios.delete(`${API_DOMAIN}/api/job-orders/${jobOrderId}/delete-image`, {
      data: { path: imagePath },
    });

    await fetchImages();
  } catch (error) {
    console.error("Error deleting image:", error);
    alert("Failed to delete the image. Please try again.");
  }
};


// Fetch on mount
onMounted(fetchImages);
</script>
