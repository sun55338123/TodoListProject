import axios from 'axios';

/**
 * API 用的 axios 實例。
 * 驗證採用 Sanctum Cookie Session：瀏覽器自動帶 Session Cookie，
 * axios 自動讀取 XSRF-TOKEN Cookie 並放入 X-XSRF-TOKEN 標頭，前端不保存任何 Token。
 */
export const http = axios.create({
    baseURL: '/api',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

/**
 * 登入或註冊前取得 CSRF Cookie。
 */
export function ensureCsrfCookie() {
    return axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

/**
 * 從 API 錯誤取出可顯示的訊息。
 * 422 回傳欄位錯誤物件，其他狀態回傳一般訊息。
 */
export function extractErrors(error) {
    const response = error?.response;

    if (response?.status === 422) {
        return { fields: response.data.errors ?? {}, message: response.data.message ?? '' };
    }

    if (response?.status === 429) {
        return { fields: {}, message: '嘗試次數過多，請稍後再試。' };
    }

    return { fields: {}, message: '發生錯誤，請稍後再試。' };
}
