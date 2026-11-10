import { createApp } from 'vue';
import { http } from './api/http';
import App from './App.vue';
import { router } from './router';
import { useAuth } from './stores/auth';

// Session 失效（例如逾時）時回到登入頁。
http.interceptors.response.use(
    (response) => response,
    async (error) => {
        const isUserCheck = error.config?.url === '/user';

        if (error.response?.status === 401 && !isUserCheck) {
            useAuth().clearUser();
            await router.push({ name: 'login' });
        }

        return Promise.reject(error);
    },
);

createApp(App).use(router).mount('#app');
