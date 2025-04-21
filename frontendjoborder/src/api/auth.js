// auth.js
import axios from './axios';

export const authService = {
    // Fetch the CSRF cookie before making login requests
    async getCsrfCookie() {
        try {
            await axios.get('/sanctum/csrf-cookie');  // Correct relative URL based on your axios config
        } catch (error) {
            console.error("Error fetching CSRF cookie:", error);
            throw error;  // Throw to handle it in the login method
        }
    },

    // Handle login
    async login(credentials) {
        try {
            // First, get the CSRF token
            await this.getCsrfCookie();

            // Then, send the login request with the credentials
            return axios.post('/login', credentials);  // Again, relative URL because of axios baseURL
        } catch (error) {
            console.error("Login failed:", error);
            throw error;
        }
    },

    // Optionally, add other auth-related functions (logout, get user, etc.)
};
