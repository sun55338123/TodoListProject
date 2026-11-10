<script setup>
import { computed, ref, watch } from 'vue';
import { createDailyTask } from '../api/dailyTasks';
import { extractErrors } from '../api/http';
import { createWeeklyGoal, deleteWeeklyGoal, listWeeklyGoals, updateWeeklyGoal } from '../api/weeklyGoals';
import AddItemForm from '../components/AddItemForm.vue';
import AppIcon from '../components/AppIcon.vue';
import ChecklistPanel from '../components/ChecklistPanel.vue';
import PeriodNavigator from '../components/PeriodNavigator.vue';
import ProgressSummary from '../components/ProgressSummary.vue';
import { useChecklist } from '../composables/useChecklist';
import { completionStats } from '../utils/completion';
import { addDays, clampToWeek, formatMonthDay, formatShortDate, mondayOf, today } from '../utils/date';

const checklist = useChecklist({
    list: listWeeklyGoals,
    create: createWeeklyGoal,
    update: updateWeeklyGoal,
    remove: deleteWeeklyGoal,
});

// 永遠以星期一代表一週
const weekStart = ref(mondayOf(today()));
const isCurrentWeek = computed(() => weekStart.value === mondayOf(today()));
const weekTitle = computed(() => `${formatMonthDay(weekStart.value)} 至 ${formatMonthDay(addDays(weekStart.value, 6))}`);
const weekSubtitle = computed(() => `${weekStart.value.slice(0, 4)} 年 星期一至星期日`);
// 依目前清單即時計算，打勾後不需等待伺服器回應
const stats = computed(() => completionStats(checklist.items.value));

const subtaskError = ref('');

function lockedReasonFor(goal) {
    return goal.has_subtasks ? '此目標有子任務，子任務全部完成時會自動打勾' : '';
}

/**
 * 子任務加入每日清單的日期：本週為今天；其他週則取該週範圍內最接近今天的一天。
 * 子任務未完成會每天延續，因此加在過去的週也會出現在今天的清單。
 */
async function addSubtask(goal, title) {
    subtaskError.value = '';

    try {
        await createDailyTask(clampToWeek(today(), weekStart.value), title, { weeklyGoalId: goal.id });
        await checklist.reload();
    } catch (e) {
        subtaskError.value = extractErrors(e).message;
    }
}

watch(weekStart, (value) => checklist.load(value), { immediate: true });
</script>

<template>
    <div class="space-y-6">
        <PeriodNavigator
            eyebrow="週目標"
            :title="weekTitle"
            :subtitle="weekSubtitle"
            current-label="本週"
            :is-current="isCurrentWeek"
            @prev="weekStart = addDays(weekStart, -7)"
            @next="weekStart = addDays(weekStart, 7)"
            @current="weekStart = mondayOf(today())"
        >
            <ProgressSummary :pending-count="stats.pending" :completed-count="stats.completed" :percentage="stats.percentage" />
        </PeriodNavigator>

        <p v-if="subtaskError" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ subtaskError }}</p>

        <ChecklistPanel
            :items="checklist.items.value"
            :loading="checklist.loading.value"
            :error="checklist.error.value"
            placeholder="新增本週目標，按 Enter 送出"
            empty-text="這一週還沒有目標"
            :locked-reason-for="lockedReasonFor"
            @add="checklist.add"
            @toggle="checklist.toggle"
            @edit="(goal, { title }) => checklist.rename(goal, title)"
            @remove="checklist.remove"
        >
            <template #item-extra="{ item: goal }">
                <div class="mt-3 space-y-2 border-t border-slate-100 pt-3 pl-10">
                    <div v-if="goal.has_subtasks" class="space-y-1">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>子任務 {{ goal.subtask_completed }} / {{ goal.subtask_total }}</span>
                            <span class="font-semibold text-emerald-700">{{ goal.subtask_percentage }}%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-emerald-500 transition-all" :style="{ width: `${goal.subtask_percentage}%` }"></div>
                        </div>
                    </div>

                    <ul v-if="goal.has_subtasks" class="space-y-1">
                        <li v-for="task in goal.subtasks" :key="task.id" class="flex items-center gap-2 text-sm">
                            <span
                                class="grid size-4 shrink-0 place-items-center rounded-full border"
                                :class="task.is_completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300'"
                            >
                                <AppIcon v-if="task.is_completed" name="check" class="size-3" />
                            </span>
                            <span class="min-w-0 flex-1 truncate" :class="task.is_completed ? 'text-slate-400 line-through' : 'text-slate-700'">
                                {{ task.title }}
                            </span>
                            <span class="shrink-0 text-xs text-slate-400">
                                {{ task.is_completed ? `${formatShortDate(task.completed_on)} 完成` : `${formatShortDate(task.task_date)} 起` }}
                            </span>
                        </li>
                    </ul>

                    <AddItemForm compact placeholder="新增子任務（加入每日清單）" @submit="(title) => addSubtask(goal, title)" />
                </div>
            </template>
        </ChecklistPanel>

        <p class="text-center text-xs text-slate-400">子任務在每日清單打勾。子任務未完成時，會每天出現在每日清單直到完成。</p>
    </div>
</template>
