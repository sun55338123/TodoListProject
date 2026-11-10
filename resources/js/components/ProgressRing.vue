<script setup>
import { computed } from 'vue';

const props = defineProps({
    percentage: { type: Number, required: true },
    label: { type: String, default: '完成率' },
});

const RADIUS = 34;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

const clamped = computed(() => Math.min(100, Math.max(0, props.percentage)));
const offset = computed(() => CIRCUMFERENCE * (1 - clamped.value / 100));
</script>

<template>
    <div
        class="relative size-24 shrink-0"
        role="progressbar"
        :aria-label="label"
        :aria-valuenow="clamped"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <svg viewBox="0 0 80 80" class="size-full -rotate-90">
            <circle cx="40" cy="40" :r="RADIUS" fill="none" stroke-width="8" class="stroke-white/25" />
            <circle
                cx="40"
                cy="40"
                :r="RADIUS"
                fill="none"
                stroke-width="8"
                stroke-linecap="round"
                class="stroke-white transition-[stroke-dashoffset] duration-500"
                :stroke-dasharray="CIRCUMFERENCE"
                :stroke-dashoffset="offset"
            />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
            <span class="text-xl font-bold leading-none">{{ clamped }}%</span>
            <span class="mt-1 text-[11px] opacity-80">{{ label }}</span>
        </div>
    </div>
</template>
