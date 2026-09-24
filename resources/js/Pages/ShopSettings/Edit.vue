<script setup>
import { ref, watch } from 'vue';
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
    features: props.shop.features?.length ? [...props.shop.features] : [],
    gallery_keep: (props.shop.gallery || []).map((g) => g.path),
    gallery_new: [],

    working_hours: props.shop.working_hours || [],
    map_address: props.shop.map_address || '',
    map_url: props.shop.map_url || '',
    social_links: {
        facebook: props.shop.social_links?.facebook || '',
        instagram: props.shop.social_links?.instagram || '',
        tiktok: props.shop.social_links?.tiktok || '',
        snapchat: props.shop.social_links?.snapchat || '',
    },
    testimonials: props.shop.testimonials?.length ? [...props.shop.testimonials] : [],
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

/* ---------------- المزايا ---------------- */
const emojiChoices = ['⭐', '✅', '🚀', '💎', '🏆', '✨', '🕐', '📍', '💳', '🎁'];

const addFeature = () => {
    if (form.features.length >= 8) return;
    form.features.push({ icon: '⭐', title: '' });
};
const removeFeature = (i) => form.features.splice(i, 1);

/* ---------------- معرض الصور ---------------- */

const existingGallery = ref([...(props.shop.gallery || [])]); // [{path, url}]

// كل ما توصل بيانات جديدة من السيرفر (بعد الحفظ)، حدّث القائمة المحلية تلقائياً
watch(
    () => props.shop.gallery,
    (newGallery) => {
        existingGallery.value = [...(newGallery || [])];
        form.gallery_keep = (newGallery || []).map((g) => g.path);
    }
);
const newGalleryPreviews = ref([]); // [{file, url}]

const isKept = (path) => form.gallery_keep.includes(path);
const toggleKeep = (path) => {
    if (isKept(path)) {
        form.gallery_keep = form.gallery_keep.filter((p) => p !== path);
    } else {
        form.gallery_keep.push(path);
    }
};

const onGalleryFilesChange = (e) => {
    const files = Array.from(e.target.files || []);
    const remainingSlots = 12 - form.gallery_keep.length - form.gallery_new.length;
    const toAdd = files.slice(0, Math.max(remainingSlots, 0));

    toAdd.forEach((file) => {
        form.gallery_new.push(file);
        newGalleryPreviews.value.push({ url: URL.createObjectURL(file) });
    });

    e.target.value = '';
};

const removeNewGalleryImage = (i) => {
    form.gallery_new.splice(i, 1);
    newGalleryPreviews.value.splice(i, 1);
};

/* ---------------- تقييمات الزبائن ---------------- */
const addTestimonial = () => {
    if (form.testimonials.length >= 6) return;
    form.testimonials.push({ name: '', text: '', rating: 5 });
};
const removeTestimonial = (i) => form.testimonials.splice(i, 1);

const submit = () => {
    form.post(route('shop-settings.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // بعد الحفظ، الصور الجديدة تصير "موجودة" فعلياً من ناحية الحالة المحلية
            form.gallery_new = [];
            newGalleryPreviews.value = [];
        },
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
                                    وصف المحل (يظهر بقسم "من نحن" بصفحة الحجز)
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


                    <!-- ============ Features ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-100 text-emerald-600 ring-1 ring-emerald-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">مزايا محلك</h3>
                                <p class="mt-1 text-sm text-gray-500">توضح لزبائنك ليش يختاروك (حتى 8 مزايا)</p>
                            </div>
                        </div>

                        <div class="space-y-3 px-6 py-7 sm:px-8">

                            <div
                                v-for="(feature, i) in form.features"
                                :key="i"
                                class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-3"
                            >
                                <select
                                    v-model="feature.icon"
                                    class="w-16 shrink-0 rounded-xl border-gray-200 bg-white text-center text-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option v-for="e in emojiChoices" :key="e" :value="e">{{ e }}</option>
                                </select>

                                <input
                                    type="text"
                                    v-model="feature.title"
                                    maxlength="60"
                                    placeholder="مثال: خبرة أكتر من 10 سنين"
                                    class="grow rounded-xl border-gray-200 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                <button
                                    type="button"
                                    @click="removeFeature(i)"
                                    class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <button
                                v-if="form.features.length < 8"
                                type="button"
                                @click="addFeature"
                                class="w-full rounded-2xl border-2 border-dashed border-gray-200 py-3 text-sm font-semibold text-gray-500 transition hover:border-indigo-300 hover:text-indigo-600"
                            >
                                + إضافة ميزة
                            </button>

                            <InputError class="mt-1" :message="form.errors.features" />
                        </div>

                    </div>


                    <!-- ============ ساعات العمل ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-50 to-amber-100 text-orange-600 ring-1 ring-orange-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">ساعات العمل</h3>
                                <p class="mt-1 text-sm text-gray-500">تظهر لزبائنك بصفحة الحجز مع حالة "مفتوح الآن"</p>
                            </div>
                        </div>

                        <div class="space-y-2 px-6 py-7 sm:px-8">
                            <div
                                v-for="(dayItem, i) in form.working_hours"
                                :key="i"
                                class="flex flex-wrap items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-3"
                            >
                                <label class="flex w-28 shrink-0 items-center gap-2">
                                    <input type="checkbox" v-model="dayItem.enabled" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm font-bold text-gray-700">{{ dayItem.day }}</span>
                                </label>

                                <template v-if="dayItem.enabled">
                                    <input type="time" v-model="dayItem.start" class="rounded-xl border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <span class="text-xs text-gray-400">إلى</span>
                                    <input type="time" v-model="dayItem.end" class="rounded-xl border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </template>
                                <span v-else class="text-xs font-semibold text-gray-400">مغلق</span>
                            </div>

                            <InputError class="mt-1" :message="form.errors.working_hours" />
                        </div>
                    </div>


                    <!-- ============ الموقع ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-50 to-rose-100 text-red-600 ring-1 ring-red-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">موقع المحل</h3>
                                <p class="mt-1 text-sm text-gray-500">يظهر لزبائنك خريطة وعنوان بصفحة الحجز</p>
                            </div>
                        </div>

                        <div class="space-y-4 px-6 py-7 sm:px-8">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">العنوان</label>
                                <input
                                    type="text"
                                    v-model="form.map_address"
                                    placeholder="مثال: دمشق - المزة - شارع الجلاء"
                                    class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <InputError class="mt-2" :message="form.errors.map_address" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">رابط خرائط جوجل</label>
                                <input
                                    type="url"
                                    v-model="form.map_url"
                                    dir="ltr"
                                    placeholder="https://maps.app.goo.gl/..."
                                    class="block w-full rounded-xl border-gray-200 text-left text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p class="mt-2 text-xs text-gray-400">افتح موقع محلك بگوگل مابس، دوس "مشاركة"، وانسخ الرابط هون.</p>
                                <InputError class="mt-2" :message="form.errors.map_url" />
                            </div>
                        </div>
                    </div>


                    <!-- ============ سوشال ميديا ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-50 to-fuchsia-100 text-purple-600 ring-1 ring-purple-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.684 13.342a4 4 0 100-2.684m0 2.684a4 4 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a4 4 0 105.367-1.684 4 4 0 00-5.367 1.684zm0 9.632a4 4 0 105.367 1.684 4 4 0 00-5.367-1.684z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">حسابات التواصل الاجتماعي</h3>
                                <p class="mt-1 text-sm text-gray-500">اترك أي حقل فاضي إذا ما عندك حساب فيه</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 px-6 py-7 sm:grid-cols-2 sm:px-8">
                            <div v-for="key in ['facebook', 'instagram', 'tiktok', 'snapchat']" :key="key">
                                <label class="mb-2 block text-sm font-semibold capitalize text-gray-700">{{ key }}</label>
                                <input
                                    type="url"
                                    v-model="form.social_links[key]"
                                    dir="ltr"
                                    placeholder="https://..."
                                    class="block w-full rounded-xl border-gray-200 text-left text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <InputError class="mt-2" :message="form.errors[`social_links.${key}`]" />
                            </div>
                        </div>
                    </div>


                    <!-- ============ تقييمات الزبائن ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-yellow-50 to-amber-100 text-yellow-600 ring-1 ring-yellow-100">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.113 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">تقييمات الزبائن</h3>
                                <p class="mt-1 text-sm text-gray-500">ضيف آراء زبائنك يدوياً لتظهر بصفحة الحجز (حتى 6)</p>
                            </div>
                        </div>

                        <div class="space-y-3 px-6 py-7 sm:px-8">
                            <div v-for="(t, i) in form.testimonials" :key="i" class="rounded-2xl border border-gray-100 bg-gray-50/60 p-4">
                                <div class="mb-2 flex items-center gap-3">
                                    <input
                                        type="text"
                                        v-model="t.name"
                                        placeholder="اسم الزبون"
                                        class="grow rounded-xl border-gray-200 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <select v-model.number="t.rating" class="rounded-xl border-gray-200 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option v-for="n in 5" :key="n" :value="n">{{ n }} ⭐</option>
                                    </select>
                                    <button
                                        type="button"
                                        @click="removeTestimonial(i)"
                                        class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <textarea
                                    v-model="t.text"
                                    rows="2"
                                    maxlength="300"
                                    placeholder="نص التقييم..."
                                    class="w-full rounded-xl border-gray-200 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <button
                                v-if="form.testimonials.length < 6"
                                type="button"
                                @click="addTestimonial"
                                class="w-full rounded-2xl border-2 border-dashed border-gray-200 py-3 text-sm font-semibold text-gray-500 transition hover:border-indigo-300 hover:text-indigo-600"
                            >
                                + إضافة تقييم
                            </button>

                            <InputError class="mt-1" :message="form.errors.testimonials" />
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


                    <!-- ============ Gallery ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-50 to-blue-100 text-sky-600 ring-1 ring-sky-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16l5-5 4 4 5-6 4 5M4 6h16v12H4z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">معرض الصور</h3>
                                <p class="mt-1 text-sm text-gray-500">صور من محلك وشغلك، تظهر بصفحة الحجز (حتى 12 صورة)</p>
                            </div>
                        </div>

                        <div class="px-6 py-7 sm:px-8">

                            <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">

                                <!-- الصور الموجودة -->
                                <div
                                    v-for="img in existingGallery"
                                    :key="img.path"
                                    class="group relative aspect-square overflow-hidden rounded-2xl border border-gray-100"
                                    :class="{ 'opacity-40 grayscale': !isKept(img.path) }"
                                >
                                    <img :src="img.url" class="h-full w-full object-cover" />
                                    <button
                                        type="button"
                                        @click="toggleKeep(img.path)"
                                        class="absolute inset-0 flex items-center justify-center bg-black/0 text-white opacity-0 transition group-hover:bg-black/40 group-hover:opacity-100"
                                    >
                                        <span class="rounded-lg bg-white/90 px-2.5 py-1 text-[11px] font-bold text-gray-800">
                                            {{ isKept(img.path) ? 'حذف' : 'تراجع' }}
                                        </span>
                                    </button>
                                </div>

                                <!-- الصور الجديدة -->
                                <div
                                    v-for="(preview, i) in newGalleryPreviews"
                                    :key="'new-' + i"
                                    class="group relative aspect-square overflow-hidden rounded-2xl border-2 border-dashed border-indigo-200"
                                >
                                    <img :src="preview.url" class="h-full w-full object-cover" />
                                    <button
                                        type="button"
                                        @click="removeNewGalleryImage(i)"
                                        class="absolute inset-0 flex items-center justify-center bg-black/0 text-white opacity-0 transition group-hover:bg-black/40 group-hover:opacity-100"
                                    >
                                        <span class="rounded-lg bg-white/90 px-2.5 py-1 text-[11px] font-bold text-gray-800">إزالة</span>
                                    </button>
                                </div>

                                <!-- زر الإضافة -->
                                <label
                                    v-if="form.gallery_keep.length + form.gallery_new.length < 12"
                                    class="flex aspect-square cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-gray-200 text-gray-400 transition hover:border-indigo-300 hover:text-indigo-500"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span class="text-[11px] font-semibold">إضافة صور</span>
                                    <input type="file" accept="image/*" multiple @change="onGalleryFilesChange" class="hidden" />
                                </label>

                            </div>

                            <InputError class="mt-3" :message="form.errors.gallery_new" />
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