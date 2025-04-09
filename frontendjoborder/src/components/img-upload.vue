  <template>
    <v-container>
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

        <!-- Display uploaded images -->
        <v-row>
          <v-col v-for="(image, index) in images" :key="index" cols="4" class="pa-2">
            <v-img
  :src="image.url"
  alt="Uploaded Image"
  max-height="300"
  class="rounded-lg"
  :lazy-src="image.url"
  cover
/>          </v-col>
        </v-row>

        <v-btn
          class="mt-4"
          color="primary"
          @click="uploadImages"
        >
          Submit
        </v-btn>

        <!-- Display uploaded images below the button -->
        <v-divider class="my-4"></v-divider>
        <v-card-title>Uploaded Images</v-card-title>
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
import imageCompression from "browser-image-compression"; // Import the library

// Configure Axios to include cookies in requests
axios.defaults.withCredentials = true;

// State for uploaded files and images
const files = ref([]);
const images = ref([]);
const route = useRoute();
const jobOrderId = route.query.id; // Get the job order ID from the URL query parameters

// Allowed file types
const ALLOWED_FILE_TYPES = ["image/jpeg", "image/png", "image/gif"];

// Fetch the images for the job order on page load
const fetchImages = async () => {
  const jobOrderId = route.query.id;
  try {
    const response = await axios.get(`http://localhost:8000/api/job-orders/${jobOrderId}/images`);
    console.log("Fetched images:", response.data);

    const baseUrl = "http://localhost:8000/storage/";
    images.value = response.data.map((image) => {
      const imagePath = image.path || image.url || "";
      return {
        ...image,
        url: imagePath.startsWith("http") ? imagePath : baseUrl + imagePath,
      };
    });
  } catch (error) {
    if (error.response?.status === 404) {
      console.warn("No images found for this job order.");
      images.value = [];
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

  // Client-side validation for file type only
  for (const file of files.value) {
    if (!ALLOWED_FILE_TYPES.includes(file.type)) {
      alert(`Invalid file type: ${file.name}. Only JPEG, PNG, and GIF are allowed.`);
      return;
    }
  }

  const formData = new FormData();

  // Compress and append images to FormData
  for (const file of files.value) {
    try {
      const compressedFile = await imageCompression(file, {
        maxSizeMB: 1, // Maximum size in MB
        maxWidthOrHeight: 1024, // Maximum width or height in pixels
        useWebWorker: true, // Use a web worker for better performance
      });
      formData.append("images[]", compressedFile); // Append compressed file
    } catch (error) {
      console.error(`Error compressing file ${file.name}:`, error);
      alert(`An error occurred while compressing ${file.name}.`);
      return;
    }
  }

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

