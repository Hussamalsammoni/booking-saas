<!-- المسار: resources/js/Pages/Profile/Partials/UpdateAvatarForm.vue -->
<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import UserAvatar from '@/Components/UserAvatar.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)

const fileInput = ref(null)
const preview = ref(null)
const clientError = ref('')
const form = useForm({ avatar: null })

const MAX_BYTES = 2 * 1024 * 1024
const ALLOWED = ['image/jpeg', 'image/png', 'image/webp']

function revokePreview() {
    if (preview.value) {
        URL.revokeObjectURL(preview.value)
        preview.value = null
    }
}

function reset() {
    revokePreview()
    form.reset()
    form.clearErrors()
    if (fileInput.value) fileInput.value.value = ''
}

function onPick(e) {
    const file = e.target.files?.[0]
    clientError.value = ''
    if (!file) return

    if (!ALLOWED.includes(file.type)) {
        clientError.value = 'الصيغ المسموحة: JPG أو PNG أو WEBP.'
        reset()
        return
    }
    if (file.size > MAX_BYTES) {
        clientError.value = 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.'
        reset()
        return
    }

    form.avatar = file
    revokePreview()
    preview.value = URL.createObjectURL(file)
}

function save() {
    form.post('/profile/avatar', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => reset(),
    })
}

function removeAvatar() {
    router.delete('/profile/avatar', { preserveScroll: true })
}

onBeforeUnmount(revokePreview)
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-bold text-gray-900">الصورة الشخصية</h2>
            <p class="mt-1 text-sm text-gray-500">
                تظهر صورتك في الشريط العلوي بدل الحرفين. الصيغ المسموحة JPG وPNG وWEBP، وبحد أقصى 2 ميغابايت.
            </p>
        </header>

        <div class="mt-6 flex flex-wrap items-center gap-5">
            <UserAvatar :user="user" :src="preview" :size="96" />

            <div class="flex flex-col gap-3">
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="onPick"
                />

                <div class="flex flex-wrap gap-2">
                    <template v-if="form.avatar">
                        <button
                            type="button"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-60"
                            :disabled="form.processing"
                            @click="save"
                        >
                            {{ form.processing ? 'جارٍ الحفظ...' : 'حفظ الصورة' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                            :disabled="form.processing"
                            @click="reset"
                        >
                            إلغاء
                        </button>
                    </template>

                    <template v-else>
                        <button
                            type="button"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                            @click="fileInput?.click()"
                        >
                            {{ user.avatar_url ? 'تغيير الصورة' : 'اختيار صورة' }}
                        </button>
                        <button
                            v-if="user.avatar_url"
                            type="button"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                            @click="removeAvatar"
                        >
                            حذف الصورة
                        </button>
                    </template>
                </div>

                <p v-if="clientError || form.errors.avatar" class="text-sm text-red-600">
                    {{ clientError || form.errors.avatar }}
                </p>
                <p v-else-if="form.recentlySuccessful" class="text-sm text-green-600">تم حفظ الصورة.</p>
            </div>
        </div>
    </section>
</template>