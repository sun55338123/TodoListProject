<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    error: { type: String, default: '' },
    submitting: { type: Boolean, default: false },
    submitLabel: { type: String, required: true },
});

const emit = defineEmits(['submit']);
</script>

<template>
    <div class="mx-auto mt-8 max-w-sm sm:mt-16">
        <div class="mb-6 flex flex-col items-center gap-3 text-center">
            <span class="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-900/20">
                <AppIcon name="check" class="size-8" />
            </span>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ title }}</h1>
                <p v-if="subtitle" class="mt-1 text-sm text-slate-500">{{ subtitle }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">
            <p v-if="error" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ error }}</p>

            <form class="space-y-4" @submit.prevent="emit('submit')">
                <slot />

                <button
                    type="submit"
                    class="w-full rounded-xl bg-emerald-600 py-2.5 font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="submitting"
                >
                    {{ submitLabel }}
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">
            <slot name="footer" />
        </p>
    </div>
</template>
