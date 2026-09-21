<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    captchaQuestion: String,
    trialDays: Number,
});

const form = useForm({
    shop_name: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    captcha: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation', 'captcha'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="تسجيل محل جديد" />

        <div dir="rtl">
            <div class="mb-6 text-center">
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    ابدأ بإنشاء محلك
                </h1>
                <p v-if="trialDays" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    جرّب مجاناً لمدة {{ trialDays }} يوم، بدون بطاقة دفع.
                </p>
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="shop_name" value="اسم المحل" />
                    <TextInput id="shop_name" type="text" class="mt-1 block w-full" v-model="form.shop_name" required autofocus autocomplete="organization" />
                    <InputError class="mt-2" :message="form.errors.shop_name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="name" value="اسمك" />
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="email" value="البريد الإلكتروني" />
                    <TextInput id="email" type="email" dir="ltr" class="mt-1 block w-full text-left" v-model="form.email" required autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="كلمة المرور" />
                    <TextInput id="password" type="password" dir="ltr" class="mt-1 block w-full text-left" v-model="form.password" required autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password_confirmation" value="تأكيد كلمة المرور" />
                    <TextInput id="password_confirmation" type="password" dir="ltr" class="mt-1 block w-full text-left" v-model="form.password_confirmation" required autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <!-- السؤال الأمني -->
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700">
                    <InputLabel for="captcha" :value="`سؤال أمني: ${captchaQuestion}`" class="font-bold text-indigo-600 dark:text-indigo-400" />
                    <TextInput
                        id="captcha"
                        type="number"
                        inputmode="numeric"
                        dir="ltr"
                        class="mt-1 block w-full text-left"
                        v-model="form.captcha"
                        placeholder="اكتب الناتج"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.captcha" />
                </div>

                <div class="mt-6">
                    <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        إنشاء المحل
                    </PrimaryButton>
                </div>

                <p class="mt-4 text-center text-xs leading-6 text-gray-500 dark:text-gray-400">
                    عندك محل مسجّل؟ افتح رابط محلك وسجّل الدخول من هناك.
                </p>
            </form>
        </div>
    </GuestLayout>
</template>