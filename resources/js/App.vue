<script setup>
import { computed } from 'vue';
import { RouterLink, RouterView, useRouter } from 'vue-router';
import AppIcon from './components/AppIcon.vue';
import { useAuth } from './stores/auth';

const auth = useAuth();
const router = useRouter();

const initial = computed(() => auth.state.user?.name?.trim().charAt(0).toUpperCase() ?? '');

const tabs = [
    { name: 'daily', label: '每日清單', icon: 'calendar' },
    { name: 'weekly', label: '週目標', icon: 'flag' },
];

async function logout() {
    await auth.logout();
    await router.push({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-emerald-50/80 via-slate-50 to-slate-100">
        <header v-if="auth.state.user" class="sticky top-0 z-10 border-b border-slate-200/70 bg-white/80 backdrop-blur">
            <div class="mx-auto flex max-w-2xl items-center gap-3 px-4 py-3">
                <RouterLink :to="{ name: 'daily' }" class="flex items-center gap-2 font-bold text-slate-800">
                    <span class="grid size-8 place-items-center rounded-lg bg-emerald-600 text-white shadow-sm">
                        <AppIcon name="check" class="size-5" />
                    </span>
                    <span class="hidden sm:inline">TodoList</span>
                </RouterLink>

                <nav class="ml-2 flex rounded-xl bg-slate-100 p-1">
                    <RouterLink
                        v-for="tab in tabs"
                        :key="tab.name"
                        :to="{ name: tab.name }"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"
                        active-class="bg-white !text-emerald-700 shadow-sm"
                    >
                        <AppIcon :name="tab.icon" class="size-4" />
                        {{ tab.label }}
                    </RouterLink>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    <span
                        class="grid size-8 place-items-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700"
                        :title="auth.state.user.name"
                    >
                        {{ initial }}
                    </span>
                    <span class="hidden max-w-32 truncate text-sm text-slate-600 sm:inline">{{ auth.state.user.name }}</span>
                    <button
                        type="button"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                        aria-label="登出"
                        title="登出"
                        @click="logout"
                    >
                        <AppIcon name="logout" class="size-5" />
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-2xl px-4 py-6 sm:py-8">
            <RouterView />
        </main>
    </div>
</template>
