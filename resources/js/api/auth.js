import { ensureCsrfCookie, http } from './http';

export async function register({ name, email, password, passwordConfirmation }) {
    await ensureCsrfCookie();
    const { data } = await http.post('/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
    });

    return data.data;
}

export async function login({ email, password }) {
    await ensureCsrfCookie();
    const { data } = await http.post('/login', { email, password });

    return data.data;
}

export async function logout() {
    await http.post('/logout');
}

export async function fetchCurrentUser() {
    const { data } = await http.get('/user');

    return data.data;
}
