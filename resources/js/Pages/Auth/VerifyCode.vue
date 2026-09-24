<!-- المسار: resources/js/Pages/Auth/VerifyCode.vue -->
<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    email: { type: String, default: '' },
    status: { type: String },
    resendAfter: { type: Number, default: 60 },
})

const form = useForm({
    code: '',
})

const resendForm = useForm({})

// عدّاد إعادة الإرسال
const seconds = ref(props.resendAfter)
let timer = null

onMounted(() => {
    timer = setInterval(() => {
        if (seconds.value > 0) seconds.value--
    }, 1000)
})
onUnmounted(() => clearInterval(timer))

const onInput = () => {
    form.code = form.code.replace(/\D/g, '').slice(0, 6)
}

const submit = () => {
    form.post(route('password.verify.store'), {
        onFinish: () => form.reset('code'),
    })
}

const resend = () => {
    resendForm.post(route('password.resend'), {
        preserveScroll: true,
        onSuccess: () => {
            seconds.value = props.resendAfter
        },
    })
}
</script>

<template>
    <GuestLayout
        title="اكتب رمز التأكيد"
        :subtitle="`أرسلنا رمزاً من 6 أرقام إلى ${email}`"
    >
        <Head title="رمز التأكيد" />

        <div v-if="status" class="g-status">{{ status }}</div>

        <form @submit.prevent="submit">
            <div class="g-field">
                <label for="code" class="g-label">الرمز</label>
                <input
                    id="code"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    class="g-input ltr g-code"
                    :class="{ invalid: form.errors.code }"
                    placeholder="000000"
                    required
                    autofocus
                    autocomplete="one-time-code"
                    @input="onInput"
                />
                <p v-if="form.errors.code" class="g-err">{{ form.errors.code }}</p>
            </div>

            <button type="submit" class="g-btn" :disabled="form.processing || form.code.length < 6">
                {{ form.processing ? 'جارٍ التحقق...' : 'تأكيد الرمز' }}
            </button>
        </form>

        <div class="g-resend">
            <span v-if="seconds > 0">تقدر تطلب رمزاً جديداً بعد {{ seconds }} ثانية</span>
            <button v-else type="button" class="g-linkbtn" :disabled="resendForm.processing" @click="resend">
                إعادة إرسال الرمز
            </button>
            <p v-if="resendForm.errors.resend" class="g-err">{{ resendForm.errors.resend }}</p>
        </div>

        <template #footer>
            البريد غلط؟ <Link :href="route('password.request')">غيّر البريد</Link>
        </template>
    </GuestLayout>
</template>

<style>
.g-code {
    text-align: center;
    text-indent: .6em; /* بيعوّض المسافة الزايدة بآخر الأرقام حتى يبقى الرمز بالنص */
    letter-spacing: .6em;
    font-size: 1.6rem;
    font-weight: 600;
}
.g-resend { margin-top: 1.1rem; text-align: center; font-size: .9rem; color: var(--ink-2); }
.g-linkbtn { padding: 0; border: 0; background: none; color: var(--cobalt); font-size: .9rem; font-weight: 600; }
.g-linkbtn:hover { text-decoration: underline; }
.g-linkbtn:disabled { opacity: .6; cursor: wait; }
</style>