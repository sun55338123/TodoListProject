<script setup>
import { computed } from 'vue';
import AddItemForm from './AddItemForm.vue';
import AppIcon from './AppIcon.vue';
import ChecklistItem from './ChecklistItem.vue';

const props = defineProps({
    items: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    error: { type: String, default: '' },
    placeholder: { type: String, default: '新增項目' },
    emptyText: { type: String, default: '目前沒有項目' },
    // 是否將清單分為「未完成」與「已完成」兩區
    splitByStatus: { type: Boolean, default: false },
    // 新增表單的選填分類：{ label, noneLabel, options, emptyHint }，null 表示不顯示
    addPicker: { type: Object, default: null },
    // (item) => [{ text, tone }]
    badgesFor: { type: Function, default: () => [] },
    // (item) => 不可打勾的原因，空字串表示可以打勾
    lockedReasonFor: { type: Function, default: () => '' },
    // (item) => 編輯時的選填分類 { label, noneLabel, options, value }，null 表示只編輯標題
    editPickerFor: { type: Function, default: () => null },
});

// edit: (item, { title, option, optionChanged })
const emit = defineEmits(['add', 'toggle', 'edit', 'remove']);

const pendingItems = computed(() => props.items.filter((item) => !item.is_completed));
const completedItems = computed(() => props.items.filter((item) => item.is_completed));

const sections = computed(() => {
    if (!props.splitByStatus) {
        return [{ key: 'all', title: null, items: props.items }];
    }

    return [
        { key: 'pending', title: '未完成', items: pendingItems.value },
        { key: 'completed', title: '已完成', items: completedItems.value },
    ];
});

const allDone = computed(() => props.items.length > 0 && pendingItems.value.length === 0);
</script>

<template>
    <div class="space-y-6">
        <AddItemForm :placeholder="placeholder" :picker="addPicker" @submit="(title, option) => emit('add', title, option)" />

        <p v-if="error" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ error }}</p>

        <p v-if="loading && items.length === 0" class="py-10 text-center text-slate-400">載入中</p>

        <div
            v-else-if="items.length === 0"
            class="flex flex-col items-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 bg-white/60 py-12 text-slate-400"
        >
            <AppIcon name="clipboard" class="size-12 text-slate-300" />
            <p>{{ emptyText }}</p>
        </div>

        <template v-else>
            <section v-for="section in sections" :key="section.key" class="space-y-3">
                <h2 v-if="section.title" class="flex items-center gap-2 text-sm font-bold">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1"
                        :class="section.key === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                    >
                        <span
                            class="inline-block size-2 rounded-full"
                            :class="section.key === 'completed' ? 'bg-emerald-500' : 'bg-amber-500'"
                        ></span>
                        {{ section.title }}
                        <span class="font-semibold opacity-70">{{ section.items.length }}</span>
                    </span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </h2>

                <p
                    v-if="section.key === 'pending' && allDone"
                    class="rounded-xl bg-emerald-50 py-4 text-center text-sm font-medium text-emerald-700"
                >
                    全部完成
                </p>
                <p
                    v-else-if="section.key === 'completed' && section.items.length === 0"
                    class="py-3 text-center text-sm text-slate-400"
                >
                    尚無已完成項目
                </p>

                <ul class="space-y-2">
                    <ChecklistItem
                        v-for="item in section.items"
                        :key="item.id"
                        :item="item"
                        :badges="badgesFor(item)"
                        :locked-reason="lockedReasonFor(item)"
                        :edit-picker="editPickerFor(item)"
                        @toggle="emit('toggle', item)"
                        @save="emit('edit', item, $event)"
                        @remove="emit('remove', item)"
                    >
                        <template #extra>
                            <slot name="item-extra" :item="item" />
                        </template>
                    </ChecklistItem>
                </ul>
            </section>
        </template>
    </div>
</template>
