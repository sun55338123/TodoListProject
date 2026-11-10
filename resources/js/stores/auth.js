import { reactive, readonly } from 'vue';
import * as authApi from '../api/auth';

const state = reactive({
    user: null,
    loaded: false,
});

async function loadUser() {
    try {
        state.user = await authApi.fetchCurrentUser();
    } catch (error) {
        if (error?.response?.status !== 401) {
            throw error;
        }
        state.user = null;
    } finally {
        state.loaded = true;
    }
}

async function login(credentials) {
    state.user = await authApi.login(credentials);
}

async function register(payload) {
    state.user = await authApi.register(payload);
}

async function logout() {
    await authApi.logout();
    state.user = null;
}

/**
 * Session 失效時由 API 攔截器呼叫，只清除前端狀態。
 */
function clearUser() {
    state.user = null;
}

export function useAuth() {
    return {
        state: readonly(state),
        loadUser,
        login,
        register,
        logout,
        clearUser,
    };
}
