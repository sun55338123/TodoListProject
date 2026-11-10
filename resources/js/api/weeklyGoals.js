import { http } from './http';

/**
 * date 可以是該週任一天，回傳該週（星期一至星期日）的目標。
 * API 的 meta（週範圍與進度）目前前端未使用，進度由 utils/completion.js 依清單即時計算。
 */
export async function listWeeklyGoals(date) {
    const { data } = await http.get('/weekly-goals', { params: { date } });

    return data.data;
}

export async function createWeeklyGoal(date, title) {
    const { data } = await http.post('/weekly-goals', { title, week_start: date });

    return data.data;
}

export async function updateWeeklyGoal(id, changes) {
    const { data } = await http.patch(`/weekly-goals/${id}`, changes);

    return data.data;
}

export async function deleteWeeklyGoal(id) {
    await http.delete(`/weekly-goals/${id}`);
}
