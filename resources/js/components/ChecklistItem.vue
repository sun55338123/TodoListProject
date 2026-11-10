<script setup>
import { nextTick, ref } from 'vue';
import AppIcon from './AppIcon.vue';
import OptionPicker from './OptionPicker.vue';

const props = defineProps({
    item: { type: Object, required: true },
    // 標題下方的標籤：[{ text, tone: 'goal' | 'carry' | 'plain' }]
    badges: { type: Array, default: () => [] },
    // 不可手動打勾時的原因；空字串表示可以打勾
    lockedReason: { type: String, default: '' },
    // 編輯時的選填分類：{ label, noneLabel, options: [{ value, label }], value }。null 表示只編輯標題。
    editPicker: { type: Object, default: null },
});

// save: { title, option, optionChanged }
const emit = defineEmits(['toggle', 'save', 'remove']);

const BADGE_TONES = {
    goal: 'bg-sky-50 text-sky-700 ring-sky-200',
    carry: 'bg-amber-50 text-amber-700 ring-amber-200',
    plain: 'bg-slate-50 text-slate-500 ring-slate-200',
};

const editing = ref(false);
const draft = ref('');
const draftOption = ref(null);
const input = ref(null);

async function startEdit() {
    draft.value = props.item.title;
    draftOption.value = props.editPicker?.value ?? null;
    editing.value = true;
    await nextTick();
    input.value?.focus();
}

function saveEdit() {
    const title = draft.value.trim();

    if (title === '') {
        return;
    }

    editing.value = false;

    const titleChanged = title !== props.item.title;
    const optionChanged = props.editPicker !== null && draftOption.value !== props.editPicker.value;

    if (titleChanged || optionChanged) {
        emit('save', { title, option: draftOption.value, optionChanged });
    }
}

function cancelEdit() {
    editing.value = false;
}
</script>

<template>
    <li
        class="rounded-xl border px-4 py-3 transition-colors"
        :class="
            item.is_completed
                ? 'border-emerald-100 bg-emerald-50/70'
                : 'border-slate-200 border-l-4 border-l-amber-400 bg-white shadow-sm hover:border-slate-300 hover:border-l-amber-400'
        "
    >
        <div class="flex items-center gap-3">
            <button
                type="button"
                role="checkbox"
                :aria-checked="item.is_completed"
                :aria-label="`標記「${item.title}」為${item.is_completed ? '未完成' : '已完成'}`"
                :aria-disabled="lockedReason !== ''"
                :title="lockedReason || undefined"
                class="grid size-7 shrink-0 place-items-center rounded-full border-2 transition-colors"
                :class="[
                    item.is_completed
                        ? 'border-emerald-500 bg-emerald-500 text-white'
                        : 'border-slate-300 text-transparent',
                    lockedReason
                        ? 'cursor-not-allowed opacity-60'
                        : item.is_completed
                          ? 'hover:bg-emerald-600'
                          : 'hover:border-emerald-500 hover:text-emerald-400',
                ]"
                @click="lockedReason || emit('toggle')"
            >
                <AppIcon name="check" class="size-4" />
            </button>

            <div class="min-w-0 flex-1">
                <div v-if="editing" class="space-y-2">
                    <input
                        ref="input"
                        v-model="draft"
                        maxlength="255"
                        aria-label="項目名稱"
                        class="w-full rounded-lg border border-emerald-400 px-2 py-1 ring-4 ring-emerald-100 focus:outline-none"
                        @keydown.enter.prevent="saveEdit"
                        @keydown.esc.prevent="cancelEdit"
                    />
                    <OptionPicker
                        v-if="editPicker && editPicker.options.length > 0"
                        v-model="draftOption"
                        :label="editPicker.label"
                        :none-label="editPicker.noneLabel"
                        :options="editPicker.options"
                    />
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg bg-emerald-600 px-3 py-1 text-sm font-semibold text-white hover:bg-emerald-700 disabled:bg-slate-300"
                            :disabled="draft.trim() === ''"
                            @click="saveEdit"
                        >
                            儲存
                        </button>
                        <button type="button" class="rounded-lg px-3 py-1 text-sm text-slate-500 hover:bg-slate-100" @click="cancelEdit">
                            取消
                        </button>
                    </div>
                </div>
                <span
                    v-else
                    class="block break-all text-base"
                    :class="item.is_completed ? 'text-slate-400 line-through decoration-slate-300' : 'font-medium text-slate-800'"
                >
                    {{ item.title }}
                </span>

                <div v-if="!editing && badges.length > 0" class="mt-1 flex flex-wrap gap-1.5">
                    <span
                        v-for="badge in badges"
                        :key="badge.text"
                        class="rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                        :class="BADGE_TONES[badge.tone]"
                    >
                        {{ badge.text }}
                    </span>
                </div>
            </div>

            <div v-if="!editing" class="flex shrink-0 items-center gap-1">
                <button
                    type="button"
                    class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    :aria-label="`編輯「${item.title}」`"
                    title="編輯"
                    @click="startEdit"
                >
                    <AppIcon name="pencil" class="size-4" />
                </button>
                <button
                    type="button"
                    class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                    :aria-label="`刪除「${item.title}」`"
                    title="刪除"
                    @click="emit('remove')"
                >
                    <AppIcon name="trash" class="size-4" />
                </button>
            </div>
        </div>

        <slot name="extra" />
    </li>
</template>
