<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('admin.login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-900">
        <div class="w-full max-w-sm bg-gray-800 p-8 rounded-lg shadow-lg">
            <h1 class="text-white text-xl font-bold mb-6 text-center">لوحة تحكم الإدارة</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="text-gray-300 text-sm">البريد الإلكتروني</label>
                    <input v-model="form.email" type="email"
                        class="w-full mt-1 rounded bg-gray-700 text-white border-gray-600" />
                    <div v-if="form.errors.email" class="text-red-400 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label class="text-gray-300 text-sm">كلمة المرور</label>
                    <input v-model="form.password" type="password"
                        class="w-full mt-1 rounded bg-gray-700 text-white border-gray-600" />
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded">
                    دخول
                </button>
            </form>
        </div>
    </div>
</template>