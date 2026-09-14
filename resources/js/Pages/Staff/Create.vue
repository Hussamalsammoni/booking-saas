<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
const props = defineProps({
    services: Array,
});

const days = [
    { key: 'saturday', label: 'السبت' },
    { key: 'sunday', label: 'الأحد' },
    { key: 'monday', label: 'الإثنين' },
    { key: 'tuesday', label: 'الثلاثاء' },
    { key: 'wednesday', label: 'الأربعاء' },
    { key: 'thursday', label: 'الخميس' },
    { key: 'friday', label: 'الجمعة' },
];

const defaultHours = {};
days.forEach((day) => {
    defaultHours[day.key] = {
        enabled: !['friday', 'saturday'].includes(day.key), // افتراضياً: عطلة الجمعة والسبت
        start: '09:00',
        end: '17:00',
    };
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    title: '',
    working_hours: defaultHours,
    service_ids: [],
});

const submit = () => {
    form.post(route('staff.store'));
};
</script>

<template>
    <Head title="إضافة موظف" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إضافة موظف جديد</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="اسم الموظف" />
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
                            <InputLabel for="email" value="البريد الإلكتروني" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="كلمة المرور" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="title" value="المسمى الوظيفي (اختياري)" />
                            <TextInput
                                id="title"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.title"
                                placeholder="مثال: حلاق، أخصائي تجميل"
                            />
                        </div>
                        <!-- الخدمات -->
<div class="border-t border-gray-200 pt-4 mt-4">
    <h3 class="font-medium text-gray-800 mb-3">الخدمات التي يقدمها</h3>
    <div class="space-y-2">
        <label v-for="service in services" :key="service.id" class="flex items-center gap-2">
            <input
                type="checkbox"
                :value="service.id"
                v-model="form.service_ids"
                class="rounded border-gray-300 text-indigo-600 shadow-sm"
            />
            <span class="text-sm text-gray-700">{{ service.name }}</span>
        </label>
    </div>
</div>


                        <!-- ساعات الدوام -->
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h3 class="font-medium text-gray-800 mb-3">ساعات الدوام</h3>

                            <div v-for="day in days" :key="day.key" class="flex items-center gap-4 py-2">
                                <label class="flex items-center gap-2 w-32">
                                    <input
                                        type="checkbox"
                                        v-model="form.working_hours[day.key].enabled"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                    />
                                    <span class="text-sm text-gray-700">{{ day.label }}</span>
                                </label>

                                <template v-if="form.working_hours[day.key].enabled">
                                    <input
                                        type="time"
                                        v-model="form.working_hours[day.key].start"
                                        class="border-gray-300 rounded-md shadow-sm text-sm"
                                    />
                                    <span class="text-gray-400">إلى</span>
                                    <input
                                        type="time"
                                        v-model="form.working_hours[day.key].end"
                                        class="border-gray-300 rounded-md shadow-sm text-sm"
                                    />
                                </template>
                                <span v-else class="text-sm text-gray-400">غير متاح</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <Link :href="route('staff.index')" class="text-gray-600 text-sm hover:underline">
                                إلغاء
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                حفظ الموظف
                            </PrimaryButton>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>