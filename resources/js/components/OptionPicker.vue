<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    label: { type: String, required: true },
    // [{ value, label }]
    options: { type: Array, required: true },
    // 「不選任何選項」的按鈕文字，對應 null
    noneLabel: { type: String, required: true },
});

const model = defineModel({ type: [Number, String, null], default: null });
</script>

<!-- 以按鈕列呈現的單選選項，目前選擇一眼可見。 -->
<template>
    <div class="flex flex-wrap items-center gap-1.5" role="radiogroup" :aria-label="label">
        <span class="mr-1 text-xs font-semibold text-slate-500">{{ label }}</span>

        <button
            type="button"
            role="radio"
            :aria-checked="model === null"
            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset transition-colors"
            :class="model === null ? 'bg-slate-700 text-white ring-slate-700' : 'bg-white text-slate-600 ring-slate-300 hover:bg-slate-50'"
            @click="model = null"
        >
            <AppIcon v-if="model === null" name="check" class="size-3" />
            {{ noneLabel }}
        </button>

        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            role="radio"
            :aria-checked="model === option.value"
            class="inline-flex max-w-full items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset transition-colors"
            :class="model === option.value ? 'bg-sky-600 text-white ring-sky-600' : 'bg-sky-50 text-sky-700 ring-sky-200 hover:bg-sky-100'"
            @click="model = option.value"
        >
            <AppIcon :name="model === option.value ? 'check' : 'flag'" class="size-3 shrink-0" />
            <span class="truncate">{{ option.label }}</span>
        </button>
    </div>
</template>
