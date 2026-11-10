<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { extractErrors } from '../api/http';
import AuthCard from '../components/AuthCard.vue';
import FormField from '../components/FormField.vue';
import { useAuth } from '../stores/auth';

const router = useRouter();
const auth = useAuth();

const form = reactive({ name: '', email: '', password: '', passwordConfirmation: '' });
const fieldErrors = ref({});
const message = ref('');
const submitting = ref(false);

async function submit() {
    submitting.value = true;
    fieldErrors.value = {};
    message.value = '';

    try {
        await auth.register(form);
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
    <AuthCard title="建立帳號" subtitle="註冊後即可開始使用" submit-label="建立帳號" :error="message" :submitting="submitting" @submit="submit">
        <FormField v-model="form.name" label="名稱" autocomplete="name" :errors="fieldErrors.name" />
        <FormField v-model="form.email" label="Email" type="email" autocomplete="username" :errors="fieldErrors.email" />
        <FormField v-model="form.password" label="密碼" type="password" autocomplete="new-password" :errors="fieldErrors.password" />
        <FormField v-model="form.passwordConfirmation" label="確認密碼" type="password" autocomplete="new-password" />

        <template #footer>
            已經有帳號？<RouterLink :to="{ name: 'login' }" class="text-emerald-700 hover:underline">登入</RouterLink>
        </template>
    </AuthCard>
</template>
