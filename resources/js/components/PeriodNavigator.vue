<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    eyebrow: { type: String, required: true },
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    currentLabel: { type: String, required: true },
    isCurrent: { type: Boolean, default: false },
});

const emit = defineEmits(['prev', 'next', 'current']);
</script>

<!-- 期間切換（上一個、下一個、回到目前）。下方內容由 default slot 提供。 -->
<template>
    <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-900/10">
        <div class="flex items-center gap-2 px-5 pt-5">
            <button
                type="button"
                class="grid size-9 place-items-center rounded-full bg-white/15 hover:bg-white/25"
                aria-label="上一個"
                @click="emit('prev')"
            >
                <AppIcon name="chevron-left" class="size-5" />
            </button>

            <div class="min-w-0 flex-1 text-center">
                <p class="text-xs font-medium tracking-wider text-emerald-100">{{ eyebrow }}</p>
                <h1 class="truncate text-2xl font-bold">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-emerald-50/90">{{ subtitle }}</p>
            </div>

            <button
                type="button"
                class="grid size-9 place-items-center rounded-full bg-white/15 hover:bg-white/25"
                aria-label="下一個"
                @click="emit('next')"
            >
                <AppIcon name="chevron-right" class="size-5" />
            </button>
        </div>

        <div class="flex h-8 items-center justify-center">
            <span v-if="isCurrent" class="rounded-full bg-white/20 px-3 py-0.5 text-xs font-medium">{{ currentLabel }}</span>
            <button
                v-else
                type="button"
                class="rounded-full bg-white px-3 py-0.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                @click="emit('current')"
            >
                回到{{ currentLabel }}
            </button>
        </div>

        <slot />
    </section>
</template>
