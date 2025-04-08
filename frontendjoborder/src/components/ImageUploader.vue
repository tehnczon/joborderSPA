<template>
  <div>
    <form @submit.prevent="uploadImages">
      <input type="text" v-model="jobOrderNumber" placeholder="Enter Job Order Number" />
      <input type="file" multiple @change="onFileChange" />
      <v-btn type="submit" :disabled="!selectedFiles.length || !jobOrderNumber">Upload</v-btn>
    </form>
    <p v-if="uploadPaths.length">Uploaded Image Paths:</p>
    <ul>
      <li v-for="(path, index) in uploadPaths" :key="index">{{ path }}</li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from '@/api/axios'

const selectedFiles = ref([])
const uploadPaths = ref([])
const jobOrderNumber = ref('')

function onFileChange(event) {
  selectedFiles.value = Array.from(event.target.files)
}

async function uploadImages() {
  if (!selectedFiles.value.length || !jobOrderNumber.value) return

  const formData = new FormData()
  formData.append('job_order_number', jobOrderNumber.value)
  selectedFiles.value.forEach((file, index) => {
    formData.append(`images[${index}]`, file)
  })

  try {
    const response = await axios.post('/upload-images', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    uploadPaths.value = response.data.paths
    alert('Images uploaded successfully!')
  } catch (error) {
    console.error('Image upload failed:', error.response || error.message)
    alert('Image upload failed. Please check the console for details.')
  }
}
</script>
