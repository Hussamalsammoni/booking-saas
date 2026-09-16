<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    shop: Object,
});

const page = usePage();

const form = useForm({
    shop_name: props.shop.shop_name || '',
    description: props.shop.description || '',
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
</script>

<template>
    <Head title="إعدادات المحل" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إعدادات المحل</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <div v-if="page.props.flash?.success" class="mb-4 p-4 rounded-xl bg-green-50 text-green-700 text-sm font-bold border border-green-200">
                    {{ page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 sm:p-8 border border-gray-100">
                    <form @submit.prevent="submit" class="space-y-8">

                        <!-- المعلومات الأساسية -->
                        <div class="space-y-6">
                            <h3 class="text-base font-bold text-gray-900 border-b pb-2">المعلومات الأساسية</h3>

                            <div>
                                <InputLabel for="shop_name" value="اسم المحل" />
                                <TextInput
                                    id="shop_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.shop_name"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.shop_name" />
                            </div>

                            <div>
                                <InputLabel for="description" value="وصف قصير للمحل" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    maxlength="500"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="مثال: صالون تجميل ونسائي متخصص بقص وصبغ الشعر..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>
                        </div>

                        <!-- قسم التحكم بألوان صفحة الحجز -->
                        <div class="space-y-6 border-t border-gray-200 pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">ألوان صفحة الحجز</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">خصص ألوان واجهة العرض الخاصة بزبائنك بالكامل</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- اللون الرئيسي -->
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/60">
                                    <InputLabel for="primary_color" value="اللون الرئيسي (الأزرار والعناصر)" />
                                    <div class="flex items-center gap-3 mt-2">
                                        <input
                                            id="primary_color"
                                            type="color"
                                            v-model="form.primary_color"
                                            class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer shrink-0"
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full text-xs font-mono"
                                            v-model="form.primary_color"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.primary_color" />
                                </div>

                                <!-- اللون الثانوي -->
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/60">
                                    <InputLabel for="secondary_color" value="اللون الثانوي (خلفية الغلاف)" />
                                    <div class="flex items-center gap-3 mt-2">
                                        <input
                                            id="secondary_color"
                                            type="color"
                                            v-model="form.secondary_color"
                                            class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer shrink-0"
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full text-xs font-mono"
                                            v-model="form.secondary_color"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.secondary_color" />
                                </div>

                                <!-- لون خلفية الصفحة -->
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/60">
                                    <InputLabel for="bg_color" value="لون خلفية الصفحة" />
                                    <div class="flex items-center gap-3 mt-2">
                                        <input
                                            id="bg_color"
                                            type="color"
                                            v-model="form.bg_color"
                                            class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer shrink-0"
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full text-xs font-mono"
                                            v-model="form.bg_color"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.bg_color" />
                                </div>

                                <!-- لون النصوص -->
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/60">
                                    <InputLabel for="text_color" value="لون النصوص والعناوين" />
                                    <div class="flex items-center gap-3 mt-2">
                                        <input
                                            id="text_color"
                                            type="color"
                                            v-model="form.text_color"
                                            class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer shrink-0"
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full text-xs font-mono"
                                            v-model="form.text_color"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.text_color" />
                                </div>
                            </div>

                            <!-- معاينة حية سريعة للألوان -->
                            <div class="mt-4 p-4 rounded-2xl border text-xs space-y-3 transition-colors" :style="{ backgroundColor: form.bg_color }">
                                <div class="text-gray-400 font-bold mb-1">معاينة حية فورية للتناسق:</div>
                                <div class="p-3 rounded-xl bg-white shadow-sm flex items-center justify-between border">
                                    <span class="font-black" :style="{ color: form.text_color }">{{ form.shop_name || 'اسم المحل' }}</span>
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold" :style="{ backgroundColor: form.primary_color + '20', color: form.primary_color }">
                                        زر/شارة تجريبية
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- الشعار والغلاف -->
                        <div class="space-y-6 border-t border-gray-200 pt-6">
                            <h3 class="text-base font-bold text-gray-900">الصور واللوغو</h3>

                            <!-- الشعار -->
                            <div>
                                <InputLabel value="شعار المحل (Logo)" />
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0 border">
                                        <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                        <span v-else class="text-xs text-gray-400">بدون شعار</span>
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="onLogoChange"
                                        class="text-sm text-gray-600"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.logo" />
                            </div>

                            <!-- صورة الغلاف -->
                            <div>
                                <InputLabel value="صورة الغلاف (Cover)" />
                                <div class="mt-2">
                                    <div class="w-full h-36 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden mb-3 border">
                                        <img v-if="coverPreview" :src="coverPreview" class="w-full h-full object-cover" />
                                        <span v-else class="text-xs text-gray-400">بدون صورة غلاف</span>
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="onCoverChange"
                                        class="text-sm text-gray-600"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.cover" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                            <PrimaryButton :disabled="form.processing">
                                حفظ الإعدادات
                            </PrimaryButton>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>