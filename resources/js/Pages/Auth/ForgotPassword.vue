<!-- المسار: resources/js/Pages/Auth/ForgotPassword.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout
        title="نسيت كلمة المرور؟"
        subtitle="اكتب بريدك الإلكتروني وبنرسل لك رمز تأكيد من 6 أرقام."
    >
        <Head title="نسيت كلمة المرور" />

        <form @submit.prevent="submit">
            <div class="g-field">
                <label for="email" class="g-label">البريد الإلكتروني</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="g-input ltr"
                    :class="{ invalid: form.errors.email }"
                    required
                    autofocus
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="g-err">{{ form.errors.email }}</p>
            </div>

            <button type="submit" class="g-btn" :disabled="form.processing">
                {{ form.processing ? 'جارٍ الإرسال...' : 'إرسال الرمز' }}
            </button>
        </form>

        <template #footer>
            تذكرت كلمة المرور؟ <Link :href="route('login')">سجّل الدخول</Link>
        </template>
    </GuestLayout>
</template>