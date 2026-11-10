/**
 * 計算完成統計，規則與後端 App\Support\CompletionRate 一致：
 * 四捨五入為整數百分比，沒有項目時為 0。
 */
export function completionStats(items) {
    const total = items.length;
    const completed = items.filter((item) => item.is_completed).length;

    return {
        total,
        completed,
        pending: total - completed,
        percentage: total === 0 ? 0 : Math.round((completed / total) * 100),
    };
}
