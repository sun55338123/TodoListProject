<script setup>
defineProps({
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    autocomplete: { type: String, default: 'off' },
    errors: { type: Array, default: () => [] },
});

const model = defineModel({ type: String, default: '' });
</script>

<template>
    <label class="block space-y-1">
        <span class="text-sm font-medium text-slate-700">{{ label }}</span>
        <input
            v-model="model"
            :type="type"
            :autocomplete="autocomplete"
            required
            class="w-full rounded-xl border px-3 py-2.5 focus:outline-none focus:ring-4"
            :class="errors.length ? 'border-rose-400 focus:ring-rose-100' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100'"
        />
        <span v-for="message in errors" :key="message" class="block text-sm text-rose-600">{{ message }}</span>
    </label>
</template>
