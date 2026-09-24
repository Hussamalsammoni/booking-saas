<!-- المسار: resources/js/Pages/Auth/ResetPassword.vue -->
<script setup>
import { ref } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Eye, EyeOff } from 'lucide-vue-next'

const form = useForm({
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout
        title="كلمة مرور جديدة"
        subtitle="تم تأكيد الرمز، اختر كلمة مرور جديدة لحسابك."
    >
        <Head title="كلمة مرور جديدة" />

        <form @submit.prevent="submit">
            <div class="g-field">
                <label for="password" class="g-label">كلمة المرور الجديدة</label>
                <div class="g-pw">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="g-input ltr"
                        :class="{ invalid: form.errors.password }"
                        required
                        autofocus
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        class="g-eye"
                        :aria-label="showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                        @click="showPassword = !showPassword"
                    >
                        <EyeOff v-if="showPassword" :size="18" />
                        <Eye v-else :size="18" />
                    </button>
                </div>
                <p v-if="form.errors.password" class="g-err">{{ form.errors.password }}</p>
            </div>

            <div class="g-field">
                <label for="password_confirmation" class="g-label">تأكيد كلمة المرور</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="showPassword ? 'text' : 'password'"
                    class="g-input ltr"
                    :class="{ invalid: form.errors.password_confirmation }"
                    required
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password_confirmation" class="g-err">
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <button type="submit" class="g-btn" :disabled="form.processing">
                {{ form.processing ? 'جارٍ الحفظ...' : 'حفظ كلمة المرور' }}
            </button>
        </form>
    </GuestLayout>
</template>