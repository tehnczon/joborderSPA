import axios from './axios';

export const authService = {
    async getCsrfCookie() {
        await axios.get('/sanctum/csrf-cookie');
    },

    async login(credentials) {
        await this.getCsrfCookie();
        return axios.post(`/login`, credentials);
    },}
