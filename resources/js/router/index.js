import { createRouter, createWebHistory } from 'vue-router';
import DailyTasksPage from '../pages/DailyTasksPage.vue';
import LoginPage from '../pages/LoginPage.vue';
import RegisterPage from '../pages/RegisterPage.vue';
import WeeklyGoalsPage from '../pages/WeeklyGoalsPage.vue';
import { useAuth } from '../stores/auth';

const routes = [
    { path: '/', redirect: { name: 'daily' } },
    { path: '/daily', name: 'daily', component: DailyTasksPage, meta: { requiresAuth: true } },
    { path: '/weekly', name: 'weekly', component: WeeklyGoalsPage, meta: { requiresAuth: true } },
    { path: '/login', name: 'login', component: LoginPage, meta: { guestOnly: true } },
    { path: '/register', name: 'register', component: RegisterPage, meta: { guestOnly: true } },
    { path: '/:pathMatch(.*)*', redirect: { name: 'daily' } },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuth();

    if (!auth.state.loaded) {
        await auth.loadUser();
    }

    const loggedIn = auth.state.user !== null;

    if (to.meta.requiresAuth && !loggedIn) {
        return { name: 'login' };
    }

    if (to.meta.guestOnly && loggedIn) {
        return { name: 'daily' };
    }

    return true;
});
