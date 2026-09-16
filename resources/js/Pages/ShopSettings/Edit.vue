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
    primary_color: props.shop.primary_color || '#4f46e5',
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
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

                <div v-if="page.props.flash?.success" class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm">
                    {{ page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">

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
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                placeholder="مثال: صالون تجميل ونسائي متخصص بقص وصبغ الشعر..."
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div>
                            <InputLabel for="primary_color" value="اللون الرئيسي للتصميم" />
                            <div class="flex items-center gap-3 mt-1">
                                <input
                                    id="primary_color"
                                    type="color"
                                    v-model="form.primary_color"
                                    class="w-12 h-10 rounded border border-gray-300 cursor-pointer"
                                />
                                <TextInput
                                    type="text"
                                    class="block w-32"
                                    v-model="form.primary_color"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.primary_color" />
                        </div>

                        <!-- الشعار -->
                        <div class="border-t border-gray-200 pt-5">
                            <InputLabel value="شعار المحل (Logo)" />
                            <div class="flex items-center gap-4 mt-2">
                                <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
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
                        <div class="border-t border-gray-200 pt-5">
                            <InputLabel value="صورة الغلاف (Cover)" />
                            <div class="mt-2">
                                <div class="w-full h-32 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden mb-2">
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

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
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