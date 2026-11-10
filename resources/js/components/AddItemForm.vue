<script setup>
import { ref } from 'vue';
import AppIcon from './AppIcon.vue';
import OptionPicker from './OptionPicker.vue';

const props = defineProps({
    placeholder: { type: String, default: '新增項目' },
    // 選填的分類，例如可掛勾的週目標：{ label, noneLabel, options: [{ value, label }], emptyHint }。null 表示不顯示。
    picker: { type: Object, default: null },
    compact: { type: Boolean, default: false },
});

const emit = defineEmits(['submit']);

const title = ref('');
const selected = ref(null);

function submit() {
    const value = title.value.trim();

    if (value === '') {
        return;
    }

    emit('submit', value, selected.value);
    title.value = '';
}
</script>

<template>
    <form
        class="border bg-white focus-within:border-emerald-400 focus-within:ring-4 focus-within:ring-emerald-100"
        :class="compact ? 'rounded-xl border-slate-200 p-1' : 'rounded-2xl border-slate-200 p-2 shadow-sm'"
        @submit.prevent="submit"
    >
        <div class="flex items-center gap-2">
            <AppIcon name="plus" class="ml-2 shrink-0 text-slate-400" :class="compact ? 'size-4' : 'size-5'" />
            <input
                v-model="title"
                maxlength="255"
                :placeholder="placeholder"
                class="min-w-0 flex-1 bg-transparent px-1 placeholder:text-slate-400 focus:outline-none"
                :class="compact ? 'py-1 text-sm' : 'py-2 text-base'"
            />
            <button
                type="submit"
                class="rounded-xl bg-emerald-600 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                :class="compact ? 'px-3 py-1 text-sm' : 'px-5 py-2'"
                :disabled="title.trim() === ''"
            >
                新增
            </button>
        </div>

        <div v-if="props.picker" class="mt-2 border-t border-slate-100 px-2 pt-2 pb-1">
            <OptionPicker
                v-if="props.picker.options.length > 0"
                v-model="selected"
                :label="props.picker.label"
                :none-label="props.picker.noneLabel"
                :options="props.picker.options"
            />
            <p v-else-if="props.picker.emptyHint" class="text-xs text-slate-400">{{ props.picker.emptyHint }}</p>
        </div>
    </form>
</template>
