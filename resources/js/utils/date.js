const WEEKDAYS = ['日', '一', '二', '三', '四', '五', '六'];

function pad(value) {
    return String(value).padStart(2, '0');
}

/**
 * Date 轉為使用者當地時區的 Y-m-d 字串。
 */
export function toDateString(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

/**
 * Y-m-d 字串轉為當地時區的 Date（避免 new Date('Y-m-d') 被當成 UTC）。
 */
export function parseDateString(value) {
    const [year, month, day] = value.split('-').map(Number);

    return new Date(year, month - 1, day);
}

export function today() {
    return toDateString(new Date());
}

export function addDays(value, days) {
    const date = parseDateString(value);
    date.setDate(date.getDate() + days);

    return toDateString(date);
}

/**
 * 取得該日期所在週的星期一。
 */
export function mondayOf(value) {
    const date = parseDateString(value);
    const offset = (date.getDay() + 6) % 7;

    return addDays(value, -offset);
}

/**
 * 將日期限制在該週範圍內：早於星期一取星期一，晚於星期日取星期日。
 * Y-m-d 字串可直接以字典序比較。
 */
export function clampToWeek(value, weekStart) {
    const weekEnd = addDays(weekStart, 6);

    if (value < weekStart) {
        return weekStart;
    }

    return value > weekEnd ? weekEnd : value;
}

/**
 * 顯示用格式，例如 11/10。
 */
export function formatShortDate(value) {
    const date = parseDateString(value);

    return `${date.getMonth() + 1}/${date.getDate()}`;
}

/**
 * 顯示用格式，例如 11 月 10 日。
 */
export function formatMonthDay(value) {
    const date = parseDateString(value);

    return `${date.getMonth() + 1} 月 ${date.getDate()} 日`;
}

/**
 * 顯示用格式，例如 2026 年 星期二。
 */
export function formatYearWeekday(value) {
    const date = parseDateString(value);

    return `${date.getFullYear()} 年 星期${WEEKDAYS[date.getDay()]}`;
}

/**
 * 顯示用格式，例如 2026/11/10（二）。
 */
export function formatDisplayDate(value) {
    const date = parseDateString(value);

    return `${date.getFullYear()}/${pad(date.getMonth() + 1)}/${pad(date.getDate())}（${WEEKDAYS[date.getDay()]}）`;
}
