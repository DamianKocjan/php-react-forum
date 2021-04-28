import axios from 'axios';
import { Cookies } from 'react-cookie';

import {
  createAccessTokenCookie,
  createRefreshTokenCookie,
} from './authCookies';

const baseURL = 'http://localhost/api/';
const cookies = new Cookies();

const axiosInstance = axios.create({
  baseURL,
  timeout: 5000,
  headers: {
    Authorization: cookies.get('access_token')
      ? cookies.get('access_token')
      : null,
    'Content-Type': 'application/json',
    accept: 'application/json',
  },
});

axiosInstance.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // Prevent infinite loops early
    if (
      error.response.status === 401 &&
      originalRequest.url === `${baseURL}token/refresh/`
    ) {
      window.location.href = '/login/';
      return Promise.reject(error);
    }

    if (
      error.response.data.code === 'token_not_valid' &&
      error.response.status === 401 &&
      error.response.statusText === 'Unauthorized'
    ) {
      const refreshToken = cookies.get('refresh_token');

      if (refreshToken) {
        const tokenParts = JSON.parse(atob(refreshToken.split('.')[1]));

        // exp date in token is expressed in seconds, while now() returns milliseconds:
        const now = Math.ceil(Date.now() / 1000);

        if (tokenParts.exp > now) {
          try {
            const response = await axiosInstance.post('/token/refresh/', {
              refresh: refreshToken,
            });
            createAccessTokenCookie(response.data.accessToken);
            createRefreshTokenCookie(response.data.refreshToken);
            axiosInstance.defaults.headers.Authorization = response.data.access;
            originalRequest.headers.Authorization = response.data.access;
            return await axiosInstance(originalRequest);
          } catch (err) {
            cookies.remove('access_token', { path: '/' });
            cookies.remove('refresh_token', { path: '/' });
            cookies.remove('user', { path: '/' });
            console.error(err);
          }
        }
        window.location.href = '/login/';
      } else {
        window.location.href = '/login/';
      }
    }

    // specific error handling done elsewhere
    return Promise.reject(error);
  }
);

export default axiosInstance;
