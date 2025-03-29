import axios from "@/api/axios"; // Your Axios instance

export const jobOrderService = {
  async getAll() {
    return axios.get("/api/job-orders");
  },

  async getOne(id) {
    return axios.get(`/api/job-orders/${id}`);
  },

  async create(data) {
    return axios.post("/api/job-orders", data);
  },

  async update(id, data) {
    return axios.put(`/api/job-orders/${id}`, data);
  },

  async delete(id) {
    return axios.delete(`/api/job-orders/${id}`);
  },
};
