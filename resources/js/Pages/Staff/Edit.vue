<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    staff: Object,
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
    const existing = props.staff.working_hours?.[day.key];
    defaultHours[day.key] = {
        enabled: existing ? existing.enabled : false,
        start: existing?.start || '09:00',
        end: existing?.end || '17:00',
    };
});

const form = useForm({
    name: props.staff.user.name,
    title: props.staff.title,
    is_active: !!props.staff.is_active,
    can_view_all_bookings: !!props.staff.can_view_all_bookings,
    working_hours: defaultHours,
    service_ids: props.staff.services.map((s) => s.id),
});

const submit = () => {
    form.put(route('staff.update', props.staff.id));
};
</script>

<template>
    <Head title="تعديل موظف" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل بيانات الموظف</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                            <InputLabel value="البريد الإلكتروني" />
                            <p class="mt-1 text-gray-500 text-sm">{{ staff.user.email }} (لا يمكن تعديله حالياً)</p>
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

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                type="checkbox"
                                v-model="form.is_active"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                            />
                            <label for="is_active" class="ms-2 text-sm text-gray-600">الموظف نشط</label>
                        </div>

                        <!-- خيار صلاحية مشاهدة كافة الحجوزات -->
                        <div class="flex items-center border-t border-gray-100 pt-3">
                            <input
                                id="can_view_all_bookings"
                                type="checkbox"
                                v-model="form.can_view_all_bookings"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                            />
                            <div class="ms-2">
                                <label for="can_view_all_bookings" class="text-sm font-medium text-gray-700 block">
                                    السماح ورؤية كافة الحجوزات
                                </label>
                                <span class="text-xs text-gray-500">
                                    تسمح للموظف بالاطلاع على الجدول العام لكل الموظفين عبر الـ API والتطبيق.
                                </span>
                            </div>
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
                                حفظ التعديلات
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>