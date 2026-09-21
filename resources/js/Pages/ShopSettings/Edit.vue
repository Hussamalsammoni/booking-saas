<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    shop: Object,
});

const page = usePage();

const form = useForm({
    shop_name: props.shop.shop_name || '',
    description: props.shop.description || '',
    phone: props.shop.phone || '',
    primary_color: props.shop.primary_color || props.shop.color || '#ff0569',
    secondary_color: props.shop.secondary_color || '#1E1E24',
    bg_color: props.shop.bg_color || '#F9F8F6',
    text_color: props.shop.text_color || '#2D2D2D',
    logo: null,
    cover: null,
});

const logoPreview = ref(props.shop.logo_url || null);
const coverPreview = ref(props.shop.cover_url || null);

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
};

const onCoverChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.cover = file;
    coverPreview.value = URL.createObjectURL(file);
};

const submit = () => {
    form.post(route('shop-settings.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const colorFields = [
    { key: 'primary_color', label: 'اللون الرئيسي', hint: 'الأزرار والعناصر التفاعلية' },
    { key: 'secondary_color', label: 'اللون الثانوي', hint: 'خلفية الغلاف' },
    { key: 'bg_color', label: 'لون الخلفية', hint: 'خلفية صفحة الحجز' },
    { key: 'text_color', label: 'لون النصوص', hint: 'العناوين والنصوص' },
];
</script>

<template>
    <Head title="إعدادات المحل" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    إعدادات المحل
                </h2>

                <p class="mt-1.5 text-sm text-gray-500">
                    خصص هوية محلك كما تظهر لعملائك
                </p>
            </div>
        </template>


        <div class="min-h-screen bg-[#f8fafc]">
            <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">

                <!-- Success flash -->
                <div
                    v-if="page.props.flash?.success"
                    class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ page.props.flash.success }}
                </div>


                <form @submit.prevent="submit" class="space-y-6">

                    <!-- ============ Basic Info ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 text-indigo-600 ring-1 ring-indigo-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M13 21V11l6 4v6M9 9h.01M9 12h.01M9 15h.01" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">المعلومات الأساسية</h3>
                                <p class="mt-1 text-sm text-gray-500">اسم ووصف ورقم محلك</p>
                            </div>
                        </div>


                        <div class="space-y-5 px-6 py-7 sm:px-8">

                            <div>
                                <label for="shop_name" class="mb-2 block text-sm font-semibold text-gray-700">
                                    اسم المحل
                                </label>

                                <input
                                    id="shop_name"
                                    type="text"
                                    v-model="form.shop_name"
                                    required
                                    class="block w-full rounded-xl border-gray-200 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                <InputError class="mt-2" :message="form.errors.shop_name" />
                            </div>

                            <div>
                                <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">
                                    وصف قصير للمحل
                                </label>

                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    maxlength="500"
                                    placeholder="مثال: صالون تجميل ونسائي متخصص بقص وصبغ الشعر..."
                                    class="block w-full rounded-xl border-gray-200 text-sm text-gray-800 shadow-sm placeholder:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>

                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div>
                                <label for="phone" class="mb-2 block text-sm font-semibold text-gray-700">
                                    رقم واتساب المحل
                                </label>

                                <input
                                    id="phone"
                                    type="tel"
                                    inputmode="tel"
                                    v-model="form.phone"
                                    dir="ltr"
                                    placeholder="09XXXXXXXX"
                                    class="block w-full rounded-xl border-gray-200 text-left text-sm text-gray-800 shadow-sm placeholder:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                <p class="mt-2 text-xs leading-5 text-gray-400">
                                    يظهر للزبائن كزر تواصل بعد إتمام الحجز، وتصلك عليه رسالة واتساب عند كل حجز جديد.
                                </p>

                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                        </div>

                    </div>


                    <!-- ============ Booking Page Colors ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-50 to-rose-100 text-pink-600 ring-1 ring-pink-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h10a4 4 0 004-4V6a2 2 0 00-2-2h-2M7 9h.01M7 13h.01" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">ألوان صفحة الحجز</h3>
                                <p class="mt-1 text-sm text-gray-500">خصص ألوان واجهة العرض الخاصة بزبائنك</p>
                            </div>
                        </div>


                        <div class="px-6 py-7 sm:px-8">

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                <div
                                    v-for="field in colorFields"
                                    :key="field.key"
                                    class="rounded-2xl border border-gray-100 bg-gray-50/60 p-4"
                                >
                                    <label class="mb-0.5 block text-sm font-semibold text-gray-700">
                                        {{ field.label }}
                                    </label>

                                    <p class="mb-3 text-xs text-gray-400">
                                        {{ field.hint }}
                                    </p>

                                    <div class="flex items-center gap-3">
                                        <label
                                            class="relative h-11 w-11 shrink-0 cursor-pointer overflow-hidden rounded-xl border border-gray-200 shadow-sm"
                                            :style="{ backgroundColor: form[field.key] }"
                                        >
                                            <input
                                                type="color"
                                                v-model="form[field.key]"
                                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                            />
                                        </label>

                                        <input
                                            type="text"
                                            v-model="form[field.key]"
                                            dir="ltr"
                                            class="block w-full rounded-xl border-gray-200 bg-white text-left font-mono text-xs text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <InputError class="mt-2" :message="form.errors[field.key]" />
                                </div>

                            </div>


                            <!-- Live preview -->
                            <div
                                class="mt-5 rounded-2xl border border-gray-100 p-4 transition-colors"
                                :style="{ backgroundColor: form.bg_color }"
                            >
                                <p class="mb-2 text-xs font-bold text-gray-400">
                                    معاينة حية فورية للتناسق
                                </p>

                                <div class="flex items-center justify-between gap-3 rounded-xl border bg-white p-3 shadow-sm">
                                    <span class="font-black" :style="{ color: form.text_color }">
                                        {{ form.shop_name || 'اسم المحل' }}
                                    </span>

                                    <span
                                        class="rounded-full px-3 py-1 text-[11px] font-bold"
                                        :style="{ backgroundColor: form.primary_color + '20', color: form.primary_color }"
                                    >
                                        زر/شارة تجريبية
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>


                    <!-- ============ Logo & Cover ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 ring-1 ring-amber-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8V6a2 2 0 012-2h12a2 2 0 012 2v2M4 8v10a2 2 0 002 2h12a2 2 0 002-2V8" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">الصور واللوغو</h3>
                                <p class="mt-1 text-sm text-gray-500">شعار المحل وصورة الغلاف</p>
                            </div>
                        </div>


                        <div class="space-y-6 px-6 py-7 sm:px-8">

                            <!-- Logo -->
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    شعار المحل (Logo)
                                </label>

                                <div class="flex items-center gap-4">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
                                        <img v-if="logoPreview" :src="logoPreview" class="h-full w-full object-cover" />
                                        <svg v-else class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8V6a2 2 0 012-2h12a2 2 0 012 2v2M4 8v10a2 2 0 002 2h12a2 2 0 002-2V8" />
                                        </svg>
                                    </div>

                                    <label class="cursor-pointer rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50">
                                        اختيار صورة
                                        <input type="file" accept="image/*" @change="onLogoChange" class="hidden" />
                                    </label>
                                </div>

                                <InputError class="mt-2" :message="form.errors.logo" />
                            </div>


                            <!-- Cover -->
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    صورة الغلاف (Cover)
                                </label>

                                <div class="mb-3 flex h-36 w-full items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
                                    <img v-if="coverPreview" :src="coverPreview" class="h-full w-full object-cover" />
                                    <svg v-else class="h-9 w-9 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8V6a2 2 0 012-2h12a2 2 0 012 2v2M4 8v10a2 2 0 002 2h12a2 2 0 002-2V8" />
                                    </svg>
                                </div>

                                <label class="inline-block cursor-pointer rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50">
                                    اختيار صورة
                                    <input type="file" accept="image/*" @change="onCoverChange" class="hidden" />
                                </label>

                                <InputError class="mt-2" :message="form.errors.cover" />
                            </div>

                        </div>

                    </div>


                    <!-- ============ Footer ============ -->
                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 hover:shadow-md active:scale-[0.98] disabled:opacity-60"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>

                            حفظ الإعدادات
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>