<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { createDailyTask, deleteDailyTask, listDailyTasks, updateDailyTask } from '../api/dailyTasks';
import { listWeeklyGoals } from '../api/weeklyGoals';
import ChecklistPanel from '../components/ChecklistPanel.vue';
import PeriodNavigator from '../components/PeriodNavigator.vue';
import ProgressSummary from '../components/ProgressSummary.vue';
import { useChecklist } from '../composables/useChecklist';
import { completionStats } from '../utils/completion';
import { addDays, formatMonthDay, formatShortDate, formatYearWeekday, mondayOf, today } from '../utils/date';

const checklist = useChecklist({
    list: listDailyTasks,
    create: (date, title, goalId) => createDailyTask(date, title, { weeklyGoalId: goalId }),
    update: updateDailyTask,
    remove: deleteDailyTask,
});

const date = ref(today());
const isToday = computed(() => date.value === today());
const stats = computed(() => completionStats(checklist.items.value));

// 各週可掛勾的週目標，key 為該週星期一。任務只能掛勾「任務日期所在週」的目標，
// 延續過來的任務可能屬於其他週，因此依需要載入多週。
const goalsByWeek = reactive({});

async function ensureWeekGoals(weekStart) {
    if (goalsByWeek[weekStart]) {
        return;
    }

    goalsByWeek[weekStart] = [];
    try {
        goalsByWeek[weekStart] = await listWeeklyGoals(weekStart);
    } catch {
        // 讀不到週目標時只是無法掛勾，不影響每日清單本身。
        delete goalsByWeek[weekStart];
    }
}

function goalOptionsFor(weekStart) {
    return (goalsByWeek[weekStart] ?? []).map((goal) => ({ value: goal.id, label: goal.title }));
}

const addPicker = computed(() => ({
    label: '歸屬',
    noneLabel: '單日任務',
    options: goalOptionsFor(mondayOf(date.value)),
    emptyHint: '本週還沒有週目標，新增的任務為單日任務。可到「週目標」頁建立目標後再掛勾。',
}));

function editPickerFor(task) {
    return {
        label: '歸屬',
        noneLabel: '單日任務',
        options: goalOptionsFor(mondayOf(task.task_date)),
        value: task.weekly_goal?.id ?? null,
    };
}

function badgesFor(task) {
    const badges = [];

    if (task.weekly_goal) {
        badges.push({ text: `週目標：${task.weekly_goal.title}`, tone: 'goal' });
    } else {
        badges.push({ text: '單日任務', tone: 'plain' });
    }

    if (task.task_date < date.value) {
        badges.push({ text: `延續自 ${formatShortDate(task.task_date)}`, tone: 'carry' });
    }

    return badges;
}

/**
 * 從之前延續過來的未完成任務改為單日任務時，日期移到目前檢視的這一天。
 * 單日任務只出現在任務日期，不移動的話會從目前的清單消失。
 */
function editTask(task, { title, option, optionChanged }) {
    if (!optionChanged) {
        return checklist.rename(task, title);
    }

    const changes = { title, weekly_goal_id: option };

    if (option === null && task.task_date < date.value && !task.is_completed) {
        changes.task_date = date.value;
    }

    return checklist.save(task, changes);
}

watch(date, (value) => checklist.load(value), { immediate: true });
watch(() => mondayOf(date.value), ensureWeekGoals, { immediate: true });
watch(
    () => [...new Set(checklist.items.value.map((task) => mondayOf(task.task_date)))],
    (weeks) => weeks.forEach(ensureWeekGoals),
);
</script>

<template>
    <div class="space-y-6">
        <PeriodNavigator
            eyebrow="每日清單"
            :title="formatMonthDay(date)"
            :subtitle="formatYearWeekday(date)"
            current-label="今天"
            :is-current="isToday"
            @prev="date = addDays(date, -1)"
            @next="date = addDays(date, 1)"
            @current="date = today()"
        >
            <ProgressSummary :pending-count="stats.pending" :completed-count="stats.completed" :percentage="stats.percentage" />
        </PeriodNavigator>

        <ChecklistPanel
            :items="checklist.items.value"
            :loading="checklist.loading.value"
            :error="checklist.error.value"
            placeholder="新增今日待辦，按 Enter 送出"
            empty-text="這一天還沒有待辦項目"
            split-by-status
            :add-picker="addPicker"
            :badges-for="badgesFor"
            :edit-picker-for="editPickerFor"
            @add="checklist.add"
            @toggle="checklist.toggle"
            @edit="editTask"
            @remove="checklist.remove"
        />

        <p class="text-center text-xs text-slate-400">掛勾週目標的任務未完成時，會每天持續顯示直到完成。點編輯可以更改歸屬。</p>
    </div>
</template>
