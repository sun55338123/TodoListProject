import { http } from './http';

/**
 * 回傳該日的任務，包含延到該日的週目標子任務。
 */
export async function listDailyTasks(date) {
    const { data } = await http.get('/daily-tasks', { params: { date } });

    return data.data;
}

/**
 * @param {string} date 任務日期
 * @param {string} title
 * @param {{weeklyGoalId?: number|null}} [options] 掛勾的週目標
 */
export async function createDailyTask(date, title, options = {}) {
    const { data } = await http.post('/daily-tasks', {
        title,
        task_date: date,
        weekly_goal_id: options.weeklyGoalId ?? null,
    });

    return data.data;
}

/**
 * @param {number} id
 * @param {object} changes
 * @param {string} viewedDate 使用者正在檢視的日期。打勾時作為完成日期（子任務可能是從之前延過來的）。
 */
export async function updateDailyTask(id, changes, viewedDate) {
    const payload = changes.is_completed === true ? { ...changes, completed_on: viewedDate } : changes;
    const { data } = await http.patch(`/daily-tasks/${id}`, payload);

    return data.data;
}

export async function deleteDailyTask(id) {
    await http.delete(`/daily-tasks/${id}`);
}
