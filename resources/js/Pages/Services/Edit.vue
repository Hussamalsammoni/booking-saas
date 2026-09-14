<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    service: Object,
});

const form = useForm({
    name: props.service.name,
    description: props.service.description,
    duration_minutes: props.service.duration_minutes,
    price: props.service.price,
    is_active: props.service.is_active,
});

const submit = () => {
    form.put(route('services.update', props.service.id));
};
</script>

<template>
    <Head title="تعديل خدمة" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل الخدمة</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="اسم الخدمة" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="description" value="الوصف (اختياري)" />
                            <textarea
                                id="description"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                v-model="form.description"
                                rows="3"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="duration_minutes" value="المدة (دقيقة)" />
                                <TextInput
                                    id="duration_minutes"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.duration_minutes"
                                    required
                                    min="1"
                                />
                                <InputError class="mt-2" :message="form.errors.duration_minutes" />
                            </div>

                            <div>
                                <InputLabel for="price" value="السعر ($)" />
                                <TextInput
                                    id="price"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    v-model="form.price"
                                    required
                                    min="0"
                                />
                                <InputError class="mt-2" :message="form.errors.price" />
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                type="checkbox"
                                v-model="form.is_active"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                            />
                            <label for="is_active" class="ms-2 text-sm text-gray-600">الخدمة نشطة</label>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <Link :href="route('services.index')" class="text-gray-600 text-sm hover:underline">
                                إلغاء
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                حفظ التعديلات
                            </PrimaryButton>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>