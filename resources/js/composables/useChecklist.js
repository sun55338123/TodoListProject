import { ref } from 'vue';
import { extractErrors } from '../api/http';

/**
 * 每日清單與週目標共用的清單操作。
 * 透過注入的 API 函式運作，不依賴特定資源。
 *
 * 打勾、編輯、刪除採用「先更新畫面」：立即修改本地資料，背景送出請求，失敗時還原並顯示錯誤。
 *
 * @param {object} api
 * @param {(date: string) => Promise<Array>} api.list
 * @param {(date: string, title: string, options?: object) => Promise<object>} api.create
 * @param {(id: number, changes: object, viewedDate: string) => Promise<object>} api.update
 * @param {(id: number) => Promise<void>} api.remove
 */
export function useChecklist(api) {
    const items = ref([]);
    const loading = ref(false);
    const error = ref('');

    let currentDate = null;

    function fail(e) {
        error.value = extractErrors(e).message;
    }

    function findItem(id) {
        return items.value.find((item) => item.id === id);
    }

    async function load(date) {
        currentDate = date;
        loading.value = true;
        error.value = '';

        try {
            const result = await api.list(date);
            // 切換日期期間可能有較早的請求晚回來，只採用最新日期的結果。
            if (currentDate === date) {
                items.value = result;
            }
        } catch (e) {
            if (currentDate === date) {
                fail(e);
            }
        } finally {
            if (currentDate === date) {
                loading.value = false;
            }
        }
    }

    /**
     * 新增需要伺服器產生的 id，因此等回應後才加入清單。
     */
    async function add(title, options = {}) {
        const date = currentDate;
        error.value = '';

        try {
            const created = await api.create(date, title, options);
            if (currentDate === date) {
                items.value.push(created);
            }
        } catch (e) {
            fail(e);
        }
    }

    async function patch(item, changes) {
        const target = findItem(item.id);
        if (!target) {
            return;
        }

        const previous = Object.fromEntries(Object.keys(changes).map((key) => [key, target[key]]));
        Object.assign(target, changes);
        error.value = '';

        try {
            await api.update(item.id, changes, currentDate);
        } catch (e) {
            // 只有在畫面仍是這次修改的值時才還原，避免覆蓋之後的操作。
            const current = findItem(item.id);
            if (current && Object.keys(changes).every((key) => current[key] === changes[key])) {
                Object.assign(current, previous);
            }
            fail(e);
        }
    }

    /**
     * 等伺服器回應後才更新畫面，用於伺服器會連帶改變其他欄位的修改（例如掛勾週目標）。
     */
    async function save(item, changes) {
        error.value = '';

        try {
            const updated = await api.update(item.id, changes, currentDate);
            const target = findItem(item.id);
            if (target) {
                Object.assign(target, updated);
            }
        } catch (e) {
            fail(e);
        }
    }

    function toggle(item) {
        return patch(item, { is_completed: !item.is_completed });
    }

    function rename(item, title) {
        return patch(item, { title });
    }

    async function remove(item) {
        const date = currentDate;
        const index = items.value.findIndex((entry) => entry.id === item.id);
        if (index === -1) {
            return;
        }

        const [removed] = items.value.splice(index, 1);
        error.value = '';

        try {
            await api.remove(item.id);
        } catch (e) {
            if (currentDate === date) {
                items.value.splice(Math.min(index, items.value.length), 0, removed);
            }
            fail(e);
        }
    }

    return { items, loading, error, load, reload: () => load(currentDate), add, toggle, rename, save, remove };
}
