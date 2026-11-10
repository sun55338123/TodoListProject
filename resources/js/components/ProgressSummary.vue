<script setup>
import ProgressRing from './ProgressRing.vue';

defineProps({
    pendingCount: { type: Number, required: true },
    completedCount: { type: Number, required: true },
    percentage: { type: Number, required: true },
});
</script>

<!-- 完成率圓環與未完成、已完成數量。設計為放在 PeriodNavigator 的漸層底色內。 -->
<template>
    <div class="flex items-center gap-4 bg-black/10 px-5 py-4">
        <ProgressRing :percentage="percentage" />

        <div class="grid flex-1 grid-cols-2 gap-3">
            <div class="rounded-xl bg-white/15 px-3 py-2">
                <p class="flex items-center gap-1.5 text-xs text-emerald-50">
                    <span class="inline-block size-2 rounded-full bg-amber-300"></span>
                    未完成
                </p>
                <p class="text-2xl font-bold">{{ pendingCount }}</p>
            </div>
            <div class="rounded-xl bg-white/15 px-3 py-2">
                <p class="flex items-center gap-1.5 text-xs text-emerald-50">
                    <span class="inline-block size-2 rounded-full bg-white"></span>
                    已完成
                </p>
                <p class="text-2xl font-bold">{{ completedCount }}</p>
            </div>
        </div>
    </div>
</template>
