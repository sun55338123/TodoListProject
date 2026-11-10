<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { extractErrors } from '../api/http';
import AuthCard from '../components/AuthCard.vue';
import FormField from '../components/FormField.vue';
import { useAuth } from '../stores/auth';

const router = useRouter();
const auth = useAuth();

const form = reactive({ email: '', password: '' });
const fieldErrors = ref({});
const message = ref('');
const submitting = ref(false);

async function submit() {
    submitting.value = true;
    fieldErrors.value = {};
    message.value = '';

    try {
        await auth.login(form);
        await router.push({ name: 'daily' });
    } catch (error) {
        const { fields, message: text } = extractErrors(error);
        fieldErrors.value = fields;
        message.value = Object.keys(fields).length === 0 ? text : '';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <AuthCard title="登入 TodoList" subtitle="管理你的每日清單與週目標" submit-label="登入" :error="message" :submitting="submitting" @submit="submit">
        <FormField v-model="form.email" label="Email" type="email" autocomplete="username" :errors="fieldErrors.email" />
        <FormField v-model="form.password" label="密碼" type="password" autocomplete="current-password" :errors="fieldErrors.password" />

        <template #footer>
            還沒有帳號？<RouterLink :to="{ name: 'register' }" class="text-emerald-700 hover:underline">註冊</RouterLink>
        </template>
    </AuthCard>
</template>
