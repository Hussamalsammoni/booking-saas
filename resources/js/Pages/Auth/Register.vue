<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    captchaQuestion: String,
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
        <Head title="Register" />

        <form @submit.prevent="submit">
            <!-- باقي الحقول العادية (Shop Name, Name, Email, Password) -->
            <div>
                <InputLabel for="shop_name" value="Shop Name" />
                <TextInput id="shop_name" type="text" class="mt-1 block w-full" v-model="form.shop_name" required autofocus />
                <InputError class="mt-2" :message="form.errors.shop_name" />
            </div>

            <div class="mt-4">
                <InputLabel for="name" value="Name" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <!-- حقل الكابتشا الحسابية الجديد -->
            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700">
                <InputLabel for="captcha" :value="`Security Question: ${captchaQuestion}`" class="font-bold text-indigo-600 dark:text-indigo-400" />
                <TextInput
                    id="captcha"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.captcha"
                    placeholder="Enter the result"
                    required
                />
                <InputError class="mt-2" :message="form.errors.captcha" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md">
                    Already registered?
                </Link>
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>